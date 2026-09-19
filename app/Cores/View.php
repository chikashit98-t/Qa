<?php

namespace App\Cores;

final class  View
{
    public static function partial(string $template, array $data = []): string
    {
        $file = APP_ROOT . "/Views/" . $template . ".php";
        if (!is_file($file)) {
            return '';
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }

    public static function page(string $template, array $data = []): string
    {
        $content = self::partial($template, $data);
        extract($data, EXTR_SKIP);
        ob_start();
        require APP_ROOT . '/Views/layout.php';
        return (string)ob_get_clean();
    }
}
