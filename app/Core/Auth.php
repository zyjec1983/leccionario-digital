<?php

class Auth
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function attempt(string $email, string $password): bool
    {
        try {
            $user = $this->db->fetch(
                "SELECT * FROM usuarios WHERE email = :email AND activo = 1",
                ['email' => $email]
            );

            if (!$user) {
                return false;
            }

            if (!password_verify($password, $user->password)) {
                return false;
            }

            $this->login($user);
            return true;
        } catch (Exception $e) {
            error_log('Error en attempt: ' . $e->getMessage());
            return false;
        }
    }

    public function verificarEmail(string $email): bool
    {
        try {
            $user = $this->db->fetch(
                "SELECT id FROM usuarios WHERE email = :email AND activo = 1",
                ['email' => $email]
            );
            return $user !== null;
        } catch (Exception $e) {
            return false;
        }
    }

    public function login(object $user): void
    {
        Session::regenerate();
        Session::set('user_id', $user->id);
        Session::set('user_email', $user->email);
        Session::set('user_name', $user->nombre . ' ' . $user->apellido);

        $roles = $this->getUserRoles($user->id);
        Session::set('user_roles', $roles);

        if (count($roles) === 1) {
            Session::set('current_role', $roles[0]->slug);
        }

        $this->updateLastLogin($user->id);
    }

    public function logout(): void
    {
        Session::destroy();
    }

    public function check(): bool
    {
        return Session::isLoggedIn();
    }

    public function user(): ?object
    {
        if (!Session::isLoggedIn()) {
            return null;
        }

        return $this->db->fetch(
            "SELECT * FROM usuarios WHERE id = :id AND activo = 1",
            ['id' => Session::getUserId()]
        );
    }

    public function hasRole(string $role): bool
    {
        $roles = Session::get('user_roles', []);
        
        foreach ($roles as $r) {
            $r = (array)$r;
            if ($r['slug'] === $role) {
                return true;
            }
        }
        
        return false;
    }

    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    public function getUserRoles(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT r.id, r.nombre, r.slug 
             FROM roles r 
             INNER JOIN usuario_roles ur ON r.id = ur.rol_id 
             WHERE ur.usuario_id = :user_id",
            ['user_id' => $userId]
        );
    }

    public function getCurrentRole(): ?array
    {
        $currentRole = Session::getCurrentRole();
        
        if (!$currentRole) {
            return null;
        }

        $roles = Session::get('user_roles', []);
        
        foreach ($roles as $role) {
            $role = (array)$role;
            if ($role['slug'] === $currentRole) {
                return $role;
            }
        }
        
        return null;
    }

    public function switchRole(string $roleSlug): bool
    {
        if (!$this->hasRole($roleSlug)) {
            return false;
        }

        Session::set('current_role', $roleSlug);
        return true;
    }

    protected function updateLastLogin(int $userId): void
    {
        $this->db->update('usuarios', [
            'ultimo_login' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $userId]);
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function getPasswordTemporal(): string
    {
        return $this->hashPassword('12345');
    }

    public function isPrimerLogin(): bool
    {
        try {
            $user = $this->user();
            if (!$user) {
                return false;
            }
            return isset($user->primer_login) && (int)$user->primer_login === 1;
        } catch (Exception $e) {
            return false;
        }
    }

    public function requiereCambioPassword(): bool
    {
        $currentRole = Session::getCurrentRole();
        $excluirRutas = ['cambiar-password'];
        
        if (in_array($currentRole, ['docente'])) {
            return $this->isPrimerLogin();
        }
        
        return false;
    }

    public function cambiarPassword(int $userId, string $nuevaPassword): bool
    {
        try {
            $hash = $this->hashPassword($nuevaPassword);
            $result = $this->db->update('usuarios', [
                'password' => $hash
            ], 'id = :id', ['id' => $userId]);
            
            try {
                $this->db->update('usuarios', ['primer_login' => 0], 'id = :id', ['id' => $userId]);
            } catch (Exception $e) {
                // Columna puede no existir
            }
            
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }

    public function resetearPassword(int $userId): bool
    {
        try {
            $hashTemporal = $this->getPasswordTemporal();
            return $this->db->update('usuarios', [
                'password' => $hashTemporal,
                'primer_login' => 1
            ], 'id = :id', ['id' => $userId]);
        } catch (Exception $e) {
            try {
                $hashTemporal = $this->getPasswordTemporal();
                return $this->db->update('usuarios', [
                    'password' => $hashTemporal
                ], 'id = :id', ['id' => $userId]);
            } catch (Exception $e2) {
                return false;
            }
        }
    }

    public function verificarPasswordActual(int $userId, string $password): bool
    {
        $user = $this->db->fetch("SELECT password FROM usuarios WHERE id = :id", ['id' => $userId]);
        if (!$user) {
            return false;
        }
        return password_verify($password, $user->password);
    }
}
