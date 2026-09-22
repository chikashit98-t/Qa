<main>
    <div class="create-box-title">
        <h1>あと少しで公開です</h1>
        <p>2/2</p>
    </div>
    <p class="main-text create-box-lead">最後にログイン情報を決めれば、あなたの質問箱がすぐに動き出します。</p>
    <button type="button" class="main-button rounded inner-shadow main-text"
        onclick="location.href='/box/create'">戻る</button>
    <form action="/box/create" method="post" data-validate><!--TODO action、method変更 -->
        <!-- TODO このフォーム送信をサーバーで受けてbox作成（INSERT）を行う -->
        <input type="text" name="boxTitle" id="boxTitle" hidden>
        <input type="text" name="boxID" id="boxID" hidden>
        <div class="form-group">
            <label for="password" class="form-label">
                <div class="form-label-container">
                    <p class="main-text">管理用パスワード</p>
                    <p class="form-required main-text">*</p>

                </div>
                <p class="form-char-count info-text">0</p>
            </label>
            <label class="password-input"><input type="password" id="password" name="password"
                    autocomplete="current-password" data-required data-invalid-char data-min-length="8"
                    data-validate-on class="form-input input-text  rounded box-shadow border "
                    placeholder="8文字以上の半角英数字"><img
                    src="/src/img/visibilityoff.svg" alt="visible"></label>
            <p class="form-error-message sub-text">
                <span data-validate-nodelete data-code="password.invalidChar">* 半角英数字、記号</span>
            </p>
        </div>
        <div class="form-group">
            <label for="passwordConfirm" class="form-label">
                <div class="form-label-container">
                    <p class="main-text">管理用パスワード確認</p>
                    <p class="form-required main-text">*</p>
                </div>

            </label>
            <label class="password-input"><input type="password" id="passwordConfirm" name="confirm"
                    autocomplete="current-password" data-required data-validate-on
                    class="form-input input-text  rounded box-shadow border " placeholder="もう一度入力してください"><img
                    src="/src/img/visibilityoff.svg" alt="visible"></label>
            <p class="form-error-message info-text"></p>
        </div>
        <div class="form-group">
            <label for="email" class="form-label">
                <div class="form-label-container">
                    <p class="main-text">メールアドレス</p>
                    <p class="form-required main-text">*</p>

                </div>

            </label>
            <input type="email" id="email" name="email" class="form-input rounded box-shadow border input-text"
                data-required data-invalid-char data-validate-on placeholder="例：you@example.com">
            <p class="form-error-message info-text"></p>
        </div>
        <p class="sub-text create-box-lead">送信すると確認メールが届きます。コードを入力すれば、質問箱が誕生します！</p>
        <input type="submit" value="作成" class="big-button rounded box-shadow">

    </form>
</main>
<script>
    const boxTitle = sessionStorage.getItem('create.boxTitle') ?? "";
    const boxID = sessionStorage.getItem('create.boxID') ?? "";
    if (boxTitle === '') location.href = "/box/create";
    const boxTitleInput = document.getElementById('boxTitle')
    const boxIDInput = document.getElementById('boxID')
    boxTitleInput.value = boxTitle
    boxIDInput.value = boxID
</script>