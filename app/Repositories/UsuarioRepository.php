<?php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/UsuarioModel.php';

class UsuarioRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findAllActive(): array
    {
        $rows = $this->db->fetchAll(
            "SELECT u.*, GROUP_CONCAT(r.nombre) as roles
             FROM usuarios u
             INNER JOIN usuario_roles ur ON u.id = ur.usuario_id
             INNER JOIN roles r ON ur.rol_id = r.id
             WHERE u.deleted_at IS NULL
             GROUP BY u.id
             ORDER BY u.nombre, u.apellido"
        );

        $usuarios = [];
        foreach ($rows as $row) {
            $usuarios[] = $this->mapToModel($row);
        }

        return $usuarios;
    }

    public function findAllDeleted(): array
    {
        $rows = $this->db->fetchAll(
            "SELECT u.*, GROUP_CONCAT(r.nombre) as roles,
                    u_del.nombre as deleted_by_nombre, u_del.apellido as deleted_by_apellido
             FROM usuarios u
             INNER JOIN usuario_roles ur ON u.id = ur.usuario_id
             INNER JOIN roles r ON ur.rol_id = r.id
             LEFT JOIN usuarios u_del ON u.deleted_by = u_del.id
             WHERE u.deleted_at IS NOT NULL
             GROUP BY u.id
             ORDER BY u.deleted_at DESC"
        );

        $usuarios = [];
        foreach ($rows as $row) {
            $usuarios[] = $this->mapToModel($row);
        }

        return $usuarios;
    }

    public function search(string $query): array
    {
        $query = '%' . $query . '%';
        $rows = $this->db->fetchAll(
            "SELECT u.*, GROUP_CONCAT(r.nombre) as roles
             FROM usuarios u
             INNER JOIN usuario_roles ur ON u.id = ur.usuario_id
             INNER JOIN roles r ON ur.rol_id = r.id
             WHERE u.deleted_at IS NULL
               AND (u.nombre LIKE :query OR u.apellido LIKE :query OR u.email LIKE :query)
             GROUP BY u.id
             ORDER BY u.nombre, u.apellido",
            ['query' => $query]
        );

        $usuarios = [];
        foreach ($rows as $row) {
            $usuarios[] = $this->mapToModel($row);
        }

        return $usuarios;
    }

    public function searchDeleted(string $query): array
    {
        $query = '%' . $query . '%';
        $rows = $this->db->fetchAll(
            "SELECT u.*, GROUP_CONCAT(r.nombre) as roles,
                    u_del.nombre as deleted_by_nombre, u_del.apellido as deleted_by_apellido
             FROM usuarios u
             INNER JOIN usuario_roles ur ON u.id = ur.usuario_id
             INNER JOIN roles r ON ur.rol_id = r.id
             LEFT JOIN usuarios u_del ON u.deleted_by = u_del.id
             WHERE u.deleted_at IS NOT NULL
               AND (u.nombre LIKE :query OR u.apellido LIKE :query OR u.email LIKE :query)
             GROUP BY u.id
             ORDER BY u.deleted_at DESC",
            ['query' => $query]
        );

        $usuarios = [];
        foreach ($rows as $row) {
            $usuarios[] = $this->mapToModel($row);
        }

        return $usuarios;
    }

    public function findAll(): array
    {
        return $this->findAllActive();
    }

    public function findById(int $id): ?UsuarioModel
    {
        $row = $this->db->fetch(
            "SELECT * FROM usuarios WHERE id = :id AND deleted_at IS NULL",
            ['id' => $id]
        );

        if (!$row) {
            return null;
        }

        return $this->mapToModel($row);
    }

    public function findByIdWithDeleted(int $id): ?UsuarioModel
    {
        $row = $this->db->fetch(
            "SELECT * FROM usuarios WHERE id = :id",
            ['id' => $id]
        );

        if (!$row) {
            return null;
        }

        return $this->mapToModel($row);
    }

    public function findByEmail(string $email): ?UsuarioModel
    {
        $row = $this->db->fetch(
            "SELECT * FROM usuarios WHERE email = :email AND deleted_at IS NULL",
            ['email' => $email]
        );

        if (!$row) {
            return null;
        }

        return $this->mapToModel($row);
    }

    public function create(array $data): int
    {
        $model = UsuarioModel::fromArray($data);
        
        $insertData = [
            'nombre' => $model->getNombre(),
            'apellido' => $model->getApellido(),
            'email' => $model->getEmail(),
            'telefono' => $model->getTelefono(),
            'password' => $model->getPassword(),
            'primer_login' => $model->isPrimerLogin() ? 1 : 0,
            'activo' => 1
        ];

        if ($model->getNivelCoordinacion() !== null) {
            $insertData['nivel_coordinacion'] = $model->getNivelCoordinacion();
        }

        return $this->db->insert('usuarios', $insertData);
    }

    public function update(int $id, array $data): bool
    {
        $updateData = [];

        if (isset($data['nombre'])) {
            $updateData['nombre'] = $data['nombre'];
        }
        if (isset($data['apellido'])) {
            $updateData['apellido'] = $data['apellido'];
        }
        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }
        if (isset($data['telefono'])) {
            $updateData['telefono'] = $data['telefono'];
        }
        if (isset($data['password']) && !empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (isset($data['firma']) && $data['firma'] !== null) {
            $updateData['firma'] = $data['firma'];
        }
        if (isset($data['nivel_coordinacion'])) {
            $updateData['nivel_coordinacion'] = $data['nivel_coordinacion'];
        }

        if (empty($updateData)) {
            return true;
        }

        $result = $this->db->update('usuarios', $updateData, 'id = :id', ['id' => $id]);
        return $result > 0;
    }

    public function softDelete(int $id, string $reason, int $deletedBy): bool
    {
        $result = $this->db->update(
            'usuarios',
            [
                'deleted_at' => date('Y-m-d H:i:s'),
                'deleted_reason' => $reason,
                'deleted_by' => $deletedBy,
                'activo' => 0
            ],
            'id = :id',
            ['id' => $id]
        );
        return $result > 0;
    }

    public function restore(int $id): bool
    {
        $result = $this->db->update(
            'usuarios',
            [
                'deleted_at' => null,
                'deleted_reason' => null,
                'deleted_by' => null,
                'activo' => 1
            ],
            'id = :id',
            ['id' => $id]
        );
        return $result > 0;
    }

    public function delete(int $id): bool
    {
        return $this->softDelete($id, 'Eliminado manualmente', 0);
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM usuarios WHERE email = :email AND deleted_at IS NULL";
        $params = ['email' => $email];

        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $user = $this->db->fetch($sql, $params);
        return $user !== null;
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function getPasswordTemporal(): string
    {
        return $this->hashPassword('12345');
    }

    public function resetPassword(int $id): bool
    {
        $hashTemporal = $this->getPasswordTemporal();
        
        $result = $this->db->update(
            'usuarios',
            [
                'password' => $hashTemporal,
                'primer_login' => 1
            ],
            'id = :id',
            ['id' => $id]
        );

        return $result > 0;
    }

    public function getRoles(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT r.id, r.nombre, r.slug 
             FROM roles r 
             INNER JOIN usuario_roles ur ON r.id = ur.rol_id 
             WHERE ur.usuario_id = :user_id",
            ['user_id' => $userId]
        );
    }

    public function getAllRoles(): array
    {
        return $this->db->fetchAll("SELECT * FROM roles ORDER BY nombre");
    }

    public function assignRoles(int $userId, array $roleIds): void
    {
        $this->db->delete('usuario_roles', 'usuario_id = :user_id', ['user_id' => $userId]);

        foreach ($roleIds as $roleId) {
            $this->db->insert('usuario_roles', [
                'usuario_id' => $userId,
                'rol_id' => (int) $roleId
            ]);
        }
    }

    public function getAsignaturas(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT a.* FROM asignaturas a
             INNER JOIN asignaturas_docentes ad ON a.id = ad.asignatura_id
             WHERE ad.usuario_id = :user_id AND a.deleted_at IS NULL",
            ['user_id' => $userId]
        );
    }

    public function getAllAsignaturas(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM asignaturas WHERE deleted_at IS NULL ORDER BY area, nombre"
        );
    }

    public function assignAsignaturas(int $userId, array $asignaturaIds): void
    {
        $this->db->delete('asignaturas_docentes', 'usuario_id = :user_id', ['user_id' => $userId]);

        foreach ($asignaturaIds as $asignaturaId) {
            $this->db->insert('asignaturas_docentes', [
                'usuario_id' => $userId,
                'asignatura_id' => (int) $asignaturaId
            ]);
        }
    }

    public function getFirma(int $userId): ?string
    {
        $user = $this->db->fetch(
            "SELECT firma FROM usuarios WHERE id = :id",
            ['id' => $userId]
        );

        return $user ? $user->firma : null;
    }

    public function saveFirma(int $userId, string $firma): bool
    {
        $result = $this->db->update(
            'usuarios',
            ['firma' => $firma],
            'id = :id',
            ['id' => $userId]
        );

        return $result > 0;
    }

    public function getByRole(string $roleSlug): array
    {
        $rows = $this->db->fetchAll(
            "SELECT u.* FROM usuarios u
             INNER JOIN usuario_roles ur ON u.id = ur.usuario_id
             INNER JOIN roles r ON ur.rol_id = r.id
             WHERE r.slug = :role_slug AND u.deleted_at IS NULL
             ORDER BY u.nombre, u.apellido",
            ['role_slug' => $roleSlug]
        );

        $usuarios = [];
        foreach ($rows as $row) {
            $usuarios[] = $this->mapToModel($row);
        }

        return $usuarios;
    }

    public function countByRole(string $roleSlug): int
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as total FROM usuarios u
             INNER JOIN usuario_roles ur ON u.id = ur.usuario_id
             INNER JOIN roles r ON ur.rol_id = r.id
             WHERE r.slug = :role_slug AND u.deleted_at IS NULL",
            ['role_slug' => $roleSlug]
        );

        return $result ? (int) $result->total : 0;
    }

    public function countActive(): int
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as total FROM usuarios WHERE deleted_at IS NULL"
        );
        return $result ? (int) $result->total : 0;
    }

    public function countDeleted(): int
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as total FROM usuarios WHERE deleted_at IS NOT NULL"
        );
        return $result ? (int) $result->total : 0;
    }

    private function mapToModel(object $row): UsuarioModel
    {
        $model = UsuarioModel::fromDatabase($row);
        
        if (isset($row->deleted_by_nombre)) {
            $model->setDeletedByNombre($row->deleted_by_nombre . ' ' . $row->deleted_by_apellido);
        }

        if (!$model->isDeleted()) {
            $roles = $this->getRoles($model->getId());
            $model->setRoles($roles);

            $asignaturas = $this->getAsignaturas($model->getId());
            $model->setAsignaturas($asignaturas);
        }

        return $model;
    }
}
