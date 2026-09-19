<?php

namespace App\Cores;

final class Validate
{
    private array $errors = [];

    public static array $valList = [
        "boxTitle" => [
            "maxLength" => 20,
            "minLength" => 8,
        ],
        "content" => [
            "maxLength" => 200,
        ],
        "boxID" => [
            "minLength" => 10,
            "maxLength" => 50,
            "invalidChar" => '/^[a-zA-Z0-9\-_!*\'()]+$/',
        ],
        "password" => [
            "minLength" => 8,
            "invalidChar" => '/^[\x21-\x7e]+$/',
            "weak" => true
        ],
        "confirm" => [
            "notSame" => true
        ],
        "email" => [
            "invalidChar" => '/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
        ],
        "loginId" => [],
        "confirmCode" => [
            "length" => 8,
            "invalidChar" => '/^[a-zA-Z0-9]+$/',
        ],
        "answerTitle" => [
            "maxLength" => 50,
        ],
        "answerContent" => [
            "maxLength" => 200,
        ],
        "questionTitle" => [
            "maxLength" => 50,
        ],
    ];
    public function validate(string $item, string $ns, string $checker = ''): void
    {
        $found = [];
        $check = self::$valList[$ns] ?? [];
        if (trim($item) === '') {
            $this->errors[$ns] = [['code' => "{$ns}.required"]];
            return;
        }
        $len = mb_strlen($item);
        foreach ($check as $type => $val) {
            $failed = match ($type) {
                'maxLength' => $len > $val,
                'minLength' => $len < $val,
                'invalidChar' => !preg_match($val, $item),
                'weak' => !preg_match('/[a-zA-Z]/', $item) || !preg_match('/\d/', $item),
                'notSame' => $item !== $checker,
                'length' => $len !== $val,
                default => false,
            };
            if ($failed) $found[] = ['code' => "{$ns}.{$type}"];
        }
        if ($found) $this->errors[$ns] = $found;
    }

    /** 任意のフィールドにエラーコードを追加する（重複IDなど、サーバー側で初めて分かるエラー用） */
    public function addError(string $ns, string $type): void
    {
        $this->errors[$ns][] = ['code' => "{$ns}.{$type}"];
    }

    /** 必須チェックのみ（ログイン時など、形式の詳細を教えたくない場合） */
    public function requiredOnly(string $item, string $ns): void
    {
        if (trim($item) === '') $this->errors[$ns] = [['code' => "{$ns}.required"]];
    }

    public function boxTitle(string $item)
    {
        $ns = 'boxTitle';
        $this->validate($item, $ns);
    }
    public function content(string $item)
    {
        $ns = 'content';
        $this->validate($item, $ns);
    }
    public function boxID(string $item)
    {
        $ns = 'boxID';
        $this->validate($item, $ns);
    }
    public function password(string $item)
    {
        $ns = 'password';
        $this->validate($item, $ns);
    }
    public function confirm(string $item, string $checker = '')
    {
        $ns = 'confirm';
        $this->validate($item, $ns, $checker);
    }
    public function email(string $item)
    {
        $ns = 'email';
        $this->validate($item, $ns);
    }
    public function loginId(string $item)
    {
        $ns = 'loginId';
        $this->validate($item, $ns);
    }
    public function confirmCode(string $item)
    {
        $ns = 'confirmCode';
        $this->validate($item, $ns);
    }
    public function answerTitle(string $item)
    {
        $ns = 'answerTitle';
        $this->validate($item, $ns);
    }
    public function answerContent(string $item)
    {
        $ns = 'answerContent';
        $this->validate($item, $ns);
    }
    public function questionTitle(string $item)
    {
        $ns = 'questionTitle';
        $this->validate($item, $ns);
    }
    public function hasError(): bool
    {
        return (bool)$this->errors;
    }
    public  function errorJson(): mixed
    {
        return ['errors' => $this->errors];
    }
    public function errorResponse(): Response
    {
        return Response::json($this->errorJson(), 422);
    }
}
