<?php
// php -S 用ルータ: 静的ファイルはそのまま返し、それ以外はindex.phpへ
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path !== '/' && is_file(__DIR__ . '/../public' . $path)) {
    return false;
}
require __DIR__ . '/../public/index.php';
