<?php

namespace App\Cores;

use PDO;

/** PDO接続とスキーマ作成。DB_DSNが未設定ならstorage/app.sqliteを使う。 */
final class Db
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $dsn = env('DB_DSN', 'sqlite:' . ROOT . '/storage/app.sqlite');
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            // MySQLは値が変わらないUPDATEのrowCountを0にするため、一致した行数を返させる
            if (defined('PDO::MYSQL_ATTR_FOUND_ROWS')) {
                $options[PDO::MYSQL_ATTR_FOUND_ROWS] = true;
            }
            $pdo = null;
            for ($i = 0; $i < 15; $i++) { // DBコンテナ起動待ち
                try {
                    $pdo = new PDO($dsn, env('DB_USER'), env('DB_PASSWORD'), $options);
                    break;
                } catch (\PDOException $e) {
                    if ($i === 14) throw $e;
                    sleep(1);
                }
            }
            self::migrate($pdo);
            self::$pdo = $pdo;
        }
        return self::$pdo;
    }

    private static function migrate(PDO $pdo): void
    {
        $mysql = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';
        // boxIDは大文字小文字を区別する（note.md）
        $boxIdType = $mysql ? 'VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin' : 'VARCHAR(50)';
        $qIndex = $mysql ? ', INDEX idx_q_box (box_id, deleted)' : '';
        $tail = $mysql ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4' : '';

        $pdo->exec("CREATE TABLE IF NOT EXISTS boxes (
            id VARCHAR(32) PRIMARY KEY,
            box_id {$boxIdType} NOT NULL UNIQUE,
            title VARCHAR(100) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            dashboard_token VARCHAR(64) NOT NULL UNIQUE,
            created_at BIGINT NOT NULL
        ){$tail}");
        $pdo->exec("CREATE TABLE IF NOT EXISTS questions (
            id VARCHAR(32) PRIMARY KEY,
            box_id VARCHAR(32) NOT NULL,
            title VARCHAR(100) NOT NULL,
            content TEXT NOT NULL,
            answer_title VARCHAR(100) NOT NULL DEFAULT '',
            answer_content TEXT NOT NULL,
            status VARCHAR(16) NOT NULL DEFAULT 'pending',
            deleted INTEGER NOT NULL DEFAULT 0,
            created_at BIGINT NOT NULL,
            answered_at BIGINT NULL{$qIndex}
        ){$tail}");
        $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
            k VARCHAR(80) PRIMARY KEY,
            fails INTEGER NOT NULL,
            first_at BIGINT NOT NULL,
            locked_until BIGINT NOT NULL DEFAULT 0
        ){$tail}");
        $pdo->exec("CREATE TABLE IF NOT EXISTS tokens (
            token_hash VARCHAR(64) PRIMARY KEY,
            type VARCHAR(24) NOT NULL,
            box_id VARCHAR(32) NULL,
            payload TEXT NOT NULL,
            code_hash VARCHAR(64) NULL,
            attempts INTEGER NOT NULL DEFAULT 0,
            expires_at BIGINT NOT NULL
        ){$tail}");
    }
}
