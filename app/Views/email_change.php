<main>
    <h1>メールアドレス変更</h1>
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
                data-required data-invalid-char data-validate-on>
            <p class="form-error-message info-text"></p>
        </div>
        <input type="submit" value="変更" class="big-button rounded box-shadow">
    </form>
</main>
