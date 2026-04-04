<?php
/** Location: leccionario-digital/public/index.php */

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

require_once dirname(__DIR__) . '/app/Core/AuthService.php';
require_once dirname(__DIR__) . '/app/Core/helpers.php';
require_once dirname(__DIR__) . '/app/Core/Controller.php';
require_once dirname(__DIR__) . '/app/Core/Middleware.php';
require_once dirname(__DIR__) . '/app/Core/Router.php';

require_once dirname(__DIR__) . '/app/Controllers/AuthController.php';
require_once dirname(__DIR__) . '/app/Controllers/DocenteController.php';
require_once dirname(__DIR__) . '/app/Controllers/CoordinadorController.php';

require_once dirname(__DIR__) . '/routes/web.php';

$router = new Router();
requireRoutes($router);
$router->dispatch();
