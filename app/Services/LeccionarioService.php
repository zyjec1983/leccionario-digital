<?php
/** Location: leccionario-digital/app/Services/LeccionarioService.php */

require_once __DIR__ . '/../Core/Result.php';
require_once __DIR__ . '/../Repositories/LeccionarioRepository.php';
require_once __DIR__ . '/../Repositories/HorarioRepository.php';
require_once __DIR__ . '/../Models/LeccionarioModel.php';

class LeccionarioService
{
    private LeccionarioRepository $leccionarioRepo;
    private HorarioRepository $horarioRepo;

    public function __construct()
    {
        $this->leccionarioRepo = new LeccionarioRepository();
        $this->horarioRepo = new HorarioRepository();
    }

    public function generarDiarios(int $usuarioId): array
    {
        $periodo = date('Y') . '-' . (date('n') <= 6 ? '1' : '2');
        $diaSemana = (int) date('N');
        $fechaHoy = date('Y-m-d');

        $horariosDelDia = $this->horarioRepo->findByUsuarioAndDia($usuarioId, $periodo, $diaSemana);

        $creados = 0;
        $errores = [];

        foreach ($horariosDelDia as $horario) {
            if ($this->leccionarioRepo->existe($usuarioId, $horario->getId(), $fechaHoy)) {
                continue;
            }

            $estado = 'pendiente';
            if ($fechaHoy < date('Y-m-d')) {
                $estado = 'atrasado';
            }

            try {
                $this->leccionarioRepo->create([
                    'usuario_id' => $usuarioId,
                    'horario_id' => $horario->getId(),
                    'fecha' => $fechaHoy,
                    'contenido' => '',
                    'observaciones' => null,
                    'firmado' => 0,
                    'fecha_registro' => date('Y-m-d H:i:s'),
                    'estado' => $estado
                ]);
                $creados++;
            } catch (Exception $e) {
                $errores[] = "Error al crear leccionario para {$horario->getAsignaturaNombre()}: " . $e->getMessage();
            }
        }

        return [
            'creados' => $creados,
            'errores' => $errores
        ];
    }

    public function obtenerLeccionesHoy(int $usuarioId): array
    {
        $fechaHoy = date('Y-m-d');
        return $this->leccionarioRepo->findByUsuarioAndFecha($usuarioId, $fechaHoy);
    }

    public function obtenerLeccionario(int $id): ?LeccionarioModel
    {
        return $this->leccionarioRepo->findById($id);
    }

    public function obtenerLeccionarioPorHorarioYFecha(int $horarioId, string $fecha): ?LeccionarioModel
    {
        return $this->leccionarioRepo->findByHorarioAndFecha($horarioId, $fecha);
    }

    public function obtenerLeccionarios(int $usuarioId, array $filtros = [], int $pagina = 1, int $porPagina = 20): array
    {
        $offset = ($pagina - 1) * $porPagina;
        $leccionarios = $this->leccionarioRepo->findByUsuario($usuarioId, $filtros, $porPagina, $offset);
        $total = $this->leccionarioRepo->countByUsuario($usuarioId, $filtros);

        return [
            'leccionarios' => $leccionarios,
            'total' => $total,
            'pagina' => $pagina,
            'porPagina' => $porPagina,
            'totalPaginas' => $total > 0 ? ceil($total / $porPagina) : 1
        ];
    }

    public function guardarLeccionario(int $usuarioId, array $data): Result
    {
        $horarioId = (int) ($data['horario_id'] ?? 0);
        $fecha = $data['fecha'] ?? date('Y-m-d');
        $contenido = $data['contenido'] ?? '';
        $observaciones = $data['observaciones'] ?? null;
        $firmado = isset($data['firmado']) && $data['firmado'];

        if (empty($contenido)) {
            return Result::error('El contenido de la lección es requerido');
        }

        if (Security::isFechaBloqueada($fecha)) {
            $semanas = Security::getBloqueoSemanas();
            return Result::error("Los leccionarios con más de {$semanas} semana(s) de antigüedad están bloqueados. Contacta al coordinador.");
        }

        $horario = $this->horarioRepo->findByIdAndUsuario($horarioId, $usuarioId);
        if (!$horario) {
            return Result::error('Horario no encontrado o no tienes acceso');
        }

        $existente = $this->leccionarioRepo->findByHorarioAndFecha($horarioId, $fecha);

        try {
            if ($existente) {
                $this->leccionarioRepo->update($existente->getId(), [
                    'contenido' => $contenido,
                    'observaciones' => $observaciones,
                    'firmado' => $firmado,
                    'estado' => 'completado'
                ]);
                return Result::success('Leccionario actualizado exitosamente');
            } else {
                $this->leccionarioRepo->create([
                    'usuario_id' => $usuarioId,
                    'horario_id' => $horarioId,
                    'fecha' => $fecha,
                    'contenido' => $contenido,
                    'observaciones' => $observaciones,
                    'firmado' => $firmado,
                    'fecha_registro' => date('Y-m-d H:i:s'),
                    'estado' => 'completado'
                ]);
                return Result::success('Leccionario guardado exitosamente');
            }
        } catch (Exception $e) {
            error_log('Error al guardar leccionario: ' . $e->getMessage());
            return Result::error('Error al guardar el leccionario');
        }
    }

    public function contarPendientes(int $usuarioId): int
    {
        return $this->leccionarioRepo->countPendientes($usuarioId);
    }

    public function contarEsperadosHoy(int $usuarioId): int
    {
        $periodo = date('Y') . '-' . (date('n') <= 6 ? '1' : '2');
        $diaSemana = (int) date('N');

        return $this->horarioRepo->countByUsuarioAndDia($usuarioId, $periodo, $diaSemana);
    }

    public function contarLeccionesHoy(int $usuarioId): int
    {
        $fechaHoy = date('Y-m-d');
        return $this->leccionarioRepo->countPorFecha($usuarioId, $fechaHoy);
    }

    public function obtenerHorario(int $horarioId, int $usuarioId): ?HorarioModel
    {
        return $this->horarioRepo->findByIdAndUsuario($horarioId, $usuarioId);
    }

    public function estaBloqueado(string $fecha): bool
    {
        return Security::isFechaBloqueada($fecha);
    }

    public function listarCoordinador(array $filtros): array
    {
        return $this->leccionarioRepo->findAllWithFilters($filtros);
    }

    public function obtenerDetalleCoordinador(int $id): ?LeccionarioModel
    {
        return $this->leccionarioRepo->findByIdWithDetails($id);
    }

    public function obtenerDocentes(): array
    {
        return $this->horarioRepo->findDocentesActivos();
    }

    public function obtenerDashboardStats(): array
    {
        return [
            'lecciones_hoy' => $this->leccionarioRepo->countHoy(),
            'pendientes' => $this->leccionarioRepo->countPendientesTotal(),
            'atrasados' => $this->leccionarioRepo->countAtrasadosTotal(),
            'recientes' => $this->leccionarioRepo->findRecientes(10)
        ];
    }

    public function exportarDatos(array $filtros): array
    {
        return $this->leccionarioRepo->findForExport($filtros);
    }
}
