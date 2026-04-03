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
                        <td><?= htmlspecialchars($u->nombre) ?> <?= htmlspecialchars($u->apellido) ?></td>
                        <td><?= htmlspecialchars($u->email) ?></td>
                        <td><?= $u->telefono ? htmlspecialchars($u->telefono) : '-' ?></td>
                        <td><?= str_replace(',', ', ', htmlspecialchars($u->roles)) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary btn-editar" 
                                    data-id="<?= $u->id ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning btn-resetear" data-id="<?= $u->id ?>" title="Resetear contraseña">
                                <i class="fas fa-key"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="<?= $u->id ?>">
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
                            <div class="mb-3" id="firmaContainer" style="display: none;">
                                <label for="firma" class="form-label">Firma (solo docentes)</label>
                                <input type="file" class="form-control" id="firma" name="firma" accept="image/png, image/jpeg, image/jpg">
                                <small class="text-muted">PNG, JPG o JPEG. Máx 500KB</small>
                                <div id="firmaPreview" class="mt-2"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <h6 class="text-primary mb-3"><i class="fas fa-id-badge me-2"></i>Roles</h6>
                            <?php if (!empty($roles)): ?>
                            <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                                <?php foreach ($roles as $rol): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roles[]" 
                                           value="<?= $rol->id ?>" id="rol_<?= $rol->id ?>">
                                    <label class="form-check-label" for="rol_<?= $rol->id ?>">
                                        <?= htmlspecialchars($rol->nombre) ?>
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
                                           value="<?= $asig->id ?>" id="asig_<?= $asig->id ?>">
                                    <label class="form-check-label" for="asig_<?= $asig->id ?>">
                                        <?= htmlspecialchars($asig->nombre) ?>
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
        
        document.querySelectorAll('input[name="roles[]"]').forEach(function(cb) {
            cb.addEventListener('change', toggleFirmaField);
        });
        
        toggleFirmaField();
    });
    
    function toggleFirmaField() {
        var rolDocente = document.getElementById('rol_1');
        var firmaContainer = document.getElementById('firmaContainer');
        if (rolDocente && rolDocente.checked) {
            firmaContainer.style.display = 'block';
        } else {
            firmaContainer.style.display = 'none';
            document.getElementById('firma').value = '';
        }
    }
    
    function abrirModalNuevo() {
        document.getElementById('formUsuario').reset();
        document.getElementById('usuarioId').value = '';
        document.getElementById('firmaExistente').value = '0';
        document.getElementById('firmaPreview').innerHTML = '';
        document.getElementById('passwordHint').textContent = '(requerido)';
        document.getElementById('modalTitulo').textContent = 'Nuevo Usuario';
        document.getElementById('nombre').required = true;
        
        deseleccionarTodos();
        toggleFirmaField();
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
        
        var firmaInput = document.getElementById('firma');
        if (firmaInput.files.length > 0) {
            formData.append('firma', firmaInput.files[0]);
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
                        setTimeout(function() {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Éxito',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(function() {
                                    location.reload();
                                });
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
            text: 'Esta acción no se puede deshacer. El usuario quedará inactivo.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.isConfirmed) {
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
                
                xhr.send(JSON.stringify({ id: parseInt(id) }));
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
