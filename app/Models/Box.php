<?php

namespace App\Models;

use App\Cores\Db;

final class Box
{
    public static function find(string $id): ?array
    {
        return self::one('SELECT * FROM boxes WHERE id=?', [$id]);
    }

    public static function findByPublicId(string $boxId): ?array
    {
        return self::one('SELECT * FROM boxes WHERE box_id=?', [$boxId]);
    }

    public static function findByDashboardToken(string $token): ?array
    {
        return self::one('SELECT * FROM boxes WHERE dashboard_token=?', [$token]);
    }

    /** @return array|null 重複(box_id)ならnull */
    public static function create(string $boxId, string $title, string $passwordHash, string $email): ?array
    {
        $id = bin2hex(random_bytes(8));
        try {
            Db::pdo()->prepare('INSERT INTO boxes (id,box_id,title,password_hash,email,dashboard_token,created_at) VALUES (?,?,?,?,?,?,?)')
                ->execute([$id, $boxId, $title, $passwordHash, $email, bin2hex(random_bytes(32)), time()]);
        } catch (\PDOException $e) {
            if (self::findByPublicId($boxId)) return null;
            throw $e;
        }
        return self::find($id);
    }

    /** @return bool 更新できたか（box_id重複ならfalse） */
    public static function updateSetting(string $id, string $boxId, string $title): bool
    {
        try {
            Db::pdo()->prepare('UPDATE boxes SET box_id=?, title=? WHERE id=?')->execute([$boxId, $title, $id]);
        } catch (\PDOException $e) {
            $other = self::findByPublicId($boxId);
            if ($other && $other['id'] !== $id) return false;
            throw $e;
        }
        return true;
    }

    public static function setPassword(string $id, string $hash): void
    {
        Db::pdo()->prepare('UPDATE boxes SET password_hash=? WHERE id=?')->execute([$hash, $id]);
    }

    public static function setEmail(string $id, string $email): void
    {
        Db::pdo()->prepare('UPDATE boxes SET email=? WHERE id=?')->execute([$email, $id]);
    }

    /** 引き継ぎ確定：パスワード・メール更新と管理URLの再発行 */
    public static function transfer(string $id, string $hash, string $email): void
    {
        Db::pdo()->prepare('UPDATE boxes SET password_hash=?, email=?, dashboard_token=? WHERE id=?')
            ->execute([$hash, $email, bin2hex(random_bytes(32)), $id]);
    }

    private static function one(string $sql, array $args): ?array
    {
        $st = Db::pdo()->prepare($sql);
        $st->execute($args);
        return $st->fetch() ?: null;
    }
}
