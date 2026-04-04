<!-- ********** Docente Leccionarios List ********** -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-list-check me-2"></i>Mis Leccionarios</h1>
        <p class="text-muted">Historial de clases registradas</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= route('docente') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver al Inicio
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtrar por fechas</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= route('docente/leccionarios') ?>" class="row g-3">
            <div class="col-md-4">
                <label for="fecha_inicio" class="form-label">Desde</label>
                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= $filtros['fecha_inicio'] ?? '' ?>">
            </div>
            <div class="col-md-4">
                <label for="fecha_fin" class="form-label">Hasta</label>
                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= $filtros['fecha_fin'] ?? '' ?>">
            </div>
            <div class="col-md-4">
                <label for="estado" class="form-label">Estado</label>
                <select class="form-select" id="estado" name="estado">
                    <option value="">Todos</option>
                    <option value="pendiente" <?= ($filtros['estado'] ?? '') === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="completado" <?= ($filtros['estado'] ?? '') === 'completado' ? 'selected' : '' ?>>Completado</option>
                    <option value="atrasado" <?= ($filtros['estado'] ?? '') === 'atrasado' ? 'selected' : '' ?>>Atrasado</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Buscar
                </button>
                <a href="<?= route('docente/leccionarios') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lecciones</h5>
        <span class="badge bg-primary"><?= count($leccionarios) ?> resultado(s)</span>
    </div>
    <div class="card-body">
        <?php if (empty($leccionarios)): ?>
        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle me-2"></i>No tienes lecciones registradas con esos filtros.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Curso</th>
                        <th>Asignatura</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leccionarios as $leccion): ?>
                    <?php 
                    $esPasado = strtotime($leccion['fecha'] ?? date('Y-m-d')) < strtotime(date('Y-m-d'));
                    $puedeEditar = in_array($leccion['estado'] ?? '', ['pendiente', 'atrasado']);
                    ?>
                    <tr class="<?= ($leccion['estado'] ?? '') === 'atrasado' ? 'table-danger' : (($leccion['estado'] ?? '') === 'pendiente' && $esPasado ? 'table-warning' : '') ?>">
                        <td>
                            <?= date('d/m/Y', strtotime($leccion['fecha'] ?? date('Y-m-d'))) ?>
                            <?php if ($esPasado && ($leccion['estado'] ?? '') !== 'completado'): ?>
                            <i class="fas fa-exclamation-circle text-warning ms-1" title="Fecha pasada"></i>
                            <?php endif; ?>
                        </td>
                        <td><?= substr($leccion['hora_inicio'] ?? '', 0, 5) ?></td>
                        <td><?= htmlspecialchars($leccion['curso_completo'] ?? $leccion['curso'] ?? '') ?></td>
                        <td><?= htmlspecialchars($leccion['asignatura'] ?? '') ?></td>
                        <td>
                            <?php if (($leccion['estado'] ?? '') === 'completado'): ?>
                            <span class="badge bg-success">Completado</span>
                            <?php elseif (($leccion['estado'] ?? '') === 'atrasado'): ?>
                            <span class="badge bg-danger">Atrasado</span>
                            <?php else: ?>
                            <span class="badge bg-warning">Pendiente</span>
                            <?php endif; ?>
                            <?php if (!empty($leccion['firmado'])): ?>
                            <i class="fas fa-signature text-success ms-1" title="Firmado"></i>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($puedeEditar): ?>
                            <a href="<?= route('docente/leccionarios/nuevo/' . ($leccion['horario_id'] ?? 0) . '/' . ($leccion['fecha'] ?? '')) ?>" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-pen"></i> Llenar
                            </a>
                            <?php else: ?>
                            <a href="<?= route('docente/leccionarios/ver/' . ($leccion['id'] ?? 0)) ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPaginas > 1): ?>
        <nav>
            <ul class="pagination justify-content-center mt-4">
                <?php if ($paginaActual > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $paginaActual - 1 ?>&fecha_inicio=<?= $filtros['fecha_inicio'] ?? '' ?>&fecha_fin=<?= $filtros['fecha_fin'] ?? '' ?>&estado=<?= $filtros['estado'] ?? '' ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li class="page-item <?= $i === $paginaActual ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&fecha_inicio=<?= $filtros['fecha_inicio'] ?? '' ?>&fecha_fin=<?= $filtros['fecha_fin'] ?? '' ?>&estado=<?= $filtros['estado'] ?? '' ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                
                <?php if ($paginaActual < $totalPaginas): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $paginaActual + 1 ?>&fecha_inicio=<?= $filtros['fecha_inicio'] ?? '' ?>&fecha_fin=<?= $filtros['fecha_fin'] ?? '' ?>&estado=<?= $filtros['estado'] ?? '' ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="alert alert-info mt-4">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Tip:</strong> Si olvidaste llenar una lección de días anteriores, puedes buscarla usando los filtros de fecha y estado para encontrarla rápidamente.
</div>
