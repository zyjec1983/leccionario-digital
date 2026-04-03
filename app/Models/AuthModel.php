<?php

class AuthModel
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

    public function __construct()
    {
        $this->roles = [];
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

    public function hasRole(string $slug): bool
    {
        foreach ($this->roles as $role) {
            if ($role['slug'] === $slug) {
                return true;
            }
        }
        return false;
    }

    public function getPrimerRolSlug(): ?string
    {
        return $this->roles[0]['slug'] ?? null;
    }

    public function tieneMultiplesRoles(): bool
    {
        return count($this->roles) > 1;
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
            'roles' => $this->roles
        ];
    }

    public static function fromDatabase(object $row): self
    {
        $model = new self();
        
        $model->id = (int) $row->id;
        $model->nombre = $row->nombre;
        $model->apellido = $row->apellido;
        $model->email = $row->email;
        $model->telefono = $row->telefono ?? null;
        $model->password = $row->password;
        $model->firma = $row->firma ?? null;
        $model->primerLogin = isset($row->primer_login) ? (int)$row->primer_login === 1 : false;
        $model->activo = isset($row->activo) ? (int)$row->activo === 1 : true;
        $model->ultimoLogin = $row->ultimo_login ?? null;
        
        return $model;
    }
}
