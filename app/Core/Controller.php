<?php
/**
 * Location: leccionario-digital/app/Core/Controller.php
 */

/**
 * Base controller class - provides common functionality for all controllers
 */
class Controller
{
    // ********** Properties **********
    protected Database $db;
    protected array $data = [];

    // ********** Constructor **********
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->data['user'] = auth()->user();
        $this->data['current_role'] = auth()->getCurrentRole();
    }

    // ********** View Methods **********
    protected function view(string $view, array $data = []): void
    {
        $data = array_merge($this->data, $data);
        
        extract($data);
        
        $viewFile = dirname(__DIR__) . '/Views/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            throw new Exception("Vista no encontrada: {$view}");
        }
        
        require_once dirname(__DIR__) . '/Views/partials/header.php';
        require_once $viewFile;
        require_once dirname(__DIR__) . '/Views/partials/footer.php';
    }

    protected function viewOnly(string $view, array $data = []): void
    {
        $data = array_merge($this->data, $data);
        extract($data);
        
        $viewFile = dirname(__DIR__) . '/Views/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            throw new Exception("Vista no encontrada: {$view}");
        }
        
        require_once $viewFile;
    }

    // ********** Response Methods **********
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function success($message = 'Operacion exitosa', $data = null): void
    {
        $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    protected function error($message = 'Error', int $statusCode = 400): void
    {
        $this->json([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }

    // ********** Navigation Methods **********
    protected function redirect(string $uri): void
    {
        redirect($uri);
    }

    protected function back(): void
    {
        back();
    }

    // ********** Input Methods **********
    protected function input(string $key, $default = null)
    {
        $request = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
        $value = null;
        
        if (isset($request[$key])) {
            $value = $request[$key];
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = json_decode(file_get_contents('php://input'), true);
            if ($json && isset($json[$key])) {
                $value = $json[$key];
            }
        }
        
        if ($value === null) {
            return $default;
        }
        
        if (is_array($value)) {
            return array_map([Security::class, 'sanitizeString'], $value);
        }
        
        if (is_string($value)) {
            return Security::sanitizeString($value);
        }
        
        return $value;
    }

    protected function inputs(array $keys = []): array
    {
        $request = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = json_decode(file_get_contents('php://input'), true);
            if ($json) {
                $request = array_merge($request, $json);
            }
        }
        
        if (empty($keys)) {
            return array_map([Security::class, 'sanitizeString'], $request);
        }
        
        $data = [];
        foreach ($keys as $key) {
            $val = $request[$key] ?? null;
            $data[$key] = is_string($val) ? Security::sanitizeString($val) : $val;
        }
        
        return $data;
    }

    protected function hasInput(string $key): bool
    {
        $request = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
        
        if (isset($request[$key])) {
            return true;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = json_decode(file_get_contents('php://input'), true);
            if ($json && isset($json[$key])) {
                return true;
            }
        }
        
        return false;
    }

    // ********** Validation Methods **********
    protected function validate(array $rules): bool|array
    {
        $errors = [];
        $request = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
        
        foreach ($rules as $field => $ruleString) {
            $rulesArr = explode('|', $ruleString);
            $value = $request[$field] ?? null;
            
            foreach ($rulesArr as $rule) {
                $params = [];
                
                if (strpos($rule, ':') !== false) {
                    [$rule, $paramStr] = explode(':', $rule);
                    $params = explode(',', $paramStr);
                }
                
                $error = $this->validateRule($field, $value, $rule, $params);
                
                if ($error) {
                    $errors[$field] = $error;
                    break;
                }
            }
        }
        
        if (empty($errors)) {
            return true;
        }
        
        return $errors;
    }

    protected function validateRule(string $field, $value, string $rule, array $params = []): ?string
    {
        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    return "El campo {$field} es requerido";
                }
                break;
                
            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return "El campo {$field} debe ser un email valido";
                }
                break;
                
            case 'min':
                if ($value && strlen($value) < $params[0]) {
                    return "El campo {$field} debe tener al menos {$params[0]} caracteres";
                }
                break;
                
            case 'max':
                if ($value && strlen($value) > $params[0]) {
                    return "El campo {$field} no debe exceder {$params[0]} caracteres";
                }
                break;
                
            case 'numeric':
                if ($value && !is_numeric($value)) {
                    return "El campo {$field} debe ser numerico";
                }
                break;
                
            case 'date':
                if ($value && !strtotime($value)) {
                    return "El campo {$field} debe ser una fecha valida";
                }
                break;
        }
        
        return null;
    }

    // ********** Auth Methods **********
    protected function requireAuth(string $role = null): void
    {
        if (!isLoggedIn()) {
            $this->redirect('auth/login');
        }
        
        if ($role && !auth()->hasRole($role)) {
            $this->redirect('auth/unauthorized');
        }
    }

    // ********** Config Methods **********
    protected function getConfig(string $key, $default = null)
    {
        return Config::get($key, $default);
    }
}
