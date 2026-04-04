<?php
/** Location: leccionario-digital/app/Repositories/LeccionarioRepository.php */

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/LeccionarioModel.php';

class LeccionarioRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?LeccionarioModel
    {
        $row = $this->db->fetch(
            "SELECT l.*, h.hora_inicio, h.hora_fin, h.aula,
                    c.nombre as curso, c.seccion, a.nombre as asignatura, a.codigo,
                    u.nombre, u.apellido, u.email
             FROM leccionarios l
             INNER JOIN horarios h ON l.horario_id = h.id
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             INNER JOIN usuarios u ON l.usuario_id = u.id
             WHERE l.id = :id",
            ['id' => $id]
        );

        if (!$row) {
            return null;
        }

        return LeccionarioModel::fromDatabase($row);
    }

    public function findByUsuarioAndFecha(int $usuarioId, string $fecha): array
    {
        $rows = $this->db->fetchAll(
            "SELECT l.*, h.hora_inicio, h.hora_fin, c.nombre as curso, c.seccion, a.nombre as asignatura, a.codigo
             FROM leccionarios l
             INNER JOIN horarios h ON l.horario_id = h.id
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             WHERE l.usuario_id = :user_id AND l.fecha = :fecha
             ORDER BY h.hora_inicio",
            ['user_id' => $usuarioId, 'fecha' => $fecha]
        );

        return array_map(fn($row) => LeccionarioModel::fromDatabase($row), $rows);
    }

    public function findByUsuario(int $usuarioId, array $filtros = [], int $limit = 20, int $offset = 0): array
    {
        $where = "l.usuario_id = :user_id";
        $params = ['user_id' => $usuarioId];

        if (!empty($filtros['fecha_inicio'])) {
            $where .= " AND l.fecha >= :fecha_inicio";
            $params['fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $where .= " AND l.fecha <= :fecha_fin";
            $params['fecha_fin'] = $filtros['fecha_fin'];
        }

        if (!empty($filtros['estado'])) {
            $where .= " AND l.estado = :estado";
            $params['estado'] = $filtros['estado'];
        }

        $rows = $this->db->fetchAll(
            "SELECT l.*, h.hora_inicio, c.nombre as curso, c.seccion, a.nombre as asignatura
             FROM leccionarios l
             INNER JOIN horarios h ON l.horario_id = h.id
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             WHERE {$where}
             ORDER BY l.fecha DESC, h.hora_inicio DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $limit, 'offset' => $offset])
        );

        return array_map(fn($row) => LeccionarioModel::fromDatabase($row), $rows);
    }

    public function countByUsuario(int $usuarioId, array $filtros = []): int
    {
        $where = "l.usuario_id = :user_id";
        $params = ['user_id' => $usuarioId];

        if (!empty($filtros['fecha_inicio'])) {
            $where .= " AND l.fecha >= :fecha_inicio";
            $params['fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $where .= " AND l.fecha <= :fecha_fin";
            $params['fecha_fin'] = $filtros['fecha_fin'];
        }

        if (!empty($filtros['estado'])) {
            $where .= " AND l.estado = :estado";
            $params['estado'] = $filtros['estado'];
        }

        $result = $this->db->fetch(
            "SELECT COUNT(*) as total FROM leccionarios l WHERE {$where}",
            $params
        );

        return $result ? (int) $result->total : 0;
    }

    public function findPendientes(int $usuarioId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT l.*, h.hora_inicio, c.nombre as curso, c.seccion, a.nombre as asignatura
             FROM leccionarios l
             INNER JOIN horarios h ON l.horario_id = h.id
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             WHERE l.usuario_id = :user_id AND l.estado IN ('pendiente', 'atrasado') AND l.fecha <= CURDATE()
             ORDER BY l.fecha DESC",
            ['user_id' => $usuarioId]
        );

        return array_map(fn($row) => LeccionarioModel::fromDatabase($row), $rows);
    }

    public function create(array $data): int
    {
        return $this->db->insert('leccionarios', [
            'usuario_id' => $data['usuario_id'],
            'horario_id' => $data['horario_id'],
            'fecha' => $data['fecha'],
            'contenido' => $data['contenido'] ?? '',
            'observaciones' => $data['observaciones'] ?? null,
            'firmado' => isset($data['firmado']) ? 1 : 0,
            'fecha_registro' => $data['fecha_registro'] ?? date('Y-m-d H:i:s'),
            'estado' => $data['estado'] ?? 'pendiente'
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $updateData = [];

        if (array_key_exists('contenido', $data)) {
            $updateData['contenido'] = $data['contenido'];
        }
        if (array_key_exists('observaciones', $data)) {
            $updateData['observaciones'] = $data['observaciones'];
        }
        if (array_key_exists('firmado', $data)) {
            $updateData['firmado'] = $data['firmado'] ? 1 : 0;
        }
        if (array_key_exists('estado', $data)) {
            $updateData['estado'] = $data['estado'];
        }

        if (empty($updateData)) {
            return true;
        }

        $result = $this->db->update('leccionarios', $updateData, 'id = :id', ['id' => $id]);
        return $result > 0;
    }

    public function existe(int $usuarioId, int $horarioId, string $fecha): bool
    {
        $row = $this->db->fetch(
            "SELECT id FROM leccionarios WHERE usuario_id = :user_id AND horario_id = :horario_id AND fecha = :fecha",
            ['user_id' => $usuarioId, 'horario_id' => $horarioId, 'fecha' => $fecha]
        );

        return $row !== null;
    }

    public function findByHorarioAndFecha(int $horarioId, string $fecha): ?LeccionarioModel
    {
        $row = $this->db->fetch(
            "SELECT l.*, h.hora_inicio, h.hora_fin, h.aula,
                    c.nombre as curso, c.seccion, a.nombre as asignatura, a.codigo
             FROM leccionarios l
             INNER JOIN horarios h ON l.horario_id = h.id
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             WHERE l.horario_id = :horario_id AND l.fecha = :fecha",
            ['horario_id' => $horarioId, 'fecha' => $fecha]
        );

        if (!$row) {
            return null;
        }

        return LeccionarioModel::fromDatabase($row);
    }

    public function countByFecha(int $usuarioId, string $fecha): int
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as total FROM leccionarios WHERE usuario_id = :user_id AND fecha = :fecha",
            ['user_id' => $usuarioId, 'fecha' => $fecha]
        );

        return $result ? (int) $result->total : 0;
    }

    public function countPendientes(int $usuarioId): int
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as total FROM leccionarios WHERE usuario_id = :user_id AND estado IN ('pendiente', 'atrasado') AND fecha <= CURDATE()",
            ['user_id' => $usuarioId]
        );

        return $result ? (int) $result->total : 0;
    }

    public function countPorFecha(int $usuarioId, string $fecha): int
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as total FROM leccionarios WHERE usuario_id = :user_id AND fecha = :fecha",
            ['user_id' => $usuarioId, 'fecha' => $fecha]
        );

        return $result ? (int) $result->total : 0;
    }

    public function findAllWithFilters(array $filters): array
    {
        $where = "1=1";
        $params = [];

        if (!empty($filters['fecha_inicio'])) {
            $where .= " AND l.fecha >= :fecha_inicio";
            $params['fecha_inicio'] = $filters['fecha_inicio'];
        }

        if (!empty($filters['fecha_fin'])) {
            $where .= " AND l.fecha <= :fecha_fin";
            $params['fecha_fin'] = $filters['fecha_fin'];
        }

        if (!empty($filters['profesor'])) {
            $where .= " AND l.usuario_id = :profesor";
            $params['profesor'] = $filters['profesor'];
        }

        if (!empty($filters['curso'])) {
            $where .= " AND h.curso_id = :curso";
            $params['curso'] = $filters['curso'];
        }

        if (!empty($filters['estado'])) {
            $where .= " AND l.estado = :estado";
            $params['estado'] = $filters['estado'];
        }

        $rows = $this->db->fetchAll(
            "SELECT l.*, u.nombre, u.apellido, c.nombre as curso, c.seccion, a.nombre as asignatura
             FROM leccionarios l
             INNER JOIN usuarios u ON l.usuario_id = u.id
             INNER JOIN horarios h ON l.horario_id = h.id
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             WHERE {$where}
             ORDER BY l.fecha DESC, u.nombre",
            $params
        );

        return array_map(fn($row) => LeccionarioModel::fromDatabase($row), $rows);
    }

    public function findByIdWithDetails(int $id): ?LeccionarioModel
    {
        $row = $this->db->fetch(
            "SELECT l.*, u.nombre, u.apellido, u.email, c.nombre as curso, c.seccion,
                    a.nombre as asignatura, h.hora_inicio, h.hora_fin, h.aula
             FROM leccionarios l
             INNER JOIN usuarios u ON l.usuario_id = u.id
             INNER JOIN horarios h ON l.horario_id = h.id
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             WHERE l.id = :id",
            ['id' => $id]
        );

        if (!$row) {
            return null;
        }

        return LeccionarioModel::fromDatabase($row);
    }

    public function countAllWithFilters(array $filters): int
    {
        $where = "1=1";
        $params = [];

        if (!empty($filters['fecha_inicio'])) {
            $where .= " AND l.fecha >= :fecha_inicio";
            $params['fecha_inicio'] = $filters['fecha_inicio'];
        }

        if (!empty($filters['fecha_fin'])) {
            $where .= " AND l.fecha <= :fecha_fin";
            $params['fecha_fin'] = $filters['fecha_fin'];
        }

        if (!empty($filters['profesor'])) {
            $where .= " AND l.usuario_id = :profesor";
            $params['profesor'] = $filters['profesor'];
        }

        if (!empty($filters['curso'])) {
            $where .= " AND h.curso_id = :curso";
            $params['curso'] = $filters['curso'];
        }

        if (!empty($filters['estado'])) {
            $where .= " AND l.estado = :estado";
            $params['estado'] = $filters['estado'];
        }

        $result = $this->db->fetch(
            "SELECT COUNT(*) as total FROM leccionarios l
             INNER JOIN horarios h ON l.horario_id = h.id
             WHERE {$where}",
            $params
        );

        return $result ? (int) $result->total : 0;
    }

    public function countHoy(): int
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as total FROM leccionarios WHERE fecha = CURDATE()"
        );
        return $result ? (int) $result->total : 0;
    }

    public function countPendientesTotal(): int
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as total FROM leccionarios WHERE estado = 'pendiente'"
        );
        return $result ? (int) $result->total : 0;
    }

    public function countAtrasadosTotal(): int
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as total FROM leccionarios WHERE estado = 'pendiente' AND fecha < CURDATE()"
        );
        return $result ? (int) $result->total : 0;
    }

    public function findRecientes(int $limit = 10): array
    {
        $rows = $this->db->fetchAll(
            "SELECT l.*, u.nombre, u.apellido, c.nombre as curso, c.seccion, a.nombre as asignatura
             FROM leccionarios l
             INNER JOIN usuarios u ON l.usuario_id = u.id
             INNER JOIN horarios h ON l.horario_id = h.id
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             ORDER BY l.fecha_registro DESC
             LIMIT :limit",
            ['limit' => $limit]
        );

        return array_map(fn($row) => LeccionarioModel::fromDatabase($row), $rows);
    }

    public function findForExport(array $filters): array
    {
        $where = "l.fecha BETWEEN :fecha_inicio AND :fecha_fin";
        $params = [
            'fecha_inicio' => $filters['fecha_inicio'],
            'fecha_fin' => $filters['fecha_fin']
        ];

        if (!empty($filters['profesor'])) {
            $where .= " AND l.usuario_id = :profesor_id";
            $params['profesor_id'] = $filters['profesor'];
        }

        $rows = $this->db->fetchAll(
            "SELECT l.fecha, u.nombre, u.apellido, c.nombre as curso, 
                    a.nombre as asignatura, l.contenido, l.estado
             FROM leccionarios l
             INNER JOIN usuarios u ON l.usuario_id = u.id
             INNER JOIN horarios h ON l.horario_id = h.id
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             WHERE {$where}
             ORDER BY l.fecha DESC",
            $params
        );

        return $rows;
    }
}
