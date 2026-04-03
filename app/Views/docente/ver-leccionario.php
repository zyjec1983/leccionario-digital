<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-eye me-2"></i>Ver Leccionario
                </h4>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Curso:</strong> <?= $leccionario->curso ?></p>
                        <p><strong>Asignatura:</strong> <?= $leccionario->asignatura ?> (<?= $leccionario->codigo ?>)</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($leccionario->fecha)) ?></p>
                        <p><strong>Hora:</strong> <?= substr($leccionario->hora_inicio, 0, 5) ?> - <?= substr($leccionario->hora_fin, 0, 5) ?></p>
                        <?php if ($leccionario->aula): ?>
                        <p><strong>Aula:</strong> <?= $leccionario->aula ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Contenido desarrollado:</label>
                    <div class="p-3 bg-light rounded">
                        <?= nl2br(htmlspecialchars($leccionario->contenido)) ?>
                    </div>
                </div>

                <?php if ($leccionario->observaciones): ?>
                <div class="mb-4">
                    <label class="form-label fw-bold">Observaciones:</label>
                    <div class="p-3 bg-light rounded">
                        <?= nl2br(htmlspecialchars($leccionario->observaciones)) ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <p>
                            <strong>Estado:</strong>
                            <?php if ($leccionario->estado === 'completado'): ?>
                            <span class="badge bg-success">Completado</span>
                            <?php elseif ($leccionario->estado === 'atrasado'): ?>
                            <span class="badge bg-danger">Atrasado</span>
                            <?php else: ?>
                            <span class="badge bg-warning">Pendiente</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p>
                            <strong>Firma:</strong>
                            <?php if ($leccionario->firmado): ?>
                            <i class="fas fa-check-circle text-success"></i> Confirmado
                            <?php else: ?>
                            <i class="fas fa-times-circle text-muted"></i> No firmado
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="text-muted small mb-4">
                    <i class="fas fa-clock me-1"></i>
                    Registrado el: <?= date('d/m/Y H:i', strtotime($leccionario->fecha_registro)) ?>
                </div>

                                <?php 
                $esPasado = strtotime($leccionario->fecha) < strtotime(date('Y-m-d'));
                $puedeEditar = in_array($leccionario->estado, ['pendiente', 'atrasado']);
                ?>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <?php if ($puedeEditar): ?>
                    <a href="<?= route('docente/leccionarios/nuevo/' . $leccionario->horario_id . '/' . $leccionario->fecha) ?>" class="btn btn-primary">
                        <i class="fas fa-pen me-1"></i>Editar / Llenar
                    </a>
                    <?php endif; ?>
                    <a href="<?= route('docente/leccionarios') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
