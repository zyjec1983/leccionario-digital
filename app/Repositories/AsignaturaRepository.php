<?php

require_once __DIR__ . '/../Core/Database.php';

class AsignaturaRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?AsignaturaModel
    {
        $sql = "SELECT a.*, 
                u.nombre as deleted_by_nombre
                FROM asignaturas a
                LEFT JOIN usuarios u ON a.deleted_by = u.id
                WHERE a.id = :id AND a.deleted_at IS NULL";
        
        $result = $this->db->fetch($sql, ['id' => $id]);
        
        return $result ? new AsignaturaModel((array) $result) : null;
    }

    public function findAllActive(): array
    {
        $sql = "SELECT a.*, u.nombre as deleted_by_nombre
                FROM asignaturas a
                LEFT JOIN usuarios u ON a.deleted_by = u.id
                WHERE a.deleted_at IS NULL
                ORDER BY a.area, a.nombre";
        
        $results = $this->db->fetchAll($sql);
        
        return array_map(fn($r) => new AsignaturaModel((array) $r), $results);
    }

    public function findAllDeleted(): array
    {
        $sql = "SELECT a.*, u.nombre as deleted_by_nombre
                FROM asignaturas a
                LEFT JOIN usuarios u ON a.deleted_by = u.id
                WHERE a.deleted_at IS NOT NULL
                ORDER BY a.deleted_at DESC";
        
        $results = $this->db->fetchAll($sql);
        
        return array_map(fn($r) => new AsignaturaModel((array) $r), $results);
    }

    public function search(string $query): array
    {
        $sql = "SELECT a.*, u.nombre as deleted_by_nombre
                FROM asignaturas a
                LEFT JOIN usuarios u ON a.deleted_by = u.id
                WHERE a.deleted_at IS NULL 
                AND (a.nombre LIKE :q OR a.codigo LIKE :q OR a.area LIKE :q)
                ORDER BY a.area, a.nombre";
        
        $results = $this->db->fetchAll($sql, ['q' => "%{$query}%"]);
        
        return array_map(fn($r) => new AsignaturaModel((array) $r), $results);
    }

    public function searchDeleted(string $query): array
    {
        $sql = "SELECT a.*, u.nombre as deleted_by_nombre
                FROM asignaturas a
                LEFT JOIN usuarios u ON a.deleted_by = u.id
                WHERE a.deleted_at IS NOT NULL 
                AND (a.nombre LIKE :q OR a.codigo LIKE :q OR a.area LIKE :q)
                ORDER BY a.deleted_at DESC";
        
        $results = $this->db->fetchAll($sql, ['q' => "%{$query}%"]);
        
        return array_map(fn($r) => new AsignaturaModel((array) $r), $results);
    }

    public function existsByCodigo(string $codigo, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM asignaturas WHERE codigo = :codigo";
        $params = ['codigo' => $codigo];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $sql .= " AND deleted_at IS NULL";
        
        return $this->db->fetch($sql, $params) !== null;
    }

    public function create(AsignaturaModel $asignatura): int
    {
        return $this->db->insert('asignaturas', [
            'codigo' => $asignatura->getCodigo(),
            'nombre' => $asignatura->getNombre(),
            'area' => $asignatura->getArea(),
            'horas_semanales' => $asignatura->getHorasSemanales(),
            'activo' => 1
        ]);
    }

    public function update(AsignaturaModel $asignatura): bool
    {
        return $this->db->update('asignaturas', [
            'codigo' => $asignatura->getCodigo(),
            'nombre' => $asignatura->getNombre(),
            'area' => $asignatura->getArea(),
            'horas_semanales' => $asignatura->getHorasSemanales()
        ], 'id = :id', ['id' => $asignatura->getId()]);
    }

    public function softDelete(int $id, string $reason, int $deletedBy): bool
    {
        return $this->db->update('asignaturas', [
            'deleted_at' => date('Y-m-d H:i:s'),
            'deleted_reason' => $reason,
            'deleted_by' => $deletedBy
        ], 'id = :id', ['id' => $id]);
    }

    public function restore(int $id): bool
    {
        return $this->db->update('asignaturas', [
            'deleted_at' => null,
            'deleted_reason' => null,
            'deleted_by' => null
        ], 'id = :id', ['id' => $id]);
    }

    public function countActive(): int
    {
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM asignaturas WHERE deleted_at IS NULL");
        return (int) ($result->total ?? 0);
    }

    public function countDeleted(): int
    {
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM asignaturas WHERE deleted_at IS NOT NULL");
        return (int) ($result->total ?? 0);
    }
}
