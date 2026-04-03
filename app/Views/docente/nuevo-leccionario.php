<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-pen me-2"></i>Registrar Leccionario
                </h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Curso:</strong> <?= $horario->curso ?><br>
                            <strong>Asignatura:</strong> <?= $horario->asignatura ?> (<?= $horario->codigo ?>)
                        </div>
                        <div class="col-md-6">
                            <strong>Fecha:</strong> <?= date('d/m/Y', strtotime($fecha)) ?><br>
                            <strong>Hora:</strong> <?= substr($horario->hora_inicio, 0, 5) ?> - <?= substr($horario->hora_fin, 0, 5) ?>
                            <?php if ($horario->aula): ?>
                            <br><strong>Aula:</strong> <?= $horario->aula ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <form id="leccionarioForm">
                    <input type="hidden" name="horario_id" value="<?= $horario->id ?>">
                    <input type="hidden" name="fecha" value="<?= $fecha ?>">

                    <div class="mb-3">
                        <label for="contenido" class="form-label">
                            Contenido desarrollado <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="contenido" name="contenido" rows="6" required
                                  placeholder="Describe el contenido tratado en clase..."><?= $leccionario->contenido ?? '' ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3"
                                  placeholder="Observaciones adicionales (opcional)..."><?= $leccionario->observaciones ?? '' ?></textarea>
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="firmado" name="firmado" 
                                   <?= ($leccionario->firmado ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="firmado">
                                <i class="fas fa-signature me-1"></i>Confirmo que realicé la clase y los datos son correctos
                            </label>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= route('docente') ?>" class="btn btn-outline-secondary me-md-2">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Guardar Leccionario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var form = document.getElementById('leccionarioForm');
    var BASE_URL = '<?= route() ?>';
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var contenido = document.getElementById('contenido').value.trim();
            if (!contenido) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: 'El contenido desarrollado es obligatorio'
                });
                return;
            }
            
            var btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';
            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', BASE_URL + '/docente/leccionarios/guardar', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('Accept', 'application/json');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save me-1"></i>Guardar Leccionario';
                    
                    if (xhr.status === 200) {
                        try {
                            var data = JSON.parse(xhr.responseText);
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Éxito',
                                    text: data.message
                                }).then(function() {
                                    window.location.href = BASE_URL + '/docente';
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
                horario_id: parseInt(document.querySelector('input[name="horario_id"]').value),
                fecha: document.querySelector('input[name="fecha"]').value,
                contenido: contenido,
                observaciones: document.getElementById('observaciones').value || '',
                firmado: document.getElementById('firmado').checked ? 1 : 0
            };
            
            xhr.send(JSON.stringify(data));
        });
    }
})();
</script>
