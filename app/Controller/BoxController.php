<?php

namespace App\Controller;

use App\Cores\Auth;
use App\Cores\Mailer;
use App\Cores\Request;
use App\Cores\Response;
use App\Cores\Validate;
use App\Cores\View;
use App\Models\Box;
use App\Models\Question;
use App\Models\Token;

final class BoxController
{
    private const CREATE_TTL = 1800;

    public static function showCreateForm(Request $req, array $args): Response
    {
        return Response::html(View::page('box_create', ['title' => '質問箱作成 1/2']));
    }

    public static function showCreateConfirmForm(Request $req, array $args): Response
    {
        return Response::html(View::page('box_create_confirm', ['title' => '質問箱作成 2/2']));
    }

    public static function showCreateEmailConfirmForm(Request $req, array $args): Response
    {
        Auth::start();
        if (!Token::find($_SESSION['create_token'] ?? '', 'box_create')) {
            return Response::redirect('/box/create');
        }
        return Response::html(View::page('email_confirm', [
            'title' => 'メールアドレス確認',
            'action' => '/box/create/email-confirm',
            'resendAction' => '/box/create/email-confirm/resend',
            'token' => '',
        ]));
    }

    public static function show(Request $req, array $args): Response
    {
        $box = Box::findByPublicId($args['box_id']);
        if (!$box) return Response::notFound();
        return Response::html(View::page('box_show', [
            'title' => $box['title'],
            'box' => $box,
            'questions' => Question::list($box['id'], true),
        ]));
    }

    public static function showQuestionForm(Request $req, array $args): Response
    {
        $box = Box::findByPublicId($args['box_id']);
        if (!$box) return Response::notFound();
        return Response::html(View::page('question_create', ['title' => '質問する', 'box' => $box]));
    }

    public static function createQuestion(Request $req, array $args): Response
    {
        $box = Box::findByPublicId($args['box_id']);
        if (!$box) return Response::notFound();

        $title = $req->post['questionTitle'] ?? '';
        $content = $req->post['content'] ?? '';
        $validate = new Validate();
        $validate->questionTitle($title);
        $validate->content($content);
        if ($validate->hasError()) return $validate->errorResponse();

        Question::create($box['id'], $title, $content);
        return Response::redirect('/b/' . rawurlencode($box['box_id']));
    }

    public static function createBox(Request $req, array $args): Response
    {
        $boxTitle = $req->post['boxTitle'] ?? '';
        $boxID = $req->post['boxID'] ?? '';
        $password = $req->post['password'] ?? '';
        $passwordConfirm = $req->post['confirm'] ?? '';
        $email = $req->post['email'] ?? '';

        $validate = new Validate();
        $validate->boxTitle($boxTitle);
        $validate->boxID($boxID);
        $validate->password($password);
        $validate->confirm($passwordConfirm, $password);
        $validate->email($email);
        if (!$validate->hasError() && Box::findByPublicId($boxID)) {
            $validate->addError('boxID', 'taken');
        }
        if ($validate->hasError()) {
            return $validate->errorResponse();
        }

        $issued = Token::issue('box_create', null, [
            'title' => $boxTitle,
            'boxID' => $boxID,
            'hash' => password_hash($password, PASSWORD_DEFAULT),
            'email' => $email,
        ], self::CREATE_TTL, true);
        Auth::start();
        $_SESSION['create_token'] = $issued['token'];
        self::mailCode($email, $issued['code'], $boxTitle, $boxID);

        return Response::json(['type' => 'sent', 'redirect' => '/box/create/email-confirm']);
    }

    public static function confirmEmail(Request $req, array $args): Response
    {
        $emailCode = $req->post['confirmCode'] ?? '';

        $validate = new Validate();
        $validate->confirmCode($emailCode);
        if ($validate->hasError()) {
            return $validate->errorResponse();
        }

        Auth::start();
        $token = $_SESSION['create_token'] ?? '';
        $row = Token::find($token, 'box_create');
        if (!$row) return Response::tokenError();

        if (!Token::checkCode($row, $emailCode)) {
            $validate->addError('confirmCode', 'mismatch');
            return $validate->errorResponse();
        }
        $p = $row['payload'];
        $box = Box::create($p['boxID'], $p['title'], $p['hash'], $p['email']);
        Token::delete($token);
        unset($_SESSION['create_token']);
        if (!$box) {
            $validate->addError('confirmCode', 'taken');
            return $validate->errorResponse();
        }
        return Response::redirect('/login');
    }

    public static function resendEmailCode(Request $req, array $args): Response
    {
        Auth::start();
        $token = $_SESSION['create_token'] ?? '';
        $row = Token::find($token, 'box_create');
        if (!$row) return Response::tokenError();
        $p = $row['payload'];
        self::mailCode($p['email'], Token::resetCode($token, self::CREATE_TTL), $p['title'], $p['boxID']);
        return Response::json(['type' => 'resent']);
    }

    public static function mailCode(string $to, string $code, string $boxTitle, string $boxID): void
    {
        $url = Request::baseUrl() . '/b/' . rawurlencode($boxID);
        $body = "確認コード: {$code}\n\n" .
            "質問箱「{$boxTitle}」の作成手続きです。\n" .
            "URL: {$url}\n\n" .
            "30分以内に入力してください。心当たりがない場合はこのメールを破棄してください。";
        Mailer::send($to, '【happimo】メールアドレスの確認コード', $body);
    }
}
