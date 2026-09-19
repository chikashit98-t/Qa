<?php

namespace App\Controller;

use App\Cores\Auth;
use App\Cores\Request;
use App\Cores\Response;
use App\Cores\Validate;
use App\Cores\View;
use App\Models\Box;
use App\Models\LoginThrottle;
use App\Models\Question;
use App\Models\Status;

final class DashboardController
{
    public static function showLoginForm(Request $req, array $args): Response
    {
        if ($box = Auth::box()) {
            return Response::redirect('/dashboard/' . $box['dashboard_token']);
        }
        return Response::html(View::page('login', ['title' => '管理者ログイン']));
    }

    public static function login(Request $req, array $args): Response
    {
        $boxID = $req->post['boxID'] ?? '';
        $password = $req->post['password'] ?? '';

        $validate = new Validate();
        $validate->requiredOnly($boxID, 'boxID');
        $validate->requiredOnly($password, 'password');
        if ($validate->hasError()) {
            return $validate->errorResponse();
        }

        $keys = LoginThrottle::keys($boxID, $_SERVER['REMOTE_ADDR'] ?? '');
        if ($wait = LoginThrottle::lockedFor($keys)) {
            return Response::json(['errors' => [
                'password' => [['code' => 'password.locked', 'arg' => [(string) ceil($wait / 60)]]]
            ]], 429);
        }

        $box = Box::findByPublicId($boxID);
        // boxが無くてもハッシュ計算を行い、応答時間でIDの存在が分からないようにする
        $ok = password_verify($password, $box['password_hash'] ?? password_hash('dummy', PASSWORD_DEFAULT));
        if (!$box || !$ok) {
            LoginThrottle::fail($keys);
            return Response::json(['errors' => [
                'password' => [['code' => 'password.invalid']]
            ]], 401);
        }

        LoginThrottle::clear($keys[0]);
        Auth::login($box);
        return Response::redirect('/dashboard/' . $box['dashboard_token']);
    }

    public static function logout(Request $req, array $args): Response
    {
        Auth::logout();
        return Response::redirect('/');
    }

    public static function show(Request $req, array $args): Response
    {
        $box = Auth::box();
        if (!$box) return Response::redirect('/login');
        if ($box['dashboard_token'] !== $args['dashboard_id']) {
            return Response::redirect('/dashboard/' . $box['dashboard_token']);
        }
        return Response::html(View::page('dashboard', [
            'title' => '管理画面',
            'box' => $box,
            'dashboard_id' => $box['dashboard_token'],
            'questions' => Question::list($box['id'], false),
            'notice' => $req->query['notice'] ?? '',
        ]));
    }

    public static function setting(Request $req, array $args): Response
    {
        $box = Auth::box();
        if (!$box) return self::unauthorized();

        $boxID = $req->post['boxID'] ?? '';
        $boxTitle = $req->post['boxTitle'] ?? '';

        $validate = new Validate();
        $validate->boxTitle($boxTitle);
        $validate->boxID($boxID);
        if ($validate->hasError()) {
            return $validate->errorResponse();
        }
        if (!Box::updateSetting($box['id'], $boxID, $boxTitle)) {
            $validate->addError('boxID', 'taken');
            return $validate->errorResponse();
        }
        return Response::json(['type' => 'setting']);
    }

    public static function answer(Request $req, array $args): Response
    {
        $box = Auth::box();
        if (!$box) return self::unauthorized();

        $title = $req->post['answerTitle'] ?? '';
        $content = $req->post['answerContent'] ?? '';
        $status = Status::tryFrom($req->post['status'] ?? '');

        $validate = new Validate();
        $validate->answerTitle($title);
        $validate->answerContent($content);
        if (!$status) $validate->addError('status', 'invalid');
        if ($validate->hasError()) {
            return $validate->errorResponse();
        }
        // 回答保存時、回答待ちのままなら自動で公開にする（非公開は管理者の意思なので維持）
        if ($status === Status::PENDING) $status = Status::PUBLIC;
        if (!Question::answer($box['id'], $req->post['questionID'] ?? '', $title, $content, $status)) {
            return Response::json(['errors' => ['_' => [['code' => 'question.notFound']]]], 404);
        }
        return Response::json(['type' => 'answer', 'status' => $status->value]);
    }

    public static function deleteQuestion(Request $req, array $args): Response
    {
        $box = Auth::box();
        if (!$box) return self::unauthorized();
        if (!Question::softDelete($box['id'], $req->post['questionID'] ?? '')) {
            return Response::json(['errors' => ['_' => [['code' => 'question.notFound']]]], 404);
        }
        return Response::json(['type' => 'deleted']);
    }

    public static function unauthorized(): Response
    {
        return Response::json(['errors' => ['_' => [['code' => 'auth.required']]]], 401);
    }
}
