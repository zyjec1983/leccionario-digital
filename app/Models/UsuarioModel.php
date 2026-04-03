<?php

class UsuarioModel
{
    private ?int $id;
    private string $nombre;
    private string $apellido;
    private string $email;
    private ?string $telefono;
    private string $password;
    private ?string $firma;
    private bool $primerLogin;
    private bool $activo;
    private ?string $ultimoLogin;
    private array $roles;
    private array $asignaturas;
    private ?int $nivelCoordinacion;
    private ?string $deletedAt;
    private ?string $deletedReason;
    private ?int $deletedBy;
    private ?string $deletedByNombre;

    public function __construct()
    {
        $this->roles = [];
        $this->asignaturas = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    public function getApellido(): string
    {
        return $this->apellido;
    }

    public function setApellido(string $apellido): void
    {
        $this->apellido = $apellido;
    }

    public function getNombreCompleto(): string
    {
        return $this->nombre . ' ' . $this->apellido;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): void
    {
        $this->telefono = $telefono;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getFirma(): ?string
    {
        return $this->firma;
    }

    public function setFirma(?string $firma): void
    {
        $this->firma = $firma;
    }

    public function hasFirma(): bool
    {
        return $this->firma !== null && $this->firma !== '';
    }

    public function isPrimerLogin(): bool
    {
        return $this->primerLogin;
    }

    public function setPrimerLogin(bool $primerLogin): void
    {
        $this->primerLogin = $primerLogin;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }

    public function getUltimoLogin(): ?string
    {
        return $this->ultimoLogin;
    }

    public function setUltimoLogin(?string $ultimoLogin): void
    {
        $this->ultimoLogin = $ultimoLogin;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function addRole(object $role): void
    {
        $this->roles[] = $role;
    }

    public function hasRole(string $slug): bool
    {
        foreach ($this->roles as $role) {
            $roleArray = is_array($role) ? $role : (array)$role;
            if ($roleArray['slug'] === $slug) {
                return true;
            }
        }
        return false;
    }

    public function isDocente(): bool
    {
        return $this->hasRole('docente');
    }

    public function isCoordinador(): bool
    {
        return $this->hasRole('coordinador');
    }

    public function getPrimerRolSlug(): ?string
    {
        if (empty($this->roles)) {
            return null;
        }
        $firstRole = $this->roles[0];
        $roleArray = is_array($firstRole) ? $firstRole : (array)$firstRole;
        return $roleArray['slug'] ?? null;
    }

    public function tieneMultiplesRoles(): bool
    {
        return count($this->roles) > 1;
    }

    public function getAsignaturas(): array
    {
        return $this->asignaturas;
    }

    public function setAsignaturas(array $asignaturas): void
    {
        $this->asignaturas = $asignaturas;
    }

    public function getNivelCoordinacion(): ?int
    {
        return $this->nivelCoordinacion;
    }

    public function setNivelCoordinacion(?int $nivel): void
    {
        $this->nivelCoordinacion = $nivel;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function getDeletedAt(): ?string
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?string $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    public function getDeletedReason(): ?string
    {
        return $this->deletedReason;
    }

    public function setDeletedReason(?string $deletedReason): void
    {
        $this->deletedReason = $deletedReason;
    }

    public function getDeletedBy(): ?int
    {
        return $this->deletedBy;
    }

    public function setDeletedBy(?int $deletedBy): void
    {
        $this->deletedBy = $deletedBy;
    }

    public function getDeletedByNombre(): ?string
    {
        return $this->deletedByNombre;
    }

    public function setDeletedByNombre(?string $deletedByNombre): void
    {
        $this->deletedByNombre = $deletedByNombre;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'nombre_completo' => $this->getNombreCompleto(),
            'email' => $this->email,
            'telefono' => $this->telefono,
            'primer_login' => $this->primerLogin,
            'activo' => $this->activo,
            'ultimo_login' => $this->ultimoLogin,
            'roles' => $this->roles,
            'asignaturas' => $this->asignaturas,
            'nivel_coordinacion' => $this->nivelCoordinacion,
            'tiene_firma' => $this->hasFirma(),
            'deleted_at' => $this->deletedAt,
            'deleted_reason' => $this->deletedReason,
            'deleted_by' => $this->deletedBy,
            'deleted_by_nombre' => $this->deletedByNombre,
            'is_deleted' => $this->isDeleted()
        ];
    }

    public function toDbArray(bool $includePassword = true): array
    {
        $data = [
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'primer_login' => $this->primerLogin ? 1 : 0,
            'activo' => $this->activo ? 1 : 0
        ];

        if ($includePassword && $this->password) {
            $data['password'] = $this->password;
        }

        if ($this->firma !== null) {
            $data['firma'] = $this->firma;
        }

        if ($this->nivelCoordinacion !== null) {
            $data['nivel_coordinacion'] = $this->nivelCoordinacion;
        }

        return $data;
    }

    public static function fromDatabase(object $row): self
    {
        $model = new self();
        
        $model->id = (int) $row->id;
        $model->nombre = $row->nombre;
        $model->apellido = $row->apellido;
        $model->email = $row->email;
        $model->telefono = $row->telefono ?? null;
        $model->password = $row->password ?? '';
        $model->firma = $row->firma ?? null;
        $model->primerLogin = isset($row->primer_login) ? (int)$row->primer_login === 1 : false;
        $model->activo = isset($row->activo) ? (int)$row->activo === 1 : true;
        $model->ultimoLogin = $row->ultimo_login ?? null;
        $model->nivelCoordinacion = isset($row->nivel_coordinacion) ? (int)$row->nivel_coordinacion : null;
        $model->deletedAt = $row->deleted_at ?? null;
        $model->deletedReason = $row->deleted_reason ?? null;
        $model->deletedBy = isset($row->deleted_by) ? (int)$row->deleted_by : null;
        $model->deletedByNombre = $row->deleted_by_nombre ?? null;
        
        return $model;
    }

    public static function fromArray(array $data): self
    {
        $model = new self();
        
        if (isset($data['id'])) {
            $model->id = (int) $data['id'];
        }
        $model->nombre = $data['nombre'] ?? '';
        $model->apellido = $data['apellido'] ?? '';
        $model->email = $data['email'] ?? '';
        $model->telefono = $data['telefono'] ?? null;
        $model->password = $data['password'] ?? '';
        $model->firma = $data['firma'] ?? null;
        $model->primerLogin = isset($data['primer_login']) ? (bool)$data['primer_login'] : true;
        $model->activo = isset($data['activo']) ? (bool)$data['activo'] : true;
        $model->nivelCoordinacion = isset($data['nivel_coordinacion']) ? (int)$data['nivel_coordinacion'] : null;
        
        return $model;
    }
}
