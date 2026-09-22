<main>
    <div class="qc-header">
        <h1 class="title-text">管理者の引き継ぎ</h1>
        <p class="sub-text">新しい管理用パスワードを設定してください</p>
    </div>
    <p class="sub-text notice notice-warn">
        <span class="notice-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                stroke-linejoin="round">
                <path d="M12 9v4M12 17h.01" />
                <path d="M10.29 3.86 1.82 18a1 1 0 0 0 .86 1.5h18.64a1 1 0 0 0 .86-1.5L13.71 3.86a1 1 0 0 0-1.72 0Z" />
            </svg>
        </span>
        設定すると引き継ぎが完了し、現在のパスワードは無効になります。
    </p>
    <form action="/dashboard/transfer/accept" method="post" data-validate>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div class="form-group">
            <label for="password" class="form-label">
                <div class="form-label-container">
                    <p class="main-text">新しい管理用パスワード</p>
                    <p class="form-required main-text">*</p>
                </div>
                <p class="form-char-count info-text">0</p>
            </label>
            <label class="password-input"><input type="password" id="password" name="password"
                    autocomplete="new-password" data-required data-invalid-char data-min-length="8"
                    data-validate-on class="form-input input-text  rounded box-shadow border "
                    placeholder="8文字以上の半角英数字"><img src="/src/img/visibilityoff.svg" alt="visible"></label>
            <p class="form-error-message sub-text">
                <span data-validate-nodelete data-code="password.invalidChar">* 半角英数字、記号</span>
            </p>
        </div>
        <div class="form-group">
            <label for="passwordConfirm" class="form-label">
                <div class="form-label-container">
                    <p class="main-text">新しい管理用パスワード確認</p>
                    <p class="form-required main-text">*</p>
                </div>
            </label>
            <label class="password-input"><input type="password" id="passwordConfirm" name="confirm"
                    autocomplete="new-password" data-required data-validate-on
                    class="form-input input-text  rounded box-shadow border " placeholder="もう一度入力してください"><img
                    src="/src/img/visibilityoff.svg" alt="visible"></label>
            <p class="form-error-message info-text"></p>
        </div>
        <input type="submit" value="引き継ぎを完了" class="big-button rounded box-shadow">
    </form>
</main>
