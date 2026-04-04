<!-- ********** Coordinador Cursos View ********** -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-graduation-cap me-2"></i>Gestión de Cursos</h1>
        <p class="text-muted">Administración de cursos y niveles</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCurso">
        <i class="fas fa-plus me-1"></i>Nuevo Curso
    </button>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($cursos)): ?>
        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle me-2"></i>No hay cursos registrados.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Abreviatura</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cursos as $c): ?>
                    <?php 
                    $nombreCompleto = trim($c->getNombre() . ' ' . $c->getSeccion());
                    $abreviatura = strtoupper($c->getNivel() . ($c->getSeccion() ? ' ' . $c->getSeccion() : ''));
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($nombreCompleto) ?></td>
                        <td><?= htmlspecialchars($abreviatura) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary btn-editar" 
                                    data-bs-toggle="modal" data-bs-target="#modalCurso"
                                    data-id="<?= $c->getId() ?>"
                                    data-nombre="<?= htmlspecialchars($c->getNombre(), ENT_QUOTES, 'UTF-8') ?>"
                                    data-nivel="<?= htmlspecialchars($c->getNivel(), ENT_QUOTES, 'UTF-8') ?>"
                                    data-seccion="<?= htmlspecialchars($c->getSeccion(), ENT_QUOTES, 'UTF-8') ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="<?= $c->getId() ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="modalCurso" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Curso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCurso">
                <div class="modal-body">
                    <input type="hidden" id="cursoId" name="id">
                    <div class="mb-3">
                        <label for="nivel" class="form-label">Nivel *</label>
                        <select class="form-select" id="nivel" name="nivel" required>
                            <option value="">Seleccione un nivel</option>
                            <option value="1ro">Primero</option>
                            <option value="2do">Segundo</option>
                            <option value="3ro">Tercero</option>
                            <option value="4to">Cuarto</option>
                            <option value="5to">Quinto</option>
                            <option value="6to">Sexto</option>
                            <option value="7mo">Séptimo</option>
                            <option value="8vo">Octavo</option>
                            <option value="9no">Noveno</option>
                            <option value="10mo">Décimo</option>
                            <option value="1ro">Primero de Bachillerato</option>
                            <option value="2do">Segundo de Bachillerato</option>
                            <option value="3ro">Tercero de Bachillerato</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del curso *</label>
                        <select class="form-select" id="nombre" name="nombre" required>
                            <option value="">Seleccione un curso</option>
                            <option value="1RO EGB">1RO EGB</option>
                            <option value="2DO EGB">2DO EGB</option>
                            <option value="3RO EGB">3RO EGB</option>
                            <option value="4TO EGB">4TO EGB</option>
                            <option value="5TO EGB">5TO EGB</option>
                            <option value="6TO EGB">6TO EGB</option>
                            <option value="7MO EGB">7MO EGB</option>
                            <option value="8VO EGB">8VO EGB</option>
                            <option value="9NO EGB">9NO EGB</option>
                            <option value="10MO EGB">10MO EGB</option>
                            <option value="1RO BGU">1RO BGU</option>
                            <option value="2DO BGU">2DO BGU</option>
                            <option value="3RO BGU">3RO BGU</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="seccion" class="form-label">Sección *</label>
                        <select class="form-select" id="seccion" name="seccion" required>
                            <option value="">Seleccione sección</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="E">E</option>                            
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            El nombre del curso se mostrará como: <strong>"NOMBRE + SECCIÓN"</strong> (ej: "8VO EGB A")
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#modalCurso').on('show.bs.modal', function(e) {
        const btn = $(e.relatedTarget);
        const id = btn.data('id');
        
        if (id) {
            $('#cursoId').val(id);
            $('#nombre').val(btn.data('nombre'));
            $('#nivel').val(btn.data('nivel'));
            $('#seccion').val(btn.data('seccion'));
            $('.modal-title').text('Editar Curso');
        } else {
            $('#formCurso')[0].reset();
            $('#cursoId').val('');
            $('.modal-title').text('Nuevo Curso');
        }
    });

    $('#formCurso').on('submit', function(e) {
        e.preventDefault();
        
        const modal = bootstrap.Modal.getInstance($('#modalCurso')[0]);
        
        $.ajax({
            url: '<?= route('coordinador/cursos/guardar') ?>',
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            success: function(response) {
                if (modal) modal.hide();
                setTimeout(function() {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                }, 300);
            },
            error: function(xhr) {
                if (modal) modal.hide();
                let msg = 'Error de conexión';
                try {
                    const res = JSON.parse(xhr.responseText);
                    msg = res.message || msg;
                } catch(e) {}
                setTimeout(function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }, 300);
            }
        });
    });

    $('.btn-eliminar').on('click', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: '¿Eliminar curso?',
            html: 'Esta acción no se puede deshacer fácilmente.<br><br>' +
                  '<div class="mb-3 text-start">' +
                  '<label for="deleteReason" class="form-label">Motivo de eliminación:</label>' +
                  '<textarea class="form-control" id="deleteReason" rows="2" ' +
                  'placeholder="Ej: Curso cerrado, Fusión de cursos, etc."></textarea>' +
                  '</div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            preConfirm: function() {
                const reason = document.getElementById('deleteReason').value.trim();
                if (!reason) {
                    Swal.showValidationMessage('Por favor ingrese el motivo de eliminación');
                    return false;
                }
                return reason;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const reason = result.value;
                $.ajax({
                    url: '<?= route('coordinador/cursos/eliminar') ?>',
                    type: 'POST',
                    contentType: 'application/json',
                    dataType: 'json',
                    data: JSON.stringify({ id: id, reason: reason }),
                    success: function(response) {
                        if (response.success) location.reload();
                        else Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                    },
                    error: function(xhr) {
                        let msg = 'Error de conexión';
                        try {
                            const res = JSON.parse(xhr.responseText);
                            msg = res.message || msg;
                        } catch(e) {}
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    }
                });
            }
        });
    });
});
</script>
