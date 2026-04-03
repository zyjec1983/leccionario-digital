<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Configuración General</h4>
            </div>
            <div class="card-body">
                <form id="formConfig">
                    <div class="mb-4">
                        <h5><i class="fas fa-calendar-alt me-2"></i>Edición de Horarios</h5>
                        <hr>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Cuando está habilitada, los docentes pueden definir y modificar su horario semanal.
                            Se recomienda habilitar solo al inicio del período lectivo.
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="habilitar_horarios" name="habilitar_horarios" value="1"
                                       <?= Config::get('habilitar_edicion_horarios') == 1 ? 'checked' : '' ?>>
                                <label class="form-check-label" for="habilitar_horarios">
                                    <strong>Habilitar edición de horarios</strong>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="fecha_expiracion" class="form-label">
                                Fecha de expiración <small class="text-muted">(después de esta fecha se bloquea)</small>
                            </label>
                            <input type="datetime-local" class="form-control" id="fecha_expiracion" name="fecha_expiracion"
                                   value="<?= Config::get('horarios_fecha_expiracion') ?: '' ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5><i class="fas fa-lock me-2"></i>Bloqueo de Leccionarios</h5>
                        <hr>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Los docentes podrán llenar leccionarios de la semana actual y la(s) semana(s) anterior(es) configurada(s). 
                            Las semanas más antiguas se bloquearán automáticamente.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="bloqueo_semanas" class="form-label">
                                    Semanas permitidas hacia atrás <small class="text-muted">(0 = sin bloqueo)</small>
                                </label>
                                <input type="number" class="form-control" id="bloqueo_semanas" name="bloqueo_semanas"
                                       value="<?= (int)Config::get('bloqueo_semanas_atras', 1) ?>" min="0" max="12">
                                <small class="text-muted">
                                    Ej: 1 = solo semana actual, 2 = semana actual + anterior.
                                </small>
                            </div>
                        </div>
                        
                        <?php if ((int)Config::get('bloqueo_semanas_atras', 1) > 0): ?>
                        <div class="alert alert-danger mt-3">
                            <i class="fas fa-ban me-2"></i>
                            <strong>Bloqueo activo:</strong> Leccionarios con más de <?= (int)Config::get('bloqueo_semanas_atras', 1) ?> semana(s) de antigüedad están bloqueados.
                        </div>
                        <?php else: ?>
                        <div class="alert alert-success mt-3">
                            <i class="fas fa-check-circle me-2"></i>
                            No hay bloqueo activo. Los docentes pueden llenar leccionarios de cualquier fecha (excepto futura).
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <h5><i class="fas fa-shield-alt me-2"></i>Seguridad del Login</h5>
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="login_max_intentos" class="form-label">Intentos máximos fallidos</label>
                                <input type="number" class="form-control" id="login_max_intentos" name="login_max_intentos" 
                                       value="<?= (int)Config::get('login_max_intentos', 5) ?>" min="1" max="20">
                                <small class="text-muted">Después de este número de intentos fallidos, se bloquea el acceso.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="login_bloqueo_minutos" class="form-label">Minutos de bloqueo</label>
                                <input type="number" class="form-control" id="login_bloqueo_minutos" name="login_bloqueo_minutos" 
                                       value="<?= (int)Config::get('login_bloqueo_minutos', 15) ?>" min="1" max="120">
                                <small class="text-muted">Tiempo que durará el bloqueo después de exceder los intentos.</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Guardar Configuración
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var form = document.getElementById('formConfig');
    var BASE_URL = '<?= route() ?>';
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        var btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';
        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', BASE_URL + '/coordinador/configuracion/guardar', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-1"></i>Guardar Configuración';
                
                if (xhr.status === 200) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: data.message
                            }).then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message
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
                        text: 'Error de conexión (código: ' + xhr.status + ')'
                    });
                }
            }
        };
        
        var data = {
            habilitar_horarios: document.getElementById('habilitar_horarios').checked ? 1 : 0,
            fecha_expiracion: document.getElementById('fecha_expiracion').value || null,
            bloqueo_semanas_atras: parseInt(document.getElementById('bloqueo_semanas').value) || 0,
            login_max_intentos: parseInt(document.getElementById('login_max_intentos').value) || 5,
            login_bloqueo_minutos: parseInt(document.getElementById('login_bloqueo_minutos').value) || 15
        };
        
        xhr.send(JSON.stringify(data));
    });
})();
</script>
