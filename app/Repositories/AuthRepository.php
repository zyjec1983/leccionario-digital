<?php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/AuthModel.php';

class AuthRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): ?AuthModel
    {
        $row = $this->db->fetch(
            "SELECT * FROM usuarios WHERE email = :email AND activo = 1",
            ['email' => $email]
        );

        if (!$row) {
            return null;
        }

        return AuthModel::fromDatabase($row);
    }

    public function findById(int $id): ?AuthModel
    {
        $row = $this->db->fetch(
            "SELECT * FROM usuarios WHERE id = :id AND activo = 1",
            ['id' => $id]
        );

        if (!$row) {
            return null;
        }

        return AuthModel::fromDatabase($row);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function getPasswordTemporal(): string
    {
        return $this->hashPassword('12345');
    }

    public function getRoles(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT r.id, r.nombre, r.slug 
             FROM roles r 
             INNER JOIN usuario_roles ur ON r.id = ur.rol_id 
             WHERE ur.usuario_id = :user_id",
            ['user_id' => $userId]
        );
    }

    public function hasRole(int $userId, string $roleSlug): bool
    {
        $roles = $this->getRoles($userId);
        foreach ($roles as $role) {
            if ($role->slug === $roleSlug) {
                return true;
            }
        }
        return false;
    }

    public function updateLastLogin(int $userId): bool
    {
        $result = $this->db->update(
            'usuarios',
            ['ultimo_login' => date('Y-m-d H:i:s')],
            'id = :id',
            ['id' => $userId]
        );

        return $result > 0;
    }

    public function emailExists(string $email): bool
    {
        $user = $this->db->fetch(
            "SELECT id FROM usuarios WHERE email = :email AND activo = 1",
            ['email' => $email]
        );

        return $user !== null;
    }

    public function resetPassword(int $userId): bool
    {
        $hashTemporal = $this->getPasswordTemporal();
        
        $result = $this->db->update(
            'usuarios',
            [
                'password' => $hashTemporal,
                'primer_login' => 1
            ],
            'id = :id',
            ['id' => $userId]
        );

        return $result > 0;
    }

    public function changePassword(int $userId, string $newPassword): bool
    {
        $hash = $this->hashPassword($newPassword);
        
        try {
            $result = $this->db->update(
                'usuarios',
                [
                    'password' => $hash,
                    'primer_login' => 0
                ],
                'id = :id',
                ['id' => $userId]
            );
        } catch (Exception $e) {
            $result = $this->db->update(
                'usuarios',
                ['password' => $hash],
                'id = :id',
                ['id' => $userId]
            );
        }

        return $result > 0;
    }

    public function verifyCurrentPassword(int $userId, string $password): bool
    {
        $user = $this->db->fetch(
            "SELECT password FROM usuarios WHERE id = :id",
            ['id' => $userId]
        );

        if (!$user) {
            return false;
        }

        return $this->verifyPassword($password, $user->password);
    }

    public function updateFirma(int $userId, string $firma): bool
    {
        $result = $this->db->update(
            'usuarios',
            ['firma' => $firma],
            'id = :id',
            ['id' => $userId]
        );

        return $result > 0;
    }

    public function clearLoginAttempts(string $ip, ?string $email = null): void
    {
        if ($email) {
            $this->db->delete(
                'login_intentos',
                'email = :email',
                ['email' => $email]
            );
        }
        
        $this->db->delete(
            'login_intentos',
            'ip = :ip',
            ['ip' => $ip]
        );
    }

    public function recordFailedLogin(string $ip, ?string $email = null): void
    {
        $maxIntentos = (int) Config::get('login_max_intentos', 5);
        $bloqueoMinutos = (int) Config::get('login_bloqueo_minutos', 15);
        
        $existente = $this->db->fetch(
            "SELECT * FROM login_intentos WHERE ip = :ip" . ($email ? " OR email = :email" : ""),
            $email ? ['ip' => $ip, 'email' => $email] : ['ip' => $ip]
        );

        if ($existente) {
            $nuevosIntentos = $existente->intentos + 1;
            
            if ($email && $existente->email === $email) {
                $this->db->update(
                    'login_intentos',
                    [
                        'intentos' => $nuevosIntentos,
                        'ultimo_intento' => date('Y-m-d H:i:s')
                    ],
                    'email = :email',
                    ['email' => $email]
                );
            } else {
                $this->db->update(
                    'login_intentos',
                    [
                        'intentos' => $nuevosIntentos,
                        'ultimo_intento' => date('Y-m-d H:i:s')
                    ],
                    'ip = :ip',
                    ['ip' => $ip]
                );
            }
        } else {
            $this->db->insert('login_intentos', [
                'ip' => $ip,
                'email' => $email,
                'intentos' => 1,
                'ultimo_intento' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public function isLoginBlocked(string $ip, ?string $email = null): bool
    {
        $maxIntentos = (int) Config::get('login_max_intentos', 5);
        $bloqueoMinutos = (int) Config::get('login_bloqueo_minutos', 15);

        if ($email) {
            $intento = $this->db->fetch(
                "SELECT * FROM login_intentos WHERE email = :email",
                ['email' => $email]
            );
            
            if ($intento && (int)$intento->intentos >= $maxIntentos) {
                $tiempoBloqueo = strtotime($intento->ultimo_intento) + ($bloqueoMinutos * 60);
                if (time() < $tiempoBloqueo) {
                    return true;
                }
            }
        }

        $intento = $this->db->fetch(
            "SELECT * FROM login_intentos WHERE ip = :ip",
            ['ip' => $ip]
        );

        if ($intento && (int)$intento->intentos >= $maxIntentos) {
            $tiempoBloqueo = strtotime($intento->ultimo_intento) + ($bloqueoMinutos * 60);
            if (time() < $tiempoBloqueo) {
                return true;
            }
        }

        return false;
    }

    public function getLoginAttemptsRemaining(string $ip, ?string $email = null): int
    {
        $maxIntentos = (int) Config::get('login_max_intentos', 5);
        $intentosActuales = 0;

        if ($email) {
            $intento = $this->db->fetch(
                "SELECT intentos FROM login_intentos WHERE email = :email",
                ['email' => $email]
            );
            if ($intento) {
                $intentosActuales = max($intentosActuales, (int)$intento->intentos);
            }
        }

        $intento = $this->db->fetch(
            "SELECT intentos FROM login_intentos WHERE ip = :ip",
            ['ip' => $ip]
        );
        if ($intento) {
            $intentosActuales = max($intentosActuales, (int)$intento->intentos);
        }

        return max(0, $maxIntentos - $intentosActuales);
    }

    public function cleanupExpiredAttempts(): int
    {
        $bloqueoMinutos = (int) Config::get('login_bloqueo_minutos', 15);
        $tiempoExpiracion = date('Y-m-d H:i:s', time() - ($bloqueoMinutos * 60));
        
        return $this->db->delete(
            'login_intentos',
            'ultimo_intento < :fecha',
            ['fecha' => $tiempoExpiracion]
        );
    }
}
