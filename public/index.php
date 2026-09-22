<?php

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Cores\Request;
use App\Cores\Response;
use App\Cores\Router;

// 本番(APP_URLがhttps)ではhttp経由のアクセスを許可しない（Cloudflare Tunnelのバイパスや設定ミス対策）
if (str_starts_with((string) env('APP_URL'), 'https://') && !Request::isActuallyHttps()) {
    $res = Response::redirect(rtrim((string) env('APP_URL'), '/') . ($_SERVER['REQUEST_URI'] ?? '/'), 301);
    $res->send();
    exit;
}

$router = new Router();
(require APP_ROOT . '/routers.php')($router);

try {
    $res = $router->match(Request::capture()) ?? Response::notFound();
} catch (Throwable $e) {
    error_log((string) $e);
    $res = Response::json(['errors' => ['_' => [['code' => 'server.error']]]], 500);
}
$res->send();
