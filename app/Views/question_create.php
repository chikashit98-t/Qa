<main>
    <input type="button" value="戻る" class="main-button rounded inner-shadow"
        onclick="location.href='/b/<?= e(rawurlencode($box['box_id'])) ?>'">
    <div class="qc-header">
        <h1 class="title-text">質問する</h1>
        <p class="sub-text">「<?= e($box['title']) ?>」への質問です</p>
    </div>
    <p class="sub-text notice notice-info">
        <span class="notice-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                stroke-linejoin="round">
                <rect x="5" y="11" width="14" height="9" rx="2" />
                <path d="M8 11V8a4 4 0 0 1 8 0v3" />
            </svg>
        </span>
        名前や個人情報は求められません。安心して聞いてください。
    </p>
    <form action="/b/<?= e(rawurlencode($box['box_id'])) ?>/questions/create" method="post" data-validate>
        <div class="form-group">
            <label for="title" class="form-label">
                <div class="form-label-container">
                    <p class="main-text">タイトル</p>
                    <p class="form-required main-text">*</p>
                </div>
                <p class="form-char-count info-text">0/50</p>

            </label>
            <input type="text" id="title" name="questionTitle"
                class="input-text form-input rounded box-shadow border" data-required data-max-length="50"
                data-validate-on placeholder="例：休日は何をしていますか？">
            <p class="form-error-message info-text"></p>
        </div>
        <div class="form-group">
            <label for="content" class="form-label">
                <div class="form-label-container">
                    <p class="main-text">内容</p>
                    <p class="form-required main-text">*</p>
                </div>
                <p class="form-char-count info-text">0/200</p>

            </label>
            <textarea name="content" id="content" class="input-text form-input rounded box-shadow border"
                data-required data-max-length="200" data-validate-on placeholder="気になっていることを自由に書いてください"></textarea>
            <p class="form-error-message info-text"></p>
        </div>
        <input type="submit" value="質問" class="big-button rounded box-shadow">
    </form>
</main>