<?php
/**
 * Location: leccionario-digital/app/Core/Security.php
 */

/**
 * Security class - Input sanitization and security utilities
 */
class Security
{
    // ********** Sanitization Methods **********
    public static function sanitizeInput(string $input): string
    {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }

    public static function sanitizeEmail(string $email): string
    {
        $email = trim($email);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return strtolower($email);
    }

    public static function sanitizeInt($value): int
    {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    public static function sanitizeString(string $str): string
    {
        $str = trim($str);
        $str = stripslashes($str);
        $str = preg_replace('/[<>]/', '', $str);
        return $str;
    }

    public static function sanitizeDate(string $date): ?string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        return null;
    }

    // ********** IP Address Methods **********
    public static function getClientIP(): string
    {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }

    // ********** Login Attempt Methods **********
    public static function isLoginBlocked(string $ip, string $email = null): bool
    {
        $db = Database::getInstance();
        $maxIntentos = (int) Config::get('login_max_intentos', 5);
        $minutosBloqueo = (int) Config::get('login_bloqueo_minutos', 15);

        $sql = "SELECT intentos, ultimo_intento FROM login_intentos 
                WHERE ip = :ip AND ultimo_intento > DATE_SUB(NOW(), INTERVAL :minutos MINUTE)";
        $params = ['ip' => $ip, 'minutos' => $minutosBloqueo];

        if ($email) {
            $sql .= " OR (email = :email AND ultimo_intento > DATE_SUB(NOW(), INTERVAL :minutos2 MINUTE))";
            $params['email'] = $email;
            $params['minutos2'] = $minutosBloqueo;
        }

        $intentos = $db->fetchAll($sql, $params);

        foreach ($intentos as $intento) {
            if ((int)$intento->intentos >= $maxIntentos) {
                return true;
            }
        }

        return false;
    }

    public static function getLoginAttemptsRemaining(string $ip, string $email = null): int
    {
        $db = Database::getInstance();
        $maxIntentos = (int) Config::get('login_max_intentos', 5);

        $sql = "SELECT MAX(intentos) as max_intentos FROM login_intentos 
                WHERE ip = :ip AND ultimo_intento > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
        $params = ['ip' => $ip];

        if ($email) {
            $sql .= " OR (email = :email AND ultimo_intento > DATE_SUB(NOW(), INTERVAL 15 MINUTE))";
            $params['email'] = $email;
        }

        $result = $db->fetch($sql, $params);
        $actual = (int) ($result->max_intentos ?? 0);

        return max(0, $maxIntentos - $actual);
    }

    public static function recordFailedLogin(string $ip, string $email = null): void
    {
        $db = Database::getInstance();

        $existente = $db->fetch(
            "SELECT id, intentos FROM login_intentos 
             WHERE ip = :ip AND ultimo_intento > DATE_SUB(NOW(), INTERVAL 15 MINUTE)",
            ['ip' => $ip]
        );

        if ($existente) {
            $db->update('login_intentos',
                ['intentos' => $existente->intentos + 1, 'ultimo_intento' => date('Y-m-d H:i:s')],
                'id = :id',
                ['id' => $existente->id]
            );
        } else {
            $db->insert('login_intentos', [
                'ip' => $ip,
                'email' => $email,
                'intentos' => 1,
                'ultimo_intento' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public static function clearLoginAttempts(string $ip, string $email = null): void
    {
        $db = Database::getInstance();

        if ($email) {
            $db->delete('login_intentos', 'ip = :ip OR email = :email', ['ip' => $ip, 'email' => $email]);
        } else {
            $db->delete('login_intentos', 'ip = :ip', ['ip' => $ip]);
        }
    }

    // ********** Lesson Blocking Methods **********
    public static function isLeccionarioBlocked(): bool
    {
        return self::isFechaBloqueada(date('Y-m-d'));
    }

    public static function isFechaBloqueada(string $fecha): bool
    {
        $semanasBloqueo = (int) Config::get('bloqueo_semanas_atras', 1);

        if ($semanasBloqueo <= 0) {
            return false;
        }

        $fechaActual = strtotime(date('Y-m-d'));
        $fechaLeccionario = strtotime($fecha);

        if ($fechaLeccionario > $fechaActual) {
            return false;
        }

        $inicioSemanaActual = strtotime('monday this week', $fechaActual);
        $limiteBloqueo = strtotime("-{$semanasBloqueo} weeks", $inicioSemanaActual);

        return $fechaLeccionario < $limiteBloqueo;
    }

    public static function getBloqueoSemanas(): int
    {
        return (int) Config::get('bloqueo_semanas_atras', 1);
    }

    // ********** Password Validation Methods **********
    public static function validarPassword(string $password): array
    {
        $errores = [];
        
        if (strlen($password) < 5) {
            $errores[] = 'La contrasenia debe tener al menos 5 caracteres';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errores[] = 'La contrasenia debe tener al menos 1 mayuscula (A-Z)';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errores[] = 'La contrasenia debe tener al menos 1 minuscula (a-z)';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errores[] = 'La contrasenia debe tener al menos 1 numero (0-9)';
        }
        
        if (!preg_match('/[!@#$%^&*()_+\-=]/', $password)) {
            $errores[] = 'La contrasenia debe tener al menos 1 caracter especial (!@#$%^&*()_+-=)';
        }
        
        return $errores;
    }

    public static function verificarRequisitosPassword(string $password): array
    {
        return [
            'length' => strlen($password) >= 5,
            'uppercase' => preg_match('/[A-Z]/', $password) === 1,
            'lowercase' => preg_match('/[a-z]/', $password) === 1,
            'number' => preg_match('/[0-9]/', $password) === 1,
            'special' => preg_match('/[!@#$%^&*()_+\-=]/', $password) === 1
        ];
    }
}
