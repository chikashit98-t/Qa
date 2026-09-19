<?php

use App\Controller\AccountController;
use App\Controller\BoxController;
use App\Controller\DashboardController;
use App\Controller\HomeController;
use App\Cores\Router;

return static function (Router $router) {
    $boxId = '{box_id:[A-Za-z0-9\-_!*\'()]+}';
    $token = '{token:[a-z0-9]{64}}';

    $router->add('GET', '/', [HomeController::class, 'index']);

    // 質問箱の作成・公開・質問投稿
    $router->add('GET', '/box/create', [BoxController::class, 'showCreateForm']);
    $router->add('GET', '/box/create/confirm', [BoxController::class, 'showCreateConfirmForm']);
    $router->add('GET', '/box/create/email-confirm', [BoxController::class, 'showCreateEmailConfirmForm']);
    $router->add('GET', "/b/$boxId", [BoxController::class, 'show']);
    $router->add('GET', "/b/$boxId/questions/create", [BoxController::class, 'showQuestionForm']);
    $router->add('POST', '/box/create', [BoxController::class, 'createBox']);
    $router->add('POST', '/box/create/email-confirm', [BoxController::class, 'confirmEmail']);
    $router->add('POST', '/box/create/email-confirm/resend', [BoxController::class, 'resendEmailCode']);
    $router->add('POST', "/b/$boxId/questions/create", [BoxController::class, 'createQuestion']);

    // ログイン・ダッシュボード
    $router->add('GET', '/login', [DashboardController::class, 'showLoginForm']);
    $router->add('POST', '/login', [DashboardController::class, 'login']);
    $router->add('POST', '/logout', [DashboardController::class, 'logout']);
    $router->add('GET', '/dashboard/{dashboard_id:[a-z0-9]{64}}', [DashboardController::class, 'show']);
    $router->add('POST', '/dashboard/setting', [DashboardController::class, 'setting']);
    $router->add('POST', '/dashboard/answer', [DashboardController::class, 'answer']);
    $router->add('POST', '/dashboard/question/delete', [DashboardController::class, 'deleteQuestion']);

    // アカウント設定（パスワード・メール・引き継ぎ）
    $router->add('POST', '/dashboard/password/request', [AccountController::class, 'requestPasswordChange']);
    $router->add('GET', "/dashboard/password/change/$token", [AccountController::class, 'showPasswordChangeForm']);
    $router->add('POST', '/dashboard/password/change', [AccountController::class, 'changePassword']);
    $router->add('POST', '/dashboard/email/request', [AccountController::class, 'requestEmailChange']);
    $router->add('GET', "/dashboard/email/change/$token", [AccountController::class, 'showEmailChangeForm']);
    $router->add('POST', '/dashboard/email/change', [AccountController::class, 'changeEmail']);
    $router->add('GET', "/dashboard/email/change/confirm/$token", [AccountController::class, 'showEmailChangeConfirmForm']);
    $router->add('POST', '/dashboard/email/change/confirm', [AccountController::class, 'confirmEmailChange']);
    $router->add('POST', '/dashboard/email/change/confirm/resend', [AccountController::class, 'resendEmailChangeCode']);
    $router->add('GET', '/dashboard/transfer', [AccountController::class, 'showTransferForm']);
    $router->add('POST', '/dashboard/transfer', [AccountController::class, 'requestTransfer']);
    $router->add('GET', "/dashboard/transfer/accept/$token", [AccountController::class, 'showTransferAcceptForm']);
    $router->add('POST', '/dashboard/transfer/accept', [AccountController::class, 'acceptTransfer']);
};
