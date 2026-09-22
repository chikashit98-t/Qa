<main>
    <div class="qc-header">
        <h1 class="title-text">メールアドレス変更</h1>
        <p class="sub-text">新しいメールアドレスを入力してください</p>
    </div>
    <p class="sub-text notice notice-info">
        <span class="notice-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                stroke-linejoin="round">
                <path d="M4 5h16a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H9l-4 4v-4H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
            </svg>
        </span>
        送信後、新しいアドレス宛に確認コードが届きます。
    </p>
    <form action="/dashboard/email/change" method="post" data-validate>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div class="form-group">
            <label for="email" class="form-label">
                <div class="form-label-container">
                    <p class="main-text">新しいメールアドレス</p>
                    <p class="form-required main-text">*</p>
                </div>
            </label>
            <input type="email" id="email" name="email" class="form-input rounded box-shadow border input-text"
                data-required data-invalid-char data-validate-on placeholder="例：you@example.com">
            <p class="form-error-message info-text"></p>
        </div>
        <input type="submit" value="変更" class="big-button rounded box-shadow">
    </form>
</main>
