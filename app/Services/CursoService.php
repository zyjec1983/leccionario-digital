<?php
/** Location: leccionario-digital/app/Services/CursoService.php */

require_once __DIR__ . '/../Models/CursoModel.php';
require_once __DIR__ . '/../Repositories/CursoRepository.php';
require_once __DIR__ . '/../Core/Result.php';

class CursoService
{
    private CursoRepository $repository;

    public function __construct()
    {
        $this->repository = new CursoRepository();
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

    public function obtenerCurso(int $id): ?CursoModel
    {
        return $this->repository->findById($id);
    }

    public function crearCurso(array $data): Result
    {
        if (empty($data['nombre'])) {
            return Result::error('El nombre es requerido');
        }

        $curso = new CursoModel([
            'nombre' => trim($data['nombre']),
            'nivel' => trim($data['nivel'] ?? ''),
            'seccion' => trim($data['seccion'] ?? '')
        ]);

        $id = $this->repository->create($curso);
        $curso->setId($id);

        return Result::success('Curso creado exitosamente', $curso->toArray());
    }

    public function actualizarCurso(int $id, array $data): Result
    {
        $curso = $this->repository->findById($id);
        
        if (!$curso) {
            return Result::error('Curso no encontrado');
        }

        if (empty($data['nombre'])) {
            return Result::error('El nombre es requerido');
        }

        $curso->setNombre(trim($data['nombre']));
        $curso->setNivel(trim($data['nivel'] ?? ''));
        $curso->setSeccion(trim($data['seccion'] ?? ''));

        $this->repository->update($curso);

        return Result::success('Curso actualizado exitosamente');
    }

    public function softDeleteCurso(int $id, string $reason, int $deletedBy): Result
    {
        $curso = $this->repository->findById($id);
        
        if (!$curso) {
            return Result::error('Curso no encontrado');
        }

        if (empty($reason)) {
            return Result::error('El motivo de eliminación es requerido');
        }

        $this->repository->softDelete($id, $reason, $deletedBy);

        return Result::success('Curso eliminado exitosamente');
    }

    public function restaurarCurso(int $id): Result
    {
        $this->repository->restore($id);

        return Result::success('Curso restaurado exitosamente');
    }

    public function contarActivos(): int
    {
        return $this->repository->countActive();
    }
}
