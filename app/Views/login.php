<main>
    <div class="qc-header">
        <span class="box-header-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                stroke-linejoin="round">
                <circle cx="8" cy="15" r="3.5" />
                <path d="M10.5 12.5 18 5M18 5l2 2M18 5l-2.5 2.5" />
            </svg>
        </span>
        <h1 class="title-text">管理者ログイン</h1>
        <p class="sub-text">質問箱の管理者専用ページです</p>
    </div>
    <form action="/login" method="post" data-validate> <!--TODO action、method変更 -->
        <div class="form-group">
            <label for="boxID" class="form-label">
                <div class="form-label-container">
                    <p class="main-text">質問箱ID</p>
                    <p class="form-required main-text">*</p>
                </div>
            </label>
            <input type="text" id="boxID" name="boxID" class="form-input rounded box-shadow border input-text"
                data-required data-invalid-char data-validate-on placeholder="質問箱作成時に決めたID">
            <p class="form-error-message info-text">

            </p>
        </div>
        <div class="form-group">
            <label for="password" class="form-label">
                <div class="form-label-container">
                    <p class="main-text">パスワード</p>
                    <p class="form-required main-text">*</p>
                </div>
            </label>
            <label class="password-input"><input type="password" id="password" name="password"
                    autocomplete="current-password" data-required data-invalid-char data-validate-on
                    class="form-input input-text  rounded box-shadow border "><img src="/src/img/visibilityoff.svg"
                    alt="visible"></label>
            <p class="form-error-message info-text"></p>
        </div>
        <input type="submit" value="ログイン" class="big-button rounded box-shadow">
    </form>
    <p class="sub-text login-signup-note">質問箱をまだお持ちでない方は<a href="/box/create">こちら</a></p>
</main>