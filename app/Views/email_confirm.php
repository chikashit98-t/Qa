<main>
    <h1>メールアドレス確認</h1>
    <p class="main-text create-box-lead">送信したメールに届いた確認コードを入力すれば、あなたの質問箱がついに完成です。</p>
    <form action="<?= e($action) ?>" method="post" data-validate id="confirmForm">
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div class="form-group">
            <label for="confirmCode" class="form-label">
                <div class="form-label-container">
                    <p class="main-text">確認コード</p>
                    <p class="form-required main-text">*</p>
                </div>
                <p class="form-char-count info-text">0/8</p>

            </label>
            <input type="text" id="confirmCode" name="confirmCode"
                class="form-input rounded box-shadow border input-text" maxlength="8" data-required
                data-char-length="8" data-invalid-char data-validate-on>
            <p class="form-error-message info-text"></p>
        </div>
        <div class="create-box-btns">
            <button type="button" value="resend" id="resendCode" class="main-button rounded inner-shadow">確認コード再送</button>
            <button type="submit" value="confirm" class="main-button-primary rounded box-shadow">確認</button>
        </div>
    </form>
</main>
<script>
    document.getElementById('resendCode').addEventListener('click', async () => {
        const token = document.querySelector('#confirmForm input[name="token"]').value;
        try {
            const res = await fetch(<?= json_encode($resendAction) ?>, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token }),
            });
            if (res.ok) {
                await showPopup('message', '再送しました', '確認コードを再送しました。メールをご確認ください。', 'OK');
            } else {
                await showPopup('error', 'エラー', 'このURLは無効か、有効期限が切れています。最初からやり直してください。', '閉じる');
            }
        } catch (e) {
            await showPopup('error', 'エラー', e.message, '閉じる');
        }
    });
</script>
