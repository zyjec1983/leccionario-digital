<!-- ********** Coordinador Dashboard Index ********** -->
<div class="row mb-4">
    <div class="col">
        <h1><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h1>
        <p class="text-muted">Panel de coordinación - <?= date('d/m/Y') ?></p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4 col-lg-2 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="text-primary mb-2">
                    <i class="fas fa-users fa-2x"></i>
                </div>
                <h3 class="mb-1"><?= $stats['profesores'] ?></h3>
                <small class="text-muted">Profesores</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-lg-2 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="text-success mb-2">
                    <i class="fas fa-graduation-cap fa-2x"></i>
                </div>
                <h3 class="mb-1"><?= $stats['cursos'] ?></h3>
                <small class="text-muted">Cursos</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-lg-2 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="text-info mb-2">
                    <i class="fas fa-calendar-check fa-2x"></i>
                </div>
                <h3 class="mb-1"><?= $stats['leccionesHoy'] ?>/<?= $stats['esperadosHoy'] ?></h3>
                <small class="text-muted">Completados/Hoy</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-lg-2 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="text-warning mb-2">
                    <i class="fas fa-clock fa-2x"></i>
                </div>
                <h3 class="mb-1"><?= $stats['pendientes'] ?></h3>
                <small class="text-muted">Pendientes</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-lg-2 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="text-danger mb-2">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
                <h3 class="mb-1"><?= $stats['atrasados'] ?></h3>
                <small class="text-muted">Atrasados</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-lg-2 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <?php
                $porcentaje = $stats['esperadosHoy'] > 0 
                    ? round(($stats['leccionesHoy'] / $stats['esperadosHoy']) * 100) 
                    : 0;
                ?>
                <div class="text-secondary mb-2">
                    <i class="fas fa-chart-pie fa-2x"></i>
                </div>
                <h3 class="mb-1"><?= $porcentaje ?>%</h3>
                <small class="text-muted">Cumplimiento</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lecciones Recientes</h5>
            </div>
            <div class="card-body">
                <?php if (empty($leccionesRecientes)): ?>
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>No hay lecciones registradas aún.
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
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leccionesRecientes as $l): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($l->getFecha())) ?></td>
                                <td><?= $l->getProfesorNombreCompleto() ?></td>
                                <td><?= $l->getCursoCompleto() ?></td>
                                <td><?= $l->getAsignaturaNombre() ?></td>
                                <td>
                                    <?php if ($l->getEstado() === 'completado'): ?>
                                    <span class="badge bg-success">Completado</span>
                                    <?php elseif ($l->getEstado() === 'atrasado'): ?>
                                    <span class="badge bg-danger">Atrasado</span>
                                    <?php else: ?>
                                    <span class="badge bg-warning">Pendiente</span>
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
    </div>
    
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Acciones Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= route('coordinador/leccionarios') ?>" class="btn btn-outline-primary">
                        <i class="fas fa-clipboard-list me-2"></i>Revisar Leccionarios
                    </a>
                    <a href="<?= route('coordinador/profesores') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-users me-2"></i>Gestionar Profesores
                    </a>
                    <a href="<?= route('coordinador/cursos') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-graduation-cap me-2"></i>Gestionar Cursos
                    </a>
                    <a href="<?= route('coordinador/asignaturas') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-book me-2"></i>Gestionar Asignaturas
                    </a>
                    <a href="<?= route('coordinador/configuracion') ?>" class="btn btn-outline-info">
                        <i class="fas fa-sliders-h me-2"></i>Configuración
                    </a>
                    <a href="<?= route('coordinador/reportes') ?>" class="btn btn-outline-success">
                        <i class="fas fa-chart-bar me-2"></i>Reportes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
