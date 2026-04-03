<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-book me-2"></i>Gestión de Asignaturas</h1>
        <p class="text-muted">Administración de materias</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAsignatura">
        <i class="fas fa-plus me-1"></i>Nueva Asignatura
    </button>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($asignaturas)): ?>
        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle me-2"></i>No hay asignaturas registradas.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Área</th>
                        <th>Horas/Semana</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($asignaturas as $a): ?>
                    <tr>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($a->codigo) ?></span></td>
                        <td><?= htmlspecialchars($a->nombre) ?></td>
                        <td><?= htmlspecialchars($a->area ?? '-') ?></td>
                        <td><?= $a->horas_semanales ?: 0 ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary btn-editar" 
                                    data-bs-toggle="modal" data-bs-target="#modalAsignatura"
                                    data-id="<?= $a->id ?>"
                                    data-codigo="<?= htmlspecialchars($a->codigo, ENT_QUOTES, 'UTF-8') ?>"
                                    data-nombre="<?= htmlspecialchars($a->nombre, ENT_QUOTES, 'UTF-8') ?>"
                                    data-area="<?= htmlspecialchars($a->area ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    data-horas="<?= $a->horas_semanales ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="<?= $a->id ?>">
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

<div class="modal fade" id="modalAsignatura" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asignatura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAsignatura">
                <div class="modal-body">
                    <input type="hidden" id="asignaturaId" name="id">
                    <div class="mb-3">
                        <label for="codigo" class="form-label">Código *</label>
                        <input type="text" class="form-control" id="codigo" name="codigo" required 
                               placeholder="Ej: MAT">
                    </div>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required 
                               placeholder="Ej: Matemáticas">
                    </div>
                    <div class="mb-3">
                        <label for="area" class="form-label">Área</label>
                        <input type="text" class="form-control" id="area" name="area" 
                               placeholder="Ej: Ciencias Exactas">
                    </div>
                    <div class="mb-3">
                        <label for="horas_semanales" class="form-label">Horas semanales</label>
                        <input type="number" class="form-control" id="horas_semanales" name="horas_semanales" 
                               value="0" min="0">
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
    $('#modalAsignatura').on('show.bs.modal', function(e) {
        const btn = $(e.relatedTarget);
        const id = btn.data('id');
        
        if (id) {
            $('#asignaturaId').val(id);
            $('#codigo').val(btn.data('codigo'));
            $('#nombre').val(btn.data('nombre'));
            $('#area').val(btn.data('area'));
            $('#horas_semanales').val(btn.data('horas'));
            $('.modal-title').text('Editar Asignatura');
        } else {
            $('#formAsignatura')[0].reset();
            $('#asignaturaId').val('');
            $('.modal-title').text('Nueva Asignatura');
        }
    });

    $('#formAsignatura').on('submit', function(e) {
        e.preventDefault();
        
        const modal = bootstrap.Modal.getInstance($('#modalAsignatura')[0]);
        
        $.ajax({
            url: '<?= route('coordinador/asignaturas/guardar') ?>',
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
                    Swal.fire({ icon: 'error', title: 'Error ' + xhr.status, text: msg });
                }, 300);
            }
        });
    });

    $('.btn-eliminar').on('click', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: '¿Eliminar asignatura?',
            text: 'Las asignaciones a docentes también se eliminarán.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= route('coordinador/asignaturas/eliminar') ?>',
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
