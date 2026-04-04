<?php
/** Location: leccionario-digital/app/Services/HorarioService.php */

require_once __DIR__ . '/../Core/Result.php';
require_once __DIR__ . '/../Repositories/HorarioRepository.php';

class HorarioService
{
    private HorarioRepository $repo;

    public function __construct()
    {
        $this->repo = new HorarioRepository();
    }

    public function obtenerHorario(int $usuarioId, ?string $periodo = null): array
    {
        $periodo = $periodo ?? date('Y') . '-' . (date('n') <= 6 ? '1' : '2');
        return $this->repo->findByUsuario($usuarioId, $periodo);
    }

    public function esEditable(): bool
    {
        $habilitarHorarios = (int) Config::get('habilitar_edicion_horarios') === 1;
        
        if (!$habilitarHorarios) {
            return false;
        }

        $fechaExpiracion = Config::get('horarios_fecha_expiracion');
        
        if ($fechaExpiracion) {
            return strtotime($fechaExpiracion) > time();
        }

        return true;
    }

    public function guardarHorario(int $usuarioId, string $periodo, array $horas): Result
    {
        if (!$this->esEditable()) {
            return Result::error('La edición de horarios está deshabilitada');
        }

        $guardados = 0;

        try {
            foreach ($horas as $horaData) {
                if (empty($horaData['curso_id']) || empty($horaData['asignatura_id'])) {
                    continue;
                }

                $dia = (int) $horaData['dia'];
                $inicio = $horaData['inicio'];
                $fin = $horaData['fin'];
                $aula = $horaData['aula'] ?? null;

                $this->repo->saveOrUpdate($usuarioId, $periodo, $dia, $inicio, [
                    'curso_id' => (int) $horaData['curso_id'],
                    'asignatura_id' => (int) $horaData['asignatura_id'],
                    'hora_inicio' => $inicio,
                    'hora_fin' => $fin,
                    'aula' => $aula,
                    'activo' => 1
                ]);

                $guardados++;
            }

            return Result::success("Horario actualizado: {$guardados} clase(s) guardada(s)", ['guardados' => $guardados]);
        } catch (Exception $e) {
            error_log('Error al guardar horario: ' . $e->getMessage());
            return Result::error('Error al guardar el horario: ' . $e->getMessage());
        }
    }

    public function eliminarClase(int $usuarioId, string $periodo, int $dia, string $inicio): Result
    {
        if (!$this->esEditable()) {
            return Result::error('La edición de horarios está deshabilitada');
        }

        if ($this->repo->hardDelete($usuarioId, $periodo, $dia, $inicio)) {
            return Result::success('Clase eliminada');
        }

        return Result::error('No se encontró la clase');
    }

    public function obtenerNiveles(int $usuarioId): array
    {
        $nivelesDelUsuario = $this->repo->getNivelesByUsuario($usuarioId);
        
        if (!empty($nivelesDelUsuario)) {
            return $nivelesDelUsuario;
        }

        return $this->repo->getAllNiveles();
    }

    public function obtenerAsignaturas(?int $nivelId = null): array
    {
        if ($nivelId) {
            return $this->repo->getAsignaturasByNivel($nivelId);
        }

        return $this->repo->getAllAsignaturas();
    }

    public function obtenerCursos(): array
    {
        return $this->repo->getAllCursos();
    }

    public function getPeriodoActual(): string
    {
        return date('Y') . '-' . (date('n') <= 6 ? '1' : '2');
    }
}
