<?php
/** Location: leccionario-digital/app/Controllers/AuthController.php */

require_once __DIR__ . '/../Core/Result.php';
require_once __DIR__ . '/../Core/Session.php';
require_once __DIR__ . '/../Models/UsuarioModel.php';
require_once __DIR__ . '/../Repositories/AuthRepository.php';

class AuthController extends Controller
{
    private AuthRepository $authRepo;

    public function __construct()
    {
        parent::__construct();
        $this->authRepo = new AuthRepository();
    }

    public function login(): void
    {
        if (isLoggedIn()) {
            $currentRole = currentRole();
            if ($currentRole === 'docente') {
                $this->redirect('docente');
            } elseif ($currentRole === 'coordinador') {
                $this->redirect('coordinador');
            }
        }

        $showTimeoutMessage = Session::flash('timeout') === true;
        
        $this->viewOnly('auth/login', [
            'timeoutMessage' => $showTimeoutMessage
        ]);
    }

    public function authenticate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/login');
        }

        $clientIP = Security::getClientIP();
        $emailRaw = $this->input('email');
        $password = $this->input('password');

        if (empty($emailRaw) || empty($password)) {
            $this->json([
                'success' => false,
                'message' => 'Email y contraseña son requeridos'
            ], 400);
        }

        $email = Security::sanitizeEmail($emailRaw);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json([
                'success' => false,
                'message' => 'Formato de email inválido'
            ], 400);
        }

        if ($this->authRepo->isLoginBlocked($clientIP, $email)) {
            $remaining = $this->authRepo->getLoginAttemptsRemaining($clientIP, $email);
            $this->authRepo->recordFailedLogin($clientIP, $email);
            $this->json([
                'success' => false,
                'message' => 'Demasiados intentos fallidos. Intenta de nuevo en 15 minutos.'
            ], 429);
        }

        $emailExiste = $this->authRepo->emailExists($email);
        $user = $this->authRepo->findByEmail($email);

        // Debug: log authentication attempt
        error_log("Auth attempt - Email: $email, User found: " . ($user ? 'yes' : 'no') . ", Email exists: " . ($emailExiste ? 'yes' : 'no'));

        if ($user && $this->authRepo->verifyPassword($password, $user->getPassword())) {
            $this->authRepo->clearLoginAttempts($clientIP, $email);
            $this->authRepo->updateLastLogin($user->getId());

            $roles = $this->authRepo->getRoles($user->getId());
            $user->setRoles($roles);

            $this->setUserSession($user);

            error_log("Auth success - User ID: " . $user->getId() . ", Roles: " . count($user->getRoles()));

            if ($user->tieneMultiplesRoles()) {
                $this->json([
                    'success' => true,
                    'message' => 'Multirol detectado. Seleccione un rol para continuar.',
                    'redirect' => route('auth/select-role')
                ]);
            } else {
                $role = $user->getPrimerRolSlug() ?? 'docente';
                $redirect = $role === 'coordinador' ? 'coordinador' : 'docente';

                if ($role === 'docente' && $user->isPrimerLogin()) {
                    $this->json([
                        'success' => true,
                        'message' => 'Login exitoso. Primero debe cambiar su contraseña temporal.',
                        'redirect' => route('docente/cambiar-password')
                    ]);
                } else {
                    $this->json([
                        'success' => true,
                        'message' => 'Login exitoso. Redirigiendo...',
                        'redirect' => route($redirect)
                    ]);
                }
            }
        } else {
            $this->authRepo->recordFailedLogin($clientIP, $email);
            $remaining = $this->authRepo->getLoginAttemptsRemaining($clientIP, $email);

            if (!$emailExiste) {
                $msg = 'El email no está registrado en el sistema';
                error_log("Auth failed - Email not found: $email");
            } else {
                $msg = 'Contraseña incorrecta';
                error_log("Auth failed - Wrong password for: $email");
            }

            if ($remaining > 0 && $remaining <= 2) {
                $msg .= " ($remaining intento(s) restante(s))";
            }

            $this->json([
                'success' => false,
                'message' => $msg
            ], 401);
        }
    }

    private function setUserSession(UsuarioModel $user): void
    {
        Session::regenerate();
        Session::set('user_id', $user->getId());
        Session::set('user_email', $user->getEmail());
        Session::set('user_name', $user->getNombreCompleto());

        $rolesData = [];
        foreach ($user->getRoles() as $role) {
            $rolesData[] = [
                'id' => $role->id,
                'nombre' => $role->nombre,
                'slug' => $role->slug
            ];
        }
        Session::set('user_roles', $rolesData);

        if (count($rolesData) === 1) {
            Session::set('current_role', $rolesData[0]['slug']);
        }
    }

    public function selectRole(): void
    {
        if (!isLoggedIn()) {
            $this->redirect('auth/login');
        }

        $roles = Session::get('user_roles', []);

        if (count($roles) <= 1) {
            $role = $roles[0]['slug'] ?? 'docente';
            $redirect = $role === 'coordinador' ? 'coordinador' : 'docente';
            $this->redirect($redirect);
        }

        $this->viewOnly('auth/select-role', ['roles' => $roles]);
    }

    public function setRole(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/login');
        }

        $role = $this->input('role');

        if (empty($role)) {
            $this->json([
                'success' => false,
                'message' => 'Rol requerido'
            ], 400);
        }

        if (!$this->hasUserRole($role)) {
            $this->json([
                'success' => false,
                'message' => 'Rol no válido'
            ], 400);
        }

        Session::set('current_role', $role);

        if ($role === 'docente') {
            $userId = Session::getUserId();
            $user = $this->authRepo->findById($userId);
            if ($user && $user->isPrimerLogin()) {
                $this->json([
                    'success' => true,
                    'message' => 'Rol seleccionado. Debe cambiar su contraseña',
                    'redirect' => route('docente/cambiar-password')
                ]);
            }
        }

        $redirect = $role === 'coordinador' ? 'coordinador' : 'docente';
        $this->json([
            'success' => true,
            'message' => 'Rol seleccionado',
            'redirect' => route($redirect)
        ]);
    }

    private function hasUserRole(string $roleSlug): bool
    {
        $roles = Session::get('user_roles', []);
        foreach ($roles as $role) {
            if ($role['slug'] === $roleSlug) {
                return true;
            }
        }
        return false;
    }

    public function switchRole(string $role): void
    {
        if (!isLoggedIn()) {
            $this->redirect('auth/login');
        }

        if (!$this->hasUserRole($role)) {
            $this->redirect('auth/unauthorized');
        }

        Session::set('current_role', $role);
        $redirect = $role === 'coordinador' ? 'coordinador' : 'docente';
        $this->redirect($redirect);
    }

    public function logout(): void
    {
        Session::destroy();
        $this->redirect('auth/login');
    }

    public function unauthorized(): void
    {
        http_response_code(403);
        $this->viewOnly('auth/unauthorized');
    }

    public function cambiarPassword(): void
    {
        if (!isLoggedIn()) {
            $this->redirect('auth/login');
        }

        $userId = Session::getUserId();
        $user = $this->authRepo->findById($userId);
        $esPrimerLogin = $user ? $user->isPrimerLogin() : false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $passwordActual = $this->input('password_actual');
            $nuevaPassword = $this->input('nueva_password');
            $confirmarPassword = $this->input('confirmar_password');

            if ($nuevaPassword !== $confirmarPassword) {
                $this->json(['success' => false, 'message' => 'Las contraseñas no coinciden']);
            }

            $erroresValidacion = Security::validarPassword($nuevaPassword);
            if (!empty($erroresValidacion)) {
                $this->json(['success' => false, 'message' => implode('. ', $erroresValidacion)]);
            }

            if (!$esPrimerLogin && !empty($passwordActual)) {
                if (!$this->authRepo->verifyCurrentPassword($userId, $passwordActual)) {
                    $this->json(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
                }
            }

            if ($this->authRepo->changePassword($userId, $nuevaPassword)) {
                $this->json(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
            } else {
                $this->json(['success' => false, 'message' => 'Error al cambiar la contraseña']);
            }
        }

        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        $esCoordinador = strpos($currentUri, 'coordinador') !== false;

        $this->view('docente/cambiar-password', [
            'title' => 'Cambiar Contraseña',
            'esPrimerLogin' => $esPrimerLogin,
            'mostrarPasswordActual' => !$esPrimerLogin,
            'basePath' => $esCoordinador ? 'coordinador' : 'docente'
        ]);
    }

    public function procesarCambioPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/login');
        }

        $passwordActual = isset($_POST['password_actual']) ? $_POST['password_actual'] : '';
        $nuevaPassword = isset($_POST['nueva_password']) ? $_POST['nueva_password'] : '';
        $confirmarPassword = isset($_POST['confirmar_password']) ? $_POST['confirmar_password'] : '';

        if ($nuevaPassword !== $confirmarPassword) {
            $this->json(['success' => false, 'message' => 'Las contraseñas no coinciden']);
        }

        $erroresValidacion = Security::validarPassword($nuevaPassword);
        if (!empty($erroresValidacion)) {
            $this->json(['success' => false, 'message' => implode('. ', $erroresValidacion)]);
        }

        $userId = Session::getUserId();
        $user = $this->authRepo->findById($userId);
        $esPrimerLogin = $user ? $user->isPrimerLogin() : false;

        if (!$esPrimerLogin && !empty($passwordActual)) {
            if (!$this->authRepo->verifyCurrentPassword($userId, $passwordActual)) {
                $this->json(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
            }
        }

        if ($this->authRepo->changePassword($userId, $nuevaPassword)) {
            $currentRole = currentRole();
            $redirect = $currentRole === 'coordinador' ? 'coordinador' : 'docente';
            $this->json(['success' => true, 'message' => 'Contraseña actualizada correctamente', 'redirect' => route($redirect)]);
        } else {
            $this->json(['success' => false, 'message' => 'Error al cambiar la contraseña']);
        }
    }

    public function extendSession(): void
    {
        if (!isLoggedIn()) {
            $this->redirect('auth/login');
        }

        Session::set('_created', time());
        echo json_encode(['success' => true]);
        exit;
    }
}
