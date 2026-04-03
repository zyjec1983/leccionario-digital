<?php

function requireRoutes(Router $router): void
{
    $router->get('/', function() {
        if (isLoggedIn()) {
            $role = currentRole();
            if ($role === 'coordinador') {
                redirect('coordinador');
            } else {
                redirect('docente');
            }
        }
        redirect('auth/login');
    });

    $router->get('auth/login', function() {
        requireLogin();
        $ctrl = new AuthController();
        $ctrl->login();
    });
    $router->post('auth/authenticate', 'AuthController@authenticate');
    $router->get('auth/select-role', 'AuthController@selectRole');
    $router->post('auth/set-role', 'AuthController@setRole');
    $router->get('auth/switch-role/{role}', 'AuthController@switchRole');
    $router->get('auth/logout', 'AuthController@logout');
    $router->get('auth/unauthorized', 'AuthController@unauthorized');
    $router->get('auth/extend-session', 'AuthController@extendSession');

    $router->get('docente', function() {
        requireAuth('docente');
        $ctrl = new DocenteController();
        $ctrl->index();
    });

    $router->get('docente/horario', function() {
        requireAuth('docente');
        $ctrl = new DocenteController();
        $ctrl->horario();
    });

    $router->post('docente/horario/guardar', function() {
        requireAuth('docente');
        $ctrl = new DocenteController();
        $ctrl->guardarHorario();
    });

    $router->post('docente/horario/eliminar', function() {
        requireAuth('docente');
        $ctrl = new DocenteController();
        $ctrl->eliminarClase();
    });

    $router->post('docente/nivel/cambiar', function() {
        requireAuth('docente');
        $ctrl = new DocenteController();
        $ctrl->cambiarNivel();
    });

    $router->get('docente/leccionarios', function() {
        requireAuth('docente');
        $ctrl = new DocenteController();
        $ctrl->leccionarios();
    });

    $router->get('docente/leccionarios/nuevo/{horarioId}/{fecha}', function($horarioId, $fecha) {
        requireAuth('docente');
        $ctrl = new DocenteController();
        $ctrl->nuevoLeccionario($horarioId, $fecha);
    });

    $router->post('docente/leccionarios/guardar', function() {
        requireAuth('docente');
        $ctrl = new DocenteController();
        $ctrl->guardarLeccionario();
    });

    $router->get('docente/leccionarios/ver/{id}', function($id) {
        requireAuth('docente');
        $ctrl = new DocenteController();
        $ctrl->verLeccionario($id);
    });

    $router->get('docente/cambiar-password', function() {
        requireAuth('docente');
        $ctrl = new AuthController();
        $ctrl->cambiarPassword();
    });

    $router->post('docente/cambiar-password/procesar', function() {
        requireAuth('docente');
        $ctrl = new AuthController();
        $ctrl->procesarCambioPassword();
    });

    $router->get('coordinador', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->index();
    });

    $router->get('coordinador/usuarios', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->usuarios();
    });

    $router->get('coordinador/usuarios-eliminados', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->usuariosEliminados();
    });

    $router->post('coordinador/usuarios/guardar', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->guardarUsuario();
    });

    $router->post('coordinador/usuarios/eliminar', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->eliminarUsuario();
    });

    $router->post('coordinador/usuarios/restaurar', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->restaurarUsuario();
    });

    $router->post('coordinador/usuarios/buscar', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->buscarUsuarios();
    });

    $router->post('coordinador/usuarios/buscar-eliminados', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->buscarUsuariosEliminados();
    });

    $router->post('coordinador/usuarios/obtener', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->obtenerUsuario();
    });

    $router->get('coordinador/usuarios/firma/{id}', function($id) {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->obtenerFirma($id);
    });

    $router->post('coordinador/usuarios/resetear', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->resetearPassword();
    });

    $router->get('coordinador/cambiar-password', function() {
        requireAuth('coordinador');
        $ctrl = new AuthController();
        $ctrl->cambiarPassword();
    });

    $router->post('coordinador/cambiar-password/procesar', function() {
        requireAuth('coordinador');
        $ctrl = new AuthController();
        $ctrl->procesarCambioPassword();
    });

    $router->get('coordinador/cursos', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->cursos();
    });

    $router->get('coordinador/cursos-eliminados', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->cursosEliminados();
    });

    $router->post('coordinador/cursos/guardar', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->guardarCurso();
    });

    $router->post('coordinador/cursos/eliminar', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->eliminarCurso();
    });

    $router->post('coordinador/cursos/restaurar', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->restaurarCurso();
    });

    $router->post('coordinador/cursos/obtener', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->obtenerCursos();
    });

    $router->post('coordinador/cursos/buscar', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->buscarCursos();
    });

    $router->post('coordinador/cursos/buscar-eliminados', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->buscarCursosEliminados();
    });

    $router->get('coordinador/asignaturas', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->asignaturas();
    });

    $router->get('coordinador/asignaturas-eliminadas', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->asignaturasEliminadas();
    });

    $router->post('coordinador/asignaturas/guardar', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->guardarAsignatura();
    });

    $router->post('coordinador/asignaturas/eliminar', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->eliminarAsignatura();
    });

    $router->post('coordinador/asignaturas/restaurar', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->restaurarAsignatura();
    });

    $router->post('coordinador/asignaturas/buscar', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->buscarAsignaturas();
    });

    $router->post('coordinador/asignaturas/buscar-eliminadas', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->buscarAsignaturasEliminadas();
    });

    $router->get('coordinador/configuracion', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->configuracion();
    });

    $router->post('coordinador/configuracion/guardar', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->guardarConfiguracion();
    });

    $router->get('coordinador/leccionarios', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->leccionarios();
    });

    $router->get('coordinador/leccionarios/ver/{id}', function($id) {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->verLeccionario($id);
    });

    $router->get('coordinador/reportes', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->reportes();
    });

    $router->get('coordinador/reportes/exportar', function() {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->exportarReporte();
    });

    $router->get('coordinador/leccionarios/exportar/{id}', function($id) {
        requireAuth('coordinador');
        $ctrl = new CoordinadorController();
        $ctrl->exportarPdfLeccionario($id);
    });
}
