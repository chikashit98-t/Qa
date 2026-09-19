<main>
    <h1>管理者の引き継ぎ</h1>
    <button type="button" class="main-button rounded inner-shadow main-text"
        onclick="location.href='/dashboard/<?= e($dashboard_id) ?>'">戻る</button>
    <form action="/dashboard/transfer" method="post" data-validate>
        <div class="form-group">
            <label for="email" class="form-label">
                <div class="form-label-container">
                    <p class="main-text">次の管理者のメールアドレス</p>
                    <p class="form-required main-text">*</p>
                </div>
            </label>
            <input type="email" id="email" name="email" class="form-input rounded box-shadow border input-text"
                data-required data-invalid-char data-validate-on>
            <p class="form-error-message info-text"></p>
            <p class="sub-text">10分間有効な引き継ぎ用URLを送信します。次の管理者がパスワードを設定した時点で引き継ぎが完了し、現在のパスワードは無効になります。</p>
        </div>
        <input type="submit" value="送信" class="big-button rounded box-shadow">
    </form>
</main>