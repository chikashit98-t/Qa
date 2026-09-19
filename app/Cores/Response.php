<?php

namespace App\Cores;

final class Response
{
    public function __construct(private readonly string $body, private readonly int $status = 200, private readonly array $headers = []) {}

    public static function html(string $body, int $status = 200): self
    {
        return new self($body, $status, [
            'Content-Type' => 'text/html; charset=utf-8'
        ]);
    }

    public function send()
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $header) {
            header($name . ': ' . $header);
        }
        echo $this->body;
    }

    public static function json(mixed $data, int $status = 200): self
    {
        $json = $data === null ? '' : json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return new self($json, $status, [
            'Content-Type' => 'application/json; charset=utf-8'
        ]);
    }

    public static function redirect(string $to, int $status = 303): self
    {
        return new self('', $status, [
            'Location' => $to
        ]);
    }

    /** バリデーション以外のフォームエラー（400: トークン無効など） */
    public static function tokenError(): self
    {
        return self::json(['errors' => ['_' => [['code' => 'token.invalid']]]], 400);
    }

    public static function notFound(): self
    {
        return self::html(View::page('not_found', ['title' => 'ページが見つかりません']), 404);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function header(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }
}
