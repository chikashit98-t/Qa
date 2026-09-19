<?php

namespace App\Cores;

/**
 * メール送信。
 *  MAIL_DRIVER=smtp : SMTP送信（MAIL_HOST, MAIL_PORT, MAIL_USER, MAIL_PASSWORD, MAIL_ENCRYPTION=tls|ssl|none）
 *  MAIL_DRIVER=mail : PHPのmb_send_mail()
 *  それ以外          : storage/mail.log へ書き出す（開発用）
 */
final class Mailer
{
    public static function send(string $to, string $subject, string $body): void
    {
        $from = env('MAIL_FROM', 'noreply@localhost');
        switch (env('MAIL_DRIVER')) {
            case 'smtp':
                self::smtp($from, $to, $subject, $body);
                return;
            case 'mail':
                mb_send_mail($to, $subject, $body, "From: {$from}");
                return;
            default:
                $line = sprintf("[%s] To: %s\nSubject: %s\n%s\n\n", date('c'), $to, $subject, $body);
                file_put_contents(ROOT . '/storage/mail.log', $line, FILE_APPEND | LOCK_EX);
        }
    }

    private static function smtp(string $from, string $to, string $subject, string $body): void
    {
        // ヘッダーインジェクション対策
        foreach ([$from, $to] as $addr) {
            if (preg_match('/[\r\n<>]/', $addr)) throw new \RuntimeException('Invalid mail address');
        }
        $host = env('MAIL_HOST', 'localhost');
        $port = (int) env('MAIL_PORT', '587');
        $enc = env('MAIL_ENCRYPTION', 'tls');
        $remote = ($enc === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
        $fp = @stream_socket_client($remote, $errno, $errstr, 10);
        if (!$fp) throw new \RuntimeException("SMTP connect failed: $errstr");
        stream_set_timeout($fp, 15);

        $read = static function () use ($fp): array {
            $code = 0;
            do {
                $line = fgets($fp, 1024);
                if ($line === false) throw new \RuntimeException('SMTP connection lost');
                $code = (int) substr($line, 0, 3);
            } while (isset($line[3]) && $line[3] === '-');
            return [$code, $line];
        };
        $cmd = static function (string $c, array $ok) use ($fp, $read): void {
            fwrite($fp, $c . "\r\n");
            [$code, $line] = $read();
            if (!in_array($code, $ok, true)) throw new \RuntimeException('SMTP error: ' . trim($line));
        };

        try {
            [$code] = $read();
            if ($code !== 220) throw new \RuntimeException('SMTP greeting failed');
            $helo = parse_url(Request::baseUrl(), PHP_URL_HOST) ?: 'localhost';
            $cmd("EHLO $helo", [250]);
            if ($enc === 'tls') {
                $cmd('STARTTLS', [220]);
                if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \RuntimeException('STARTTLS failed');
                }
                $cmd("EHLO $helo", [250]);
            }
            if (env('MAIL_USER')) {
                $cmd('AUTH LOGIN', [334]);
                $cmd(base64_encode(env('MAIL_USER')), [334]);
                $cmd(base64_encode((string) env('MAIL_PASSWORD')), [235]);
            }
            $cmd("MAIL FROM:<$from>", [250]);
            $cmd("RCPT TO:<$to>", [250, 251]);
            $cmd('DATA', [354]);

            $headers = [
                "From: <$from>",
                "To: <$to>",
                'Subject: ' . mb_encode_mimeheader($subject, 'UTF-8', 'B', "\r\n"),
                'Date: ' . date('r'),
                'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $helo . '>',
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Transfer-Encoding: base64',
            ];
            $data = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($body), 76, "\r\n");
            $cmd($data . '.', [250]);
            $cmd('QUIT', [221]);
        } finally {
            fclose($fp);
        }
    }
}
