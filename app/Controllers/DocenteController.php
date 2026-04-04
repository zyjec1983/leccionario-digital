<?php
/** Location: leccionario-digital/app/Controllers/DocenteController.php */

require_once __DIR__ . '/../Services/LeccionarioService.php';
require_once __DIR__ . '/../Services/HorarioService.php';

class DocenteController extends Controller
{
    private LeccionarioService $leccionarioService;
    private HorarioService $horarioService;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('docente');
        $this->leccionarioService = new LeccionarioService();
        $this->horarioService = new HorarioService();
    }

    public function index(): void
    {
        $userId = Session::getUserId();

        $this->leccionarioService->generarDiarios($userId);

        $leccionesHoy = $this->leccionarioService->obtenerLeccionesHoy($userId);
        $totalEsperados = $this->leccionarioService->contarEsperadosHoy($userId);
        $pendientes = $this->leccionarioService->contarPendientes($userId);

        $this->view('docente/index', [
            'title' => 'Panel Docente',
            'leccionesHoy' => array_map(fn($l) => $l->toArray(), $leccionesHoy),
            'totalEsperados' => $totalEsperados,
            'pendientes' => $pendientes
        ]);
    }

    public function horario(): void
    {
        $userId = Session::getUserId();
        $periodoActual = $this->horarioService->getPeriodoActual();

        $horarioEditable = $this->horarioService->esEditable();
        $horarios = $this->horarioService->obtenerHorario($userId, $periodoActual);
        $cursos = $this->horarioService->obtenerCursos();
        $niveles = $this->horarioService->obtenerNiveles($userId);

        $nivelSeleccionado = Session::get('nivel_seleccionado');

        if (!empty($niveles)) {
            if (!$nivelSeleccionado || !in_array($nivelSeleccionado, array_map(fn($n) => $n->id, $niveles))) {
                $nivelSeleccionado = $niveles[0]->id;
                Session::set('nivel_seleccionado', $nivelSeleccionado);
            }
        }

        $asignaturas = $this->horarioService->obtenerAsignaturas($nivelSeleccionado);

        $this->view('docente/horario', [
            'title' => 'Mi Horario',
            'horarios' => array_map(fn($h) => $h->toArray(), $horarios),
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

        $userId = Session::getUserId();
        
        $jsonInput = file_get_contents('php://input');
        $jsonData = json_decode($jsonInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->json(['success' => false, 'message' => 'Error al leer datos: ' . json_last_error_msg()]);
        }

        if (empty($jsonData) || !isset($jsonData['horas'])) {
            $this->json(['success' => false, 'message' => 'No se recibieron datos de horario']);
        }

        $periodo = $jsonData['periodo'] ?? $this->horarioService->getPeriodoActual();
        $horas = $jsonData['horas'];

        $resultado = $this->horarioService->guardarHorario($userId, $periodo, $horas);
        $this->json($resultado->toArray());
    }

    public function eliminarClase(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $userId = Session::getUserId();
        $dia = (int) $this->input('dia');
        $inicio = $this->input('inicio');
        $periodo = $this->input('periodo', $this->horarioService->getPeriodoActual());

        $resultado = $this->horarioService->eliminarClase($userId, $periodo, $dia, $inicio);
        $this->json($resultado->toArray());
    }

    public function cambiarNivel(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $nivelId = (int) $this->input('nivel_id');

        if ($nivelId > 0) {
            $userId = Session::getUserId();
            $nivelesDelUsuario = $this->horarioService->obtenerNiveles($userId);
            $nivelIds = array_map(fn($n) => $n->id, $nivelesDelUsuario);

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

        $filtros = [
            'fecha_inicio' => $this->input('fecha_inicio'),
            'fecha_fin' => $this->input('fecha_fin'),
            'estado' => $this->input('estado')
        ];

        $resultado = $this->leccionarioService->obtenerLeccionarios($userId, $filtros, $pagina);

        $this->view('docente/leccionarios', [
            'title' => 'Mis Leccionarios',
            'leccionarios' => array_map(fn($l) => $l->toArray(), $resultado['leccionarios']),
            'paginaActual' => $resultado['pagina'],
            'totalPaginas' => $resultado['totalPaginas'],
            'filtros' => $filtros
        ]);
    }

    public function nuevoLeccionario(string $horarioId, string $fecha): void
    {
        $userId = Session::getUserId();

        $horario = $this->leccionarioService->obtenerHorario((int)$horarioId, $userId);

        if (!$horario) {
            $this->redirect('docente');
        }

        $leccionarioExistente = $this->leccionarioService->obtenerLeccionarioPorHorarioYFecha((int)$horarioId, $fecha);

        $this->view('docente/nuevo-leccionario', [
            'title' => 'Registrar Leccionario',
            'horario' => $horario->toArray(),
            'fecha' => $fecha,
            'leccionario' => $leccionarioExistente ? $leccionarioExistente->toArray() : null
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
        $firmado = $this->input('firmado') ? true : false;

        if (!$fecha) {
            $this->json(['success' => false, 'message' => 'Fecha inválida']);
        }

        $resultado = $this->leccionarioService->guardarLeccionario($userId, [
            'horario_id' => $horarioId,
            'fecha' => $fecha,
            'contenido' => $contenido,
            'observaciones' => $observaciones,
            'firmado' => $firmado
        ]);

        $this->json($resultado->toArray());
    }

    public function verLeccionario(string $id): void
    {
        $userId = Session::getUserId();

        $leccionario = $this->leccionarioService->obtenerLeccionario((int)$id);

        if (!$leccionario || $leccionario->getUsuarioId() !== $userId) {
            $this->redirect('docente/leccionarios');
        }

        $this->view('docente/ver-leccionario', [
            'title' => 'Ver Leccionario',
            'leccionario' => $leccionario->toArray()
        ]);
    }
}
