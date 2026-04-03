<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-graduation-cap me-2"></i>Cursos Eliminados</h1>
        <p class="text-muted">Cursos eliminados del sistema (soft delete)</p>
    </div>
    <a href="<?= route('/coordinador/cursos') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Volver a Cursos
    </a>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="buscarEliminados" placeholder="Buscar por nombre o nivel...">
                </div>
            </div>
            <div class="col-md-6 text-end">
                <span class="badge bg-secondary" id="contadorEliminados">
                    <?= count($cursosEliminados) ?> eliminados
                </span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($cursosEliminados)): ?>
        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle me-2"></i>No hay cursos eliminados.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover" id="tablaEliminados">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Abreviatura</th>
                        <th>Fecha de Eliminación</th>
                        <th>Motivo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyEliminados">
                    <?php foreach ($cursosEliminados as $c): ?>
                    <?php 
                    $nombreCompleto = trim($c->getNombre() . ' ' . $c->getSeccion());
                    $abreviatura = strtoupper($c->getNivel() . ($c->getSeccion() ? ' ' . $c->getSeccion() : ''));
                    ?>
                    <tr data-nombre="<?= htmlspecialchars(strtolower($nombreCompleto)) ?>"
                        data-motivo="<?= htmlspecialchars(strtolower($c->getDeletedReason() ?? '')) ?>">
                        <td><?= htmlspecialchars($nombreCompleto) ?></td>
                        <td><?= htmlspecialchars($abreviatura) ?></td>
                        <td>
                            <?php if ($c->getDeletedAt()): ?>
                                <?= date('d/m/Y H:i', strtotime($c->getDeletedAt())) ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($c->getDeletedReason()): ?>
                                <span class="text-muted"><?= htmlspecialchars($c->getDeletedReason()) ?></span>
                            <?php else: ?>
                                <span class="text-muted">Sin especificar</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-success btn-restaurar" 
                                    data-id="<?= $c->getId() ?>"
                                    data-nombre="<?= htmlspecialchars($nombreCompleto) ?>">
                                <i class="fas fa-trash-restore"></i> Restaurar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="alert alert-warning mt-3 mb-0" id="noResults" style="display: none;">
            <i class="fas fa-search me-2"></i>No se encontraron cursos eliminados con ese criterio de búsqueda.
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
                    var matches = query === '' || nombre.includes(query);
                    
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
                restaurarCurso(id, nombre);
            });
        });
    });
    
    function restaurarCurso(id, nombre) {
        Swal.fire({
            title: '¿Restaurar curso?',
            html: '¿Está seguro que desea restaurar el curso <strong>' + nombre + '</strong>?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, restaurar',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.isConfirmed) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', BASE_URL + '/coordinador/cursos/restaurar', true);
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
