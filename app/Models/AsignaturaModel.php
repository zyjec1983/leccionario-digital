<?php

class AsignaturaModel
{
    private ?int $id = null;
    private string $codigo = '';
    private string $nombre = '';
    private string $area = '';
    private int $horasSemanales = 0;
    private bool $activo = true;
    private ?string $deletedAt = null;
    private ?string $deletedReason = null;
    private ?int $deletedBy = null;
    private ?string $deletedByNombre = null;

    public function __construct(?array $data = null)
    {
        if ($data) {
            $this->id = (int) ($data['id'] ?? null);
            $this->codigo = $data['codigo'] ?? '';
            $this->nombre = $data['nombre'] ?? '';
            $this->area = $data['area'] ?? '';
            $this->horasSemanales = (int) ($data['horas_semanales'] ?? 0);
            $this->activo = (bool) ($data['activo'] ?? true);
            $this->deletedAt = $data['deleted_at'] ?? null;
            $this->deletedReason = $data['deleted_reason'] ?? null;
            $this->deletedBy = isset($data['deleted_by']) ? (int) $data['deleted_by'] : null;
            $this->deletedByNombre = $data['deleted_by_nombre'] ?? null;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): self
    {
        $this->codigo = $codigo;
        return $this;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getArea(): string
    {
        return $this->area;
    }

    public function setArea(string $area): self
    {
        $this->area = $area;
        return $this;
    }

    public function getHorasSemanales(): int
    {
        return $this->horasSemanales;
    }

    public function setHorasSemanales(int $horas): self
    {
        $this->horasSemanales = $horas;
        return $this;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): self
    {
        $this->activo = $activo;
        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function getDeletedAt(): ?string
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?string $deletedAt): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function getDeletedReason(): ?string
    {
        return $this->deletedReason;
    }

    public function setDeletedReason(?string $reason): self
    {
        $this->deletedReason = $reason;
        return $this;
    }

    public function getDeletedBy(): ?int
    {
        return $this->deletedBy;
    }

    public function setDeletedBy(?int $deletedBy): self
    {
        $this->deletedBy = $deletedBy;
        return $this;
    }

    public function getDeletedByNombre(): ?string
    {
        return $this->deletedByNombre;
    }

    public function setDeletedByNombre(?string $nombre): self
    {
        $this->deletedByNombre = $nombre;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'area' => $this->area,
            'horas_semanales' => $this->horasSemanales,
            'activo' => $this->activo,
            'deleted_at' => $this->deletedAt,
            'deleted_reason' => $this->deletedReason,
            'deleted_by' => $this->deletedBy,
            'deleted_by_nombre' => $this->deletedByNombre
        ];
    }
}
