<div class="row mb-4">
    <div class="col">
        <h1><i class="fas fa-chalkboard-teacher me-2"></i>Bienvenido, <?= $user->nombre ?></h1>
        <p class="text-muted">Panel de docente - <?= date('d/m/Y') ?></p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Lecciones de hoy</h6>
                        <h3 class="mb-0"><?= count($leccionesHoy) ?> / <?= $totalEsperados ?></h3>
                    </div>
                    <div class="text-primary">
                        <i class="fas fa-calendar-day fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Pendientes</h6>
                        <h3 class="mb-0"><?= $pendientes ?></h3>
                    </div>
                    <div class="text-warning">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Completados hoy</h6>
                        <h3 class="mb-0"><?= count(array_filter($leccionesHoy, fn($l) => $l->estado === 'completado')) ?></h3>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list-check me-2"></i>Lecciones de hoy</h5>
        <a href="<?= route('docente/leccionarios') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-history me-1"></i>Ver todos
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($leccionesHoy)): ?>
        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle me-2"></i>
            No tienes lecciones programadas para hoy. 
            <a href="<?= route('docente/horario') ?>" class="alert-link">Configura tu horario</a> para comenzar.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Curso</th>
                        <th>Asignatura</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leccionesHoy as $leccion): ?>
                    <tr>
                        <td><?= substr($leccion->hora_inicio, 0, 5) ?> - <?= substr($leccion->hora_fin, 0, 5) ?></td>
                        <td><?= $leccion->curso ?></td>
                        <td><?= $leccion->asignatura ?></td>
                        <td>
                            <?php if ($leccion->estado === 'completado'): ?>
                            <span class="badge bg-success">Completado</span>
                            <?php elseif ($leccion->estado === 'atrasado'): ?>
                            <span class="badge bg-danger">Atrasado</span>
                            <?php else: ?>
                            <span class="badge bg-warning">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($leccion->estado === 'completado'): ?>
                            <a href="<?= route('docente/leccionarios/ver/' . $leccion->id) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            <?php else: ?>
                            <a href="<?= route('docente/leccionarios/nuevo/' . $leccion->horario_id . '/' . $leccion->fecha) ?>" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-pen"></i> Registrar
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="mt-4">
    <a href="<?= route('docente/horario') ?>" class="btn btn-outline-primary me-2">
        <i class="fas fa-calendar-week me-1"></i>Mi Horario
    </a>
    <a href="<?= route('docente/leccionarios') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-history me-1"></i>Ver todos
    </a>
</div>
