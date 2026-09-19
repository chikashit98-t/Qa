<main>
    <h1>管理者の引き継ぎ</h1>
    <p class="main-text">新しいパスワードを設定すると引き継ぎが完了し、現在のパスワードは無効になります。</p>
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
                    data-validate-on class="form-input input-text  rounded box-shadow border "><img
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
                    class="form-input input-text  rounded box-shadow border "><img src="/src/img/visibilityoff.svg"
                    alt="visible"></label>
            <p class="form-error-message info-text"></p>
        </div>
        <input type="submit" value="引き継ぎを完了" class="big-button rounded box-shadow">
    </form>
</main>
