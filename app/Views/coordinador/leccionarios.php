<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-clipboard-list me-2"></i>Revisar Leccionarios</h1>
        <p class="text-muted">Consulta y auditoría de leccionarios</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= route('coordinador/leccionarios') ?>" class="row g-3">
            <div class="col-md-3">
                <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                       value="<?= $filtros['fecha_inicio'] ?>">
            </div>
            <div class="col-md-3">
                <label for="fecha_fin" class="form-label">Fecha fin</label>
                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                       value="<?= $filtros['fecha_fin'] ?>">
            </div>
            <div class="col-md-3">
                <label for="profesor" class="form-label">Profesor</label>
                <select class="form-select" id="profesor" name="profesor">
                    <option value="">Todos</option>
                    <?php foreach ($profesores as $p): ?>
                    <option value="<?= $p->id ?>" <?= $filtros['profesor'] == $p->id ? 'selected' : '' ?>>
                        <?= $p->nombre ?> <?= $p->apellido ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="estado" class="form-label">Estado</label>
                <select class="form-select" id="estado" name="estado">
                    <option value="">Todos</option>
                    <option value="completado" <?= $filtros['estado'] === 'completado' ? 'selected' : '' ?>>Completado</option>
                    <option value="pendiente" <?= $filtros['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="atrasado" <?= $filtros['estado'] === 'atrasado' ? 'selected' : '' ?>>Atrasado</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Buscar
                </button>
                <a href="<?= route('coordinador/leccionarios') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($leccionarios)): ?>
        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle me-2"></i>No se encontraron leccionarios con los filtros seleccionados.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Profesor</th>
                        <th>Curso</th>
                        <th>Asignatura</th>
                        <th>Contenido</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leccionarios as $l): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($l->fecha)) ?></td>
                        <td><?= $l->nombre ?> <?= $l->apellido ?></td>
                        <td><?= $l->curso ?></td>
                        <td><?= $l->asignatura ?></td>
                        <td>
                            <span title="<?= htmlspecialchars($l->contenido) ?>">
                                <?= substr($l->contenido, 0, 50) ?><?= strlen($l->contenido) > 50 ? '...' : '' ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($l->estado === 'completado'): ?>
                            <span class="badge bg-success">Completado</span>
                            <?php elseif ($l->estado === 'atrasado'): ?>
                            <span class="badge bg-danger">Atrasado</span>
                            <?php else: ?>
                            <span class="badge bg-warning">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= route('coordinador/leccionarios/ver/' . $l->id) ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
