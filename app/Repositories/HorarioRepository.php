<?php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/HorarioModel.php';

class HorarioRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?HorarioModel
    {
        $row = $this->db->fetch(
            "SELECT h.*, c.nombre as curso, c.seccion, a.nombre as asignatura, a.codigo, a.nivel_id,
                    n.nombre as nivel_nombre
             FROM horarios h
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             LEFT JOIN niveles_educativos n ON a.nivel_id = n.id
             WHERE h.id = :id",
            ['id' => $id]
        );

        if (!$row) {
            return null;
        }

        return HorarioModel::fromDatabase($row);
    }

    public function findByUsuario(int $usuarioId, string $periodo): array
    {
        $rows = $this->db->fetchAll(
            "SELECT h.*, c.nombre as curso, c.seccion, a.nombre as asignatura, a.codigo, a.nivel_id
             FROM horarios h
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             WHERE h.usuario_id = :user_id AND h.periodo = :periodo AND h.activo = 1
             ORDER BY h.dia_semana, h.hora_inicio",
            ['user_id' => $usuarioId, 'periodo' => $periodo]
        );

        return array_map(fn($row) => HorarioModel::fromDatabase($row), $rows);
    }

    public function findByUsuarioAndDia(int $usuarioId, string $periodo, int $diaSemana): array
    {
        $rows = $this->db->fetchAll(
            "SELECT h.*, c.nombre as curso, c.seccion, a.nombre as asignatura, a.codigo, a.nivel_id
             FROM horarios h
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             WHERE h.usuario_id = :user_id AND h.periodo = :periodo AND h.dia_semana = :dia AND h.activo = 1
             ORDER BY h.hora_inicio",
            ['user_id' => $usuarioId, 'periodo' => $periodo, 'dia' => $diaSemana]
        );

        return array_map(fn($row) => HorarioModel::fromDatabase($row), $rows);
    }

    public function countByUsuarioAndDia(int $usuarioId, string $periodo, int $diaSemana): int
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as total FROM horarios 
             WHERE usuario_id = :user_id AND periodo = :periodo AND dia_semana = :dia AND activo = 1",
            ['user_id' => $usuarioId, 'periodo' => $periodo, 'dia' => $diaSemana]
        );

        return $result ? (int) $result->total : 0;
    }

    public function create(array $data): int
    {
        return $this->db->insert('horarios', [
            'usuario_id' => $data['usuario_id'],
            'curso_id' => $data['curso_id'],
            'asignatura_id' => $data['asignatura_id'],
            'dia_semana' => $data['dia_semana'],
            'hora_inicio' => $data['hora_inicio'],
            'hora_fin' => $data['hora_fin'],
            'aula' => $data['aula'] ?? null,
            'periodo' => $data['periodo'],
            'activo' => 1
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $updateData = [];

        if (array_key_exists('curso_id', $data)) {
            $updateData['curso_id'] = $data['curso_id'];
        }
        if (array_key_exists('asignatura_id', $data)) {
            $updateData['asignatura_id'] = $data['asignatura_id'];
        }
        if (array_key_exists('hora_inicio', $data)) {
            $updateData['hora_inicio'] = $data['hora_inicio'];
        }
        if (array_key_exists('hora_fin', $data)) {
            $updateData['hora_fin'] = $data['hora_fin'];
        }
        if (array_key_exists('aula', $data)) {
            $updateData['aula'] = $data['aula'];
        }
        if (array_key_exists('activo', $data)) {
            $updateData['activo'] = $data['activo'] ? 1 : 0;
        }

        if (empty($updateData)) {
            return true;
        }

        $result = $this->db->update('horarios', $updateData, 'id = :id', ['id' => $id]);
        return $result > 0;
    }

    public function delete(int $id): bool
    {
        $result = $this->db->update('horarios', ['activo' => 0], 'id = :id', ['id' => $id]);
        return $result > 0;
    }

    public function hardDelete(int $usuarioId, string $periodo, int $diaSemana, string $horaInicio): bool
    {
        $result = $this->db->delete(
            'horarios',
            'usuario_id = :user_id AND periodo = :periodo AND dia_semana = :dia AND hora_inicio = :inicio',
            ['user_id' => $usuarioId, 'periodo' => $periodo, 'dia' => $diaSemana, 'inicio' => $horaInicio]
        );
        return $result > 0;
    }

    public function exists(int $usuarioId, string $periodo, int $diaSemana, string $horaInicio): bool
    {
        $row = $this->db->fetch(
            "SELECT id FROM horarios WHERE usuario_id = :user_id AND periodo = :periodo AND dia_semana = :dia AND hora_inicio = :inicio",
            ['user_id' => $usuarioId, 'periodo' => $periodo, 'dia' => $diaSemana, 'inicio' => $horaInicio]
        );

        return $row !== null;
    }

    public function findByIdAndUsuario(int $id, int $usuarioId): ?HorarioModel
    {
        $row = $this->db->fetch(
            "SELECT h.*, c.nombre as curso, c.seccion, a.nombre as asignatura, a.codigo, a.nivel_id
             FROM horarios h
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             WHERE h.id = :id AND h.usuario_id = :user_id",
            ['id' => $id, 'user_id' => $usuarioId]
        );

        if (!$row) {
            return null;
        }

        return HorarioModel::fromDatabase($row);
    }

    public function getAllCursos(): array
    {
        return $this->db->fetchAll("SELECT * FROM cursos WHERE activo = 1 ORDER BY nivel, seccion");
    }

    public function getAllNiveles(): array
    {
        return $this->db->fetchAll("SELECT * FROM niveles_educativos WHERE activo = 1 ORDER BY orden");
    }

    public function getNivelesByUsuario(int $usuarioId): array
    {
        return $this->db->fetchAll(
            "SELECT DISTINCT n.id, n.nombre, n.abreviatura, n.orden, n.activo FROM niveles_educativos n
             INNER JOIN usuarios_niveles un ON n.id = un.nivel_id
             WHERE un.usuario_id = :user_id AND n.activo = 1
             ORDER BY n.orden",
            ['user_id' => $usuarioId]
        );
    }

    public function getAsignaturasByNivel(int $nivelId): array
    {
        $result = $this->db->fetchAll(
            "SELECT DISTINCT a.id, a.codigo, a.nombre, a.area, a.horas_semanales, n.nombre as nivel_nombre 
             FROM asignaturas a
             LEFT JOIN niveles_educativos n ON a.nivel_id = n.id
             WHERE a.activo = 1 AND a.nivel_id = :nivel_id
             ORDER BY a.nombre",
            ['nivel_id' => $nivelId]
        );
        
        if (empty($result)) {
            return $this->db->fetchAll(
                "SELECT DISTINCT a.id, a.codigo, a.nombre, a.area, a.horas_semanales, n.nombre as nivel_nombre 
                 FROM asignaturas a
                 LEFT JOIN niveles_educativos n ON a.nivel_id = n.id
                 WHERE a.activo = 1
                 ORDER BY a.nombre"
            );
        }
        
        return $result;
    }

    public function getAllAsignaturas(): array
    {
        return $this->db->fetchAll(
            "SELECT DISTINCT a.id, a.codigo, a.nombre, a.area, a.horas_semanales, n.nombre as nivel_nombre 
             FROM asignaturas a
             LEFT JOIN niveles_educativos n ON a.nivel_id = n.id
             WHERE a.activo = 1
             ORDER BY n.orden, a.nombre"
        );
    }

    public function saveOrUpdate(int $usuarioId, string $periodo, int $diaSemana, string $horaInicio, array $data): bool
    {
        $existente = $this->db->fetch(
            "SELECT id FROM horarios WHERE usuario_id = :user_id AND periodo = :periodo AND dia_semana = :dia AND hora_inicio = :inicio",
            ['user_id' => $usuarioId, 'periodo' => $periodo, 'dia' => $diaSemana, 'inicio' => $horaInicio]
        );

        if ($existente) {
            return $this->update($existente->id, $data);
        } else {
            $this->create(array_merge($data, [
                'usuario_id' => $usuarioId,
                'periodo' => $periodo,
                'dia_semana' => $diaSemana,
                'hora_inicio' => $horaInicio
            ]));
            return true;
        }
    }
}
