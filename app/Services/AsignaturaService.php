<?php

require_once __DIR__ . '/../Models/AsignaturaModel.php';
require_once __DIR__ . '/../Repositories/AsignaturaRepository.php';
require_once __DIR__ . '/../Core/Result.php';

class AsignaturaService
{
    private AsignaturaRepository $repository;

    public function __construct()
    {
        $this->repository = new AsignaturaRepository();
    }

    public function listarTodos(): array
    {
        return $this->repository->findAllActive();
    }

    public function listarEliminados(): array
    {
        return $this->repository->findAllDeleted();
    }

    public function buscar(string $query): array
    {
        return $this->repository->search($query);
    }

    public function buscarEliminados(string $query): array
    {
        return $this->repository->searchDeleted($query);
    }

    public function contarEliminados(): int
    {
        return $this->repository->countDeleted();
    }

    public function obtenerAsignatura(int $id): ?AsignaturaModel
    {
        return $this->repository->findById($id);
    }

    public function crearAsignatura(array $data): Result
    {
        if (empty($data['codigo']) || empty($data['nombre'])) {
            return Result::error('Código y nombre son requeridos');
        }

        if ($this->repository->existsByCodigo($data['codigo'])) {
            return Result::error('Ya existe una asignatura con ese código');
        }

        $asignatura = new AsignaturaModel([
            'codigo' => trim($data['codigo']),
            'nombre' => trim($data['nombre']),
            'area' => trim($data['area'] ?? ''),
            'horas_semanales' => (int) ($data['horas_semanales'] ?? 0)
        ]);

        $id = $this->repository->create($asignatura);
        $asignatura->setId($id);

        return Result::success('Asignatura creada exitosamente', $asignatura->toArray());
    }

    public function actualizarAsignatura(int $id, array $data): Result
    {
        $asignatura = $this->repository->findById($id);
        
        if (!$asignatura) {
            return Result::error('Asignatura no encontrada');
        }

        if (empty($data['codigo']) || empty($data['nombre'])) {
            return Result::error('Código y nombre son requeridos');
        }

        if ($this->repository->existsByCodigo($data['codigo'], $id)) {
            return Result::error('Ya existe otra asignatura con ese código');
        }

        $asignatura->setCodigo(trim($data['codigo']));
        $asignatura->setNombre(trim($data['nombre']));
        $asignatura->setArea(trim($data['area'] ?? ''));
        $asignatura->setHorasSemanales((int) ($data['horas_semanales'] ?? 0));

        $this->repository->update($asignatura);

        return Result::success('Asignatura actualizada exitosamente');
    }

    public function softDeleteAsignatura(int $id, string $reason, int $deletedBy): Result
    {
        $asignatura = $this->repository->findById($id);
        
        if (!$asignatura) {
            return Result::error('Asignatura no encontrada');
        }

        if (empty($reason)) {
            return Result::error('El motivo de eliminación es requerido');
        }

        $this->repository->softDelete($id, $reason, $deletedBy);

        return Result::success('Asignatura eliminada exitosamente');
    }

    public function restaurarAsignatura(int $id): Result
    {
        $this->repository->restore($id);

        return Result::success('Asignatura restaurada exitosamente');
    }
}
