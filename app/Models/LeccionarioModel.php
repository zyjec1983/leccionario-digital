<?php
/** Location: leccionario-digital/app/Models/LeccionarioModel.php */

class LeccionarioModel
{
    private ?int $id;
    private int $usuarioId;
    private int $horarioId;
    private string $fecha;
    private string $contenido;
    private ?string $observaciones;
    private bool $firmado;
    private string $fechaRegistro;
    private string $estado;

    private ?string $horaInicio;
    private ?string $horaFin;
    private ?string $aula;
    private ?string $cursoNombre;
    private ?string $seccion;
    private ?string $asignaturaNombre;
    private ?string $asignaturaCodigo;
    private ?string $profesorNombre;
    private ?string $profesorApellido;
    private ?string $profesorEmail;

    public function __construct()
    {
        $this->id = null;
        $this->usuarioId = 0;
        $this->horarioId = 0;
        $this->fecha = '';
        $this->contenido = '';
        $this->observaciones = null;
        $this->firmado = false;
        $this->fechaRegistro = '';
        $this->estado = 'pendiente';
        $this->horaInicio = null;
        $this->horaFin = null;
        $this->aula = null;
        $this->cursoNombre = null;
        $this->seccion = null;
        $this->asignaturaNombre = null;
        $this->asignaturaCodigo = null;
        $this->profesorNombre = null;
        $this->profesorApellido = null;
        $this->profesorEmail = null;
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

    public function getHorarioId(): int
    {
        return $this->horarioId;
    }

    public function setHorarioId(int $horarioId): void
    {
        $this->horarioId = $horarioId;
    }

    public function getFecha(): string
    {
        return $this->fecha;
    }

    public function setFecha(string $fecha): void
    {
        $this->fecha = $fecha;
    }

    public function getContenido(): string
    {
        return $this->contenido;
    }

    public function setContenido(string $contenido): void
    {
        $this->contenido = $contenido;
    }

    public function getObservaciones(): ?string
    {
        return $this->observaciones;
    }

    public function setObservaciones(?string $observaciones): void
    {
        $this->observaciones = $observaciones;
    }

    public function isFirmado(): bool
    {
        return $this->firmado;
    }

    public function setFirmado(bool $firmado): void
    {
        $this->firmado = $firmado;
    }

    public function getFechaRegistro(): string
    {
        return $this->fechaRegistro;
    }

    public function setFechaRegistro(string $fechaRegistro): void
    {
        $this->fechaRegistro = $fechaRegistro;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): void
    {
        $this->estado = $estado;
    }

    public function getHoraInicio(): ?string
    {
        return $this->horaInicio;
    }

    public function setHoraInicio(?string $horaInicio): void
    {
        $this->horaInicio = $horaInicio;
    }

    public function getHoraFin(): ?string
    {
        return $this->horaFin;
    }

    public function setHoraFin(?string $horaFin): void
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

    public function getCursoCompleto(): string
    {
        $curso = $this->cursoNombre ?? '';
        $seccion = $this->seccion ?? '';
        if (!empty($seccion)) {
            return trim($curso . ' ' . $seccion);
        }
        return $curso;
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

    public function getProfesorNombre(): ?string
    {
        return $this->profesorNombre;
    }

    public function setProfesorNombre(?string $profesorNombre): void
    {
        $this->profesorNombre = $profesorNombre;
    }

    public function getProfesorApellido(): ?string
    {
        return $this->profesorApellido;
    }

    public function setProfesorApellido(?string $profesorApellido): void
    {
        $this->profesorApellido = $profesorApellido;
    }

    public function getProfesorEmail(): ?string
    {
        return $this->profesorEmail;
    }

    public function setProfesorEmail(?string $profesorEmail): void
    {
        $this->profesorEmail = $profesorEmail;
    }

    public function getProfesorNombreCompleto(): string
    {
        return trim(($this->profesorNombre ?? '') . ' ' . ($this->profesorApellido ?? ''));
    }

    public function isPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    public function isCompletado(): bool
    {
        return $this->estado === 'completado';
    }

    public function isAtrasado(): bool
    {
        return $this->estado === 'atrasado';
    }

    public function estaBloqueado(): bool
    {
        return Security::isFechaBloqueada($this->fecha);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'usuario_id' => $this->usuarioId,
            'horario_id' => $this->horarioId,
            'fecha' => $this->fecha,
            'contenido' => $this->contenido,
            'observaciones' => $this->observaciones,
            'firmado' => $this->firmado,
            'fecha_registro' => $this->fechaRegistro,
            'estado' => $this->estado,
            'hora_inicio' => $this->horaInicio,
            'hora_fin' => $this->horaFin,
            'aula' => $this->aula,
            'curso' => $this->cursoNombre,
            'seccion' => $this->seccion,
            'curso_completo' => $this->getCursoCompleto(),
            'asignatura' => $this->asignaturaNombre,
            'codigo' => $this->asignaturaCodigo,
            'profesor' => $this->getProfesorNombreCompleto(),
            'email' => $this->profesorEmail
        ];
    }

    public static function fromDatabase(object $row): self
    {
        $model = new self();

        $model->id = (int) $row->id;
        $model->usuarioId = (int) $row->usuario_id;
        $model->horarioId = (int) $row->horario_id;
        $model->fecha = $row->fecha;
        $model->contenido = $row->contenido ?? '';
        $model->observaciones = $row->observaciones ?? null;
        $model->firmado = isset($row->firmado) ? (int)$row->firmado === 1 : false;
        $model->fechaRegistro = $row->fecha_registro ?? date('Y-m-d H:i:s');
        $model->estado = $row->estado ?? 'pendiente';

        if (isset($row->hora_inicio)) {
            $model->horaInicio = $row->hora_inicio;
        }
        if (isset($row->hora_fin)) {
            $model->horaFin = $row->hora_fin;
        }
        if (isset($row->aula)) {
            $model->aula = $row->aula;
        }
        if (isset($row->curso)) {
            $model->cursoNombre = $row->curso;
        } elseif (isset($row->curso_nombre)) {
            $model->cursoNombre = $row->curso_nombre;
        }
        if (isset($row->asignatura)) {
            $model->asignaturaNombre = $row->asignatura;
        } elseif (isset($row->asignatura_nombre)) {
            $model->asignaturaNombre = $row->asignatura_nombre;
        }
        if (isset($row->seccion)) {
            $model->seccion = $row->seccion;
        }
        if (isset($row->codigo)) {
            $model->asignaturaCodigo = $row->codigo;
        }
        if (isset($row->nombre)) {
            $model->profesorNombre = $row->nombre;
        }
        if (isset($row->apellido)) {
            $model->profesorApellido = $row->apellido;
        }
        if (isset($row->email)) {
            $model->profesorEmail = $row->email;
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
        $model->horarioId = (int) ($data['horario_id'] ?? 0);
        $model->fecha = $data['fecha'] ?? date('Y-m-d');
        $model->contenido = $data['contenido'] ?? '';
        $model->observaciones = $data['observaciones'] ?? null;
        $model->firmado = isset($data['firmado']) ? (bool)$data['firmado'] : false;
        $model->fechaRegistro = $data['fecha_registro'] ?? date('Y-m-d H:i:s');
        $model->estado = $data['estado'] ?? 'pendiente';

        return $model;
    }
}
