<?php

if (function_exists('opcache_reset')) {
    opcache_reset();
}

date_default_timezone_set('America/Guayaquil');

require_once dirname(__DIR__) . '/app/Core/Database.php';
require_once dirname(__DIR__) . '/app/Core/Config.php';
Config::load('development');

require_once dirname(__DIR__) . '/app/Core/Security.php';
require_once dirname(__DIR__) . '/app/Core/Session.php';
Session::start();

require_once dirname(__DIR__) . '/app/Core/Auth.php';
require_once dirname(__DIR__) . '/app/Core/Controller.php';
require_once dirname(__DIR__) . '/app/Core/Middleware.php';
require_once dirname(__DIR__) . '/app/Core/Router.php';

require_once dirname(__DIR__) . '/app/Controllers/AuthController.php';
require_once dirname(__DIR__) . '/app/Controllers/DocenteController.php';
require_once dirname(__DIR__) . '/app/Controllers/CoordinadorController.php';

require_once dirname(__DIR__) . '/routes/web.php';

function route(string $path = '', array $params = []): string
{
    $base = Config::basePath();
    if (empty($path)) {
        return $base;
    }
    $path = ltrim($path, '/');
    if (!empty($params)) {
        $path .= '?' . http_build_query($params);
    }
    return $base . '/' . $path;
}

function redirect(string $uri): void
{
    $url = Config::basePath($uri);
    header("Location: {$url}");
    exit;
}

function back(): void
{
    $referer = $_SERVER['HTTP_REFERER'] ?? Config::basePath();
    header("Location: {$referer}");
    exit;
}

function auth(): Auth
{
    static $auth = null;
    if ($auth === null) {
        $auth = new Auth();
    }
    return $auth;
}

function user(): ?object
{
    return auth()->user();
}

function isLoggedIn(): bool
{
    return auth()->check();
}

function isDocente(): bool
{
    return auth()->hasRole('docente');
}

function isCoordinador(): bool
{
    return auth()->hasRole('coordinador');
}

function currentRole(): ?string
{
    return Session::getCurrentRole();
}

$router = new Router();
requireRoutes($router);
$router->dispatch();
