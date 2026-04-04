<?php
/**
 * Location: leccionario-digital/app/Core/Config.php
 */

/**
 * Configuration manager class - handles application settings
 */
class Config
{
    // ********** Properties **********
    private static $config = [];
    private static $dbLoaded = false;

    // ********** Load Methods **********
    public static function load(string $env = 'development'): void
    {
        $configFile = dirname(__DIR__, 2) . '/config/config.php';
        
        if (file_exists($configFile)) {
            self::$config = require $configFile;
        }

        self::$config['environment'] = $env;
        
        self::loadFromDatabase();
    }
    
    private static function loadFromDatabase(): void
    {
        if (self::$dbLoaded) {
            return;
        }
        
        try {
            $db = Database::getInstance();
            $configs = $db->fetchAll("SELECT clave, valor FROM configuraciones");
            foreach ($configs as $config) {
                self::$config[$config->clave] = $config->valor;
            }
            self::$dbLoaded = true;
        } catch (Exception $e) {
            // Si falla la conexion a BD, usar config.php
        }
    }

    // ********** Getter & Setter Methods **********
    public static function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public static function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &self::$config;

        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $config[$k] = $value;
            } else {
                if (!isset($config[$k]) || !is_array($config[$k])) {
                    $config[$k] = [];
                }
                $config = &$config[$k];
            }
        }
    }

    // ********** Utility Methods **********
    public static function basePath(string $path = ''): string
    {
        $base = self::get('base_path', '/leccionario-digital/public');
        return $base . ($path ? '/' . ltrim($path, '/') : '');
    }

    public static function isProduction(): bool
    {
        return self::get('environment') === 'production';
    }
}
