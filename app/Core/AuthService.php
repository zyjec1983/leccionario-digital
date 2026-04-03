<?php

require_once __DIR__ . '/../Repositories/AuthRepository.php';
require_once __DIR__ . '/../Models/UsuarioModel.php';

class AuthService
{
    private AuthRepository $repo;

    public function __construct()
    {
        $this->repo = new AuthRepository();
    }

    public function attempt(string $email, string $password): bool
    {
        $user = $this->repo->findByEmail($email);

        if (!$user) {
            return false;
        }

        if (!$this->repo->verifyPassword($password, $user->getPassword())) {
            return false;
        }

        $this->login($user);
        return true;
    }

    public function login(UsuarioModel $user): void
    {
        Session::regenerate();
        Session::set('user_id', $user->getId());
        Session::set('user_email', $user->getEmail());
        Session::set('user_name', $user->getNombreCompleto());

        $roles = $this->repo->getRoles($user->getId());
        Session::set('user_roles', $roles);

        if (count($roles) === 1) {
            Session::set('current_role', $roles[0]->slug);
        }

        $this->repo->updateLastLogin($user->getId());
    }

    public function logout(): void
    {
        Session::destroy();
    }

    public function check(): bool
    {
        return Session::isLoggedIn();
    }

    public function user(): ?UsuarioModel
    {
        if (!Session::isLoggedIn()) {
            return null;
        }

        return $this->repo->findById(Session::getUserId());
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

    public function hashPassword(string $password): string
    {
        return $this->repo->hashPassword($password);
    }

    public function isPrimerLogin(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }
        return $user->isPrimerLogin();
    }

    public function requiereCambioPassword(): bool
    {
        $currentRole = Session::getCurrentRole();

        if (in_array($currentRole, ['docente'])) {
            return $this->isPrimerLogin();
        }

        return false;
    }

    public function cambiarPassword(int $userId, string $nuevaPassword): bool
    {
        return $this->repo->changePassword($userId, $nuevaPassword);
    }

    public function resetearPassword(int $userId): bool
    {
        return $this->repo->resetPassword($userId);
    }

    public function verificarPasswordActual(int $userId, string $password): bool
    {
        return $this->repo->verifyCurrentPassword($userId, $password);
    }
}
