<main>
    <button type="button" class="main-button rounded inner-shadow main-text"
        onclick="location.href='/dashboard/<?= e($dashboard_id) ?>'">戻る</button>
    <div class="qc-header">
        <h1 class="title-text">管理者の引き継ぎ</h1>
        <p class="sub-text">次の管理者のメールアドレスを入力してください</p>
    </div>
    <p class="sub-text notice notice-warn">
        <span class="notice-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                stroke-linejoin="round">
                <path d="M12 9v4M12 17h.01" />
                <path d="M10.29 3.86 1.82 18a1 1 0 0 0 .86 1.5h18.64a1 1 0 0 0 .86-1.5L13.71 3.86a1 1 0 0 0-1.72 0Z" />
            </svg>
        </span>
        10分間有効な引き継ぎ用URLを送信します。次の管理者がパスワードを設定した時点で引き継ぎが完了し、現在のパスワードは無効になります。
    </p>
    <form action="/dashboard/transfer" method="post" data-validate>
        <div class="form-group">
            <label for="email" class="form-label">
                <div class="form-label-container">
                    <p class="main-text">次の管理者のメールアドレス</p>
                    <p class="form-required main-text">*</p>
                </div>
            </label>
            <input type="email" id="email" name="email" class="form-input rounded box-shadow border input-text"
                data-required data-invalid-char data-validate-on placeholder="例：you@example.com">
            <p class="form-error-message info-text"></p>
        </div>
        <input type="submit" value="送信" class="big-button rounded box-shadow">
    </form>
</main>