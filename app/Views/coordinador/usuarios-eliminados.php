<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-users-slash me-2"></i>Usuarios Eliminados</h1>
        <p class="text-muted">Usuarios eliminados del sistema (soft delete)</p>
    </div>
    <a href="<?= route('/coordinador/usuarios') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Volver a Usuarios
    </a>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="buscarEliminados" placeholder="Buscar por nombre, email o motivo...">
                </div>
            </div>
            <div class="col-md-6 text-end">
                <span class="badge bg-secondary" id="contadorEliminados">
                    <?= count($usuariosEliminados) ?> eliminados
                </span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($usuariosEliminados)): ?>
        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle me-2"></i>No hay usuarios eliminados.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover" id="tablaEliminados">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Fecha de Eliminación</th>
                        <th>Motivo</th>
                        <th>Eliminado Por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyEliminados">
                    <?php foreach ($usuariosEliminados as $u): ?>
                    <tr data-nombre="<?= htmlspecialchars(strtolower($u->getNombre() . ' ' . $u->getApellido())) ?>"
                        data-email="<?= htmlspecialchars(strtolower($u->getEmail())) ?>"
                        data-motivo="<?= htmlspecialchars(strtolower($u->getDeletedReason() ?? '')) ?>">
                        <td><?= htmlspecialchars($u->getNombre()) ?> <?= htmlspecialchars($u->getApellido()) ?></td>
                        <td><?= htmlspecialchars($u->getEmail()) ?></td>
                        <td>
                            <?php if ($u->getDeletedAt()): ?>
                                <?= date('d/m/Y H:i', strtotime($u->getDeletedAt())) ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u->getDeletedReason()): ?>
                                <span class="text-muted"><?= htmlspecialchars($u->getDeletedReason()) ?></span>
                            <?php else: ?>
                                <span class="text-muted">Sin especificar</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u->getDeletedByNombre()): ?>
                                <?= htmlspecialchars($u->getDeletedByNombre()) ?>
                            <?php else: ?>
                                <span class="text-muted">Sistema</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-success btn-restaurar" 
                                    data-id="<?= $u->getId() ?>"
                                    data-nombre="<?= htmlspecialchars($u->getNombre() . ' ' . $u->getApellido()) ?>">
                                <i class="fas fa-trash-restore"></i> Restaurar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="alert alert-warning mt-3 mb-0" id="noResults" style="display: none;">
            <i class="fas fa-search me-2"></i>No se encontraron usuarios eliminados con ese criterio de búsqueda.
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    var BASE_URL = '<?= route() ?>';
    
    document.addEventListener('DOMContentLoaded', function() {
        var buscarInput = document.getElementById('buscarEliminados');
        var tbody = document.getElementById('tbodyEliminados');
        var noResults = document.getElementById('noResults');
        var contadorEliminados = document.getElementById('contadorEliminados');
        
        if (buscarInput && tbody) {
            buscarInput.addEventListener('input', function() {
                var query = this.value.toLowerCase().trim();
                var rows = tbody.querySelectorAll('tr');
                var visibleCount = 0;
                
                rows.forEach(function(row) {
                    var nombre = row.dataset.nombre || '';
                    var email = row.dataset.email || '';
                    var motivo = row.dataset.motivo || '';
                    
                    var matches = query === '' || 
                                  nombre.includes(query) || 
                                  email.includes(query) || 
                                  motivo.includes(query);
                    
                    row.style.display = matches ? '' : 'none';
                    if (matches) visibleCount++;
                });
                
                if (noResults) {
                    noResults.style.display = visibleCount === 0 ? 'block' : 'none';
                }
                if (contadorEliminados) {
                    var total = rows.length;
                    contadorEliminados.textContent = visibleCount + ' de ' + total + ' eliminados';
                }
            });
        }
        
        document.querySelectorAll('.btn-restaurar').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.dataset.id;
                var nombre = this.dataset.nombre;
                restaurarUsuario(id, nombre);
            });
        });
    });
    
    function restaurarUsuario(id, nombre) {
        Swal.fire({
            title: '¿Restaurar usuario?',
            html: '¿Está seguro que desea restaurar al usuario <strong>' + nombre + '</strong>?<br><br>El usuario podrá volver a iniciar sesión.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, restaurar',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.isConfirmed) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', BASE_URL + '/coordinador/usuarios/restaurar', true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Restaurado',
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
})();
</script>
