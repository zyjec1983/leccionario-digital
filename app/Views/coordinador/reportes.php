<!-- ********** Coordinador Reportes View ********** -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-chart-bar me-2"></i>Reportes</h1>
        <p class="text-muted">Generación de reportes y estadísticas</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-csv me-2"></i>Exportar Leccionarios</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Descarga un archivo CSV con los leccionarios según los filtros seleccionados.</p>
                
                <form id="formExportar" method="GET" action="<?= route('coordinador/reportes/exportar') ?>">
                    <div class="mb-3">
                        <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                               value="<?= date('Y-m-01') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="fecha_fin" class="form-label">Fecha fin</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                               value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="profesor_id" class="form-label">Profesor (opcional)</label>
                        <select class="form-select" id="profesor_id" name="profesor_id">
                            <option value="">Todos los profesores</option>
                            <?php foreach ($profesores as $p): ?>
                            <option value="<?= $p->id ?>"><?= $p->nombre ?> <?= $p->apellido ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-download me-1"></i>Descargar CSV
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Resumen Rápido</h5>
            </div>
            <div class="card-body">
                <?php
                $stats = $this->db->fetchAll("
                    SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN estado = 'completado' THEN 1 ELSE 0 END) as completados,
                        SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN estado = 'atrasado' THEN 1 ELSE 0 END) as atrasados
                    FROM leccionarios
                    WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                ");
                $stat = $stats[0] ?? null;
                $total = $stat->total ?? 0;
                $completados = $stat->completados ?? 0;
                $pendientes = $stat->pendientes ?? 0;
                $atrasados = $stat->atrasados ?? 0;
                $porcentaje = $total > 0 ? round(($completados / $total) * 100) : 0;
                ?>
                
                <div class="mb-4">
                    <h6 class="text-muted mb-3">Últimos 30 días</h6>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Cumplimiento</span>
                            <span class="fw-bold"><?= $porcentaje ?>%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: <?= $porcentaje ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="h3 mb-0 text-success"><?= $completados ?></div>
                            <small class="text-muted">Completados</small>
                        </div>
                        <div class="col-4">
                            <div class="h3 mb-0 text-warning"><?= $pendientes ?></div>
                            <small class="text-muted">Pendientes</small>
                        </div>
                        <div class="col-4">
                            <div class="h3 mb-0 text-danger"><?= $atrasados ?></div>
                            <small class="text-muted">Atrasados</small>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="text-center">
                    <p class="text-muted mb-2">Total leccionarios (30 días)</p>
                    <div class="h2 mb-0"><?= $total ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Cumplimiento por Profesor</h5>
    </div>
    <div class="card-body">
        <?php
        $cumplimiento = $this->db->fetchAll("
            SELECT 
                u.id, u.nombre, u.apellido,
                COUNT(l.id) as total_leccionarios,
                SUM(CASE WHEN l.estado = 'completado' THEN 1 ELSE 0 END) as completados
            FROM usuarios u
            INNER JOIN usuario_roles ur ON u.id = ur.usuario_id
            INNER JOIN roles r ON ur.rol_id = r.id
            LEFT JOIN leccionarios l ON u.id = l.usuario_id 
                AND l.fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            WHERE r.slug = 'docente' AND u.activo = 1
            GROUP BY u.id
            ORDER BY completados DESC
        ");
        ?>
        
        <?php if (empty($cumplimiento)): ?>
        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle me-2"></i>No hay datos de cumplimiento.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Profesor</th>
                        <th>Leccionarios</th>
                        <th>Completados</th>
                        <th>Cumplimiento</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cumplimiento as $c): ?>
                    <?php 
                    $porc = $c->total_leccionarios > 0 
                        ? round(($c->completados / $c->total_leccionarios) * 100) 
                        : 0;
                    ?>
                    <tr>
                        <td><?= $c->nombre ?> <?= $c->apellido ?></td>
                        <td><?= $c->total_leccionarios ?></td>
                        <td><?= $c->completados ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                    <div class="progress-bar <?= $porc >= 80 ? 'bg-success' : ($porc >= 50 ? 'bg-warning' : 'bg-danger') ?>" 
                                         role="progressbar" style="width: <?= $porc ?>%">
                                    </div>
                                </div>
                                <span class="fw-bold"><?= $porc ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
