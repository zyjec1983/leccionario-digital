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
                        <th>Nombre</th>
                        <th>Nivel</th>
                        <th>Sección</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cursos as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c->nombre) ?></td>
                        <td><?= htmlspecialchars($c->nivel ?? '-') ?></td>
                        <td><?= htmlspecialchars($c->seccion ?? '-') ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary btn-editar" 
                                    data-bs-toggle="modal" data-bs-target="#modalCurso"
                                    data-id="<?= $c->id ?>"
                                    data-nombre="<?= htmlspecialchars($c->nombre, ENT_QUOTES, 'UTF-8') ?>"
                                    data-nivel="<?= htmlspecialchars($c->nivel ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    data-seccion="<?= htmlspecialchars($c->seccion ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="<?= $c->id ?>">
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
                            <option value="8vo">8vo - Octavo</option>
                            <option value="9no">9no - Noveno</option>
                            <option value="10mo">10mo - Décimo</option>
                            <option value="1ro">1ro - Primero Bachillerato</option>
                            <option value="2do">2do - Segundo Bachillerato</option>
                            <option value="3ro">3ro - Tercero Bachillerato</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del curso *</label>
                        <select class="form-select" id="nombre" name="nombre" required>
                            <option value="">Seleccione un curso</option>
                            <option value="Octavo">Octavo</option>
                            <option value="Noveno">Noveno</option>
                            <option value="Décimo">Décimo</option>
                            <option value="1ero Bachillerato">1ero Bachillerato</option>
                            <option value="2do Bachillerato">2do Bachillerato</option>
                            <option value="3ro Bachillerato">3ro Bachillerato</option>
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
                            <option value="F">F</option>
                            <option value="G">G</option>
                            <option value="H">H</option>
                        </select>
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
            text: 'Los horarios asociados también se verán afectados.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= route('coordinador/cursos/eliminar') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: { id: id },
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
