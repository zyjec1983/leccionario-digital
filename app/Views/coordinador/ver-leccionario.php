<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-eye me-2"></i>Detalle del Leccionario</h4>
                <div>
                    <a href="<?= route('coordinador/leccionarios/exportar/' . $leccionario->id) ?>" class="btn btn-danger btn-sm" target="_blank">
                        <i class="fas fa-file-pdf me-1"></i>Exportar PDF
                    </a>
                    <a href="<?= route('coordinador/leccionarios') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Profesor:</strong> <?= $leccionario->nombre ?> <?= $leccionario->apellido ?></p>
                        <p><strong>Email:</strong> <?= $leccionario->email ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Curso:</strong> <?= $leccionario->curso ?></p>
                        <p><strong>Asignatura:</strong> <?= $leccionario->asignatura ?></p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($leccionario->fecha)) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Hora:</strong> <?= substr($leccionario->hora_inicio, 0, 5) ?> - <?= substr($leccionario->hora_fin, 0, 5) ?></p>
                    </div>
                    <div class="col-md-4">
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
                    <div class="col-md-4">
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
                    <div class="col-md-4">
                        <p>
                            <strong>Firma:</strong>
                            <?php if ($leccionario->firmado): ?>
                            <i class="fas fa-check-circle text-success"></i> Confirmado
                            <?php else: ?>
                            <i class="fas fa-times-circle text-muted"></i> No firmado
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <p>
                            <strong>Registrado:</strong>
                            <?= date('d/m/Y H:i', strtotime($leccionario->fecha_registro)) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
