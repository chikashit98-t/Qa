<main>
    <div class="qc-header">
        <span class="box-header-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                stroke-linejoin="round">
                <rect x="5" y="11" width="14" height="9" rx="2" />
                <path d="M8 11V8a4 4 0 0 1 8 0v3" />
            </svg>
        </span>
        <h1 class="title-text">パスワード再設定</h1>
        <p class="sub-text">新しい管理用パスワードを設定してください</p>
    </div>
    <form action="/dashboard/password/change" method="post" data-validate>
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
                    autocomplete="new-password" data-required data-invalid-char data-min-length="8" data-validate-on
                    class="form-input input-text  rounded box-shadow border " placeholder="8文字以上の半角英数字"><img
                    src="/src/img/visibilityoff.svg" alt="visible"></label>
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
        <input type="submit" value="変更" class="big-button rounded box-shadow">
    </form>
</main>
