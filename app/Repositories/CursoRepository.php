<?php

require_once __DIR__ . '/../Core/Database.php';

class CursoRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?CursoModel
    {
        $sql = "SELECT c.*, u.nombre as deleted_by_nombre
                FROM cursos c
                LEFT JOIN usuarios u ON c.deleted_by = u.id
                WHERE c.id = :id AND c.deleted_at IS NULL";
        
        $result = $this->db->fetch($sql, ['id' => $id]);
        
        return $result ? new CursoModel((array) $result) : null;
    }

    public function findAllActive(): array
    {
        $sql = "SELECT c.*, u.nombre as deleted_by_nombre
                FROM cursos c
                LEFT JOIN usuarios u ON c.deleted_by = u.id
                WHERE c.deleted_at IS NULL
                ORDER BY c.nivel, c.seccion";
        
        $results = $this->db->fetchAll($sql);
        
        return array_map(fn($r) => new CursoModel((array) $r), $results);
    }

    public function findAllDeleted(): array
    {
        $sql = "SELECT c.*, u.nombre as deleted_by_nombre
                FROM cursos c
                LEFT JOIN usuarios u ON c.deleted_by = u.id
                WHERE c.deleted_at IS NOT NULL
                ORDER BY c.deleted_at DESC";
        
        $results = $this->db->fetchAll($sql);
        
        return array_map(fn($r) => new CursoModel((array) $r), $results);
    }

    public function search(string $query): array
    {
        $sql = "SELECT c.*, u.nombre as deleted_by_nombre
                FROM cursos c
                LEFT JOIN usuarios u ON c.deleted_by = u.id
                WHERE c.deleted_at IS NULL 
                AND (c.nombre LIKE :q OR c.nivel LIKE :q OR c.seccion LIKE :q)
                ORDER BY c.nivel, c.seccion";
        
        $results = $this->db->fetchAll($sql, ['q' => "%{$query}%"]);
        
        return array_map(fn($r) => new CursoModel((array) $r), $results);
    }

    public function searchDeleted(string $query): array
    {
        $sql = "SELECT c.*, u.nombre as deleted_by_nombre
                FROM cursos c
                LEFT JOIN usuarios u ON c.deleted_by = u.id
                WHERE c.deleted_at IS NOT NULL 
                AND (c.nombre LIKE :q OR c.nivel LIKE :q OR c.seccion LIKE :q)
                ORDER BY c.deleted_at DESC";
        
        $results = $this->db->fetchAll($sql, ['q' => "%{$query}%"]);
        
        return array_map(fn($r) => new CursoModel((array) $r), $results);
    }

    public function create(CursoModel $curso): int
    {
        return $this->db->insert('cursos', [
            'nombre' => $curso->getNombre(),
            'nivel' => $curso->getNivel(),
            'seccion' => $curso->getSeccion(),
            'activo' => 1
        ]);
    }

    public function update(CursoModel $curso): bool
    {
        return $this->db->update('cursos', [
            'nombre' => $curso->getNombre(),
            'nivel' => $curso->getNivel(),
            'seccion' => $curso->getSeccion()
        ], 'id = :id', ['id' => $curso->getId()]);
    }

    public function softDelete(int $id, string $reason, int $deletedBy): bool
    {
        return $this->db->update('cursos', [
            'deleted_at' => date('Y-m-d H:i:s'),
            'deleted_reason' => $reason,
            'deleted_by' => $deletedBy
        ], 'id = :id', ['id' => $id]);
    }

    public function restore(int $id): bool
    {
        return $this->db->update('cursos', [
            'deleted_at' => null,
            'deleted_reason' => null,
            'deleted_by' => null
        ], 'id = :id', ['id' => $id]);
    }

    public function countActive(): int
    {
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM cursos WHERE deleted_at IS NULL");
        return (int) ($result->total ?? 0);
    }

    public function countDeleted(): int
    {
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM cursos WHERE deleted_at IS NOT NULL");
        return (int) ($result->total ?? 0);
    }
}
