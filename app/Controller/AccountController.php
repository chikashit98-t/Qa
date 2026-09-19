<?php

namespace App\Controller;

use App\Cores\Auth;
use App\Cores\Mailer;
use App\Cores\Request;
use App\Cores\Response;
use App\Cores\Validate;
use App\Cores\View;
use App\Models\Box;
use App\Models\Token;

/** パスワード再設定・メールアドレス変更・管理者引き継ぎ */
final class AccountController
{
    private const LINK_TTL = 1800;
    private const TRANSFER_TTL = 600;

    // ---- パスワード再設定 ----

    public static function requestPasswordChange(Request $req, array $args): Response
    {
        return self::sendLink('password_change', '/dashboard/password/change/', 'パスワード再設定のご案内', 'パスワードを再設定するには次のURLを開いてください（30分有効）。');
    }

    public static function showPasswordChangeForm(Request $req, array $args): Response
    {
        if (!Token::find($args['token'], 'password_change')) return self::invalidPage();
        return Response::html(View::page('password_change', ['title' => 'パスワード再設定', 'token' => $args['token']]));
    }

    public static function changePassword(Request $req, array $args): Response
    {
        $token = $req->post['token'] ?? '';
        $validate = new Validate();
        $validate->password($req->post['password'] ?? '');
        $validate->confirm($req->post['confirm'] ?? '', $req->post['password'] ?? '');
        if ($validate->hasError()) return $validate->errorResponse();

        $row = Token::find($token, 'password_change');
        if (!$row) return Response::tokenError();
        Box::setPassword($row['box_id'], password_hash($req->post['password'], PASSWORD_DEFAULT));
        Token::delete($token);
        return Response::redirect('/login');
    }

    // ---- メールアドレス変更 ----

    public static function requestEmailChange(Request $req, array $args): Response
    {
        return self::sendLink('email_change', '/dashboard/email/change/', 'メールアドレス変更のご案内', 'メールアドレスを変更するには次のURLを開いてください（30分有効）。');
    }

    public static function showEmailChangeForm(Request $req, array $args): Response
    {
        if (!Token::find($args['token'], 'email_change')) return self::invalidPage();
        return Response::html(View::page('email_change', ['title' => 'メールアドレス変更', 'token' => $args['token']]));
    }

    public static function changeEmail(Request $req, array $args): Response
    {
        $token = $req->post['token'] ?? '';
        $email = $req->post['email'] ?? '';
        $validate = new Validate();
        $validate->email($email);
        if ($validate->hasError()) return $validate->errorResponse();

        $row = Token::find($token, 'email_change');
        if (!$row) return Response::tokenError();

        $issued = Token::issue('email_confirm', $row['box_id'], ['email' => $email], self::LINK_TTL, true);
        Token::delete($token);
        BoxController::mailCode($email, $issued['code']);
        return Response::redirect('/dashboard/email/change/confirm/' . $issued['token']);
    }

    public static function showEmailChangeConfirmForm(Request $req, array $args): Response
    {
        if (!Token::find($args['token'], 'email_confirm')) return self::invalidPage();
        return Response::html(View::page('email_confirm', [
            'title' => 'メールアドレス確認',
            'action' => '/dashboard/email/change/confirm',
            'resendAction' => '/dashboard/email/change/confirm/resend',
            'token' => $args['token'],
        ]));
    }

    public static function confirmEmailChange(Request $req, array $args): Response
    {
        $token = $req->post['token'] ?? '';
        $code = $req->post['confirmCode'] ?? '';
        $validate = new Validate();
        $validate->confirmCode($code);
        if ($validate->hasError()) return $validate->errorResponse();

        $row = Token::find($token, 'email_confirm');
        if (!$row) return Response::tokenError();
        if (!Token::checkCode($row, $code)) {
            $validate->addError('confirmCode', 'mismatch');
            return $validate->errorResponse();
        }
        Box::setEmail($row['box_id'], $row['payload']['email']);
        Token::delete($token);
        return Response::redirect('/login');
    }

    public static function resendEmailChangeCode(Request $req, array $args): Response
    {
        $token = $req->post['token'] ?? '';
        $row = Token::find($token, 'email_confirm');
        if (!$row) return Response::tokenError();
        BoxController::mailCode($row['payload']['email'], Token::resetCode($token, self::LINK_TTL));
        return Response::json(['type' => 'resent']);
    }

    // ---- 管理者引き継ぎ ----

    public static function showTransferForm(Request $req, array $args): Response
    {
        $box = Auth::box();
        if (!$box) return Response::redirect('/login');
        return Response::html(View::page('transfer_request', ['title' => '管理者の引き継ぎ', 'dashboard_id' => $box['dashboard_token']]));
    }

    public static function requestTransfer(Request $req, array $args): Response
    {
        $box = Auth::box();
        if (!$box) return DashboardController::unauthorized();

        $email = $req->post['email'] ?? '';
        $validate = new Validate();
        $validate->email($email);
        if ($validate->hasError()) return $validate->errorResponse();

        $issued = Token::issue('transfer', $box['id'], ['email' => $email], self::TRANSFER_TTL);
        $url = Request::baseUrl() . '/dashboard/transfer/accept/' . $issued['token'];
        Mailer::send($email, '【happimo】質問箱管理者の引き継ぎのご案内', "「{$box['title']}」の管理者を引き継ぐには、10分以内に次のURLを開いて新しいパスワードを設定してください。\n{$url}");
        return Response::redirect('/dashboard/' . $box['dashboard_token'] . '?notice=transfer_sent');
    }

    public static function showTransferAcceptForm(Request $req, array $args): Response
    {
        if (!Token::find($args['token'], 'transfer')) return self::invalidPage();
        return Response::html(View::page('transfer_accept', ['title' => '管理者の引き継ぎ', 'token' => $args['token']]));
    }

    public static function acceptTransfer(Request $req, array $args): Response
    {
        $token = $req->post['token'] ?? '';
        $validate = new Validate();
        $validate->password($req->post['password'] ?? '');
        $validate->confirm($req->post['confirm'] ?? '', $req->post['password'] ?? '');
        if ($validate->hasError()) return $validate->errorResponse();

        $row = Token::find($token, 'transfer');
        if (!$row) return Response::tokenError();
        Box::transfer($row['box_id'], password_hash($req->post['password'], PASSWORD_DEFAULT), $row['payload']['email']);
        Token::delete($token);
        return Response::redirect('/login');
    }

    // ---- 共通 ----

    /** ログイン中の管理者の登録メール宛にトークン付きURLを送る */
    private static function sendLink(string $type, string $path, string $subject, string $lead): Response
    {
        $box = Auth::box();
        if (!$box) return DashboardController::unauthorized();
        $issued = Token::issue($type, $box['id'], [], self::LINK_TTL);
        Mailer::send($box['email'], "【happimo】{$subject}", $lead . "\n" . Request::baseUrl() . $path . $issued['token']);
        return Response::json(['type' => 'sent']);
    }

    private static function invalidPage(): Response
    {
        return Response::html(View::page('invalid_token', ['title' => 'URLが無効です']), 400);
    }
}
