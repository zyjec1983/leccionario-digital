<?php

class DocenteController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('docente');
    }

    public function index(): void
    {
        $userId = Session::getUserId();
        $periodoActual = date('Y') . '-' . (date('n') <= 6 ? '1' : '2');
        $diaSemana = date('N');
        $fechaHoy = date('Y-m-d');

        // Generar leccionarios si no existen para hoy
        $this->generarLeccionariosDiarios($userId, $periodoActual, $diaSemana, $fechaHoy);

        $leccionesHoy = $this->db->fetchAll(
            "SELECT l.*, h.hora_inicio, h.hora_fin, c.nombre as curso, a.nombre as asignatura, a.codigo
             FROM leccionarios l
             INNER JOIN horarios h ON l.horario_id = h.id
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             WHERE l.usuario_id = :user_id AND l.fecha = :fecha
             ORDER BY h.hora_inicio",
            ['user_id' => $userId, 'fecha' => $fechaHoy]
        );

        $totalEsperados = $this->db->fetch(
            "SELECT COUNT(*) as total FROM horarios WHERE usuario_id = :user_id AND periodo = :periodo AND dia_semana = :dia",
            ['user_id' => $userId, 'periodo' => $periodoActual, 'dia' => $diaSemana]
        );

        $pendientes = $this->db->fetch(
            "SELECT COUNT(*) as total FROM leccionarios 
             WHERE usuario_id = :user_id AND estado IN ('pendiente', 'atrasado') AND fecha <= CURDATE()",
            ['user_id' => $userId]
        );

        $this->view('docente/index', [
            'title' => 'Panel Docente',
            'leccionesHoy' => $leccionesHoy,
            'totalEsperados' => $totalEsperados->total ?? 0,
            'pendientes' => $pendientes->total ?? 0
        ]);
    }

    private function generarLeccionariosDiarios(int $userId, string $periodo, int $diaSemana, string $fecha): void
    {
        $horariosDelDia = $this->db->fetchAll(
            "SELECT h.*, c.nombre as curso, a.nombre as asignatura
             FROM horarios h
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             WHERE h.usuario_id = :user_id AND h.periodo = :periodo AND h.dia_semana = :dia AND h.activo = 1",
            ['user_id' => $userId, 'periodo' => $periodo, 'dia' => $diaSemana]
        );

        foreach ($horariosDelDia as $horario) {
            $existe = $this->db->fetch(
                "SELECT id FROM leccionarios WHERE usuario_id = :user_id AND horario_id = :horario_id AND fecha = :fecha",
                ['user_id' => $userId, 'horario_id' => $horario->id, 'fecha' => $fecha]
            );

            if (!$existe) {
                $estado = 'pendiente';
                if ($fecha < date('Y-m-d')) {
                    $estado = 'atrasado';
                }

                $this->db->insert('leccionarios', [
                    'usuario_id' => $userId,
                    'horario_id' => $horario->id,
                    'fecha' => $fecha,
                    'contenido' => '',
                    'observaciones' => null,
                    'firmado' => 0,
                    'fecha_registro' => date('Y-m-d H:i:s'),
                    'estado' => $estado
                ]);
            }
        }
    }

    public function horario(): void
    {
        $userId = Session::getUserId();
        $periodoActual = date('Y') . '-' . (date('n') <= 6 ? '1' : '2');

        $horarioEditable = (int) Config::get('habilitar_edicion_horarios') === 1;
        $fechaExpiracion = Config::get('horarios_fecha_expiracion');

        if ($horarioEditable && $fechaExpiracion) {
            $horarioEditable = strtotime($fechaExpiracion) > time();
        }

        $horarios = $this->db->fetchAll(
            "SELECT h.*, c.nombre as curso, a.nombre as asignatura, a.codigo, a.nivel_id
             FROM horarios h
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             WHERE h.usuario_id = :user_id AND h.periodo = :periodo
             ORDER BY h.dia_semana, h.hora_inicio",
            ['user_id' => $userId, 'periodo' => $periodoActual]
        );

        $cursos = $this->db->fetchAll("SELECT * FROM cursos WHERE activo = 1 ORDER BY nivel, seccion");

        $nivelesDelUsuario = $this->db->fetchAll(
            "SELECT n.* FROM niveles_educativos n
             INNER JOIN usuarios_niveles un ON n.id = un.nivel_id
             WHERE un.usuario_id = :user_id AND n.activo = 1
             ORDER BY n.orden",
            ['user_id' => $userId]
        );

        $nivelSeleccionado = Session::get('nivel_seleccionado');

        if (!empty($nivelesDelUsuario)) {
            $niveles = $nivelesDelUsuario;
            if (!$nivelSeleccionado || !in_array($nivelSeleccionado, array_map(fn($n) => $n->id, $niveles))) {
                $nivelSeleccionado = $niveles[0]->id;
                Session::set('nivel_seleccionado', $nivelSeleccionado);
            }
            
            $asignaturas = $this->db->fetchAll(
                "SELECT a.*, n.nombre as nivel_nombre 
                 FROM asignaturas a
                 LEFT JOIN niveles_educativos n ON a.nivel_id = n.id
                 WHERE a.activo = 1 
                 AND a.nivel_id = :nivel_id
                 ORDER BY n.orden, a.nombre",
                ['nivel_id' => $nivelSeleccionado]
            );
        } else {
            $niveles = $this->db->fetchAll("SELECT * FROM niveles_educativos WHERE activo = 1 ORDER BY orden");
            $nivelSeleccionado = Session::get('nivel_seleccionado') ?? ($niveles[0]->id ?? null);
            
            $asignaturas = $this->db->fetchAll(
                "SELECT a.*, n.nombre as nivel_nombre 
                 FROM asignaturas a
                 LEFT JOIN niveles_educativos n ON a.nivel_id = n.id
                 WHERE a.activo = 1
                 ORDER BY n.orden, a.nombre"
            );
        }

        $this->view('docente/horario', [
            'title' => 'Mi Horario',
            'horarios' => $horarios,
            'niveles' => $niveles,
            'nivelSeleccionado' => $nivelSeleccionado,
            'cursos' => $cursos,
            'asignaturas' => $asignaturas,
            'horarioEditable' => $horarioEditable,
            'periodo' => $periodoActual
        ]);
    }

    public function guardarHorario(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('docente/horario');
        }

        $horarioEditable = (int) Config::get('habilitar_edicion_horarios') === 1;

        if (!$horarioEditable) {
            $this->json(['success' => false, 'message' => 'La edición de horarios está deshabilitada']);
        }

        $userId = Session::getUserId();
        
        $jsonInput = file_get_contents('php://input');
        $jsonData = json_decode($jsonInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->json(['success' => false, 'message' => 'Error al leer datos: ' . json_last_error_msg()]);
        }
        
        if (empty($jsonData) || !isset($jsonData['horas'])) {
            $this->json(['success' => false, 'message' => 'No se recibieron datos de horario']);
        }
        
        $periodo = $jsonData['periodo'] ?? date('Y') . '-1';
        $horas = $jsonData['horas'];

        $guardados = 0;
        
        try {
            foreach ($horas as $horaData) {
                if (empty($horaData['curso_id']) || empty($horaData['asignatura_id'])) {
                    continue;
                }

                $dia = (int) $horaData['dia'];
                $inicio = $horaData['inicio'];
                
                $existente = $this->db->fetch(
                    "SELECT id FROM horarios WHERE usuario_id = :user_id AND periodo = :periodo AND dia_semana = :dia AND hora_inicio = :inicio",
                    ['user_id' => $userId, 'periodo' => $periodo, 'dia' => $dia, 'inicio' => $inicio]
                );

                if ($existente) {
                    $this->db->update('horarios',
                        [
                            'curso_id' => (int) $horaData['curso_id'],
                            'asignatura_id' => (int) $horaData['asignatura_id'],
                            'hora_fin' => $horaData['fin'],
                            'aula' => $horaData['aula'] ?? null,
                            'activo' => 1
                        ],
                        'id = :id',
                        ['id' => $existente->id]
                    );
                } else {
                    $this->db->insert('horarios', [
                        'usuario_id' => $userId,
                        'curso_id' => (int) $horaData['curso_id'],
                        'asignatura_id' => (int) $horaData['asignatura_id'],
                        'dia_semana' => $dia,
                        'hora_inicio' => $inicio,
                        'hora_fin' => $horaData['fin'],
                        'aula' => $horaData['aula'] ?? null,
                        'periodo' => $periodo,
                        'activo' => 1
                    ]);
                }
                $guardados++;
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);
        }

        $this->json(['success' => true, 'message' => "Horario actualizado: {$guardados} clase(s) guardada(s)"]);
    }

    public function eliminarClase(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $horarioEditable = (int) Config::get('habilitar_edicion_horarios') === 1;

        if (!$horarioEditable) {
            $this->json(['success' => false, 'message' => 'La edición de horarios está deshabilitada']);
        }

        $userId = Session::getUserId();
        $dia = (int) $this->input('dia');
        $inicio = $this->input('inicio');
        $periodo = $this->input('periodo', date('Y') . '-1');

        $deleted = $this->db->delete(
            'horarios',
            'usuario_id = :user_id AND periodo = :periodo AND dia_semana = :dia AND hora_inicio = :inicio',
            ['user_id' => $userId, 'periodo' => $periodo, 'dia' => $dia, 'inicio' => $inicio]
        );

        if ($deleted) {
            $this->json(['success' => true, 'message' => 'Clase eliminada']);
        } else {
            $this->json(['success' => false, 'message' => 'No se encontró la clase']);
        }
    }

    public function cambiarNivel(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $nivelId = (int) $this->input('nivel_id');
        
        if ($nivelId > 0) {
            $userId = Session::getUserId();
            
            $nivelesDelUsuario = $this->db->fetchAll(
                "SELECT nivel_id FROM usuarios_niveles WHERE usuario_id = :user_id",
                ['user_id' => $userId]
            );
            
            $nivelIds = array_map(fn($n) => $n->nivel_id, $nivelesDelUsuario);
            
            if (empty($nivelIds) || in_array($nivelId, $nivelIds)) {
                Session::set('nivel_seleccionado', $nivelId);
                $this->json(['success' => true, 'message' => 'Nivel actualizado']);
            } else {
                $this->json(['success' => false, 'message' => 'No tienes acceso a este nivel']);
            }
        } else {
            $this->json(['success' => false, 'message' => 'Nivel inválido']);
        }
    }

    public function leccionarios(): void
    {
        $userId = Session::getUserId();
        $pagina = (int) ($this->input('page') ?? 1);
        $porPagina = 20;
        $offset = ($pagina - 1) * $porPagina;

        $filtros = [
            'fecha_inicio' => $this->input('fecha_inicio'),
            'fecha_fin' => $this->input('fecha_fin'),
            'estado' => $this->input('estado')
        ];

        $where = "l.usuario_id = :user_id";
        $params = ['user_id' => $userId];

        if ($filtros['fecha_inicio']) {
            $where .= " AND l.fecha >= :fecha_inicio";
            $params['fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if ($filtros['fecha_fin']) {
            $where .= " AND l.fecha <= :fecha_fin";
            $params['fecha_fin'] = $filtros['fecha_fin'];
        }

        if ($filtros['estado']) {
            $where .= " AND l.estado = :estado";
            $params['estado'] = $filtros['estado'];
        }

        $leccionarios = $this->db->fetchAll(
            "SELECT l.*, h.hora_inicio, c.nombre as curso, a.nombre as asignatura
             FROM leccionarios l
             INNER JOIN horarios h ON l.horario_id = h.id
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             WHERE {$where}
             ORDER BY l.fecha DESC, h.hora_inicio DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $porPagina, 'offset' => $offset])
        );

        $total = $this->db->fetch(
            "SELECT COUNT(*) as total FROM leccionarios l WHERE {$where}",
            $params
        );

        $this->view('docente/leccionarios', [
            'title' => 'Mis Leccionarios',
            'leccionarios' => $leccionarios,
            'paginaActual' => $pagina,
            'totalPaginas' => ceil($total->total / $porPagina),
            'filtros' => $filtros
        ]);
    }

    public function nuevoLeccionario(string $horarioId, string $fecha): void
    {
        $userId = Session::getUserId();

        $horario = $this->db->fetch(
            "SELECT h.*, c.nombre as curso, a.nombre as asignatura, a.codigo
             FROM horarios h
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             WHERE h.id = :id AND h.usuario_id = :user_id",
            ['id' => $horarioId, 'user_id' => $userId]
        );

        if (!$horario) {
            $this->redirect('docente');
        }

        $leccionarioExistente = $this->db->fetch(
            "SELECT * FROM leccionarios WHERE horario_id = :horario_id AND fecha = :fecha",
            ['horario_id' => $horarioId, 'fecha' => $fecha]
        );

        $this->view('docente/nuevo-leccionario', [
            'title' => 'Registrar Leccionario',
            'horario' => $horario,
            'fecha' => $fecha,
            'leccionario' => $leccionarioExistente
        ]);
    }

    public function guardarLeccionario(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('docente/leccionarios');
        }

        $userId = Session::getUserId();
        $horarioId = Security::sanitizeInt($this->input('horario_id'));
        $fechaRaw = $this->input('fecha');
        $fecha = Security::sanitizeDate($fechaRaw);
        $contenidoRaw = $this->input('contenido');
        $contenido = Security::sanitizeString($contenidoRaw);
        $observacionesRaw = $this->input('observaciones');
        $observaciones = Security::sanitizeString($observacionesRaw);
        $firmado = $this->input('firmado') ? 1 : 0;

        if (!$fecha) {
            $this->json(['success' => false, 'message' => 'Fecha inválida']);
        }

        if (Security::isFechaBloqueada($fecha)) {
            $semanas = Security::getBloqueoSemanas();
            $this->json([
                'success' => false,
                'message' => "Los leccionarios con más de {$semanas} semana(s) de antigüedad están bloqueados. Contacta al coordinador."
            ], 403);
        }

        if (empty($contenido)) {
            $this->json(['success' => false, 'message' => 'El contenido es requerido']);
        }

        $horario = $this->db->fetch(
            "SELECT * FROM horarios WHERE id = :id AND usuario_id = :user_id",
            ['id' => $horarioId, 'user_id' => $userId]
        );

        if (!$horario) {
            $this->json(['success' => false, 'message' => 'Horario no encontrado']);
        }

        $existente = $this->db->fetch(
            "SELECT * FROM leccionarios WHERE horario_id = :horario_id AND fecha = :fecha",
            ['horario_id' => $horarioId, 'fecha' => $fecha]
        );

        if ($existente) {
            $this->db->update('leccionarios', [
                'contenido' => $contenido,
                'observaciones' => $observaciones,
                'firmado' => $firmado,
                'estado' => 'completado'
            ], 'id = :id', ['id' => $existente->id]);

            $this->json(['success' => true, 'message' => 'Leccionario actualizado']);
        } else {
            $this->db->insert('leccionarios', [
                'usuario_id' => $userId,
                'horario_id' => $horarioId,
                'fecha' => $fecha,
                'contenido' => $contenido,
                'observaciones' => $observaciones,
                'firmado' => $firmado,
                'fecha_registro' => date('Y-m-d H:i:s'),
                'estado' => 'completado'
            ]);

            $this->json(['success' => true, 'message' => 'Leccionario guardado']);
        }
    }

    public function verLeccionario(string $id): void
    {
        $userId = Session::getUserId();

        $leccionario = $this->db->fetch(
            "SELECT l.*, h.hora_inicio, h.hora_fin, h.aula,
                    c.nombre as curso, a.nombre as asignatura, a.codigo
             FROM leccionarios l
             INNER JOIN horarios h ON l.horario_id = h.id
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             WHERE l.id = :id AND l.usuario_id = :user_id",
            ['id' => $id, 'user_id' => $userId]
        );

        if (!$leccionario) {
            $this->redirect('docente/leccionarios');
        }

        $this->view('docente/ver-leccionario', [
            'title' => 'Ver Leccionario',
            'leccionario' => $leccionario
        ]);
    }
}
