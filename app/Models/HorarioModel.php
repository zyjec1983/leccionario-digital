<?php
/** Location: leccionario-digital/app/Models/HorarioModel.php */

class HorarioModel
{
    private ?int $id;
    private int $usuarioId;
    private int $cursoId;
    private int $asignaturaId;
    private int $diaSemana;
    private string $horaInicio;
    private string $horaFin;
    private ?string $aula;
    private string $periodo;
    private bool $activo;

    private ?string $cursoNombre;
    private ?string $seccion;
    private ?string $asignaturaNombre;
    private ?string $asignaturaCodigo;
    private ?int $nivelId;
    private ?string $nivelNombre;

    public function __construct()
    {
        $this->id = null;
        $this->usuarioId = 0;
        $this->cursoId = 0;
        $this->asignaturaId = 0;
        $this->diaSemana = 1;
        $this->horaInicio = '';
        $this->horaFin = '';
        $this->aula = null;
        $this->periodo = date('Y') . '-1';
        $this->activo = true;
        $this->cursoNombre = null;
        $this->seccion = null;
        $this->asignaturaNombre = null;
        $this->asignaturaCodigo = null;
        $this->nivelId = null;
        $this->nivelNombre = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUsuarioId(): int
    {
        return $this->usuarioId;
    }

    public function setUsuarioId(int $usuarioId): void
    {
        $this->usuarioId = $usuarioId;
    }

    public function getCursoId(): int
    {
        return $this->cursoId;
    }

    public function setCursoId(int $cursoId): void
    {
        $this->cursoId = $cursoId;
    }

    public function getAsignaturaId(): int
    {
        return $this->asignaturaId;
    }

    public function setAsignaturaId(int $asignaturaId): void
    {
        $this->asignaturaId = $asignaturaId;
    }

    public function getDiaSemana(): int
    {
        return $this->diaSemana;
    }

    public function setDiaSemana(int $diaSemana): void
    {
        $this->diaSemana = $diaSemana;
    }

    public function getDiaNombre(): string
    {
        $dias = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        return $dias[$this->diaSemana] ?? '';
    }

    public function getHoraInicio(): string
    {
        return $this->horaInicio;
    }

    public function setHoraInicio(string $horaInicio): void
    {
        $this->horaInicio = $horaInicio;
    }

    public function getHoraFin(): string
    {
        return $this->horaFin;
    }

    public function setHoraFin(string $horaFin): void
    {
        $this->horaFin = $horaFin;
    }

    public function getAula(): ?string
    {
        return $this->aula;
    }

    public function setAula(?string $aula): void
    {
        $this->aula = $aula;
    }

    public function getPeriodo(): string
    {
        return $this->periodo;
    }

    public function setPeriodo(string $periodo): void
    {
        $this->periodo = $periodo;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }

    public function getCursoNombre(): ?string
    {
        return $this->cursoNombre;
    }

    public function setCursoNombre(?string $cursoNombre): void
    {
        $this->cursoNombre = $cursoNombre;
    }

    public function getSeccion(): ?string
    {
        return $this->seccion;
    }

    public function setSeccion(?string $seccion): void
    {
        $this->seccion = $seccion;
    }

    public function getAsignaturaNombre(): ?string
    {
        return $this->asignaturaNombre;
    }

    public function setAsignaturaNombre(?string $asignaturaNombre): void
    {
        $this->asignaturaNombre = $asignaturaNombre;
    }

    public function getAsignaturaCodigo(): ?string
    {
        return $this->asignaturaCodigo;
    }

    public function setAsignaturaCodigo(?string $asignaturaCodigo): void
    {
        $this->asignaturaCodigo = $asignaturaCodigo;
    }

    public function getNivelId(): ?int
    {
        return $this->nivelId;
    }

    public function setNivelId(?int $nivelId): void
    {
        $this->nivelId = $nivelId;
    }

    public function getNivelNombre(): ?string
    {
        return $this->nivelNombre;
    }

    public function setNivelNombre(?string $nivelNombre): void
    {
        $this->nivelNombre = $nivelNombre;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'usuario_id' => $this->usuarioId,
            'curso_id' => $this->cursoId,
            'asignatura_id' => $this->asignaturaId,
            'dia_semana' => $this->diaSemana,
            'dia_nombre' => $this->getDiaNombre(),
            'hora_inicio' => $this->horaInicio,
            'hora_fin' => $this->horaFin,
            'aula' => $this->aula,
            'periodo' => $this->periodo,
            'activo' => $this->activo,
            'curso' => $this->cursoNombre,
            'seccion' => $this->seccion,
            'asignatura' => $this->asignaturaNombre,
            'codigo' => $this->asignaturaCodigo,
            'nivel_id' => $this->nivelId ?? null,
            'nivel_nombre' => $this->nivelNombre
        ];
    }

    public static function fromDatabase(object $row): self
    {
        $model = new self();

        $model->id = (int) $row->id;
        $model->usuarioId = (int) $row->usuario_id;
        $model->cursoId = (int) $row->curso_id;
        $model->asignaturaId = (int) $row->asignatura_id;
        $model->diaSemana = (int) $row->dia_semana;
        $model->horaInicio = $row->hora_inicio;
        $model->horaFin = $row->hora_fin;
        $model->aula = $row->aula ?? null;
        $model->periodo = $row->periodo;
        $model->activo = isset($row->activo) ? (int)$row->activo === 1 : true;

        if (isset($row->curso)) {
            $model->cursoNombre = $row->curso;
        }
        if (isset($row->seccion)) {
            $model->seccion = $row->seccion;
        }
        if (isset($row->asignatura)) {
            $model->asignaturaNombre = $row->asignatura;
        }
        if (isset($row->codigo)) {
            $model->asignaturaCodigo = $row->codigo;
        }
        if (isset($row->nivel_id)) {
            $model->nivelId = (int) $row->nivel_id;
        }
        if (isset($row->nivel_nombre)) {
            $model->nivelNombre = $row->nivel_nombre;
        }

        return $model;
    }

    public static function fromArray(array $data): self
    {
        $model = new self();

        if (isset($data['id'])) {
            $model->id = (int) $data['id'];
        }
        $model->usuarioId = (int) ($data['usuario_id'] ?? 0);
        $model->cursoId = (int) ($data['curso_id'] ?? 0);
        $model->asignaturaId = (int) ($data['asignatura_id'] ?? 0);
        $model->diaSemana = (int) ($data['dia_semana'] ?? 1);
        $model->horaInicio = $data['hora_inicio'] ?? '';
        $model->horaFin = $data['hora_fin'] ?? '';
        $model->aula = $data['aula'] ?? null;
        $model->periodo = $data['periodo'] ?? date('Y') . '-1';
        $model->activo = isset($data['activo']) ? (bool)$data['activo'] : true;

        return $model;
    }
}
