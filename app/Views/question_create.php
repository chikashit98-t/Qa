<main>
    <h1>質問</h1>
    <input type="button" value="戻る" class="main-button rounded inner-shadow"
        onclick="location.href='/b/<?= e(rawurlencode($box['box_id'])) ?>'">
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
                data-validate-on>
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
                data-required data-max-length="200" data-validate-on></textarea>
            <p class="form-error-message info-text"></p>
        </div>
        <input type="submit" value="質問" class="big-button rounded box-shadow">
    </form>
</main>