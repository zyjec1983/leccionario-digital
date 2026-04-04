<?php
/** Location: leccionario-digital/app/Repositories/ConfiguracionRepository.php */

require_once __DIR__ . '/../Core/Database.php';

class ConfiguracionRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function obtenerPorClave(string $clave): ?array
    {
        $sql = "SELECT * FROM configuraciones WHERE clave = :clave";
        $result = $this->db->fetch($sql, ['clave' => $clave]);
        
        return $result ? (array) $result : null;
    }

    public function obtenerValor(string $clave, $default = null)
    {
        $result = $this->obtenerPorClave($clave);
        return $result ? $result['valor'] : $default;
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM configuraciones ORDER BY clave";
        $results = $this->db->fetchAll($sql);
        
        $configs = [];
        foreach ($results as $row) {
            $configs[$row->clave] = $row->valor;
        }
        
        return $configs;
    }

    public function actualizar(string $clave, $valor): bool
    {
        $result = $this->db->update('configuraciones', [
            'valor' => $valor
        ], 'clave = :clave', ['clave' => $clave]);
        
        return $result > 0;
    }

    public function guardarMultiple(array $configuraciones): bool
    {
        foreach ($configuraciones as $clave => $valor) {
            $this->actualizar($clave, $valor);
        }
        
        return true;
    }
}
