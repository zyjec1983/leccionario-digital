<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-key me-2"></i>Cambiar Contraseña</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($esPrimerLogin)): ?>
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Bienvenido/a</strong>. Esta es su primera sesión. 
                    Debe cambiar su contraseña antes de continuar.
                </div>
                <?php endif; ?>
                
                <form id="formCambiarPassword">
                    <?php if (!empty($mostrarPasswordActual)): ?>
                    <div class="mb-3">
                        <label for="password_actual" class="form-label">Contraseña Actual *</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password_actual" name="password_actual" 
                                   autocomplete="off" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_actual')">
                                <i class="fas fa-eye" id="icon-password_actual"></i>
                            </button>
                        </div>
                    </div>
                    <?php else: ?>
                    <input type="hidden" id="password_actual" name="password_actual" value="">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="nueva_password" class="form-label">Nueva Contraseña *</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="nueva_password" name="nueva_password" 
                                   autocomplete="off" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('nueva_password')">
                                <i class="fas fa-eye" id="icon-nueva_password"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirmar_password" class="form-label">Confirmar Nueva Contraseña *</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirmar_password" name="confirmar_password" 
                                   autocomplete="off" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmar_password')">
                                <i class="fas fa-eye" id="icon-confirmar_password"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="alert alert-secondary">
                        <h6 class="mb-2"><i class="fas fa-shield-alt me-1"></i>Requisitos de la contraseña:</h6>
                        <ul class="mb-0 small">
                            <li id="req-length" class="text-danger">
                                <i class="fas fa-times-circle me-1"></i> Mínimo 5 caracteres
                            </li>
                            <li id="req-uppercase" class="text-danger">
                                <i class="fas fa-times-circle me-1"></i> Al menos 1 mayúscula (A-Z)
                            </li>
                            <li id="req-lowercase" class="text-danger">
                                <i class="fas fa-times-circle me-1"></i> Al menos 1 minúscula (a-z)
                            </li>
                            <li id="req-number" class="text-danger">
                                <i class="fas fa-times-circle me-1"></i> Al menos 1 número (0-9)
                            </li>
                            <li id="req-special" class="text-danger">
                                <i class="fas fa-times-circle me-1"></i> Al menos 1 carácter especial (!@#$%^&*()_+-=)
                            </li>
                            <li id="req-match" class="text-danger">
                                <i class="fas fa-times-circle me-1"></i> Las contraseñas coinciden
                            </li>
                        </ul>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" id="btnGuardar">
                            <i class="fas fa-save me-1"></i>Cambiar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var BASE_URL = '<?= route() ?>';
    var esPrimerLogin = <?= !empty($esPrimerLogin) ? 'true' : 'false' ?>;
    
    function togglePassword(id) {
        var input = document.getElementById(id);
        var icon = document.getElementById('icon-' + id);
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }
    
    function validarRequisitos() {
        var password = document.getElementById('nueva_password').value;
        var confirmar = document.getElementById('confirmar_password').value;
        
        var length = password.length >= 5;
        var uppercase = /[A-Z]/.test(password);
        var lowercase = /[a-z]/.test(password);
        var number = /[0-9]/.test(password);
        var special = /[!@#$%^&*()_+\-=]/.test(password);
        var match = password === confirmar && password.length > 0;
        
        actualizarRequisito('req-length', length);
        actualizarRequisito('req-uppercase', uppercase);
        actualizarRequisito('req-lowercase', lowercase);
        actualizarRequisito('req-number', number);
        actualizarRequisito('req-special', special);
        actualizarRequisito('req-match', match);
        
        return length && uppercase && lowercase && number && special && match;
    }
    
    function actualizarRequisito(id, cumple) {
        var el = document.getElementById(id);
        if (cumple) {
            el.className = 'text-success';
            el.innerHTML = '<i class="fas fa-check-circle me-1"></i>' + el.textContent.substring(el.textContent.indexOf(' '));
        } else {
            el.className = 'text-danger';
            el.innerHTML = '<i class="fas fa-times-circle me-1"></i>' + el.textContent.substring(el.textContent.indexOf(' '));
        }
    }
    
    document.getElementById('nueva_password').addEventListener('input', validarRequisitos);
    document.getElementById('confirmar_password').addEventListener('input', validarRequisitos);
    
    document.getElementById('formCambiarPassword').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validarRequisitos()) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La contraseña no cumple con todos los requisitos'
            });
            return;
        }
        
        var btn = document.getElementById('btnGuardar');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';
        
        var formData = new FormData();
        formData.append('password_actual', document.getElementById('password_actual').value || '');
        formData.append('nueva_password', document.getElementById('nueva_password').value);
        formData.append('confirmar_password', document.getElementById('confirmar_password').value);
        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', BASE_URL + '/docente/cambiar-password/procesar', true);
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-1"></i>Cambiar Contraseña';
                
                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.message
                            }).then(function() {
                                if (response.redirect) {
                                    window.location.href = response.redirect;
                                } else {
                                    window.location.href = BASE_URL;
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    } catch(e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al procesar respuesta'
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error de conexión'
                    });
                }
            }
        };
        
        xhr.send(formData);
    });
    
    window.togglePassword = togglePassword;
})();
</script>
