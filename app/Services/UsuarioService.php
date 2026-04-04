<?php
/** Location: leccionario-digital/app/Services/UsuarioService.php */

require_once __DIR__ . '/../Core/Result.php';
require_once __DIR__ . '/../Repositories/UsuarioRepository.php';
require_once __DIR__ . '/../Models/UsuarioModel.php';

class UsuarioService
{
    private UsuarioRepository $repo;

    public function __construct()
    {
        $this->repo = new UsuarioRepository();
    }

    public function listarTodos(): array
    {
        return $this->repo->findAll();
    }

    public function obtenerUsuario(int $id): ?UsuarioModel
    {
        return $this->repo->findById($id);
    }

    public function obtenerUsuarioConRelaciones(int $id): ?array
    {
        $usuario = $this->repo->findById($id);
        
        if (!$usuario) {
            return null;
        }

        return [
            'usuario' => $usuario,
            'roles' => $this->repo->getRoles($id),
            'asignaturas' => $this->repo->getAsignaturas($id)
        ];
    }

    public function crearUsuario(array $data): Result
    {
        $errores = $this->validarDatos($data);
        
        if (!empty($errores)) {
            return Result::error('Error de validación', $errores);
        }

        if ($this->repo->emailExists($data['email'])) {
            return Result::error('El email ya está registrado en el sistema');
        }

        $esDocente = $this->tieneRolDocente($data['roles'] ?? []);
        
        if ($esDocente && empty($data['firma_data'])) {
            return Result::error('Los docentes deben tener una firma');
        }

        try {
            $passwordTemporal = '12345';
            $data['password'] = $this->repo->hashPassword($passwordTemporal);
            $data['primer_login'] = true;

            $userId = $this->repo->create($data);

            if (!empty($data['roles'])) {
                $this->repo->assignRoles($userId, $data['roles']);
            }

            if (!empty($data['asignaturas'])) {
                $this->repo->assignAsignaturas($userId, $data['asignaturas']);
            }

            if (!empty($data['firma_data'])) {
                $firmaData = $this->processFirmaData($data['firma_data']);
                if ($firmaData) {
                    $this->repo->saveFirma($userId, $firmaData);
                }
            }

            $roles = $this->repo->getRoles($userId);
            $rolNombre = !empty($roles) ? $roles[0]->nombre : 'Usuario';

            return Result::success('Usuario creado exitosamente', [
                'id' => $userId,
                'nombre' => $data['nombre'] . ' ' . $data['apellido'],
                'email' => $data['email'],
                'password_temporal' => $passwordTemporal,
                'rol' => $rolNombre
            ]);
        } catch (Exception $e) {
            error_log('Error al crear usuario: ' . $e->getMessage());
            return Result::error('Error al crear el usuario');
        }
    }

    public function actualizarUsuario(int $id, array $data): Result
    {
        $usuario = $this->repo->findById($id);
        
        if (!$usuario) {
            return Result::error('Usuario no encontrado');
        }

        $errores = $this->validarDatos($data, $id);
        
        if (!empty($errores)) {
            return Result::error('Error de validación', $errores);
        }

        if ($this->repo->emailExists($data['email'], $id)) {
            return Result::error('El email ya está registrado');
        }

        try {
            $updateData = [
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'email' => $data['email'],
                'telefono' => $data['telefono'] ?? null
            ];

            if (!empty($data['password'])) {
                $updateData['password'] = $data['password'];
            }

            if (isset($data['firma_data']) && !empty($data['firma_data'])) {
                $firmaData = $this->processFirmaData($data['firma_data']);
                if ($firmaData) {
                    $updateData['firma'] = $firmaData;
                }
            }

            $this->repo->update($id, $updateData);

            if (isset($data['roles'])) {
                $this->repo->assignRoles($id, $data['roles']);
            }

            if (isset($data['asignaturas'])) {
                $this->repo->assignAsignaturas($id, $data['asignaturas']);
            }

            return Result::success('Usuario actualizado exitosamente');
        } catch (Exception $e) {
            error_log('Error al actualizar usuario: ' . $e->getMessage());
            return Result::error('Error al actualizar el usuario');
        }
    }

    public function eliminarUsuario(int $id): Result
    {
        $usuario = $this->repo->findById($id);
        
        if (!$usuario) {
            return Result::error('Usuario no encontrado');
        }

        $currentUserId = Session::getUserId();
        if ($id === $currentUserId) {
            return Result::error('No puedes eliminarte a ti mismo');
        }

        try {
            $this->repo->delete($id);
            return Result::success('Usuario eliminado exitosamente');
        } catch (Exception $e) {
            error_log('Error al eliminar usuario: ' . $e->getMessage());
            return Result::error('Error al eliminar el usuario');
        }
    }

    public function resetearPassword(int $id): Result
    {
        $usuario = $this->repo->findById($id);
        
        if (!$usuario) {
            return Result::error('Usuario no encontrado');
        }

        $currentUserId = Session::getUserId();
        if ($id === $currentUserId) {
            return Result::error('No puedes resetear tu propia contraseña');
        }

        try {
            $this->repo->resetPassword($id);
            return Result::success('Contraseña reseteada. La nueva contraseña temporal es: 12345');
        } catch (Exception $e) {
            error_log('Error al resetear password: ' . $e->getMessage());
            return Result::error('Error al resetear la contraseña');
        }
    }

    public function obtenerRolesDisponibles(): array
    {
        return $this->repo->getAllRoles();
    }

    public function obtenerAsignaturasDisponibles(): array
    {
        return $this->repo->getAllAsignaturas();
    }

    public function obtenerDocentes(): array
    {
        return $this->repo->getByRole('docente');
    }

    public function listarEliminados(): array
    {
        return $this->repo->findAllDeleted();
    }

    public function buscar(string $query): array
    {
        return $this->repo->search($query);
    }

    public function buscarEliminados(string $query): array
    {
        return $this->repo->searchDeleted($query);
    }

    public function contarEliminados(): int
    {
        return $this->repo->countDeleted();
    }

    public function softDeleteUsuario(int $id, string $reason): Result
    {
        $usuario = $this->repo->findById($id);
        
        if (!$usuario) {
            return Result::error('Usuario no encontrado');
        }

        $currentUserId = Session::getUserId();
        if ($id === $currentUserId) {
            return Result::error('No puedes eliminarte a ti mismo');
        }

        try {
            $this->repo->softDelete($id, $reason, $currentUserId);
            return Result::success('Usuario eliminado exitosamente');
        } catch (Exception $e) {
            error_log('Error al eliminar usuario: ' . $e->getMessage());
            return Result::error('Error al eliminar el usuario');
        }
    }

    public function restaurarUsuario(int $id): Result
    {
        $usuario = $this->repo->findByIdWithDeleted($id);
        
        if (!$usuario) {
            return Result::error('Usuario no encontrado');
        }

        if (!$usuario->isDeleted()) {
            return Result::error('Este usuario no está eliminado');
        }

        try {
            $this->repo->restore($id);
            return Result::success('Usuario reactivado exitosamente. Podrá iniciar sesión nuevamente.');
        } catch (Exception $e) {
            error_log('Error al restaurar usuario: ' . $e->getMessage());
            return Result::error('Error al restaurar el usuario');
        }
    }

    public function getFirma(int $userId): ?string
    {
        return $this->repo->getFirma($userId);
    }

    public function tieneFirma(int $userId): bool
    {
        $firma = $this->repo->getFirma($userId);
        return $firma !== null && $firma !== '';
    }

    private function validarDatos(array $data, ?int $excludeId = null): array
    {
        $errores = [];

        if (empty($data['nombre'])) {
            $errores['nombre'] = 'El nombre es requerido';
        }

        if (empty($data['apellido'])) {
            $errores['apellido'] = 'El apellido es requerido';
        }

        if (empty($data['email'])) {
            $errores['email'] = 'El email es requerido';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'El formato de email es inválido';
        }

        if ($excludeId === null && empty($data['password'])) {
            $errores['password'] = 'La contraseña es requerida';
        }

        if (!empty($data['password']) && strlen($data['password']) < 5) {
            $errores['password'] = 'La contraseña debe tener al menos 5 caracteres';
        }

        return $errores;
    }

    private function tieneRolDocente(array $roles): bool
    {
        foreach ($roles as $rolId) {
            if ((string)$rolId === '1' || (int)$rolId === 1) {
                return true;
            }
        }
        return false;
    }

    private function processFirmaData(string $firmaBase64): ?string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $firmaBase64, $matches)) {
            return base64_decode(substr($firmaBase64, strpos($firmaBase64, ',') + 1));
        }
        return null;
    }

    public function contarDocentesActivos(): int
    {
        return $this->repo->countByRol('docente');
    }

    public function contarCoordinadoresActivos(): int
    {
        return $this->repo->countByRol('coordinador');
    }
}
