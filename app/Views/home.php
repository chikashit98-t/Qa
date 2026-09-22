<main>
    <div class="home-hero">
        <p class="sub-text home-eyebrow">匿名の質問箱サービス</p>
        <h1 class="title-text">匿名だから、本音が届く。</h1>
        <p class="main-text home-tagline">自分だけの質問箱をつくって共有するだけ。<br>名前を明かさずに、聞きたいことを聞ける・答えられる。</p>
        <input type="button" value="質問箱をつくる" class="big-button box-shadow rounded"
            onclick="location.href='/box/create'">
        <p class="sub-text home-hero-note">メールアドレスだけで、今すぐ無料ではじめられます。</p>
    </div>

    <section class="home-section">
        <p class="subtitle-text home-section-title">使い方</p>
        <div class="home-steps">
            <div class="home-step">
                <span class="home-step-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 20h4L18.5 9.5a1.5 1.5 0 0 0 0-2.12l-1.88-1.88a1.5 1.5 0 0 0-2.12 0L4 16v4Z" />
                        <path d="M13.5 6.5l4 4" />
                    </svg>
                </span>
                <div>
                    <p class="main-text home-step-title">質問箱をつくる</p>
                    <p class="sub-text">タイトルを決めるだけ。1分で完成します。</p>
                </div>
            </div>
            <div class="home-step">
                <span class="home-step-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3v12M12 3l-3.5 3.5M12 3l3.5 3.5" />
                        <path d="M5 13v5a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-5" />
                    </svg>
                </span>
                <div>
                    <p class="main-text home-step-title">URLを共有する</p>
                    <p class="sub-text">発行されたURLをSNSやグループで共有します。</p>
                </div>
            </div>
            <div class="home-step">
                <span class="home-step-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 5h16a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H9l-4 4v-4H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                    </svg>
                </span>
                <div>
                    <p class="main-text home-step-title">匿名の質問に答える</p>
                    <p class="sub-text">届いた質問に、気になったものから答えます。</p>
                </div>
            </div>
        </div>
    </section>

    <section class="home-section">
        <p class="subtitle-text home-section-title">できあがりイメージ</p>
        <div class="box-view">
            <div class="box-card-list">
                <div class="card rounded box-shadow" data-card="view" show>
                    <div class="question rounded box-shadow" tabindex="0" role="button" aria-expanded="false">
                        <div class="card-main">
                            <p class="card-label inner-shadow">Q</p>
                            <div>
                                <p class="card-top"><span class="subtitle-text">大学生活で困ったことは？</span><span
                                        class="card-infos info-text">
                                        <span>回答済み</span>-<span>3分前</span></span></p>
                                <p class="main-text font-regular">名前を出さずに、気になっていたことを聞けました。</p>
                            </div>
                        </div>
                        <img src="/src/img/down.svg" alt="down arrow">
                    </div>
                    <div class="answer rounded">
                        <div class="card-main answer-card">
                            <p class="card-label box-shadow">A</p>
                            <div>
                                <p class="card-top"><span class="subtitle-text">最初は時間割で迷いました</span><span
                                        class="card-infos info-text">
                                        <span>1分前</span></span></p>
                                <p class="main-text font-regular">先輩に聞くのが一番早かったです。遠慮なく質問してくださいね。</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="home-section">
        <p class="subtitle-text home-section-title">happimoのいいところ</p>
        <div class="home-benefits">
            <div class="home-benefit-card rounded">
                <span class="home-benefit-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="11" width="14" height="9" rx="2" />
                        <path d="M8 11V8a4 4 0 0 1 8 0v3" />
                    </svg>
                </span>
                <p class="main-text home-benefit-title">質問する人にとって</p>
                <p class="sub-text">個人情報を求めないから、気軽に聞けます。</p>
            </div>
            <div class="home-benefit-card rounded">
                <span class="home-benefit-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12Z" />
                        <circle cx="12" cy="12" r="2.5" />
                    </svg>
                </span>
                <p class="main-text home-benefit-title">運営する人にとって</p>
                <p class="sub-text">気付かなかった声が見えて、距離が縮まります。</p>
            </div>
        </div>
    </section>

    <input type="button" value="質問箱をつくる" class="big-button box-shadow rounded"
        onclick="location.href='/box/create'">
</main>