<?php
/** Location: leccionario-digital/app/Controllers/CoordinadorController.php */

require_once __DIR__ . '/../Services/UsuarioService.php';
require_once __DIR__ . '/../Services/AsignaturaService.php';
require_once __DIR__ . '/../Services/CursoService.php';
require_once __DIR__ . '/../Services/LeccionarioService.php';
require_once __DIR__ . '/../Services/ConfiguracionService.php';

class CoordinadorController extends Controller
{
    private UsuarioService $usuarioService;
    private AsignaturaService $asignaturaService;
    private CursoService $cursoService;
    private LeccionarioService $leccionarioService;
    private ConfiguracionService $configuracionService;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('coordinador');
        $this->usuarioService = new UsuarioService();
        $this->asignaturaService = new AsignaturaService();
        $this->cursoService = new CursoService();
        $this->leccionarioService = new LeccionarioService();
        $this->configuracionService = new ConfiguracionService();
    }

    public function index(): void
    {
        $stats = $this->leccionarioService->obtenerDashboardStats();
        $totalProfesores = $this->usuarioService->contarDocentesActivos();
        $totalCursos = $this->cursoService->contarActivos();

        $this->view('coordinador/index', [
            'title' => 'Dashboard',
            'stats' => [
                'profesores' => $totalProfesores,
                'leccionesHoy' => $stats['lecciones_hoy'],
                'esperadosHoy' => $stats['esperados'] ?? 0,
                'pendientes' => $stats['pendientes'],
                'atrasados' => $stats['atrasados'],
                'cursos' => $totalCursos
            ],
            'leccionesRecientes' => $stats['recientes']
        ]);
    }

    public function usuarios(): void
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        $usuarios = $this->usuarioService->listarTodos();
        $roles = $this->usuarioService->obtenerRolesDisponibles();
        $asignaturas = $this->usuarioService->obtenerAsignaturasDisponibles();
        $totalEliminados = $this->usuarioService->contarEliminados();

        $this->view('coordinador/usuarios', [
            'title' => 'Gestión de Usuarios',
            'usuarios' => $usuarios,
            'roles' => $roles,
            'asignaturas' => $asignaturas,
            'totalEliminados' => $totalEliminados,
            'mostrarEliminados' => false
        ]);
    }

    public function usuariosEliminados(): void
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        $usuariosEliminados = $this->usuarioService->listarEliminados();

        $this->view('coordinador/usuarios-eliminados', [
            'title' => 'Usuarios Eliminados',
            'usuariosEliminados' => $usuariosEliminados
        ]);
    }

    public function buscarUsuarios(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $query = $this->input('q', '');
        
        if (empty($query)) {
            $usuarios = $this->usuarioService->listarTodos();
        } else {
            $usuarios = $this->usuarioService->buscar($query);
        }

        $usuariosArray = array_map(fn($u) => $u->toArray(), $usuarios);
        
        $this->json([
            'success' => true,
            'usuarios' => $usuariosArray,
            'total' => count($usuariosArray)
        ]);
    }

    public function buscarUsuariosEliminados(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $query = $this->input('q', '');
        
        if (empty($query)) {
            $usuarios = $this->usuarioService->listarEliminados();
        } else {
            $usuarios = $this->usuarioService->buscarEliminados($query);
        }

        $usuariosArray = array_map(fn($u) => $u->toArray(), $usuarios);
        
        $this->json([
            'success' => true,
            'usuarios' => $usuariosArray,
            'total' => count($usuariosArray)
        ]);
    }

    public function obtenerUsuario(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $id = (int) $this->input('id');
        
        $resultado = $this->usuarioService->obtenerUsuarioConRelaciones($id);
        
        if (!$resultado) {
            $this->json(['success' => false, 'message' => 'Usuario no encontrado']);
        }

        $usuario = $resultado['usuario'];
        
        $this->json([
            'success' => true,
            'usuario' => [
                'id' => $usuario->getId(),
                'nombre' => $usuario->getNombre(),
                'apellido' => $usuario->getApellido(),
                'email' => $usuario->getEmail(),
                'telefono' => $usuario->getTelefono(),
                'tiene_firma' => $usuario->hasFirma()
            ],
            'roles' => array_map(fn($r) => $r->id, $resultado['roles']),
            'asignaturas' => array_map(fn($a) => $a->id, $resultado['asignaturas'])
        ]);
    }

    public function obtenerFirma(string $id): void
    {
        $firma = $this->usuarioService->getFirma((int)$id);
        
        if (!$firma) {
            http_response_code(404);
            exit;
        }

        header('Content-Type: image/png');
        echo $firma;
        exit;
    }

    public function guardarUsuario(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('coordinador/usuarios');
        }

        $id = $this->input('id');
        $nombre = $this->input('nombre');
        $apellido = $this->input('apellido');
        $email = $this->input('email');
        $telefono = $this->input('telefono');
        $password = $this->input('password');
        $roles = $this->input('roles', []);
        $asignaturas = $this->input('asignaturas', []);
        $firmaData = $this->input('firma_data');

        $data = [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'telefono' => $telefono,
            'roles' => $roles,
            'asignaturas' => $asignaturas,
            'firma_data' => $firmaData
        ];

        if (!empty($password)) {
            $data['password'] = $password;
        }

        if ($id) {
            $resultado = $this->usuarioService->actualizarUsuario((int)$id, $data);
            
            if ($resultado->isSuccess()) {
                $this->json($resultado->toArray());
            } else {
                $this->json($resultado->toArray());
            }
        } else {
            $resultado = $this->usuarioService->crearUsuario($data);
            
            $response = $resultado->toArray();
            
            if ($response['success']) {
                $this->json($response);
            } else {
                $this->json($response);
            }
        }
    }

    public function eliminarUsuario(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('coordinador/usuarios');
        }

        $jsonInput = file_get_contents('php://input');
        $jsonData = json_decode($jsonInput, true);
        $id = isset($jsonData['id']) ? (int)$jsonData['id'] : 0;
        $reason = isset($jsonData['reason']) ? trim($jsonData['reason']) : 'Sin motivo especificado';

        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID de usuario inválido']);
        }

        if (empty($reason)) {
            $this->json(['success' => false, 'message' => 'El motivo de eliminación es requerido']);
        }

        $resultado = $this->usuarioService->softDeleteUsuario($id, $reason);
        $this->json($resultado->toArray());
    }

    public function restaurarUsuario(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('coordinador/usuarios-eliminados');
        }

        $jsonInput = file_get_contents('php://input');
        $jsonData = json_decode($jsonInput, true);
        $id = isset($jsonData['id']) ? (int)$jsonData['id'] : 0;

        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID de usuario inválido']);
        }

        $resultado = $this->usuarioService->restaurarUsuario($id);
        $this->json($resultado->toArray());
    }

    public function resetearPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('coordinador/usuarios');
        }

        $jsonInput = file_get_contents('php://input');
        $jsonData = json_decode($jsonInput, true);
        $id = isset($jsonData['id']) ? (int)$jsonData['id'] : 0;

        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID de usuario inválido']);
        }

        $resultado = $this->usuarioService->resetearPassword($id);
        $this->json($resultado->toArray());
    }

    public function cursos(): void
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        $cursos = $this->cursoService->listarTodos();
        $totalEliminados = $this->cursoService->contarEliminados();

        $this->view('coordinador/cursos', [
            'title' => 'Gestión de Cursos',
            'cursos' => $cursos,
            'totalEliminados' => $totalEliminados
        ]);
    }

    public function cursosEliminados(): void
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        $cursosEliminados = $this->cursoService->listarEliminados();

        $this->view('coordinador/cursos-eliminados', [
            'title' => 'Cursos Eliminados',
            'cursosEliminados' => $cursosEliminados
        ]);
    }

    public function obtenerCursos(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $cursos = $this->cursoService->listarTodos();
        $cursosFormateados = array_map(fn($c) => $c->toArray(), $cursos);

        $this->json(['success' => true, 'cursos' => $cursosFormateados]);
    }

    public function buscarCursos(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $query = $this->input('q', '');
        
        if (empty($query)) {
            $cursos = $this->cursoService->listarTodos();
        } else {
            $cursos = $this->cursoService->buscar($query);
        }

        $this->json([
            'success' => true,
            'cursos' => array_map(fn($c) => $c->toArray(), $cursos)
        ]);
    }

    public function buscarCursosEliminados(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $query = $this->input('q', '');
        
        if (empty($query)) {
            $cursos = $this->cursoService->listarEliminados();
        } else {
            $cursos = $this->cursoService->buscarEliminados($query);
        }

        $this->json([
            'success' => true,
            'cursos' => array_map(fn($c) => $c->toArray(), $cursos)
        ]);
    }

    public function guardarCurso(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('coordinador/cursos');
        }

        $id = $this->input('id');
        $data = [
            'nombre' => $this->input('nombre'),
            'nivel' => $this->input('nivel'),
            'seccion' => $this->input('seccion')
        ];

        if ($id) {
            $resultado = $this->cursoService->actualizarCurso((int)$id, $data);
        } else {
            $resultado = $this->cursoService->crearCurso($data);
        }

        $this->json($resultado->toArray());
    }

    public function eliminarCurso(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('coordinador/cursos');
        }

        $jsonInput = file_get_contents('php://input');
        $jsonData = json_decode($jsonInput, true);
        $id = isset($jsonData['id']) ? (int)$jsonData['id'] : 0;
        $reason = isset($jsonData['reason']) ? trim($jsonData['reason']) : '';

        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID inválido']);
        }

        $userId = Session::get('user_id');
        $resultado = $this->cursoService->softDeleteCurso($id, $reason, $userId);
        $this->json($resultado->toArray());
    }

    public function restaurarCurso(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('coordinador/cursos-eliminados');
        }

        $jsonInput = file_get_contents('php://input');
        $jsonData = json_decode($jsonInput, true);
        $id = isset($jsonData['id']) ? (int)$jsonData['id'] : 0;

        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID inválido']);
        }

        $resultado = $this->cursoService->restaurarCurso($id);
        $this->json($resultado->toArray());
    }

    public function asignaturas(): void
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        $asignaturas = $this->asignaturaService->listarTodos();
        $totalEliminados = $this->asignaturaService->contarEliminados();

        $this->view('coordinador/asignaturas', [
            'title' => 'Gestión de Asignaturas',
            'asignaturas' => $asignaturas,
            'totalEliminados' => $totalEliminados
        ]);
    }

    public function asignaturasEliminadas(): void
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        $asignaturasEliminadas = $this->asignaturaService->listarEliminados();

        $this->view('coordinador/asignaturas-eliminadas', [
            'title' => 'Asignaturas Eliminadas',
            'asignaturasEliminadas' => $asignaturasEliminadas
        ]);
    }

    public function buscarAsignaturas(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $query = $this->input('q', '');
        
        if (empty($query)) {
            $asignaturas = $this->asignaturaService->listarTodos();
        } else {
            $asignaturas = $this->asignaturaService->buscar($query);
        }

        $this->json([
            'success' => true,
            'asignaturas' => array_map(fn($a) => $a->toArray(), $asignaturas)
        ]);
    }

    public function buscarAsignaturasEliminadas(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $query = $this->input('q', '');
        
        if (empty($query)) {
            $asignaturas = $this->asignaturaService->listarEliminados();
        } else {
            $asignaturas = $this->asignaturaService->buscarEliminados($query);
        }

        $this->json([
            'success' => true,
            'asignaturas' => array_map(fn($a) => $a->toArray(), $asignaturas)
        ]);
    }

    public function guardarAsignatura(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('coordinador/asignaturas');
        }

        $id = $this->input('id');
        $data = [
            'codigo' => $this->input('codigo'),
            'nombre' => $this->input('nombre'),
            'area' => $this->input('area'),
            'horas_semanales' => $this->input('horas_semanales', 0)
        ];

        if ($id) {
            $resultado = $this->asignaturaService->actualizarAsignatura((int)$id, $data);
        } else {
            $resultado = $this->asignaturaService->crearAsignatura($data);
        }

        $this->json($resultado->toArray());
    }

    public function eliminarAsignatura(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('coordinador/asignaturas');
        }

        $jsonInput = file_get_contents('php://input');
        $jsonData = json_decode($jsonInput, true);
        $id = isset($jsonData['id']) ? (int)$jsonData['id'] : 0;
        $reason = isset($jsonData['reason']) ? trim($jsonData['reason']) : '';

        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID inválido']);
        }

        $userId = Session::get('user_id');
        $resultado = $this->asignaturaService->softDeleteAsignatura($id, $reason, $userId);
        $this->json($resultado->toArray());
    }

    public function restaurarAsignatura(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('coordinador/asignaturas-eliminadas');
        }

        $jsonInput = file_get_contents('php://input');
        $jsonData = json_decode($jsonInput, true);
        $id = isset($jsonData['id']) ? (int)$jsonData['id'] : 0;

        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID inválido']);
        }

        $resultado = $this->asignaturaService->restaurarAsignatura($id);
        $this->json($resultado->toArray());
    }

    public function configuracion(): void
    {
        $this->view('coordinador/configuracion', [
            'title' => 'Configuración'
        ]);
    }

    public function guardarConfiguracion(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('coordinador/configuracion');
        }

        $data = [
            'habilitar_horarios' => $this->input('habilitar_horarios', 0),
            'fecha_expiracion' => $this->input('fecha_expiracion'),
            'bloqueo_semanas_atras' => $this->input('bloqueo_semanas_atras', 1),
            'login_max_intentos' => $this->input('login_max_intentos', 5),
            'login_bloqueo_minutos' => $this->input('login_bloqueo_minutos', 15)
        ];

        $resultado = $this->configuracionService->guardar($data);
        $this->json($resultado->toArray());
    }

    public function leccionarios(): void
    {
        $filtros = [
            'fecha_inicio' => $this->input('fecha_inicio', date('Y-m-01')),
            'fecha_fin' => $this->input('fecha_fin', date('Y-m-d')),
            'profesor' => $this->input('profesor'),
            'curso' => $this->input('curso'),
            'estado' => $this->input('estado')
        ];

        $leccionarios = $this->leccionarioService->listarCoordinador($filtros);
        $profesores = $this->leccionarioService->obtenerDocentes();
        $cursos = $this->cursoService->listarTodos();

        $this->view('coordinador/leccionarios', [
            'title' => 'Revisar Leccionarios',
            'leccionarios' => $leccionarios,
            'profesores' => $profesores,
            'cursos' => $cursos,
            'filtros' => $filtros
        ]);
    }

    public function verLeccionario(string $id): void
    {
        $leccionario = $this->leccionarioService->obtenerDetalleCoordinador((int)$id);

        if (!$leccionario) {
            $this->redirect('coordinador/leccionarios');
        }

        $this->view('coordinador/ver-leccionario', [
            'title' => 'Ver Leccionario',
            'leccionario' => $leccionario
        ]);
    }

    public function reportes(): void
    {
        $profesores = $this->leccionarioService->obtenerDocentes();

        $this->view('coordinador/reportes', [
            'title' => 'Reportes',
            'profesores' => $profesores
        ]);
    }

    public function exportarReporte(): void
    {
        $filtros = [
            'fecha_inicio' => $this->input('fecha_inicio', date('Y-m-01')),
            'fecha_fin' => $this->input('fecha_fin', date('Y-m-d')),
            'profesor' => $this->input('profesor_id')
        ];

        $leccionarios = $this->leccionarioService->exportarDatos($filtros);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=leccionarios_' . date('Ymd') . '.csv');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['Fecha', 'Profesor', 'Curso', 'Asignatura', 'Contenido', 'Estado'], ';');

        foreach ($leccionarios as $l) {
            fputcsv($output, [
                $l->fecha,
                $l->nombre . ' ' . $l->apellido,
                $l->curso,
                $l->asignatura,
                $l->contenido,
                $l->estado
            ], ';');
        }

        fclose($output);
        exit;
    }

    public function exportarPdfLeccionario(string $id): void
    {
        require_once dirname(__DIR__) . '/Core/PdfGenerator.php';
        
        $leccionario = $this->leccionarioService->obtenerDetalleCoordinador((int)$id);

        if (!$leccionario) {
            $this->redirect('coordinador/leccionarios');
        }

        $firma = null;
        if ($leccionario->isFirmado()) {
            $firma = $this->usuarioService->getFirma($leccionario->getUsuarioId());
        }

        $firmaRevisor = null;
        $nombreRevisor = null;
        $revisorId = Session::getUserId();
        $revisorFirma = $this->usuarioService->getFirma($revisorId);
        if ($revisorFirma) {
            $firmaRevisor = $revisorFirma;
        }
        
        $revisor = $this->usuarioService->obtenerUsuario($revisorId);
        if ($revisor) {
            $nombreRevisor = $revisor->getNombreCompleto();
        }

        $data = [
            'id' => $leccionario->getId(),
            'profesor' => $leccionario->getProfesorNombreCompleto(),
            'email' => $leccionario->getProfesorEmail(),
            'curso' => $leccionario->getCursoNombre(),
            'seccion' => $leccionario->getSeccion(),
            'asignatura' => $leccionario->getAsignaturaNombre(),
            'fecha' => $leccionario->getFecha(),
            'hora_inicio' => $leccionario->getHoraInicio(),
            'hora_fin' => $leccionario->getHoraFin(),
            'contenido' => $leccionario->getContenido(),
            'observaciones' => $leccionario->getObservaciones(),
            'estado' => $leccionario->getEstado(),
            'firmado' => $leccionario->isFirmado(),
            'fecha_registro' => $leccionario->getFechaRegistro()
        ];

        $pdf = new PdfGenerator();
        $pdfContent = $pdf->generarLeccionario($data, $firma, $firmaRevisor, $nombreRevisor);
        $pdf->enviarRespuesta($pdfContent, 'leccionario_' . $leccionario->getFecha() . '_' . $leccionario->getId() . '.pdf');
    }
}
