<!-- ********** Header Partial ********** -->
<!DOCTYPE html>
<html lang="es>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Leccionario Digital' ?></title>
    
    <link id="theme-litera" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/litera/bootstrap.min.css">
    <link id="theme-slate" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/slate/bootstrap.min.css" disabled>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= route('/assets/css/custom.css') ?>">
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script src="<?= route('/assets/js/session-timeout.js') ?>"></script>
    
    <script>
        const BASE_URL = '<?= route() ?>';
        
        function showAlert(type, title, message, callback) {
            Swal.fire({
                icon: type,
                title: title,
                text: message,
                confirmButtonText: 'Aceptar'
            }).then(function() {
                if (callback) callback();
            });
        }
        
        function showSuccess(message, callback) {
            showAlert('success', 'Éxito', message, callback);
        }
        
        function showError(message, callback) {
            showAlert('error', 'Error', message, callback);
        }
        
        function showConfirm(title, message, callback) {
            Swal.fire({
                title: title,
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.isConfirmed && callback) callback();
            });
        }
    </script>
</head>
<body style="padding-bottom: 45px;">
    <?php if (isset($user) && $user): ?>
    <nav class="navbar navbar-expand-lg navbar-app sticky-top mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= route() ?>">
                <img src="<?= route('/img/LOGO-ECOMUNDO.png') ?>" alt="Logo Ecomundo" style="max-height: 35px; margin-right: 10px;">
                <span>Leccionario Digital</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if ($current_role && $current_role['slug'] === 'docente'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= route('docente') ?>">
                            <i class="fas fa-home me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= route('docente/horario') ?>">
                            <i class="fas fa-calendar-week me-1"></i>Mi Horario
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= route('docente/leccionarios') ?>">
                            <i class="fas fa-list-check me-1"></i>Leccionarios
                        </a>
                    </li>
                    <?php elseif ($current_role && $current_role['slug'] === 'coordinador'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= route('coordinador') ?>">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i>Gestión
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= route('coordinador/usuarios') ?>">
                                <i class="fas fa-users me-2"></i>Usuarios
                            </a></li>
                            <li><a class="dropdown-item" href="<?= route('coordinador/usuarios-eliminados') ?>">
                                <i class="fas fa-users-slash me-2"></i>Usuarios Eliminados
                            </a></li>
                            <li><a class="dropdown-item" href="<?= route('coordinador/cursos') ?>">
                                <i class="fas fa-graduation-cap me-2"></i>Cursos
                            </a></li>
                            <li><a class="dropdown-item" href="<?= route('coordinador/cursos-eliminados') ?>">
                                <i class="fas fa-graduation-cap me-2"></i>Cursos Eliminados
                            </a></li>
                            <li><a class="dropdown-item" href="<?= route('coordinador/asignaturas') ?>">
                                <i class="fas fa-book me-2"></i>Asignaturas
                            </a></li>
                            <li><a class="dropdown-item" href="<?= route('coordinador/asignaturas-eliminadas') ?>">
                                <i class="fas fa-book me-2"></i>Asignaturas Eliminadas
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= route('coordinador/configuracion') ?>">
                                <i class="fas fa-sliders-h me-2"></i>Configuración
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= route('coordinador/leccionarios') ?>">
                            <i class="fas fa-clipboard-list me-1"></i>Revisar Leccionarios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= route('coordinador/reportes') ?>">
                            <i class="fas fa-chart-bar me-1"></i>Reportes
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i><?= $user->getNombreCompleto() ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <span class="dropdown-item-text text-muted small">
                                    <i class="fas fa-id-badge me-1"></i><?= is_array($current_role) ? $current_role['nombre'] : '' ?>
                                </span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <?php 
                            $roles = Session::get('user_roles', []);
                            if (count($roles) > 1):
                            ?>
                            <li><h6 class="dropdown-header">Cambiar rol</h6></li>
                            <?php foreach ($roles as $r): ?>
                            <?php $r = (array)$r; ?>
                            <?php if ($r['slug'] !== $current_role['slug']): ?>
                            <li>
                                <a class="dropdown-item" href="<?= route('auth/switch-role/' . $r['slug']) ?>">
                                    <i class="fas fa-exchange-alt me-2"></i><?= $r['nombre'] ?>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item" href="<?= route($current_role['slug'] . '/cambiar-password') ?>">
                                    <i class="fas fa-key me-2"></i>Cambiar contraseña
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= route('auth/logout') ?>">
                                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item ms-2">
                        <button id="theme-toggle-btn" class="btn btn-outline-secondary btn-sm" title="Cambiar tema">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <main class="container fade-in">
