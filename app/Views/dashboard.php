<?php

/** @var string $dashboard_id */
/** @var array $box */
/** @var list<App\Models\Question> $questions */
/** @var string $notice */
$boxUrl = App\Cores\Request::baseUrl() . '/b/' . rawurlencode($box['box_id']);
$dashboardUrl = App\Cores\Request::baseUrl() . '/dashboard/' . $dashboard_id;
?>
<main class="box-main">
    <div class="dashboard-controll">
        <input type="button" value="質問箱を開く" class="inner-shadow main-button rounded"
            onclick="window.open('/b/<?= e(rawurlencode($box['box_id'])) ?>', '_blank', 'noopener')">
        <input type="button" value="設定" id="setting" class="box-shadow main-button-primary rounded">
    </div>
    <div class="box-header">
        <span class="box-header-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                stroke-linejoin="round">
                <rect x="6" y="4" width="12" height="16" rx="2" />
                <path d="M9 4V3a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1" />
                <path d="M9 10h6M9 14h6M9 18h3" />
            </svg>
        </span>
        <div>
            <p class="subtitle-text box-subtitle"><?= e($box['title']) ?></p>
            <p class="sub-text box-header-note">管理者ダッシュボード</p>
        </div>
    </div>
    <div class="box-body">
        <div class="list-control">
            <p class="list-search"><img src="/src/img/search.svg" alt="search"><input type="text"
                    class=" rounded box-shadow border input-text"></p>
            <div class="list-sort">
                <div class="list-sort-view">
                    <div class="list-sort-list">
                        <span class="sort-label sub-text">並び替え</span>
                        <button type="button" class="sort-button rounded" data-key="posted" data-sort="desc" data-type="order">
                            <p class="sub-text">投稿順</p>
                            <svg width="12" height="12" viewBox="-12 -12 24 24" version="1.1"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    style="stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:3.9;stroke-dasharray:none;stroke-opacity:1;paint-order:stroke fill markers"
                                    d="m -5.9999993,-1.3228348e-6 8.4852823,-4e-7" class="sort-arrow-left" />
                                <path
                                    style="stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:3.9;stroke-dasharray:none;stroke-opacity:1;paint-order:stroke fill markers"
                                    d="M 6.000002,-1.3228348e-6 H -2.4852808" class="sort-arrow-right" />
                            </svg>
                        </button>
                        <button type="button" class="sort-button rounded" data-key="answered" data-sort="none" data-type="order">
                            <p class="sub-text">回答順</p>
                            <svg width="12" height="12" viewBox="-12 -12 24 24" version="1.1"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    style="stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:3.9;stroke-dasharray:none;stroke-opacity:1;paint-order:stroke fill markers"
                                    d="m -5.9999993,-1.3228348e-6 8.4852823,-4e-7" class="sort-arrow-left" />
                                <path
                                    style="stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:3.9;stroke-dasharray:none;stroke-opacity:1;paint-order:stroke fill markers"
                                    d="M 6.000002,-1.3228348e-6 H -2.4852808" class="sort-arrow-right" />
                            </svg>

                        </button>
                        <span class="sort-divider" aria-hidden="true"></span>
                        <span class="sort-label sub-text">絞り込み</span>
                        <button type="button" class="sort-button rounded" data-key="waiting" data-sort="none" data-type="exist">
                            <p class="sub-text">回答待ち</p>
                        </button>
                        <button type="button" class="sort-button rounded" data-key="public" data-sort="none" data-type="exist">
                            <p class="sub-text">公開</p>
                        </button>
                        <button type="button" class="sort-button rounded" data-key="publicoff" data-sort="none" data-type="exist">
                            <p class="sub-text">非公開</p>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-view">
            <div class="box-card-list">
                <?php foreach ($questions as $question): ?>
                    <form method="post" action="/dashboard/answer" class="card rounded box-shadow" data-card="edit" data-validate
                        data-posted="<?= $question->createdAt ?>" data-answered="<?= (int) $question->answeredAt ?>" data-status="<?= e($question->status->value) ?>"
                        data-search="<?= e(mb_strtolower($question->title . ' ' . $question->content . ' ' . $question->answerTitle . ' ' . $question->answerContent)) ?>"
                        data-only-validate="save">
                        <input type="hidden" name="questionID" value="<?= e($question->id) ?>">
                        <div class="question rounded box-shadow" tabindex="0" role="button" aria-expanded="false">
                            <div class="card-main">
                                <p class="card-label inner-shadow">
                                    Q
                                </p>
                                <div>
                                    <p class="card-top"><span class="subtitle-text"><?= e($question->title) ?></span><span
                                            class="card-infos info-text">
                                            <span><?= e($question->answeredStatus()) ?></span>-<span><?= e(App\Models\Question::ago($question->createdAt)) ?></span></span></p>
                                    <p class="main-text font-regular"><?= nl2br(e($question->content)) ?></p>
                                </div>
                            </div>
                            <img src="/src/img/down.svg" alt="down arrow">
                        </div>
                        <div class="answer rounded">
                            <div class="card-main answer-card">
                                <p class="card-label box-shadow">A</p>
                                <div>
                                    <div class="card-top">
                                        <div class="dashboard-answer">
                                            <div class="form-group">
                                                <label for="title" class="form-label">
                                                    <div class="form-label-container">
                                                        <p class="main-text">タイトル</p>
                                                        <p class="form-required main-text">*</p>
                                                    </div>
                                                    <p class="form-char-count info-text">0/50</p>
                                                    <!-- 計算してくれるからBG処理いらない -->
                                                </label>
                                                <textarea name="answerTitle"
                                                    class="form-input rounded box-shadow border input-text"
                                                    data-required data-max-length="50" data-min-length="1"
                                                    data-validate-on><?= e($question->answerTitle) ?></textarea>
                                                <p class="form-error-message sub-text"></p>
                                            </div>
                                            <div class="form-group">
                                                <label for="content" class="form-label">
                                                    <div class="form-label-container">
                                                        <p class="main-text">内容</p>
                                                        <p class="form-required main-text">*</p>
                                                    </div>
                                                    <p class="form-char-count info-text">0/200</p>
                                                    <!-- 計算してくれるからBG処理いらない -->

                                                </label>
                                                <textarea name="answerContent"
                                                    class="input-text form-input rounded box-shadow border "
                                                    data-required data-max-length="200" data-min-length="1"
                                                    data-validate-on><?= e($question->answerContent) ?></textarea>
                                                <p class="form-error-message sub-text"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="dashboard-answer-submit-button">
                            <label for="status">
                                <button type="button" class="status-icon border rounded status-change-button"
                                    data-status="<?= e($question->status->value) ?>">
                                    <img src="/src/img/<?= e($question->status->value) ?>.svg" alt="">
                                    <span class="main-text">公開</span><!-- 計算してくれるからBG処理いらない -->
                                </button>
                                <input type="text" name="status" hidden>
                            </label>
                            <div class="dashborad-form-submit">
                                <button type="button" value="delete"
                                    class="main-button-danger rounded box-shadow border delete-question-button">削除</button>
                                <button type="button" value="cancel"
                                    class="inner-shadow main-button rounded reset-answer-button">戻す</button>
                                <button type="submit" value="save"
                                    class="box-shadow main-button-primary rounded dashboard-submit-button"
                                    disabled>保存</button>
                            </div>
                        </div>
                    </form>
                <?php endforeach; ?>
                <?php if (!$questions): ?>
                    <div class="box-empty" id="emptyMessage">
                        <span class="box-empty-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 5h16a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H9l-4 4v-4H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                            </svg>
                        </span>
                        <p class="main-text">まだ質問はありません</p>
                        <p class="sub-text">質問箱のURLを共有すると、ここに届きます</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<dialog id="settingView" class="setting-view rounded box-shadow">
    <form action="/dashboard/setting" method="post" data-validate data-only-validate="save" id="settingForm">
                <p class="subtitle-text">設定</p>
        <div class="form-group">
            <label class="form-label">
                <div class="form-label-container">
                    <p class="main-text">リンク</p>
                </div>
            </label>
            <div class="setting-links">
                <div>
                    <p class="main-text"><?= e($boxUrl) ?></p>
                    <button type="button" id="copyBoxURL" name="copyBoxURL" value="copyBoxURL"
                        class="main-button-primary  rounded box-shadow border"
                        data-copy="<?= e($boxUrl) ?>">質問箱URLをコピー</button>
                </div>
                <div>
                    <p class="main-text"><?= e($dashboardUrl) ?></p>
                    <button type="button" id="copyDashboardURL" name="copyDashboardURL" value="copyDashboardURL"
                        class="main-button-primary  rounded box-shadow border"
                        data-copy="<?= e($dashboardUrl) ?>">管理URLをコピー</button>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="boxTitle" class="form-label">
                <div class="form-label-container">
                    <p class="main-text">タイトル</p>
                </div>
                <p class="form-char-count info-text">0/20</p>

            </label>
            <input type="text" id="boxTitle" name="boxTitle" class="input-text form-input rounded box-shadow border"
                value="<?= e($box['title']) ?>" data-required data-min-length="8" data-max-length="20" data-validate-on>
            <p class="form-error-message info-text"></p>
        </div>
        <div class="form-group">
            <label class="form-label">
                <div class="form-label-container">
                    <p class="main-text">パスワード・メールアドレス</p>
                </div>
            </label>
            <div>
                <button type="button" id="changePassword" value="changePassword"
                    class="main-button-primary  rounded box-shadow border">パスワード変更</button>
                <button type="button" id="changeEmail" name="changeEmail" value="changeEmail"
                    class="main-button-primary  rounded box-shadow border">メールアドレス変更</button>
            </div>
        </div>
        <div class="form-group">
            <label for="boxID" class="form-label">
                <div class="form-label-container">
                    <p class="main-text">質問箱ID</p>
                    <p class="form-required main-text"></p>
                </div>
                <p class="form-char-count info-text">0/20</p>

            </label>
            <input type="text" id="boxID" name="boxID" value="<?= e($box['box_id']) ?>"
                class="form-input rounded box-shadow border input-text" data-max-length="50" data-min-length="10" data-required
                data-invalid-char data-validate-on>
            <p class="main-text">質問箱のURL：<?= e(App\Cores\Request::baseUrl()) ?>/b/<span id="box_id"><?= e($box['box_id']) ?></span></p>
            <p class="form-error-message sub-text">
                <span class="sub-text" data-validate-nodelete>* ログインIDも兼ねる</span>
                <span class="sub-text" data-validate-nodelete data-code="boxID.invalidChar">*
                    半角英数字、記号「-_!*'()」が使用できます。</span>
                <span data-validate-nodelete data-code="boxID.minLength">
                    * 10文字以上</span>
            </p>
        </div>
        <div class="settings-danger-zone">
            <p class="sub-text settings-danger-label">アカウント操作</p>
            <div class="form-group">
                <label class="form-label">
                    <div class="form-label-container">
                        <p class="main-text">管理者の引き継ぎ</p>
                    </div>
                </label>
                <button type="button" id="transferAdmin" name="transferAdmin" value="transferAdmin"
                    class="main-button-primary  rounded box-shadow border">次の管理者に引き継ぐ</button>
            </div>
            <div class="form-group">

                <button type="button" id="logout" name="logout" value="logout"
                    class="main-button-danger  rounded box-shadow border">ログアウト</button>

            </div>
        </div>
        <div>
            <button type="button" class="popup-sub main-button rounded inner-shadow" value="close" id="closeSetting">閉じる</button>
            <button type="submit" class="popup-submit main-button-primary rounded box-shadow" id="submitSetting"
                value="save">保存</button>
        </div>
    </form>
</dialog>
<script>
    const box_id_input = document.getElementById("boxID");
    const box_id_output = document.getElementById("box_id");
    box_id_input.addEventListener(("input"), () => {
        box_id_output.innerText = box_id_input.value;
    })

    async function postJson(url, body = {}) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        return res;
    }

    document.querySelectorAll('[data-copy]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(btn.dataset.copy);
                await showPopup('message', 'コピーしました', btn.dataset.copy, 'OK');
            } catch (e) {
                await showPopup('error', 'コピーできませんでした', btn.dataset.copy, '閉じる');
            }
        });
    });

    document.querySelectorAll('.delete-question-button').forEach((btn) => {
        btn.addEventListener('click', async () => {
            if (await showPopup('error', '削除確認', 'この質問を削除しますか。元に戻せません。', '削除', 'キャンセル')) {
                const card = btn.closest('.card');
                const res = await postJson('/dashboard/question/delete', {
                    questionID: card.querySelector('input[name="questionID"]').value,
                });
                if (res.ok) {
                    card.remove();
                } else {
                    await showPopup('error', 'エラー', '削除できませんでした。ログインし直してください。', '閉じる');
                }
            }
        });
    });

    // 「戻す」: ページを再読み込みせず、そのカードだけ保存済みの内容に戻す（並び替え・絞り込みを維持）
    const snapshot = (card) => ({
        title: card.querySelector('[name="answerTitle"]').value,
        content: card.querySelector('[name="answerContent"]').value,
        status: card.dataset.status,
    });
    document.querySelectorAll('form.card').forEach((card) => {
        card.saved = snapshot(card);
        card.addEventListener('answer-saved', () => {
            card.saved = snapshot(card);
        });
    });
    document.querySelectorAll('.reset-answer-button').forEach((btn) => {
        btn.addEventListener('click', async () => {
            if (!await showPopup('error', '確認', '編集内容を破棄して保存済みの内容に戻しますか。', '戻す', 'キャンセル')) return;
            const card = btn.closest('.card');
            const saved = card.saved;
            [['answerTitle', saved.title], ['answerContent', saved.content]].forEach(([name, value]) => {
                const field = card.querySelector(`[name="${name}"]`);
                field.value = value;
                field.dispatchEvent(new Event('input', { bubbles: true })); // 文字数表示・エラー表示を更新
            });
            card.querySelector('.status-change-button').setStatus(saved.status);
            card.dataset.status = saved.status;
            card.querySelector('.dashboard-submit-button').disabled = true;
        });
    });

    document.getElementById('logout').addEventListener('click', async () => {
        if (await showPopup('error', 'ログアウト', 'ログアウトしますか。', 'ログアウト', '戻る')) {
            await postJson('/logout');
            location.href = '/';
        }
    })

    const settingView = document.getElementById('settingView');
    const settingBtn = document.getElementById('setting');
    const settingCloseBtn = document.getElementById('closeSetting');

    settingView.addEventListener("click", (e) => {
        if (e.target === settingView) settingView.close("cancel");
    });
    settingBtn.addEventListener('click', () => settingView.showModal());
    settingCloseBtn.addEventListener('click', () => settingView.close("cancel"));

    async function sendLink(url, message) {
        const res = await postJson(url);
        if (res.ok) {
            await showPopup('message', '送信完了', message, 'OK');
        } else {
            await showPopup('error', 'エラー', '送信できませんでした。ログインし直してください。', '閉じる');
        }
    }
    document.getElementById('changePassword').addEventListener('click', () =>
        sendLink('/dashboard/password/request', '登録メールアドレスにパスワード再設定用のURLを送信しました。'));
    document.getElementById('changeEmail').addEventListener('click', () =>
        sendLink('/dashboard/email/request', '登録メールアドレスにメールアドレス変更用のURLを送信しました。'));
    document.getElementById('transferAdmin').addEventListener('click', () => {
        location.href = '/dashboard/transfer';
    });

    <?php if ($notice === 'transfer_sent'): ?>
        showPopup('message', '送信完了', '次の管理者に引き継ぎ用のURLを送信しました（10分間有効）。', 'OK');
        history.replaceState(null, '', location.pathname);
    <?php endif; ?>
</script>
