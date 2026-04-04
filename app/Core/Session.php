<?php
/**
 * Location: leccionario-digital/app/Core/Session.php
 */

/**
 * Session class - Manages user sessions with inactivity timeout
 */
class Session
{
    // ********** Properties **********
    private static bool $started = false;
    private static int $inactivityTimeout = 900;

    // ********** Session Lifecycle **********
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
        
        if (self::isLoggedIn()) {
            self::checkInactivityTimeout();
        } else {
            self::regenerate();
        }
    }

    // ********** Inactivity Timeout Methods **********
    private static function checkInactivityTimeout(): void
    {
        $lastActivity = $_SESSION['_last_activity'] ?? null;
        
        if ($lastActivity === null) {
            $_SESSION['_last_activity'] = time();
            return;
        }
        
        if ((time() - $lastActivity) > self::$inactivityTimeout) {
            self::destroy();
            self::start();
            return;
        }
        
        $_SESSION['_last_activity'] = time();
    }

    public static function touch(): void
    {
        $_SESSION['_last_activity'] = time();
    }

    public static function isTimedOut(): bool
    {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        $lastActivity = $_SESSION['_last_activity'] ?? null;
        if ($lastActivity === null) {
            return false;
        }
        
        return (time() - $lastActivity) > self::$inactivityTimeout;
    }

    // ********** Session Data Methods **********
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

    // ********** Session Management **********
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

    // ********** User Session Methods **********
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] !== null;
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
