<?php

namespace App\Cores;

use App\Models\Box;

/** 管理者ログイン（PHPセッション） */
final class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'secure' => Request::isHttps(), 'samesite' => 'Lax']);
            session_start();
        }
    }

    public static function login(array $box): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['box_id'] = $box['id'];
        $_SESSION['pwh'] = self::fingerprint($box);
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();
    }

    /** ログイン中のbox。パスワードが変わっていたら無効（引き継ぎ・再設定で旧セッションを失効させる） */
    public static function box(): ?array
    {
        self::start();
        $id = $_SESSION['box_id'] ?? null;
        if (!$id) return null;
        $box = Box::find($id);
        if (!$box || ($_SESSION['pwh'] ?? '') !== self::fingerprint($box)) {
            unset($_SESSION['box_id'], $_SESSION['pwh']);
            return null;
        }
        return $box;
    }

    private static function fingerprint(array $box): string
    {
        return hash('sha256', $box['password_hash']);
    }
}
