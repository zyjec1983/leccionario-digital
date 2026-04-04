<?php
/** Location: leccionario-digital/app/Services/ConfiguracionService.php */

require_once __DIR__ . '/../Core/Result.php';
require_once __DIR__ . '/../Core/Config.php';
require_once __DIR__ . '/../Repositories/ConfiguracionRepository.php';

class ConfiguracionService
{
    private ConfiguracionRepository $repository;

    public function __construct()
    {
        $this->repository = new ConfiguracionRepository();
    }

    public function obtenerTodos(): array
    {
        return $this->repository->obtenerTodos();
    }

    public function obtener(string $clave, $default = null)
    {
        $valor = $this->repository->obtenerValor($clave, $default);
        
        if ($valor === null) {
            return $this->obtenerDefault($clave, $default);
        }
        
        return $valor;
    }

    private function obtenerDefault(string $clave, $default)
    {
        $defaults = [
            'habilitar_edicion_horarios' => 0,
            'horarios_fecha_expiracion' => null,
            'bloqueo_semanas_atras' => 1,
            'login_max_intentos' => 5,
            'login_bloqueo_minutos' => 15
        ];
        
        return $defaults[$clave] ?? $default;
    }

    public function guardar(array $data): Result
    {
        $configuraciones = [
            'habilitar_edicion_horarios' => (int) ($data['habilitar_horarios'] ?? 0),
            'horarios_fecha_expiracion' => $data['fecha_expiracion'] ?: null,
            'bloqueo_semanas_atras' => (int) ($data['bloqueo_semanas_atras'] ?? 1),
            'login_max_intentos' => (int) ($data['login_max_intentos'] ?? 5),
            'login_bloqueo_minutos' => (int) ($data['login_bloqueo_minutos'] ?? 15)
        ];
        
        $this->repository->guardarMultiple($configuraciones);
        
        foreach ($configuraciones as $clave => $valor) {
            Config::set($clave, $valor);
        }
        
        return Result::success('Configuración guardada correctamente');
    }

    public function recargarEnCache(): void
    {
        $configs = $this->repository->obtenerTodos();
        
        foreach ($configs as $clave => $valor) {
            Config::set($clave, $valor);
        }
    }
}
