<!-- ********** Docente Horario View ********** -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-calendar-week me-2"></i>Mi Horario</h1>
        <p class="text-muted">Período: <?= $periodo ?></p>
    </div>
    <?php if ($horarioEditable): ?>
    <div class="d-flex gap-2">
        <button id="btnGuardarTodo" class="btn btn-success">
            <i class="fas fa-save me-1"></i>Guardar Todo
        </button>
    </div>
    <?php else: ?>
    <div class="alert alert-warning mb-0">
        <i class="fas fa-lock me-1"></i>Horario bloqueado. Contacta al coordinador.
    </div>
    <?php endif; ?>
</div>

<?php if (count($niveles) > 1): ?>
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-4">
                <label for="selectorNivel" class="form-label fw-bold">
                    <i class="fas fa-layer-group me-2"></i>Seleccionar Nivel:
                </label>
            </div>
            <div class="col-md-5">
                <select class="form-select" id="selectorNivel">
                    <?php foreach ($niveles as $nivel): ?>
                    <option value="<?= $nivel->id ?>" <?= $nivel->id == $nivelSeleccionado ? 'selected' : '' ?>>
                        <?= htmlspecialchars($nivel->nombre) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Se mostrarán materias del nivel seleccionado
                </small>
            </div>
        </div>
    </div>
</div>
<?php elseif (count($niveles) == 1): ?>
<div class="alert alert-secondary mb-4">
    <i class="fas fa-layer-group me-2"></i>
    Nivel: <strong><?= htmlspecialchars($niveles[0]->nombre) ?></strong>
</div>
<?php endif; ?>

<?php if ($horarioEditable): ?>
<div class="alert alert-info mb-4">
    <i class="fas fa-edit me-2"></i>Haz clic en una celda vacía para seleccionar una clase. También puedes editar o eliminar clases existentes.
</div>
<?php endif; ?>

<?php
$horas = [
    ['inicio' => '07:55', 'fin' => '08:40'],
    ['inicio' => '08:40', 'fin' => '09:20'],
    ['inicio' => '09:20', 'fin' => '10:00'],
    ['inicio' => '10:00', 'fin' => '10:40'],
    ['inicio' => '10:40', 'fin' => '11:00', 'receso' => true],
    ['inicio' => '11:00', 'fin' => '11:40'],
    ['inicio' => '11:40', 'fin' => '12:20'],
    ['inicio' => '12:20', 'fin' => '13:00'],
    ['inicio' => '13:00', 'fin' => '13:20', 'receso' => true],
    ['inicio' => '13:20', 'fin' => '14:00'],
    ['inicio' => '14:00', 'fin' => '14:45'],
];

$horarioPorCelda = [];
foreach ($horarios as $h) {
    $key = ($h['dia_semana'] ?? 0) . '-' . substr($h['hora_inicio'] ?? '', 0, 5);
    $horarioPorCelda[$key] = $h;
}
?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 80px;">Hora</th>
                        <th>Lunes</th>
                        <th>Martes</th>
                        <th>Miércoles</th>
                        <th>Jueves</th>
                        <th>Viernes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($horas as $index => $hora): ?>
                    <?php 
                    $inicio = $hora['inicio'];
                    $fin = $hora['fin'];
                    $receso = $hora['receso'] ?? false;
                    ?>
                    <tr>
                        <td class="fw-bold<?= $receso ? ' table-secondary' : '' ?>">
                            <?= $inicio ?><br><small class="text-muted"><?= $fin ?></small>
                            <?php if ($receso): ?>
                            <br><span class="badge bg-secondary">RECESO</span>
                            <?php endif; ?>
                        </td>
                        <?php for ($dia = 1; $dia <= 5; $dia++): ?>
                        <?php
                        $key = $dia . '-' . $inicio;
                        $celda = $horarioPorCelda[$key] ?? null;
                        $celdaId = $celda ? ($celda['id'] ?? null) : null;
                        ?>
                        <td class="<?= $receso ? 'table-secondary' : '' ?>" 
                            style="min-width: 150px; min-height: 80px;"
                            data-dia="<?= $dia ?>" 
                            data-inicio="<?= $inicio ?>"
                            data-fin="<?= $fin ?>"
                            data-receso="<?= $receso ? '1' : '0' ?>"
                            data-horario-id="<?= $celdaId ?>">
                            <?php if ($receso): ?>
                            <span class="text-muted"><i class="fas fa-coffee"></i></span>
                            <?php elseif ($celda): ?>
                            <div class="clase-guardada" data-id="<?= $celda['id'] ?? 0 ?>">
                                <strong><?= htmlspecialchars(trim(($celda['curso'] ?? '') . ' ' . ($celda['seccion'] ?? ''))) ?></strong><br>
                                <span class="text-muted small"><?= htmlspecialchars($celda['asignatura'] ?? '') ?></span>
                                <?php if (!empty($celda['aula'])): ?>
                                <br><i class="fas fa-door me-1"></i><?= htmlspecialchars($celda['aula']) ?>
                                <?php endif; ?>
                                <?php if ($horarioEditable): ?>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-primary btn-editar-clase" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-eliminar-clase" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php elseif ($horarioEditable): ?>
                            <button class="btn btn-outline-primary btn-sm btn-agregar-clase w-100">
                                <i class="fas fa-plus"></i> Agregar
                            </button>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <?php endfor; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalClase" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Clase</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="claseDia" value="">
                <input type="hidden" id="claseInicio" value="">
                <input type="hidden" id="claseFin" value="">
                <input type="hidden" id="claseHorarioId" value="">
                
                <p class="mb-2"><strong>Hora:</strong> <span id="claseHora"></span></p>
                
                <div class="mb-3">
                    <label for="claseCurso" class="form-label">Curso *</label>
                    <select class="form-select" id="claseCurso" required>
                        <option value="">Seleccionar curso...</option>
                        <?php foreach ($cursos as $curso): ?>
                        <option value="<?= $curso->id ?>">
                            <?= htmlspecialchars(trim(($curso->nombre ?? '') . ' ' . ($curso->seccion ?? ''))) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="claseAsignatura" class="form-label">Asignatura *</label>
                    <select class="form-select" id="claseAsignatura" required>
                        <option value="">Seleccionar asignatura...</option>
                        <?php foreach ($asignaturas as $asignatura): ?>
                        <option value="<?= $asignatura->id ?>"><?= htmlspecialchars($asignatura->nombre) ?><?= isset($asignatura->nivel_nombre) ? ' (' . htmlspecialchars($asignatura->nivel_nombre) . ')' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="claseAula" class="form-label">Aula (opcional)</label>
                    <input type="text" class="form-control" id="claseAula" placeholder="Ej: A-101">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarClase">
                    <i class="fas fa-save me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var modalClase = null;
    var BASE_URL = '<?= route() ?>';
    var PERIODO = '<?= $periodo ?>';
    
    document.addEventListener('DOMContentLoaded', function() {
        modalClase = new bootstrap.Modal(document.getElementById('modalClase'));
        
        var selectorNivel = document.getElementById('selectorNivel');
        if (selectorNivel) {
            selectorNivel.addEventListener('change', function() {
                var nivelId = this.value;
                cambiarNivel(nivelId);
            });
        }
        
        document.querySelectorAll('.btn-agregar-clase').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var td = this.closest('td');
                abrirModal(td, null);
            });
        });
        
        document.querySelectorAll('.btn-editar-clase').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var td = this.closest('td');
                var claseDiv = this.closest('.clase-guardada');
                var horarioId = claseDiv.dataset.id;
                abrirModal(td, horarioId);
            });
        });
        
        document.querySelectorAll('.btn-eliminar-clase').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var td = this.closest('td');
                var hora = td.dataset.inicio + ' - ' + td.dataset.fin;
                
                Swal.fire({
                    title: '¿Eliminar clase?',
                    text: '¿Eliminar la clase de ' + hora + '?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        eliminarClase(td);
                    }
                });
            });
        });
        
        document.getElementById('btnGuardarClase').addEventListener('click', guardarClase);
        document.getElementById('btnGuardarTodo').addEventListener('click', guardarTodo);
    });
    
    function cambiarNivel(nivelId) {
        fetch(BASE_URL + '/docente/nivel/cambiar', {
            method: 'POST',
            credentials: 'include',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ nivel_id: parseInt(nivelId) })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                location.reload();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
            }
        })
        .catch(function() {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión' });
        });
    }
    
    function abrirModal(td, horarioId) {
        document.getElementById('claseDia').value = td.dataset.dia;
        document.getElementById('claseInicio').value = td.dataset.inicio;
        document.getElementById('claseFin').value = td.dataset.fin;
        document.getElementById('claseHorarioId').value = horarioId || '';
        document.getElementById('claseHora').textContent = td.dataset.inicio + ' - ' + td.dataset.fin;
        document.getElementById('claseAula').value = '';
        
        document.getElementById('claseCurso').selectedIndex = 0;
        document.getElementById('claseAsignatura').selectedIndex = 0;
        
        modalClase.show();
    }
    
    function guardarClase() {
        var dia = document.getElementById('claseDia').value;
        var inicio = document.getElementById('claseInicio').value;
        var fin = document.getElementById('claseFin').value;
        var horarioId = document.getElementById('claseHorarioId').value;
        var cursoId = document.getElementById('claseCurso').value;
        var asignaturaId = document.getElementById('claseAsignatura').value;
        var aula = document.getElementById('claseAula').value;
        
        if (!cursoId || !asignaturaId) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: 'Selecciona curso y asignatura'
            });
            return;
        }
        
        var data = {
            dia: parseInt(dia),
            inicio: inicio,
            fin: fin,
            curso_id: parseInt(cursoId),
            asignatura_id: parseInt(asignaturaId),
            aula: aula || null
        };
        
        fetch(BASE_URL + '/docente/horario/guardar', {
            method: 'POST',
            credentials: 'include',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({periodo: PERIODO, horas: [data]})
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            modalClase.hide();
            if (res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: 'Clase guardada correctamente'
                }).then(function() {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: res.message
                });
            }
        })
        .catch(function(err) {
            modalClase.hide();
            console.error('Fetch error:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error de conexión'
            });
        });
    }
    
    function eliminarClase(td) {
        fetch(BASE_URL + '/docente/horario/eliminar', {
            method: 'POST',
            credentials: 'include',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                dia: parseInt(td.dataset.dia),
                inicio: td.dataset.inicio,
                periodo: PERIODO
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: 'Clase eliminada'
                }).then(function() {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: res.message
                });
            }
        })
        .catch(function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error de conexión'
            });
        });
    }
    
    function guardarTodo() {
        var horas = [];
        var selects = document.querySelectorAll('.celda-editable select');
        
        for (var i = 0; i < selects.length; i++) {
            var select = selects[i];
            var val = select.value;
            
            if (val && val !== '') {
                var td = select.closest('td');
                var parts = val.split('|');
                
                if (parts.length >= 2) {
                    horas.push({
                        dia: parseInt(td.dataset.dia),
                        inicio: td.dataset.inicio,
                        fin: td.dataset.fin,
                        curso_id: parseInt(parts[0]),
                        asignatura_id: parseInt(parts[1])
                    });
                }
            }
        }
        
        if (horas.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin cambios',
                text: 'No has seleccionado ninguna clase'
            });
            return;
        }
        
        fetch(BASE_URL + '/docente/horario/guardar', {
            method: 'POST',
            credentials: 'include',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({periodo: PERIODO, horas: horas})
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: 'Horario guardado: ' + horas.length + ' clase(s)'
                }).then(function() {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: res.message
                });
            }
        })
        .catch(function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error de conexión'
            });
        });
    }
})();
</script>
