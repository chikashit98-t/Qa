<main>
    <div class="create-box-title">
        <h1>あなただけの質問箱をつくろう</h1>
        <p>1/2</p>
    </div>
    <p class="main-text create-box-lead">タイトルを決めたら、もうワクワクは始まっています。<br>みんなからの質問が届く場所を、あなたの名前で作りましょう。</p>
    <form method="get" action="/box/create/confirm" data-validate data-only-validate="next" id="createBox">
        <!--TODO action、method変更 -->
        <div class="form-group">
            <label for="boxTitle" class="form-label">
                <div class="form-label-container">
                    <p class="main-text">タイトル</p>
                    <p class="form-required main-text">*</p>
                </div>
                <p class="form-char-count info-text">0/20</p>

            </label>
            <input type="text" id="boxTitle" name="boxTitle" class="form-input rounded box-shadow border input-text"
                data-required data-min-length="8" data-max-length="20" data-validate-on placeholder="例：なんでも聞いて！">
            <p class="form-error-message sub-text"></p>
        </div>
        <div class="form-group">
            <label for="boxID" class="form-label">
                <div class="form-label-container">
                    <p class="main-text">質問箱ID</p>

                    <p class="form-required main-text">*</p>
                </div>
                <p class="form-char-count info-text">0/50</p>

            </label>
            <input type="text" id="boxID" name="boxID"
                class="form-input rounded box-shadow border input-text" data-min-length="10" data-max-length="50" data-required
                data-invalid-char data-validate-on>
            <p class="main-text"><?= e(App\Cores\Request::baseUrl()) ?>/b/<span id="box_id"></span></p>
            <p class="form-error-message sub-text">
                <span data-validate-nodelete>* ログインIDも兼ねる</span>
                <span data-validate-nodelete data-code="boxID.invalidChar">* 半角英数字、記号「-_!*'()」が使用できます。</span>
                <span data-validate-nodelete data-code="boxID.minLength">* 10文字以上</span>
            </p>
        </div>
        <div class="create-box-btns">
            <button type="button" value="back" class="main-button rounded inner-shadow"
                onclick="location.href='/'">戻る</button>
            <button type="submit" value="next" class="main-button-primary rounded box-shadow">次へ</button>
        </div>
    </form>
    <p class="sub-text box-preview-label">できあがりイメージ</p>
    <div class="subtitle-text box-subtitle" id="previewTitle">あなたの質問箱</div>
    <div class="box-view">
        <div class="box-card-list">
            <div class="card rounded box-shadow" data-card="view" show>
                <div class="question rounded box-shadow" tabindex="0" role="button" aria-expanded="false">
                    <div class="card-main">
                        <p class="card-label inner-shadow">Q</p>
                        <div>
                            <p class="card-top"><span class="subtitle-text">はじめまして！</span><span
                                    class="card-infos info-text">
                                    <span>回答済み</span>-<span>今</span></span></p>
                            <p class="main-text font-regular">これから、みんなからの質問がここに届きます。</p>
                        </div>
                    </div>
                    <img src="/src/img/down.svg" alt="down arrow">
                </div>
                <div class="answer rounded">
                    <div class="card-main answer-card">
                        <p class="card-label box-shadow">A</p>
                        <div>
                            <p class="card-top"><span class="subtitle-text">ようこそ！</span><span
                                    class="card-infos info-text">
                                    <span>今</span></span></p>
                            <p class="main-text font-regular">答えるたびに、ちょっとした幸せが増えていきます。</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    const titleInput = document.getElementById("boxTitle");
    const box_id_input = document.getElementById("boxID");

    const box_id_output = document.getElementById("box_id");
    box_id_input.addEventListener(("input"), () => {
        box_id_output.innerText = box_id_input.value;
    })
    const previewTitle = document.getElementById('previewTitle');
    titleInput.addEventListener('input', () => {
        previewTitle.textContent = titleInput.value || 'あなたの質問箱';
    });
    const boxTitle = sessionStorage.getItem('create.boxTitle') ?? "";
    const boxID = sessionStorage.getItem('create.boxID') ?? "";
    const boxTitleInput = document.getElementById('boxTitle')
    const boxIDInput = document.getElementById('boxID')
    boxTitleInput.value = boxTitle
    boxIDInput.value = boxID
    previewTitle.textContent = boxTitle || 'あなたの質問箱';

    const form = document.getElementById('createBox');
    form.addEventListener('submit', (event) => {
        const submitTitle = document.getElementById('boxTitle');
        const submitBoxID = document.getElementById('boxID');

        sessionStorage.setItem('create.boxTitle', submitTitle.value);
        sessionStorage.setItem('create.boxID', submitBoxID.value);


    });
    window.addEventListener('load', () => {
        sessionStorage.removeItem('create.boxTitle');
        sessionStorage.removeItem('create.boxID');
    })
</script>