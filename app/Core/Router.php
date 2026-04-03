<?php

class Router
{
    protected array $routes = [];
    protected array $groupMiddlewares = [];

    public function get(string $uri, $handler): self
    {
        return $this->add('GET', $uri, $handler);
    }

    public function post(string $uri, $handler): self
    {
        return $this->add('POST', $uri, $handler);
    }

    public function put(string $uri, $handler): self
    {
        return $this->add('PUT', $uri, $handler);
    }

    public function delete(string $uri, $handler): self
    {
        return $this->add('DELETE', $uri, $handler);
    }

    public function any(string $uri, $handler): self
    {
        return $this->add(['GET', 'POST', 'PUT', 'DELETE'], $uri, $handler);
    }

    protected function add(string|array $methods, string $uri, $handler): self
    {
        $methods = array_map('strtoupper', (array) $methods);
        $uri = '/' . trim($uri, '/');
        
        foreach ($methods as $method) {
            $this->routes[$method][$uri] = [
                'handler' => $handler,
                'middleware' => $this->groupMiddlewares
            ];
        }

        return $this;
    }

    public function group(array $middlewares, callable $callback): self
    {
        $previousMiddlewares = $this->groupMiddlewares;
        $this->groupMiddlewares = array_merge($this->groupMiddlewares, $middlewares);
        
        $callback($this);
        
        $this->groupMiddlewares = $previousMiddlewares;
        
        return $this;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = isset($_GET['url']) ? '/' . trim($_GET['url'], '/') : '/';
        
        $route = $this->findRoute($method, $uri);
        
        if ($route === null) {
            $this->notFound();
            return;
        }

        $this->executeRoute($route);
    }

    protected function findRoute(string $method, string $uri): ?array
    {
        if (!isset($this->routes[$method])) {
            return null;
        }

        foreach ($this->routes[$method] as $routeUri => $routeData) {
            $pattern = $this->convertToRegex($routeUri);
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $routeData['params'] = array_values($matches);
                $routeData['original_uri'] = $routeUri;
                return $routeData;
            }
        }

        return null;
    }

    protected function convertToRegex(string $uri): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $uri);
        if (substr($pattern, -1) === '/') {
            $pattern = substr($pattern, 0, -1) . '/?';
        }
        return '#^' . $pattern . '$#';
    }

    protected function executeRoute(array $route): void
    {
        $handler = $route['handler'];
        $params = $route['params'] ?? [];

        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        if (is_string($handler) && strpos($handler, '@') !== false) {
            [$controllerName, $method] = explode('@', $handler);
            $controllerFile = dirname(__DIR__) . '/Controllers/' . $controllerName . '.php';
            
            if (!file_exists($controllerFile)) {
                $this->notFound();
                return;
            }

            require_once $controllerFile;
            
            $controller = new $controllerName();
            
            if (!method_exists($controller, $method)) {
                $this->notFound();
                return;
            }

            call_user_func_array([$controller, $method], $params);
            return;
        }

        $this->notFound();
    }

    protected function notFound(): void
    {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Ruta no encontrada',
            'code' => 404
        ]);
        exit;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}
