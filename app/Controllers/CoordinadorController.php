<?php

class CoordinadorController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('coordinador');
    }

    public function index(): void
    {
        $totalProfesores = $this->db->fetch(
            "SELECT COUNT(*) as total FROM usuarios u
             INNER JOIN usuario_roles ur ON u.id = ur.usuario_id
             INNER JOIN roles r ON ur.rol_id = r.id
             WHERE r.slug = 'docente' AND u.activo = 1"
        );

        $leccionesHoy = $this->db->fetch(
            "SELECT COUNT(*) as total FROM leccionarios WHERE fecha = CURDATE()"
        );

        $esperadosHoy = $this->db->fetch(
            "SELECT COUNT(*) as total FROM horarios
             WHERE dia_semana = DAYOFWEEK(CURDATE()) - 1 AND activo = 1"
        );

        $pendientes = $this->db->fetch(
            "SELECT COUNT(*) as total FROM leccionarios WHERE estado = 'pendiente'"
        );

        $atrasados = $this->db->fetch(
            "SELECT COUNT(*) as total FROM leccionarios 
             WHERE estado = 'pendiente' AND fecha < CURDATE()"
        );

        $totalCursos = $this->db->fetch(
            "SELECT COUNT(*) as total FROM cursos WHERE activo = 1"
        );

        $leccionesRecientes = $this->db->fetchAll(
            "SELECT l.*, u.nombre, u.apellido, c.nombre as curso, a.nombre as asignatura
             FROM leccionarios l
             INNER JOIN usuarios u ON l.usuario_id = u.id
             INNER JOIN horarios h ON l.horario_id = h.id
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             ORDER BY l.fecha_registro DESC
             LIMIT 10"
        );

        $this->view('coordinador/index', [
            'title' => 'Dashboard',
            'stats' => [
                'profesores' => $totalProfesores->total ?? 0,
                'leccionesHoy' => $leccionesHoy->total ?? 0,
                'esperadosHoy' => $esperadosHoy->total ?? 0,
                'pendientes' => $pendientes->total ?? 0,
                'atrasados' => $atrasados->total ?? 0,
                'cursos' => $totalCursos->total ?? 0
            ],
            'leccionesRecientes' => $leccionesRecientes
        ]);
    }

    public function usuarios(): void
    {
        $usuarios = $this->db->fetchAll(
            "SELECT u.*, GROUP_CONCAT(r.nombre) as roles
             FROM usuarios u
             INNER JOIN usuario_roles ur ON u.id = ur.usuario_id
             INNER JOIN roles r ON ur.rol_id = r.id
             WHERE u.activo = 1
             GROUP BY u.id
             ORDER BY u.nombre, u.apellido"
        );

        $roles = $this->db->fetchAll("SELECT * FROM roles");
        $asignaturas = $this->db->fetchAll("SELECT * FROM asignaturas WHERE activo = 1");

        $this->view('coordinador/usuarios', [
            'title' => 'Gestión de Usuarios',
            'usuarios' => $usuarios,
            'roles' => $roles,
            'asignaturas' => $asignaturas
        ]);
    }

    public function obtenerUsuario(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $id = (int) $this->input('id');
        
        $usuario = $this->db->fetch("SELECT id, nombre, apellido, email, telefono, firma FROM usuarios WHERE id = :id AND activo = 1", ['id' => $id]);
        
        if (!$usuario) {
            $this->json(['success' => false, 'message' => 'Usuario no encontrado']);
        }

        $usuario->tiene_firma = !empty($usuario->firma);

        $userRoles = $this->db->fetchAll(
            "SELECT rol_id FROM usuario_roles WHERE usuario_id = :id",
            ['id' => $id]
        );
        
        $userAsignaturas = $this->db->fetchAll(
            "SELECT asignatura_id FROM asignaturas_docentes WHERE usuario_id = :id",
            ['id' => $id]
        );

        $this->json([
            'success' => true,
            'usuario' => $usuario,
            'roles' => array_map(fn($r) => $r->rol_id, $userRoles),
            'asignaturas' => array_map(fn($a) => $a->asignatura_id, $userAsignaturas)
        ]);
    }

    public function obtenerFirma(string $id): void
    {
        try {
            $usuario = $this->db->fetch("SELECT firma FROM usuarios WHERE id = :id AND activo = 1", ['id' => $id]);
            
            if (!$usuario || empty($usuario->firma)) {
                http_response_code(404);
                exit;
            }

            header('Content-Type: image/png');
            echo $usuario->firma;
            exit;
        } catch (Exception $e) {
            http_response_code(404);
            exit;
        }
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
        $firmaExistente = $this->input('firma_existente', '0');

        if (empty($nombre) || empty($apellido) || empty($email)) {
            $this->json(['success' => false, 'message' => 'Nombre, apellido y email son requeridos']);
        }

        $firmaData = null;
        
        if (isset($_FILES['firma']) && $_FILES['firma']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            $maxSize = 2 * 1024 * 1024;
            
            if (!in_array($_FILES['firma']['type'], $allowedTypes)) {
                $this->json(['success' => false, 'message' => 'La firma debe ser PNG, JPG o JPEG']);
            }
            
            if ($_FILES['firma']['size'] > $maxSize) {
                $this->json(['success' => false, 'message' => 'La firma no debe superar 2MB']);
            }
            
            $firmaData = file_get_contents($_FILES['firma']['tmp_name']);
        } elseif ($this->input('firma_data')) {
            $firmaBase64 = $this->input('firma_data');
            if (preg_match('/^data:image\/(\w+);base64,/', $firmaBase64, $matches)) {
                $firmaData = base64_decode(substr($firmaBase64, strpos($firmaBase64, ',') + 1));
            }
        }

        if ($id) {
            $data = [
                'nombre' => $nombre,
                'apellido' => $apellido,
                'email' => $email,
                'telefono' => $telefono
            ];

            if (!empty($password)) {
                $data['password'] = auth()->hashPassword($password);
            }

            if ($firmaData !== null) {
                $data['firma'] = $firmaData;
            }

            $this->db->update('usuarios', $data, 'id = :id', ['id' => $id]);

            $this->db->delete('usuario_roles', 'usuario_id = :user_id', ['user_id' => $id]);
            foreach ($roles as $rolId) {
                $this->db->insert('usuario_roles', ['usuario_id' => $id, 'rol_id' => $rolId]);
            }

            $this->db->delete('asignaturas_docentes', 'usuario_id = :user_id', ['user_id' => $id]);
            foreach ($asignaturas as $asignaturaId) {
                $this->db->insert('asignaturas_docentes', ['usuario_id' => $id, 'asignatura_id' => $asignaturaId]);
            }

            $this->json(['success' => true, 'message' => 'Usuario actualizado']);
        } else {
            if (empty($password)) {
                $this->json(['success' => false, 'message' => 'La contraseña es requerida']);
            }
            
            $esDocente = false;
            foreach ($roles as $rolId) {
                if ((string)$rolId === '1' || (int)$rolId === 1) {
                    $esDocente = true;
                    break;
                }
            }
            if ($esDocente && empty($firmaData)) {
                $this->json(['success' => false, 'message' => 'Los docentes deben tener una firma. Firme en el canvas.']);
            }

            $existente = $this->db->fetch("SELECT id FROM usuarios WHERE email = :email", ['email' => $email]);
            if ($existente) {
                $this->json(['success' => false, 'message' => 'El email ya está registrado']);
            }

            $data = [
                'nombre' => $nombre,
                'apellido' => $apellido,
                'email' => $email,
                'telefono' => $telefono,
                'password' => auth()->hashPassword($password),
                'activo' => 1
            ];

            if ($firmaData !== null) {
                $data['firma'] = $firmaData;
            }

            $userId = $this->db->insert('usuarios', $data);

            foreach ($roles as $rolId) {
                $this->db->insert('usuario_roles', ['usuario_id' => $userId, 'rol_id' => $rolId]);
            }

            foreach ($asignaturas as $asignaturaId) {
                $this->db->insert('asignaturas_docentes', ['usuario_id' => $userId, 'asignatura_id' => $asignaturaId]);
            }

            $roleNames = [];
            foreach ($roles as $rolId) {
                $rol = $this->db->fetch("SELECT nombre FROM roles WHERE id = :id", ['id' => $rolId]);
                if ($rol) $roleNames[] = $rol->nombre;
            }

            $this->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'usuario' => [
                    'nombre' => $nombre . ' ' . $apellido,
                    'email' => $email,
                    'password_temporal' => '12345',
                    'rol' => implode(', ', $roleNames)
                ]
            ]);
        }
    }

    public function eliminarUsuario(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('coordinador/usuarios');
        }

        $id = (int) $this->input('id');

        if ($id === Session::getUserId()) {
            $this->json(['success' => false, 'message' => 'No puedes eliminarte a ti mismo']);
        }

        $this->db->update('usuarios', ['activo' => 0], 'id = :id', ['id' => $id]);

        $this->json(['success' => true, 'message' => 'Usuario eliminado']);
    }

    public function cursos(): void
    {
        $cursos = $this->db->fetchAll("SELECT * FROM cursos WHERE activo = 1 ORDER BY nivel, seccion");

        $this->view('coordinador/cursos', [
            'title' => 'Gestión de Cursos',
            'cursos' => $cursos
        ]);
    }

    public function guardarCurso(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('coordinador/cursos');
        }

        $id = $this->input('id');
        $nombre = $this->input('nombre');
        $nivel = $this->input('nivel');
        $seccion = $this->input('seccion');

        if (empty($nombre)) {
            $this->json(['success' => false, 'message' => 'El nombre es requerido']);
        }

        if ($id) {
            $this->db->update('cursos', [
                'nombre' => $nombre,
                'nivel' => $nivel,
                'seccion' => $seccion
            ], 'id = :id', ['id' => $id]);

            $this->json(['success' => true, 'message' => 'Curso actualizado']);
        } else {
            $this->db->insert('cursos', [
                'nombre' => $nombre,
                'nivel' => $nivel,
                'seccion' => $seccion,
                'activo' => 1
            ]);

            $this->json(['success' => true, 'message' => 'Curso creado']);
        }
    }

    public function eliminarCurso(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('coordinador/cursos');
        }

        $id = (int) $this->input('id');
        $this->db->update('cursos', ['activo' => 0], 'id = :id', ['id' => $id]);

        $this->json(['success' => true, 'message' => 'Curso eliminado']);
    }

    public function asignaturas(): void
    {
        $asignaturas = $this->db->fetchAll("SELECT * FROM asignaturas WHERE activo = 1 ORDER BY area, nombre");

        $this->view('coordinador/asignaturas', [
            'title' => 'Gestión de Asignaturas',
            'asignaturas' => $asignaturas
        ]);
    }

    public function guardarAsignatura(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('coordinador/asignaturas');
        }

        $id = $this->input('id');
        $codigo = $this->input('codigo');
        $nombre = $this->input('nombre');
        $area = $this->input('area');
        $horas = $this->input('horas_semanales', 0);

        if (empty($codigo) || empty($nombre)) {
            $this->json(['success' => false, 'message' => 'Código y nombre son requeridos']);
        }

        if ($id) {
            $this->db->update('asignaturas', [
                'codigo' => $codigo,
                'nombre' => $nombre,
                'area' => $area,
                'horas_semanales' => $horas
            ], 'id = :id', ['id' => $id]);

            $this->json(['success' => true, 'message' => 'Asignatura actualizada']);
        } else {
            $existente = $this->db->fetch("SELECT id FROM asignaturas WHERE codigo = :codigo", ['codigo' => $codigo]);
            if ($existente) {
                $this->json(['success' => false, 'message' => 'El código ya existe']);
            }

            $this->db->insert('asignaturas', [
                'codigo' => $codigo,
                'nombre' => $nombre,
                'area' => $area,
                'horas_semanales' => $horas,
                'activo' => 1
            ]);

            $this->json(['success' => true, 'message' => 'Asignatura creada']);
        }
    }

    public function eliminarAsignatura(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('coordinador/asignaturas');
        }

        $id = (int) $this->input('id');
        $this->db->update('asignaturas', ['activo' => 0], 'id = :id', ['id' => $id]);

        $this->json(['success' => true, 'message' => 'Asignatura eliminada']);
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

        $habilitarHorarios = (int) $this->input('habilitar_horarios', 0);
        $fechaExpiracion = $this->input('fecha_expiracion');
        $bloqueoSemanas = (int) $this->input('bloqueo_semanas_atras', 1);
        $loginMaxIntentos = (int) $this->input('login_max_intentos', 5);
        $loginBloqueoMinutos = (int) $this->input('login_bloqueo_minutos', 15);

        $this->db->update('configuraciones', ['valor' => $habilitarHorarios], 'clave = :clave', ['clave' => 'habilitar_edicion_horarios']);
        $this->db->update('configuraciones', ['valor' => $fechaExpiracion ?: null], 'clave = :clave', ['clave' => 'horarios_fecha_expiracion']);
        $this->db->update('configuraciones', ['valor' => $bloqueoSemanas], 'clave = :clave', ['clave' => 'bloqueo_semanas_atras']);
        $this->db->update('configuraciones', ['valor' => $loginMaxIntentos], 'clave = :clave', ['clave' => 'login_max_intentos']);
        $this->db->update('configuraciones', ['valor' => $loginBloqueoMinutos], 'clave = :clave', ['clave' => 'login_bloqueo_minutos']);

        Config::set('habilitar_edicion_horarios', $habilitarHorarios);
        Config::set('horarios_fecha_expiracion', $fechaExpiracion);
        Config::set('bloqueo_semanas_atras', $bloqueoSemanas);
        Config::set('login_max_intentos', $loginMaxIntentos);
        Config::set('login_bloqueo_minutos', $loginBloqueoMinutos);

        $this->json(['success' => true, 'message' => 'Configuración guardada correctamente']);
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

        $where = "1=1";
        $params = [];

        if ($filtros['fecha_inicio']) {
            $where .= " AND l.fecha >= :fecha_inicio";
            $params['fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if ($filtros['fecha_fin']) {
            $where .= " AND l.fecha <= :fecha_fin";
            $params['fecha_fin'] = $filtros['fecha_fin'];
        }

        if ($filtros['profesor']) {
            $where .= " AND l.usuario_id = :profesor";
            $params['profesor'] = $filtros['profesor'];
        }

        if ($filtros['curso']) {
            $where .= " AND h.curso_id = :curso";
            $params['curso'] = $filtros['curso'];
        }

        if ($filtros['estado']) {
            $where .= " AND l.estado = :estado";
            $params['estado'] = $filtros['estado'];
        }

        $leccionarios = $this->db->fetchAll(
            "SELECT l.*, u.nombre, u.apellido, c.nombre as curso, a.nombre as asignatura
             FROM leccionarios l
             INNER JOIN usuarios u ON l.usuario_id = u.id
             INNER JOIN horarios h ON l.horario_id = h.id
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             WHERE {$where}
             ORDER BY l.fecha DESC, u.nombre",
            $params
        );

        $profesores = $this->db->fetchAll(
            "SELECT u.id, u.nombre, u.apellido FROM usuarios u
             INNER JOIN usuario_roles ur ON u.id = ur.usuario_id
             INNER JOIN roles r ON ur.rol_id = r.id
             WHERE r.slug = 'docente' AND u.activo = 1"
        );

        $cursos = $this->db->fetchAll("SELECT * FROM cursos WHERE activo = 1");

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
        $leccionario = $this->db->fetch(
            "SELECT l.*, u.nombre, u.apellido, u.email, c.nombre as curso, 
                    a.nombre as asignatura, h.hora_inicio, h.hora_fin, h.aula
             FROM leccionarios l
             INNER JOIN usuarios u ON l.usuario_id = u.id
             INNER JOIN horarios h ON l.horario_id = h.id
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             WHERE l.id = :id",
            ['id' => $id]
        );

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
        $profesores = $this->db->fetchAll(
            "SELECT u.id, u.nombre, u.apellido FROM usuarios u
             INNER JOIN usuario_roles ur ON u.id = ur.usuario_id
             INNER JOIN roles r ON ur.rol_id = r.id
             WHERE r.slug = 'docente' AND u.activo = 1"
        );

        $this->view('coordinador/reportes', [
            'title' => 'Reportes',
            'profesores' => $profesores
        ]);
    }

    public function exportarReporte(): void
    {
        $fechaInicio = $this->input('fecha_inicio', date('Y-m-01'));
        $fechaFin = $this->input('fecha_fin', date('Y-m-d'));
        $profesorId = $this->input('profesor_id');

        $where = "l.fecha BETWEEN :fecha_inicio AND :fecha_fin";
        $params = [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ];

        if ($profesorId) {
            $where .= " AND l.usuario_id = :profesor_id";
            $params['profesor_id'] = $profesorId;
        }

        $leccionarios = $this->db->fetchAll(
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
        
        $leccionario = $this->db->fetch(
            "SELECT l.*, u.nombre as nombre_profesor, u.apellido as apellido_profesor, u.email,
                    c.nombre as curso, a.nombre as asignatura, h.hora_inicio, h.hora_fin
             FROM leccionarios l
             INNER JOIN usuarios u ON l.usuario_id = u.id
             INNER JOIN horarios h ON l.horario_id = h.id
             INNER JOIN cursos c ON h.curso_id = c.id
             INNER JOIN asignaturas a ON h.asignatura_id = a.id
             WHERE l.id = :id",
            ['id' => $id]
        );

        if (!$leccionario) {
            $this->redirect('coordinador/leccionarios');
        }

        $firma = null;
        if ($leccionario->firmado) {
            try {
                $usuario = $this->db->fetch("SELECT firma FROM usuarios WHERE id = :id", ['id' => $leccionario->usuario_id]);
                if ($usuario && !empty($usuario->firma)) {
                    $firma = $usuario->firma;
                }
            } catch (Exception $e) {
                // Columna firma no existe aun
            }
        }

        $data = [
            'id' => $leccionario->id,
            'profesor' => $leccionario->nombre_profesor . ' ' . $leccionario->apellido_profesor,
            'email' => $leccionario->email,
            'curso' => $leccionario->curso,
            'asignatura' => $leccionario->asignatura,
            'fecha' => $leccionario->fecha,
            'hora_inicio' => $leccionario->hora_inicio,
            'hora_fin' => $leccionario->hora_fin,
            'contenido' => $leccionario->contenido,
            'observaciones' => $leccionario->observaciones,
            'estado' => $leccionario->estado,
            'firmado' => $leccionario->firmado,
            'fecha_registro' => $leccionario->fecha_registro
        ];

        $pdf = new PdfGenerator();
        $pdfContent = $pdf->generarLeccionario($data, $firma);
        $pdf->enviarRespuesta($pdfContent, 'leccionario_' . $leccionario->fecha . '_' . $leccionario->id . '.pdf');
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

        if ($id === Session::getUserId()) {
            $this->json(['success' => false, 'message' => 'No puedes resetear tu propia contraseña']);
        }

        if (auth()->resetearPassword($id)) {
            $this->json(['success' => true, 'message' => 'Contraseña reseteada. La nueva contraseña temporal es: 12345']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al resetear la contraseña']);
        }
    }
}
