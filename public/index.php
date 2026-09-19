<?php

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Cores\Request;
use App\Cores\Response;
use App\Cores\Router;

$router = new Router();
(require APP_ROOT . '/routers.php')($router);

try {
    $res = $router->match(Request::capture()) ?? Response::notFound();
} catch (Throwable $e) {
    error_log((string) $e);
    $res = Response::json(['errors' => ['_' => [['code' => 'server.error']]]], 500);
}
$res->send();
