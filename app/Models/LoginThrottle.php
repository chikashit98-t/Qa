<?php

namespace App\Models;

use App\Cores\Db;

/** ログイン試行回数制限。boxID単位とIP単位の両方で、WINDOW秒内にLIMIT回失敗したらLOCK秒ロックする */
final class LoginThrottle
{
    public const BOX_LIMIT = 5;
    public const IP_LIMIT = 20;
    public const WINDOW = 900;
    public const LOCK = 900;

    /** @return list<string> */
    public static function keys(string $boxID, string $ip): array
    {
        return ['box:' . hash('sha256', $boxID), 'ip:' . hash('sha256', $ip)];
    }

    /** ロック中なら残り秒数、そうでなければ0 */
    public static function lockedFor(array $keys): int
    {
        $max = 0;
        $st = Db::pdo()->prepare('SELECT locked_until FROM login_attempts WHERE k=?');
        foreach ($keys as $k) {
            $st->execute([$k]);
            $row = $st->fetch();
            if ($row) $max = max($max, (int) $row['locked_until'] - time());
        }
        return max(0, $max);
    }

    public static function fail(array $keys): void
    {
        $pdo = Db::pdo();
        $now = time();
        foreach ($keys as $k) {
            $limit = str_starts_with($k, 'box:') ? self::BOX_LIMIT : self::IP_LIMIT;
            $st = $pdo->prepare('SELECT * FROM login_attempts WHERE k=?');
            $st->execute([$k]);
            $row = $st->fetch();
            if (!$row || $now - (int) $row['first_at'] > self::WINDOW) {
                $pdo->prepare('DELETE FROM login_attempts WHERE k=?')->execute([$k]);
                $pdo->prepare('INSERT INTO login_attempts (k,fails,first_at,locked_until) VALUES (?,1,?,0)')->execute([$k, $now]);
                continue;
            }
            $fails = (int) $row['fails'] + 1;
            $lock = $fails >= $limit ? $now + self::LOCK : 0;
            $pdo->prepare('UPDATE login_attempts SET fails=?, locked_until=? WHERE k=?')->execute([$fails, $lock, $k]);
        }
    }

    /** 成功時はboxの失敗回数をリセットする（IP側は残す） */
    public static function clear(string $key): void
    {
        Db::pdo()->prepare('DELETE FROM login_attempts WHERE k=?')->execute([$key]);
    }
}
