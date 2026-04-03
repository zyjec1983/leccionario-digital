<?php

class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started) {
            return;
        }

        $config = Config::get('session');
        
        ini_set('session.cookie_httponly', $config['httponly'] ? '1' : '0');
        ini_set('session.use_strict_mode', '1');
        
        session_name($config['name']);
        
        $basePath = Config::get('base_path', '/leccionario-digital/public');
        session_set_cookie_params([
            'lifetime' => $config['lifetime'],
            'path' => $basePath,
            'secure' => $config['secure'],
            'httponly' => $config['httponly'],
            'samesite' => 'Lax'
        ]);
        
        session_start();
        
        self::$started = true;
        
        if (!isset($_SESSION['_created'])) {
            $_SESSION['_created'] = time();
        }
        
        if (isset($_SESSION['_created']) && (time() - $_SESSION['_created']) > $config['lifetime']) {
            self::destroy();
            self::start();
        }
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, $value = null)
    {
        if ($value === null) {
            $flash = self::get("_flash_{$key}");
            self::forget("_flash_{$key}");
            return $flash;
        }
        self::set("_flash_{$key}", $value);
    }

    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        self::$started = false;
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function isLoggedIn(): bool
    {
        return self::has('user_id');
    }

    public static function getUserId()
    {
        return self::get('user_id');
    }

    public static function getCurrentRole(): ?string
    {
        return self::get('current_role');
    }

    public static function setCurrentRole(string $role): void
    {
        self::set('current_role', $role);
    }
}
