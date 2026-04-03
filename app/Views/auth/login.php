<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Leccionario Digital</title>
    
    <link id="theme-litera" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/litera/bootstrap.min.css">
    <link id="theme-slate" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/slate/bootstrap.min.css" disabled>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= route('/assets/css/custom.css') ?>">
    <style>
        body.login-bg {
            background-image: url('<?= route('/img/leccionario.jpg') ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
        }
        body.login-bg .card {
            border: none;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.85) !important;
        }
    </style>
</head>
<body class="login-bg">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-lg bg-white bg-opacity-75">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-book-open fa-3x text-primary mb-3"></i>
                            <h3 class="card-title">Leccionario Digital</h3>
                            <p class="text-muted">Ingresa tus credenciales</p>
                        </div>
                        
                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Correo electrónico
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="correo@ejemplo.com" required autofocus>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Contraseña
                                </label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Ingrese su contraseña" required autocomplete="off">
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg" id="btnLogin">
                                    <i class="fas fa-sign-in-alt me-2"></i>Ingresar
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <button id="theme-toggle-btn" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-moon me-1"></i>Cambiar tema
                            </button>
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
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            const btn = $('#btnLogin');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Ingresando...');
            
            $.ajax({
                url: '<?= route('auth/authenticate') ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    email: $('#email').val(),
                    password: $('#password').val()
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Bienvenido!',
                            text: response.message,
                            timer: 1000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = response.redirect;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                        btn.prop('disabled', false).html('<i class="fas fa-sign-in-alt me-2"></i>Ingresar');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-sign-in-alt me-2"></i>Ingresar');
                    
                    let msg = 'Error de conexión';
                    try {
                        const res = JSON.parse(xhr.responseText);
                        if (res.message) {
                            msg = res.message;
                        }
                    } catch(e) {
                        msg = xhr.statusText || 'Error de conexión';
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msg
                    });
                }
            });
        });
    });
    </script>
</body>
</html>
