<?php

class AuthController extends Controller
{
    public function __construct()
    {
        parent::__construct();
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

        $this->viewOnly('auth/login');
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

        if (Security::isLoginBlocked($clientIP, $email)) {
            $remaining = Security::getLoginAttemptsRemaining($clientIP, $email);
            Security::recordFailedLogin($clientIP, $email);
            $this->json([
                'success' => false,
                'message' => 'Demasiados intentos fallidos. Intenta de nuevo en 15 minutos.'
            ], 429);
        }

        $auth = new Auth();
        
        $emailExiste = $auth->verificarEmail($email);

        if ($auth->attempt($email, $password)) {
            Security::clearLoginAttempts($clientIP, $email);
            
            $roles = Session::get('user_roles', []);
            
            if (count($roles) > 1) {
                $this->json([
                    'success' => true,
                    'message' => 'Multirol detectado',
                    'redirect' => route('auth/select-role')
                ]);
            } else {
                $role = $roles[0]->slug ?? 'docente';
                $redirect = $role === 'coordinador' ? 'coordinador' : 'docente';
                
                if ($role === 'docente' && $auth->isPrimerLogin()) {
                    $this->json([
                        'success' => true,
                        'message' => 'Login exitoso. Debe cambiar su contraseña',
                        'redirect' => route('docente/cambiar-password')
                    ]);
                } else {
                    $this->json([
                        'success' => true,
                        'message' => 'Login exitoso',
                        'redirect' => route($redirect)
                    ]);
                }
            }
        } else {
            Security::recordFailedLogin($clientIP, $email);
            $remaining = Security::getLoginAttemptsRemaining($clientIP, $email);
            
            if (!$emailExiste) {
                $msg = 'El email no está registrado en el sistema';
            } else {
                $msg = 'Contraseña incorrecta';
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

    public function selectRole(): void
    {
        if (!isLoggedIn()) {
            $this->redirect('auth/login');
        }

        $roles = Session::get('user_roles', []);
        
        if (count($roles) <= 1) {
            $role = $roles[0]->slug ?? 'docente';
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

        if (auth()->switchRole($role)) {
            $redirect = $role === 'coordinador' ? 'coordinador' : 'docente';
            
            if ($role === 'docente' && auth()->isPrimerLogin()) {
                $this->json([
                    'success' => true,
                    'message' => 'Rol seleccionado. Debe cambiar su contraseña',
                    'redirect' => route('docente/cambiar-password')
                ]);
            } else {
                $this->json([
                    'success' => true,
                    'message' => 'Rol seleccionado',
                    'redirect' => route($redirect)
                ]);
            }
        } else {
            $this->json([
                'success' => false,
                'message' => 'Rol no válido'
            ], 400);
        }
    }

    public function switchRole(string $role): void
    {
        if (!isLoggedIn()) {
            $this->redirect('auth/login');
        }

        if (!auth()->switchRole($role)) {
            $this->redirect('auth/unauthorized');
        }

        $redirect = $role === 'coordinador' ? 'coordinador' : 'docente';
        $this->redirect($redirect);
    }

    public function logout(): void
    {
        auth()->logout();
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

        $user = auth()->user();
        $esPrimerLogin = auth()->isPrimerLogin();
        
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
            
            if (!$esPrimerLogin && !auth()->verificarPasswordActual(Session::getUserId(), $passwordActual)) {
                $this->json(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
            }
            
            if (auth()->cambiarPassword(Session::getUserId(), $nuevaPassword)) {
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

        $user = auth()->user();
        
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
        
        $esPrimerLogin = false;
        try {
            $esPrimerLogin = auth()->isPrimerLogin();
        } catch (Exception $e) {
            $esPrimerLogin = false;
        }
        
        if (!$esPrimerLogin && !empty($passwordActual)) {
            if (!auth()->verificarPasswordActual(Session::getUserId(), $passwordActual)) {
                $this->json(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
            }
        }
        
        if (auth()->cambiarPassword(Session::getUserId(), $nuevaPassword)) {
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
