<?php

/**
 * E2Eテスト。サーバー起動後に実行する:
 *   DB_DSN=sqlite:storage/test.sqlite php -d extension=pdo_sqlite -S 127.0.0.1:8099 tests/router.php
 *   php -d extension=curl tests/e2e.php http://127.0.0.1:8099
 * メール内容は storage/mail.log から読む（MAIL_DRIVER=log）。
 */

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8099', '/');
$fails = 0;
$total = 0;

function check(string $name, bool $cond, string $detail = ''): void
{
    global $fails, $total;
    $total++;
    if (!$cond) {
        $fails++;
        echo "FAIL: $name $detail\n";
    } else {
        echo "ok:   $name\n";
    }
}

/** @return array{status:int,body:string,json:mixed,location:?string} */
function req(string $method, string $path, ?array $json = null, string $jar = 'a'): array
{
    global $base;
    $ch = curl_init($base . $path);
    $cookie = sys_get_temp_dir() . "/e2e_cookie_$jar";
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
    ]);
    if ($json !== null) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
    }
    $raw = curl_exec($ch);
    $size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $headers = substr($raw, 0, $size);
    $body = substr($raw, $size);
    preg_match('/^Location:\s*(.+?)\r?$/mi', $headers, $m);
    return ['status' => $status, 'body' => $body, 'json' => json_decode($body, true), 'location' => $m[1] ?? null];
}

function lastMail(string $to): string
{
    $log = file_get_contents(__DIR__ . '/../storage/mail.log');
    $parts = array_filter(explode("\n\n", $log));
    $found = '';
    foreach ($parts as $p) {
        if (str_contains($p, "To: $to\n")) $found = $p;
    }
    return $found;
}

@unlink(__DIR__ . '/../storage/mail.log');
foreach (['a', 'b', 'c'] as $j) @unlink(sys_get_temp_dir() . "/e2e_cookie_$j");

$boxId = 'testbox' . random_int(1000, 9999);
$email = 'admin@example.com';

// --- 基本ページ ---
check('home 200', req('GET', '/')['status'] === 200);
check('unknown route 404', req('GET', '/nope')['status'] === 404);
check('box not found 404', req('GET', '/b/doesnotexist123')['status'] === 404);
check('dashboard without login redirects', req('GET', '/dashboard/' . str_repeat('a', 64))['location'] === '/login');
check('email-confirm page without session redirects', req('GET', '/box/create/email-confirm')['location'] === '/box/create');

// --- 作成 バリデーション ---
$r = req('POST', '/box/create', ['boxTitle' => 'short', 'boxID' => 'a', 'password' => 'abc', 'confirm' => 'x', 'email' => 'bad']);
check('create validation 422', $r['status'] === 422);
$codes = array_merge(...array_values(array_map(fn($v) => array_column($v, "code"), $r["json"]["errors"])));
foreach (['boxTitle.minLength', 'boxID.minLength', 'password.minLength', 'password.weak', 'confirm.notSame', 'email.invalidChar'] as $c) {
    check("create error $c", in_array($c, $codes, true), json_encode($codes));
}
$r = req('POST', '/box/create', ['boxTitle' => 'validtitle', 'boxID' => 'bad:id;x!!!', 'password' => 'password1', 'confirm' => 'password1', 'email' => $email]);
check('boxID with ":" or ";" rejected', $r['status'] === 422 && isset($r['json']['errors']['boxID']));

// --- 作成 ---
$body = ['boxTitle' => 'テスト質問箱タイトル', 'boxID' => $boxId, 'password' => 'password1', 'confirm' => 'password1', 'email' => $email];
$r = req('POST', '/box/create', $body);
check('create sends mail json', $r['status'] === 200 && $r['json']['type'] === 'sent' && $r['json']['redirect'] === '/box/create/email-confirm');
check('confirm page 200', req('GET', '/box/create/email-confirm')['status'] === 200);
$mail = lastMail($email);
preg_match('/確認コード: (\w{8})/', $mail, $m);
$code = $m[1] ?? '';
check('confirm code mailed', strlen($code) === 8);
check('box not yet created', req('GET', "/b/$boxId")['status'] === 404);

$r = req('POST', '/box/create/email-confirm', ['confirmCode' => 'zzzzzzzz']);
check('wrong code 422', $r['status'] === 422 && $r['json']['errors']['confirmCode'][0]['code'] === 'confirmCode.mismatch');
$r = req('POST', '/box/create/email-confirm', ['confirmCode' => 'abc']);
check('bad length code 422', $r['status'] === 422);
check('resend ok', req('POST', '/box/create/email-confirm/resend', [])['status'] === 200);
preg_match('/確認コード: (\w{8})/', lastMail($email), $m);
$code = $m[1];
$r = req('POST', '/box/create/email-confirm', ['confirmCode' => strtoupper($code)]);
check('code case-insensitive → login', $r['status'] === 303 && $r['location'] === '/login');
check('box exists', req('GET', "/b/$boxId")['status'] === 200);
check('confirm again fails (token consumed)', req('POST', '/box/create/email-confirm', ['confirmCode' => $code])['status'] === 400);
check('duplicate boxID rejected', req('POST', '/box/create', $body, 'b')['status'] === 422);
check('boxID is case-sensitive', req('GET', '/b/' . strtoupper($boxId))['status'] === 404);

// --- ログイン試行制限（専用のboxで検証） ---
$lockBox = 'lockbox' . random_int(1000, 9999);
req('POST', '/box/create', ['boxTitle' => 'ロック検証用の質問箱', 'boxID' => $lockBox . 'xx', 'password' => 'password1', 'confirm' => 'password1', 'email' => 'lock@example.com'], 'b');
preg_match('/確認コード: (\w{8})/', lastMail('lock@example.com'), $m);
req('POST', '/box/create/email-confirm', ['confirmCode' => $m[1]], 'b');
$lockId = $lockBox . 'xx';
for ($i = 0; $i < 5; $i++) {
    $r = req('POST', '/login', ['boxID' => $lockId, 'password' => 'wrongpass1'], 'b');
}
check('5 failures still 401', $r['status'] === 401);
$r = req('POST', '/login', ['boxID' => $lockId, 'password' => 'password1'], 'b');
check('locked even with correct password (429)', $r['status'] === 429 && $r['json']['errors']['password'][0]['code'] === 'password.locked');
$pdo0 = new PDO(getenv('DB_DSN') ?: 'sqlite:' . __DIR__ . '/../storage/test.sqlite', getenv('DB_USER') ?: null, getenv('DB_PASSWORD') ?: null);
$pdo0->exec('UPDATE login_attempts SET locked_until = ' . (time() - 1) . ', first_at = ' . (time() - 5000));
$r = req('POST', '/login', ['boxID' => $lockId, 'password' => 'password1'], 'b');
check('lock expires', $r['status'] === 303);

// --- ログイン ---
$r = req('POST', '/login', ['boxID' => $boxId, 'password' => 'wrongpass1']);
check('wrong password 401', $r['status'] === 401);
$r = req('POST', '/login', ['boxID' => 'nonexistentbox', 'password' => 'password1']);
check('unknown id 401', $r['status'] === 401);
$r = req('POST', '/login', ['boxID' => '', 'password' => '']);
check('empty login 422', $r['status'] === 422);
$r = req('POST', '/login', ['boxID' => $boxId, 'password' => 'password1']);
check('login redirect to dashboard', $r['status'] === 303 && preg_match('#^/dashboard/[a-f0-9]{64}$#', $r['location']));
$dash = $r['location'];
$r = req('GET', $dash);
check('dashboard 200', $r['status'] === 200 && str_contains($r['body'], 'テスト質問箱タイトル'));
check('dashboard with wrong token redirects to own', req('GET', '/dashboard/' . str_repeat('b', 64))['location'] === $dash);
check('other session cannot open dashboard', req('GET', $dash, null, 'c')['location'] === '/login');

// --- 質問投稿 ---
check('question form 200', req('GET', "/b/$boxId/questions/create")['status'] === 200);
$r = req('POST', "/b/$boxId/questions/create", ['questionTitle' => '', 'content' => str_repeat('あ', 201)], 'c');
check('question validation', $r['status'] === 422 && isset($r['json']['errors']['questionTitle']) && isset($r['json']['errors']['content']));
$r = req('POST', "/b/$boxId/questions/create", ['questionTitle' => '<script>alert(1)</script>', 'content' => "内容&\"'<b>"], 'c');
check('question created → redirect', $r['status'] === 303 && $r['location'] === "/b/$boxId");
$r = req('GET', "/b/$boxId");
check('pending question not public', !str_contains($r['body'], 'alert(1)') && str_contains($r['body'], '公開されている質問はありません'));

// --- ダッシュボードで回答 ---
$r = req('GET', $dash);
check('XSS escaped in dashboard', !str_contains($r['body'], '<script>alert(1)') && str_contains($r['body'], '&lt;script&gt;alert(1)'));
preg_match('/name="questionID" value="(\w+)"/', $r['body'], $m);
$qid = $m[1] ?? '';
check('question id present', strlen($qid) === 32);
check('answer requires login', req('POST', '/dashboard/answer', ['questionID' => $qid, 'answerTitle' => 'a', 'answerContent' => 'b', 'status' => 'public'], 'c')['status'] === 401);
$r = req('POST', '/dashboard/answer', ['questionID' => $qid, 'answerTitle' => '', 'answerContent' => '', 'status' => 'xx']);
check('answer validation', $r['status'] === 422 && isset($r['json']['errors']['answerTitle']) && isset($r['json']['errors']['status']));
$r = req('POST', '/dashboard/answer', ['questionID' => $qid, 'answerTitle' => '回答タイトル', 'answerContent' => '回答内容', 'status' => 'public']);
check('answer saved', $r['status'] === 200 && $r['json']['type'] === 'answer');
check('pending → auto public on save', $r['json']['status'] === 'public');
$r = req('POST', '/dashboard/answer', ['questionID' => $qid, 'answerTitle' => '回答タイトル', 'answerContent' => '回答内容', 'status' => 'pending']);
check('saving with pending auto-publishes', $r['status'] === 200 && $r['json']['status'] === 'public');
check('auto-published question is on public list', str_contains(req('GET', "/b/$boxId", null, 'c')['body'], '回答タイトル'));
$r = req('GET', "/b/$boxId", null, 'c');
check('public list shows answered question', str_contains($r['body'], '回答タイトル') && str_contains($r['body'], '回答済み'));
$r = req('POST', '/dashboard/answer', ['questionID' => $qid, 'answerTitle' => 'x', 'answerContent' => 'y', 'status' => 'publicoff']);
check('set publicoff', $r['status'] === 200);
check('publicoff hidden from public', !str_contains(req('GET', "/b/$boxId", null, 'c')['body'], '回答タイトル'));
check('answer nonexistent question 404', req('POST', '/dashboard/answer', ['questionID' => 'nope', 'answerTitle' => 'x', 'answerContent' => 'y', 'status' => 'public'])['status'] === 404);

// --- 削除 ---
check('delete requires login', req('POST', '/dashboard/question/delete', ['questionID' => $qid], 'c')['status'] === 401);
check('delete ok', req('POST', '/dashboard/question/delete', ['questionID' => $qid])['status'] === 200);
check('delete again 404', req('POST', '/dashboard/question/delete', ['questionID' => $qid])['status'] === 404);
check('deleted hidden in dashboard', !str_contains(req('GET', $dash)['body'], $qid));

// --- 設定 ---
check('setting requires login', req('POST', '/dashboard/setting', ['boxTitle' => 'xxxxxxxxxx', 'boxID' => 'newid123456'], 'c')['status'] === 401);
$r = req('POST', '/dashboard/setting', ['boxTitle' => 'x', 'boxID' => 'y']);
check('setting validation', $r['status'] === 422);
$newId = $boxId . 'new';
$r = req('POST', '/dashboard/setting', ['boxTitle' => '変更後のタイトル', 'boxID' => $newId]);
check('setting saved', $r['status'] === 200);
check('new boxID works', req('GET', "/b/$newId")['status'] === 200 && req('GET', "/b/$boxId")['status'] === 404);
$boxId = $newId;

// --- パスワード再設定 ---
check('password request requires login', req('POST', '/dashboard/password/request', [], 'c')['status'] === 401);
check('password request ok', req('POST', '/dashboard/password/request', [])['status'] === 200);
preg_match('#/dashboard/password/change/([a-f0-9]{64})#', lastMail($email), $m);
$tok = $m[1] ?? '';
check('password link mailed', strlen($tok) === 64);
check('password change page 200', req('GET', "/dashboard/password/change/$tok")['status'] === 200);
check('bogus token page 400', req('GET', '/dashboard/password/change/' . str_repeat('c', 64))['status'] === 400);
check('password change validation', req('POST', '/dashboard/password/change', ['token' => $tok, 'password' => 'short', 'confirm' => 'short'])['status'] === 422);
check('token of other type rejected', req('POST', '/dashboard/transfer/accept', ['token' => $tok, 'password' => 'newpass123', 'confirm' => 'newpass123'])['status'] === 400);
$r = req('POST', '/dashboard/password/change', ['token' => $tok, 'password' => 'newpass123', 'confirm' => 'newpass123']);
check('password changed → login', $r['status'] === 303 && $r['location'] === '/login');
check('token single use', req('POST', '/dashboard/password/change', ['token' => $tok, 'password' => 'newpass456', 'confirm' => 'newpass456'])['status'] === 400);
check('old session invalidated', req('GET', $dash)['location'] === '/login');
check('old password fails', req('POST', '/login', ['boxID' => $boxId, 'password' => 'password1'])['status'] === 401);
$r = req('POST', '/login', ['boxID' => $boxId, 'password' => 'newpass123']);
check('new password works', $r['status'] === 303);
$dash = $r['location'];

// --- メール変更 ---
check('email request ok', req('POST', '/dashboard/email/request', [])['status'] === 200);
preg_match('#/dashboard/email/change/([a-f0-9]{64})#', lastMail($email), $m);
$tok = $m[1];
check('email change page 200', req('GET', "/dashboard/email/change/$tok")['status'] === 200);
check('email invalid 422', req('POST', '/dashboard/email/change', ['token' => $tok, 'email' => 'bad'])['status'] === 422);
$newEmail = 'new@example.com';
$r = req('POST', '/dashboard/email/change', ['token' => $tok, 'email' => $newEmail]);
check('email change → confirm page', $r['status'] === 303 && preg_match('#^/dashboard/email/change/confirm/([a-f0-9]{64})$#', $r['location'], $m2));
$ctok = $m2[1];
preg_match('/確認コード: (\w{8})/', lastMail($newEmail), $m);
check('confirm page 200', req('GET', "/dashboard/email/change/confirm/$ctok")['status'] === 200);
$r = req('POST', '/dashboard/email/change/confirm', ['token' => $ctok, 'confirmCode' => 'zzzzzzzz']);
check('wrong email code', $r['status'] === 422);
check('resend email code', req('POST', '/dashboard/email/change/confirm/resend', ['token' => $ctok])['status'] === 200);
preg_match('/確認コード: (\w{8})/', lastMail($newEmail), $m);
$r = req('POST', '/dashboard/email/change/confirm', ['token' => $ctok, 'confirmCode' => $m[1]]);
check('email confirmed', $r['status'] === 303 && $r['location'] === '/login');
req('POST', '/dashboard/password/request', []);
check('mail now goes to new address', str_contains(lastMail($newEmail), '再設定'));

// --- 引き継ぎ ---
check('transfer page requires login', req('GET', '/dashboard/transfer', null, 'c')['location'] === '/login');
check('transfer page 200', req('GET', '/dashboard/transfer')['status'] === 200);
check('transfer request requires login', req('POST', '/dashboard/transfer', ['email' => 'x@example.com'], 'c')['status'] === 401);
check('transfer validation', req('POST', '/dashboard/transfer', ['email' => 'bad'])['status'] === 422);
$next = 'next@example.com';
$r = req('POST', '/dashboard/transfer', ['email' => $next]);
check('transfer sent → dashboard', $r['status'] === 303 && str_contains($r['location'], $dash));
preg_match('#/dashboard/transfer/accept/([a-f0-9]{64})#', lastMail($next), $m);
$tok = $m[1];
check('accept page 200', req('GET', "/dashboard/transfer/accept/$tok")['status'] === 200);
$r = req('POST', '/dashboard/transfer/accept', ['token' => $tok, 'password' => 'brandnew99', 'confirm' => 'brandnew99'], 'b');
check('transfer accepted', $r['status'] === 303 && $r['location'] === '/login');
check('old admin locked out', req('POST', '/login', ['boxID' => $boxId, 'password' => 'newpass123'])['status'] === 401);
check('old dashboard URL dead', req('GET', $dash)['location'] === '/login');
$r = req('POST', '/login', ['boxID' => $boxId, 'password' => 'brandnew99'], 'b');
check('new admin logs in with new dashboard URL', $r['status'] === 303 && $r['location'] !== $dash);

// --- 期限切れ ---
$pdo = new PDO(getenv('DB_DSN') ?: 'sqlite:' . __DIR__ . '/../storage/test.sqlite', getenv('DB_USER') ?: null, getenv('DB_PASSWORD') ?: null);
req('POST', '/dashboard/password/request', [], 'b');
preg_match('#/dashboard/password/change/([a-f0-9]{64})#', lastMail($next), $m);
$pdo->exec('UPDATE tokens SET expires_at = ' . (time() - 1));
check('expired token page 400', req('GET', "/dashboard/password/change/{$m[1]}")['status'] === 400);

// --- 確認コード総当たり対策 ---
$r = req('POST', '/box/create', ['boxTitle' => 'ブルートフォース', 'boxID' => 'bruteforce123', 'password' => 'password1', 'confirm' => 'password1', 'email' => 'bf@example.com'], 'c');
for ($i = 0; $i < 6; $i++) {
    req('POST', '/box/create/email-confirm', ['confirmCode' => 'zzzzzzzz'], 'c');
}
preg_match('/確認コード: (\w{8})/', lastMail('bf@example.com'), $m);
$r = req('POST', '/box/create/email-confirm', ['confirmCode' => $m[1]], 'c');
check('correct code rejected after too many attempts', $r['status'] === 400);
check('locked box not created', req('GET', '/b/bruteforce123')['status'] === 404);

// --- ログアウト ---
req('POST', '/logout', [], 'b');
check('logout invalidates session', req('GET', $r2 = '/dashboard/' . str_repeat('a', 64), null, 'b')['location'] === '/login');

echo "\n$total checks, $fails failed\n";
exit($fails ? 1 : 0);
