<?php
/** Location: leccionario-digital/app/Core/helpers.php */

// ********** Route Helper **********
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

// ********** Redirect Helpers **********
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

// ********** Auth Helpers **********
function auth(): AuthService
{
    static $auth = null;
    if ($auth === null) {
        $auth = new AuthService();
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
