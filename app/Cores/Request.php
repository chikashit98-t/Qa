<?php

namespace App\Cores;

final class Request
{
    private function __construct(public readonly string $method, public readonly string $path, public readonly array $query, public readonly array $post) {}

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode(file_get_contents('php://input'), true);
            $post = is_array($decoded) ? $decoded : [];
        } else {
            $post = $_POST;
        }
        // 値は常に文字列として扱う（配列などが来ても型エラーにしない）
        $post = array_map(static fn($v) => is_scalar($v) ? (string) $v : '', $post);
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH) ?: "/";
        $path = rawurldecode($path);
        if ($path !== '/') {
            $path = rtrim($path, '/');
            if ($path === '') {
                $path = '/';
            }
        }
        return new self($method, $path, $_GET, $post);
    }

    public static function isHttps(): bool
    {
        if (str_starts_with((string) env('APP_URL'), 'https://')) return true;
        return self::isActuallyHttps();
    }

    /** APP_URLの設定に関わらず、実際のリクエストがhttpsで届いたかを判定する（本番でのhttps強制チェック用） */
    public static function isActuallyHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') return true;
        if (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') return true;
        // Cloudflare Tunnel経由の場合、CF-Visitorヘッダーにクライアントの実際のスキームが入る
        return str_contains((string) ($_SERVER['HTTP_CF_VISITOR'] ?? ''), '"scheme":"https"');
    }

    /** メール内リンク用のベースURL。APP_URL未設定ならリクエストのホストから組み立てる */
    public static function baseUrl(): string
    {
        $url = env('APP_URL');
        if ($url) return rtrim($url, '/');
        return (self::isHttps() ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }
}
