<!-- ********** Coordinador Asignaturas Eliminadas View ********** -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-book-slash me-2"></i>Asignaturas Eliminadas</h1>
        <p class="text-muted">Asignaturas eliminadas del sistema (soft delete)</p>
    </div>
    <a href="<?= route('/coordinador/asignaturas') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Volver a Asignaturas
    </a>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="buscarEliminadas" placeholder="Buscar por nombre, código o área...">
                </div>
            </div>
            <div class="col-md-6 text-end">
                <span class="badge bg-secondary" id="contadorEliminadas">
                    <?= count($asignaturasEliminadas) ?> eliminadas
                </span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($asignaturasEliminadas)): ?>
        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle me-2"></i>No hay asignaturas eliminadas.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover" id="tablaEliminadas">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Área</th>
                        <th>Fecha de Eliminación</th>
                        <th>Motivo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyEliminadas">
                    <?php foreach ($asignaturasEliminadas as $a): ?>
                    <tr data-nombre="<?= htmlspecialchars(strtolower($a->getNombre())) ?>"
                        data-codigo="<?= htmlspecialchars(strtolower($a->getCodigo())) ?>"
                        data-area="<?= htmlspecialchars(strtolower($a->getArea() ?? '')) ?>"
                        data-motivo="<?= htmlspecialchars(strtolower($a->getDeletedReason() ?? '')) ?>">
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($a->getCodigo()) ?></span></td>
                        <td><?= htmlspecialchars($a->getNombre()) ?></td>
                        <td><?= htmlspecialchars($a->getArea() ?: '-') ?></td>
                        <td>
                            <?php if ($a->getDeletedAt()): ?>
                                <?= date('d/m/Y H:i', strtotime($a->getDeletedAt())) ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($a->getDeletedReason()): ?>
                                <span class="text-muted"><?= htmlspecialchars($a->getDeletedReason()) ?></span>
                            <?php else: ?>
                                <span class="text-muted">Sin especificar</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-success btn-restaurar" 
                                    data-id="<?= $a->getId() ?>"
                                    data-nombre="<?= htmlspecialchars($a->getNombre()) ?>">
                                <i class="fas fa-trash-restore"></i> Restaurar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="alert alert-warning mt-3 mb-0" id="noResults" style="display: none;">
            <i class="fas fa-search me-2"></i>No se encontraron asignaturas eliminadas con ese criterio de búsqueda.
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    var BASE_URL = '<?= route() ?>';
    
    document.addEventListener('DOMContentLoaded', function() {
        var buscarInput = document.getElementById('buscarEliminadas');
        var tbody = document.getElementById('tbodyEliminadas');
        var noResults = document.getElementById('noResults');
        var contadorEliminadas = document.getElementById('contadorEliminadas');
        
        if (buscarInput && tbody) {
            buscarInput.addEventListener('input', function() {
                var query = this.value.toLowerCase().trim();
                var rows = tbody.querySelectorAll('tr');
                var visibleCount = 0;
                
                rows.forEach(function(row) {
                    var nombre = row.dataset.nombre || '';
                    var codigo = row.dataset.codigo || '';
                    var area = row.dataset.area || '';
                    
                    var matches = query === '' || 
                                  nombre.includes(query) || 
                                  codigo.includes(query) || 
                                  area.includes(query);
                    
                    row.style.display = matches ? '' : 'none';
                    if (matches) visibleCount++;
                });
                
                if (noResults) {
                    noResults.style.display = visibleCount === 0 ? 'block' : 'none';
                }
                if (contadorEliminadas) {
                    var total = rows.length;
                    contadorEliminadas.textContent = visibleCount + ' de ' + total + ' eliminadas';
                }
            });
        }
        
        document.querySelectorAll('.btn-restaurar').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.dataset.id;
                var nombre = this.dataset.nombre;
                restaurarAsignatura(id, nombre);
            });
        });
    });
    
    function restaurarAsignatura(id, nombre) {
        Swal.fire({
            title: '¿Restaurar asignatura?',
            html: '¿Está seguro que desea restaurar la asignatura <strong>' + nombre + '</strong>?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, restaurar',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.isConfirmed) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', BASE_URL + '/coordinador/asignaturas/restaurar', true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Restaurada',
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
