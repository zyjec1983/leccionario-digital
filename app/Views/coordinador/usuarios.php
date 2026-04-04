<!-- ********** Coordinador Usuarios View ********** -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-users me-2"></i>Gestión de Usuarios</h1>
        <p class="text-muted">Administración de usuarios del sistema</p>
    </div>
    <button class="btn btn-primary" id="btnNuevoUsuario">
        <i class="fas fa-plus me-1"></i>Nuevo Usuario
    </button>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($usuarios)): ?>
        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle me-2"></i>No hay usuarios registrados.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Roles</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u->getNombre()) ?> <?= htmlspecialchars($u->getApellido()) ?></td>
                        <td><?= htmlspecialchars($u->getEmail()) ?></td>
                        <td><?= $u->getTelefono() ? htmlspecialchars($u->getTelefono()) : '-' ?></td>
                        <td><?= htmlspecialchars(implode(', ', array_map(fn($r) => is_object($r) ? $r->nombre : $r['nombre'], $u->getRoles()))) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary btn-editar" 
                                    data-id="<?= $u->getId() ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning btn-resetear" data-id="<?= $u->getId() ?>" title="Resetear contraseña">
                                <i class="fas fa-key"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="<?= $u->getId() ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formUsuario" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="usuarioId" name="id">
                    <input type="hidden" id="firmaExistente" name="firma_existente" value="0">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <h6 class="text-primary mb-3"><i class="fas fa-user me-2"></i>Datos Personales</h6>
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="apellido" class="form-label">Apellido *</label>
                                <input type="text" class="form-control" id="apellido" name="apellido" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña <span id="passwordHint"></span></label>
                                <input type="password" class="form-control" id="password" name="password">
                            </div>
                            <div class="mb-3" id="firmaPreviewContainer" style="display: none;">
                                <label class="form-label">Firma del docente:</label>
                                <div id="firmaPreviewForm"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <h6 class="text-primary mb-3"><i class="fas fa-id-badge me-2"></i>Roles</h6>
                            <?php if (!empty($roles)): ?>
                            <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                                <?php foreach ($roles as $rol): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roles[]" 
                                           value="<?= is_object($rol) ? $rol->id : $rol['id'] ?>" id="rol_<?= is_object($rol) ? $rol->id : $rol['id'] ?>">
                                    <label class="form-check-label" for="rol_<?= is_object($rol) ? $rol->id : $rol['id'] ?>">
                                        <?= htmlspecialchars(is_object($rol) ? $rol->nombre : $rol['nombre']) ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-warning mb-0 py-2">
                                <i class="fas fa-exclamation-triangle me-1"></i> No hay roles disponibles
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-md-4">
                            <h6 class="text-primary mb-3"><i class="fas fa-book me-2"></i>Asignaturas</h6>
                            <?php if (!empty($asignaturas)): ?>
                            <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                                <?php foreach ($asignaturas as $asig): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="asignaturas[]" 
                                           value="<?= is_object($asig) ? $asig->id : $asig['id'] ?>" id="asig_<?= is_object($asig) ? $asig->id : $asig['id'] ?>">
                                    <label class="form-check-label" for="asig_<?= is_object($asig) ? $asig->id : $asig['id'] ?>">
                                        <?= htmlspecialchars(is_object($asig) ? $asig->nombre : $asig['nombre']) ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-warning mb-0 py-2">
                                <i class="fas fa-exclamation-triangle me-1"></i> No hay asignaturas disponibles
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarUsuario">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalFirma" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-signature me-2"></i>Firma del Docente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="signature-instructions">
                    <i class="fas fa-hand-pointer me-1"></i>Firme con el dedo, mouse o stylus
                </p>
                <div class="signature-canvas-container">
                    <canvas id="signatureCanvas" class="signature-canvas"></canvas>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    <button type="button" class="btn btn-outline-secondary" onclick="clearSignaturePad()">
                        <i class="fas fa-eraser me-1"></i>Limpiar
                    </button>
                </div>
                <div class="firma-preview-container" id="firmaPreview"></div>
                <input type="hidden" id="firma_hidden" name="firma_data">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmFirma()">
                    <i class="fas fa-check me-1"></i>Confirmar Firma
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= route('/assets/js/signature-pad.js') ?>"></script>
<script>
(function() {
    var modal = null;
    var BASE_URL = '<?= route() ?>';
    
    document.addEventListener('DOMContentLoaded', function() {
        modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
        
        document.getElementById('btnNuevoUsuario').addEventListener('click', function() {
            abrirModalNuevo();
        });
        
        document.querySelectorAll('.btn-editar').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.dataset.id;
                abrirModalEditar(id);
            });
        });
        
        document.querySelectorAll('.btn-eliminar').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.dataset.id;
                eliminarUsuario(id);
            });
        });
        
        document.querySelectorAll('.btn-resetear').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.dataset.id;
                resetearPassword(id);
            });
        });
        
        document.getElementById('formUsuario').addEventListener('submit', function(e) {
            e.preventDefault();
            guardarUsuario();
        });
        
        var rolDocenteCheckbox = document.getElementById('rol_1');
        if (rolDocenteCheckbox) {
            rolDocenteCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    var firmaPreviewContainer = document.getElementById('firmaPreviewContainer');
                    if (firmaPreviewContainer) firmaPreviewContainer.style.display = 'block';
                    var modalFirma = document.getElementById('modalFirma');
                    var firmaModalInstance = new bootstrap.Modal(modalFirma);
                    firmaModalInstance.show();
                }
            });
        }
        
        document.querySelectorAll('input[name="roles[]"]').forEach(function(cb) {
            cb.addEventListener('change', function() {
                var rolDocente = document.getElementById('rol_1');
                if (!rolDocente.checked) {
                    var firmaPreviewContainer = document.getElementById('firmaPreviewContainer');
                    var firmaPreviewForm = document.getElementById('firmaPreviewForm');
                    if (firmaPreviewContainer) firmaPreviewContainer.style.display = 'none';
                    if (firmaPreviewForm) firmaPreviewForm.innerHTML = '';
                    clearAllFirma();
                }
            });
        });
    });
    
    function toggleFirmaField() {
        var rolDocente = document.getElementById('rol_1');
        var firmaPreviewContainer = document.getElementById('firmaPreviewContainer');
        var firmaPreviewForm = document.getElementById('firmaPreviewForm');
        
        if (rolDocente && rolDocente.checked) {
            firmaPreviewContainer.style.display = 'block';
            var modalFirma = document.getElementById('modalFirma');
            var firmaModalInstance = new bootstrap.Modal(modalFirma);
            firmaModalInstance.show();
        } else {
            firmaPreviewContainer.style.display = 'none';
            if (firmaPreviewForm) firmaPreviewForm.innerHTML = '';
            clearAllFirma();
        }
    }
    
    function abrirModalNuevo() {
        document.getElementById('formUsuario').reset();
        document.getElementById('usuarioId').value = '';
        document.getElementById('firmaExistente').value = '0';
        document.getElementById('firmaPreview').innerHTML = '';
        document.getElementById('firmaPreviewForm').innerHTML = '';
        document.getElementById('firmaPreviewContainer').style.display = 'none';
        document.getElementById('firma_hidden').value = '';
        document.getElementById('passwordHint').textContent = '(requerido)';
        document.getElementById('modalTitulo').textContent = 'Nuevo Usuario';
        document.getElementById('nombre').required = true;
        
        clearAllFirma();
        deseleccionarTodos();
        modal.show();
    }
    
    function abrirModalEditar(id) {
        deseleccionarTodos();
        document.getElementById('nombre').required = false;
        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', BASE_URL + '/coordinador/usuarios/obtener', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        var u = response.usuario;
                        document.getElementById('usuarioId').value = u.id;
                        document.getElementById('nombre').value = u.nombre;
                        document.getElementById('apellido').value = u.apellido;
                        document.getElementById('email').value = u.email;
                        document.getElementById('telefono').value = u.telefono || '';
                        document.getElementById('password').value = '';
                        document.getElementById('passwordHint').textContent = '(dejar vacío para no cambiar)';
                        document.getElementById('modalTitulo').textContent = 'Editar Usuario';
                        document.getElementById('firmaExistente').value = u.tiene_firma ? '1' : '0';
                        
                        if (u.tiene_firma) {
                            document.getElementById('firmaPreview').innerHTML = '<img src="' + BASE_URL + '/coordinador/usuarios/firma/' + u.id + '" style="max-height: 60px; border: 1px solid #ddd; padding: 5px;">';
                        } else {
                            document.getElementById('firmaPreview').innerHTML = '';
                        }
                        
                        response.roles.forEach(function(rolId) {
                            var cb = document.getElementById('rol_' + rolId);
                            if (cb) cb.checked = true;
                        });
                        
                        response.asignaturas.forEach(function(asigId) {
                            var cb = document.getElementById('asig_' + asigId);
                            if (cb) cb.checked = true;
                        });
                        
                        toggleFirmaField();
                        modal.show();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                    }
                } catch(e) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Error al procesar respuesta' });
                }
            }
        };
        
        xhr.send(JSON.stringify({ id: parseInt(id) }));
    }
    
    function deseleccionarTodos() {
        document.querySelectorAll('input[name="roles[]"]').forEach(function(cb) { cb.checked = false; });
        document.querySelectorAll('input[name="asignaturas[]"]').forEach(function(cb) { cb.checked = false; });
    }
    
    function guardarUsuario() {
        var btn = document.getElementById('btnGuardarUsuario');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';
        
        var roles = [];
        document.querySelectorAll('input[name="roles[]"]:checked').forEach(function(cb) {
            roles.push(cb.value);
        });
        
        var asignaturas = [];
        document.querySelectorAll('input[name="asignaturas[]"]:checked').forEach(function(cb) {
            asignaturas.push(cb.value);
        });
        
        var formData = new FormData();
        formData.append('id', document.getElementById('usuarioId').value || '');
        formData.append('nombre', document.getElementById('nombre').value);
        formData.append('apellido', document.getElementById('apellido').value);
        formData.append('email', document.getElementById('email').value);
        formData.append('telefono', document.getElementById('telefono').value);
        formData.append('password', document.getElementById('password').value);
        formData.append('firma_existente', document.getElementById('firmaExistente').value);
        
        var firmaData = document.getElementById('firma_hidden').value;
        if (firmaData) {
            formData.append('firma_data', firmaData);
        }
        
        roles.forEach(function(r) { formData.append('roles[]', r); });
        asignaturas.forEach(function(a) { formData.append('asignaturas[]', a); });
        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', BASE_URL + '/coordinador/usuarios/guardar', true);
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                btn.disabled = false;
                btn.innerHTML = 'Guardar';
                
                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        modal.hide();
                        clearAllFirma();
                        setTimeout(function() {
                            if (response.success) {
                                if (response.usuario) {
                                    var u = response.usuario;
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Usuario creado exitosamente',
                                        html: '<div class="text-start">' +
                                            '<p><strong>Nombre:</strong> ' + u.nombre + '</p>' +
                                            '<p><strong>Email:</strong> ' + u.email + '</p>' +
                                            '<p><strong>Contraseña temporal:</strong> <code style="background:#f8f9fa;padding:2px 8px;border-radius:4px;font-size:14px;">' + u.password_temporal + '</code></p>' +
                                            '<p><strong>Rol:</strong> ' + u.rol + '</p>' +
                                            '<hr>' +
                                            '<p class="text-muted small mb-0"><i class="fas fa-exclamation-triangle me-1"></i>El usuario deberá cambiar su contraseña en el primer inicio de sesión.</p>' +
                                            '</div>',
                                        confirmButtonText: 'Cerrar'
                                    }).then(function() {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Éxito',
                                        text: response.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(function() {
                                        location.reload();
                                    });
                                }
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                            }
                        }, 300);
                    } catch(e) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Error al procesar respuesta' });
                    }
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión' });
                }
            }
        };
        
        xhr.send(formData);
    }
    
    function eliminarUsuario(id) {
        Swal.fire({
            title: '¿Eliminar usuario?',
            html: 'Esta acción no se puede deshacer fácilmente.<br><br>' +
                  '<div class="mb-3 text-start">' +
                  '<label for="deleteReason" class="form-label">Motivo de eliminación:</label>' +
                  '<textarea class="form-control" id="deleteReason" rows="2" ' +
                  'placeholder="Ej: Usuario renunció, Cambio de personal, etc."></textarea>' +
                  '</div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            preConfirm: function() {
                var reason = document.getElementById('deleteReason').value.trim();
                if (!reason) {
                    Swal.showValidationMessage('Por favor ingrese el motivo de eliminación');
                    return false;
                }
                return reason;
            }
        }).then(function(result) {
            if (result.isConfirmed) {
                var reason = result.value;
                var xhr = new XMLHttpRequest();
                xhr.open('POST', BASE_URL + '/coordinador/usuarios/eliminar', true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Eliminado',
                                    text: response.message
                                }).then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                            }
                        } catch(e) {
                            Swal.fire({ icon: 'error', title: 'Error', text: 'Error al procesar respuesta' });
                        }
                    }
                };
                
                xhr.send(JSON.stringify({ id: parseInt(id), reason: reason }));
            }
        });
    }
    
    function resetearPassword(id) {
        Swal.fire({
            title: '¿Resetear contraseña?',
            html: 'El usuario deberá cambiar su contraseña.<br><br>La nueva contraseña temporal será: <strong>12345</strong>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, resetear',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.isConfirmed) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', BASE_URL + '/coordinador/usuarios/resetear', true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Contraseña reseteada',
                                    text: response.message
                                });
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                            }
                        } catch(e) {
                            Swal.fire({ icon: 'error', title: 'Error', text: 'Error al procesar respuesta' });
                        }
                    }
                };
                
                xhr.send(JSON.stringify({ id: parseInt(id) }));
            }
        });
    }
})();
</script>
