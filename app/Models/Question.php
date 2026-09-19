<?php

namespace App\Models;

use App\Cores\Db;

final class Question
{
    private const UNITS = [[31536000, '年前'], [2592000, 'ヵ月前'], [86400, '日前'], [3600, '時間前'], [60, '分前']];

    public function __construct(
        public string $id,
        public string $title,
        public string $content,
        public string $answerTitle,
        public string $answerContent,
        public Status $status,
        public int $createdAt,
        public ?int $answeredAt,
    ) {}

    public static function fromRow(array $r): self
    {
        return new self(
            $r['id'],
            $r['title'],
            $r['content'],
            $r['answer_title'],
            $r['answer_content'],
            Status::tryFrom($r['status']) ?? Status::PENDING,
            (int) $r['created_at'],
            $r['answered_at'] === null ? null : (int) $r['answered_at'],
        );
    }

    /** @return list<self> 削除済みは含まない。$publicOnlyなら公開のみ */
    public static function list(string $boxId, bool $publicOnly): array
    {
        $sql = 'SELECT * FROM questions WHERE box_id=? AND deleted=0' . ($publicOnly ? " AND status='public'" : '') . ' ORDER BY created_at DESC, id';
        $st = Db::pdo()->prepare($sql);
        $st->execute([$boxId]);
        return array_map([self::class, 'fromRow'], $st->fetchAll());
    }

    public static function create(string $boxId, string $title, string $content): void
    {
        Db::pdo()->prepare("INSERT INTO questions (id,box_id,title,content,answer_content,status,deleted,created_at) VALUES (?,?,?,?,'','pending',0,?)")
            ->execute([bin2hex(random_bytes(16)), $boxId, $title, $content, time()]);
    }

    /** @return bool 更新できたか（他boxの質問・削除済みならfalse） */
    public static function answer(string $boxId, string $id, string $title, string $content, Status $status): bool
    {
        $st = Db::pdo()->prepare('UPDATE questions SET answer_title=?, answer_content=?, status=?, answered_at=? WHERE id=? AND box_id=? AND deleted=0');
        $st->execute([$title, $content, $status->value, time(), $id, $boxId]);
        return $st->rowCount() > 0;
    }

    public static function softDelete(string $boxId, string $id): bool
    {
        $st = Db::pdo()->prepare('UPDATE questions SET deleted=1 WHERE id=? AND box_id=? AND deleted=0');
        $st->execute([$id, $boxId]);
        return $st->rowCount() > 0;
    }

    public function isAnswered(): bool
    {
        return $this->answeredAt !== null;
    }

    public function answeredStatus(): string
    {
        return $this->isAnswered() ? '回答済み' : '回答待ち';
    }

    public static function ago(int $ts): string
    {
        $sec = max(0, time() - $ts);
        foreach (self::UNITS as [$size, $unit]) {
            if ($sec >= $size) return intdiv($sec, $size) . $unit;
        }
        return 'たった今';
    }
}
