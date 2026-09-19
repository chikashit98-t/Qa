<?php

namespace App\Models;

use App\Cores\Db;

/** メール認証・再設定・引き継ぎ用のワンタイムトークン（DBにはハッシュのみ保存） */
final class Token
{
    public const CODE_MAX_ATTEMPTS = 5;

    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    /** @return array{token:string,code:string} */
    public static function issue(string $type, ?string $boxId, array $payload, int $ttl, bool $withCode = false): array
    {
        $token = bin2hex(random_bytes(32)); // 64桁 [a-f0-9]
        $code = $withCode ? self::newCode() : null;
        Db::pdo()->prepare('DELETE FROM tokens WHERE expires_at < ?')->execute([time()]);
        Db::pdo()->prepare('INSERT INTO tokens (token_hash,type,box_id,payload,code_hash,attempts,expires_at) VALUES (?,?,?,?,?,0,?)')
            ->execute([self::hash($token), $type, $boxId, json_encode($payload, JSON_UNESCAPED_UNICODE), $code ? self::hash($code) : null, time() + $ttl]);
        return ['token' => $token, 'code' => $code ?? ''];
    }

    /** 有効なトークン行を返す。種別違い・期限切れは null */
    public static function find(string $token, string $type): ?array
    {
        if (!preg_match('/^[a-z0-9]{64}$/', $token)) return null;
        $st = Db::pdo()->prepare('SELECT * FROM tokens WHERE token_hash=? AND type=? AND expires_at>=?');
        $st->execute([self::hash($token), $type, time()]);
        $row = $st->fetch();
        if (!$row) return null;
        $row['payload'] = json_decode($row['payload'], true) ?: [];
        return $row;
    }

    public static function delete(string $token): void
    {
        Db::pdo()->prepare('DELETE FROM tokens WHERE token_hash=?')->execute([self::hash($token)]);
    }

    /** 確認コード照合。誤入力が上限を超えたらトークンごと失効させる */
    public static function checkCode(array $row, string $code): bool
    {
        $pdo = Db::pdo();
        if ($row['attempts'] >= self::CODE_MAX_ATTEMPTS) {
            $pdo->prepare('DELETE FROM tokens WHERE token_hash=?')->execute([$row['token_hash']]);
            return false;
        }
        if (hash_equals((string) $row['code_hash'], self::hash(strtolower($code)))) return true;
        $pdo->prepare('UPDATE tokens SET attempts=attempts+1 WHERE token_hash=?')->execute([$row['token_hash']]);
        return false;
    }

    /** コード再発行（試行回数リセット、有効期限延長） */
    public static function resetCode(string $token, int $ttl): string
    {
        $code = self::newCode();
        Db::pdo()->prepare('UPDATE tokens SET code_hash=?, attempts=0, expires_at=? WHERE token_hash=?')
            ->execute([self::hash($code), time() + $ttl, self::hash($token)]);
        return $code;
    }

    private static function newCode(): string
    {
        $chars = 'abcdefghjkmnpqrstuvwxyz23456789';
        $code = '';
        for ($i = 0; $i < 8; $i++) $code .= $chars[random_int(0, strlen($chars) - 1)];
        return $code;
    }
}
