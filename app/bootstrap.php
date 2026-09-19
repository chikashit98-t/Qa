<?php

define('APP_ROOT', __DIR__);
define('ROOT', dirname(__DIR__));

spl_autoload_register(static function (string $class) {
    $relative = str_replace('\\', '/', substr($class, strlen('App\\')));
    $file = ROOT . '/app/' . $relative . '.php';
    if (is_file($file)) {
        require $file;
    }
});

/** HTMLエスケープ */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** 環境変数の取得（未設定ならデフォルト） */
function env(string $key, ?string $default = null): ?string
{
    $v = getenv($key);
    return $v === false || $v === '' ? $default : $v;
}
