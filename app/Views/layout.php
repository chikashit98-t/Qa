<?php

/** @var string $content */
/** @var string|null $title */ ?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($title ?? '') !== '' ? $title . ' | happimo' : 'happimo') ?></title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic:wght@300;400;500;700;900&display=swap">
    <link rel="stylesheet" href="/src/root.css?v=<?= (int) @filemtime(ROOT . '/public/src/root.css') ?>">
</head>

<body>

    <header>
        <a href="/"><img src="/src/img/logo.svg" alt="happimo" class="logo"></a>
        <input type="button" value="管理者ログイン" onclick="location.href='/login'"
            class="main-button rounded inner-shadow">
    </header>
    <?= $content ?>
    <dialog id="popup" class="popup rounded border box-shadow">
        <form method="dialog">
            <p class="main-text popup-title">エラー</p>
            <p class="main-text popup-message"></p>
            <div>
                <button type="submit" class="popup-sub main-button rounded inner-shadow" value="cancel"></button>
                <button type="submit" class="popup-submit rounded box-shadow" value="ok"></button>
            </div>
        </form>
    </dialog>
    <script src="/src/root.js?v=<?= (int) @filemtime(ROOT . '/public/src/root.js') ?>"></script>
    <script src="/src/validation.js?v=<?= (int) @filemtime(ROOT . '/public/src/validation.js') ?>"></script>
</body>

</html>