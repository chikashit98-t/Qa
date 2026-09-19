<main>
    <h1>管理者ログイン</h1>
    <form action="/login" method="post" data-validate> <!--TODO action、method変更 -->
        <div class="form-group">
            <label for="boxID" class="form-label">
                <div class="form-label-container">
                    <p class="main-text">質問箱ID</p>
                    <p class="form-required main-text">*</p>
                </div>
            </label>
            <input type="text" id="boxID" name="boxID" class="form-input rounded box-shadow border input-text"
                data-required data-invalid-char data-validate-on>
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
</main>