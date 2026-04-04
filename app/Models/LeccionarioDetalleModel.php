<?php
/** Location: leccionario-digital/app/Models/LeccionarioDetalleModel.php */

class LeccionarioDetalleModel
{
    private int $id;
    private int $usuarioId;
    private string $usuarioNombre;
    private string $usuarioApellido;
    private string $usuarioEmail;
    private int $horarioId;
    private string $fecha;
    private string $contenido;
    private ?string $observaciones;
    private string $estado;
    private bool $firmado;
    private string $fechaRegistro;
    private string $cursoNombre;
    private string $asignaturaNombre;
    private string $horaInicio;
    private string $horaFin;
    private ?string $aula;

    public function __construct(array $data)
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->usuarioId = (int) ($data['usuario_id'] ?? 0);
        $this->usuarioNombre = $data['nombre'] ?? ($data['usuario_nombre'] ?? '');
        $this->usuarioApellido = $data['apellido'] ?? '';
        $this->usuarioEmail = $data['email'] ?? '';
        $this->horarioId = (int) ($data['horario_id'] ?? 0);
        $this->fecha = $data['fecha'] ?? '';
        $this->contenido = $data['contenido'] ?? '';
        $this->observaciones = $data['observaciones'] ?? null;
        $this->estado = $data['estado'] ?? 'pendiente';
        $this->firmado = (bool) ($data['firmado'] ?? false);
        $this->fechaRegistro = $data['fecha_registro'] ?? '';
        $this->cursoNombre = $data['curso'] ?? '';
        $this->asignaturaNombre = $data['asignatura'] ?? '';
        $this->horaInicio = $data['hora_inicio'] ?? '';
        $this->horaFin = $data['hora_fin'] ?? '';
        $this->aula = $data['aula'] ?? null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsuarioId(): int
    {
        return $this->usuarioId;
    }

    public function getUsuarioNombreCompleto(): string
    {
        return $this->usuarioNombre . ' ' . $this->usuarioApellido;
    }

    public function getUsuarioEmail(): string
    {
        return $this->usuarioEmail;
    }

    public function getHorarioId(): int
    {
        return $this->horarioId;
    }

    public function getFecha(): string
    {
        return $this->fecha;
    }

    public function getContenido(): string
    {
        return $this->contenido;
    }

    public function getObservaciones(): ?string
    {
        return $this->observaciones;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function isFirmado(): bool
    {
        return $this->firmado;
    }

    public function getFechaRegistro(): string
    {
        return $this->fechaRegistro;
    }

    public function getCursoNombre(): string
    {
        return $this->cursoNombre;
    }

    public function getAsignaturaNombre(): string
    {
        return $this->asignaturaNombre;
    }

    public function getHoraInicio(): string
    {
        return $this->horaInicio;
    }

    public function getHoraFin(): string
    {
        return $this->horaFin;
    }

    public function getAula(): ?string
    {
        return $this->aula;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'usuario_id' => $this->usuarioId,
            'usuario_nombre' => $this->usuarioNombre,
            'usuario_apellido' => $this->usuarioApellido,
            'usuario_email' => $this->usuarioEmail,
            'horario_id' => $this->horarioId,
            'fecha' => $this->fecha,
            'contenido' => $this->contenido,
            'observaciones' => $this->observaciones,
            'estado' => $this->estado,
            'firmado' => $this->firmado,
            'fecha_registro' => $this->fechaRegistro,
            'curso' => $this->cursoNombre,
            'asignatura' => $this->asignaturaNombre,
            'hora_inicio' => $this->horaInicio,
            'hora_fin' => $this->horaFin,
            'aula' => $this->aula
        ];
    }
}
