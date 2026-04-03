<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Rol - Leccionario Digital</title>
    
    <link id="theme-litera" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/litera/bootstrap.min.css">
    <link id="theme-slate" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/slate/bootstrap.min.css" disabled>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= route('/assets/css/custom.css') ?>">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-user-circle fa-3x text-primary mb-3"></i>
                            <h3 class="card-title">Selecciona tu rol</h3>
                            <p class="text-muted">¿Cómo deseas trabajar hoy?</p>
                        </div>
                        
                        <form id="roleForm">
                            <div class="d-grid gap-3">
                                <?php foreach ($roles as $index => $rol): 
                                    $rol = (array)$rol;
                                ?>
                                <button type="button" class="btn btn-outline-primary btn-lg role-btn p-4" 
                                        data-role="<?= $rol['slug'] ?>">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <?php if ($rol['slug'] === 'docente'): ?>
                                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                                            <?php else: ?>
                                            <i class="fas fa-user-tie fa-2x"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-start">
                                            <h5 class="mb-1"><?= $rol['nombre'] ?></h5>
                                            <small class="text-muted"><?= $rol['descripcion'] ?? '' ?></small>
                                        </div>
                                    </div>
                                </button>
                                <?php endforeach; ?>
                            </div>
                            
                            <input type="hidden" name="role" id="selectedRole">
                        </form>
                        
                        <div class="text-center mt-4">
                            <a href="<?= route('auth/logout') ?>" class="text-muted">
                                <i class="fas fa-sign-out-alt me-1"></i>Cerrar sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.all.min.js"></script>
    <script src="<?= route('/assets/js/app.js') ?>"></script>
    
    <script>
    $(document).ready(function() {
        $('.role-btn').on('click', function() {
            const role = $(this).data('role');
            
            Swal.fire({
                title: 'Confirmar',
                text: '¿Deseas continuar como ' + role + '?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= route('auth/set-role') ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: { role: role },
                        success: function(response) {
                            if (response.success) {
                                window.location.href = response.redirect;
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error de conexión'
                            });
                        }
                    });
                }
            });
        });
    });
    </script>
</body>
</html>
