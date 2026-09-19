<?php

/** @var array $box */
/** @var list<App\Models\Question> $questions */

$askUrl = '/b/' . rawurlencode($box['box_id']) . '/questions/create';
?>
<main class="box-main">
    <div class="subtitle-text box-subtitle"><?= e($box['title']) ?></div>
    <div class="box-body">
        <div class="list-control">
            <p class="list-search"><img src="/src/img/search.svg" alt="search"><input type="text"
                    class=" rounded box-shadow border input-text"></p>
            <div class="list-sort">
                <div class="list-sort-view">
                    <div class="list-sort-list">
                        <span class="sort-label sub-text">並び替え</span>
                        <button type="button" class="sort-button rounded" data-key="posted" data-sort="desc" data-type="order">
                            <p class="sub-text">投稿順</p>
                            <svg width="12" height="12" viewBox="-12 -12 24 24" version="1.1"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    style="stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:3.9;stroke-dasharray:none;stroke-opacity:1;paint-order:stroke fill markers"
                                    d="m -5.9999993,-1.3228348e-6 8.4852823,-4e-7" class="sort-arrow-left" />
                                <path
                                    style="stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:3.9;stroke-dasharray:none;stroke-opacity:1;paint-order:stroke fill markers"
                                    d="M 6.000002,-1.3228348e-6 H -2.4852808" class="sort-arrow-right" />
                            </svg>
                        </button>
                        <button type="button" class="sort-button rounded" data-key="answered" data-sort="none" data-type="order">
                            <p class="sub-text">回答順</p>
                            <svg width="12" height="12" viewBox="-12 -12 24 24" version="1.1"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    style="stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:3.9;stroke-dasharray:none;stroke-opacity:1;paint-order:stroke fill markers"
                                    d="m -5.9999993,-1.3228348e-6 8.4852823,-4e-7" class="sort-arrow-left" />
                                <path
                                    style="stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:3.9;stroke-dasharray:none;stroke-opacity:1;paint-order:stroke fill markers"
                                    d="M 6.000002,-1.3228348e-6 H -2.4852808" class="sort-arrow-right" />
                            </svg>

                        </button>
                    </div>
                </div>
                <button type="button" class="main-button sub-text rounded inner-shadow"
                    onclick="extendAll(false);">すべて閉じる</button>
            </div>
        </div>
        <div class="box-view">
            <div class="box-card-list">
                <?php foreach ($questions as $q): ?>
                    <div class="card rounded box-shadow" data-card="view" data-posted="<?= $q->createdAt ?>"
                        data-answered="<?= (int) $q->answeredAt ?>" data-status="<?= e($q->status->value) ?>"
                        data-search="<?= e(mb_strtolower($q->title . ' ' . $q->content . ' ' . $q->answerTitle . ' ' . $q->answerContent)) ?>">
                        <div class="question rounded box-shadow" tabindex="0" role="button" aria-expanded="false">
                            <div class="card-main">
                                <p class="card-label inner-shadow">Q</p>
                                <div>
                                    <p class="card-top"><span class="subtitle-text"><?= e($q->title) ?></span><span
                                            class="card-infos info-text">
                                            <span><?= e($q->answeredStatus()) ?></span>-<span><?= e(App\Models\Question::ago($q->createdAt)) ?></span></span></p>
                                    <p class="main-text font-regular"><?= nl2br(e($q->content)) ?></p>
                                </div>
                            </div>
                            <img src="/src/img/down.svg" alt="down arrow">
                        </div>
                        <div class="answer rounded">
                            <div class="card-main answer-card">
                                <p class="card-label box-shadow">A</p>
                                <div>
                                    <?php if ($q->isAnswered()): ?>
                                        <p class="card-top"><span class="subtitle-text"><?= e($q->answerTitle) ?></span><span
                                                class="card-infos info-text">
                                                <span><?= e(App\Models\Question::ago($q->answeredAt)) ?></span></span></p>
                                        <p class="main-text font-regular"><?= nl2br(e($q->answerContent)) ?></p>
                                    <?php else: ?>
                                        <p class="main-text font-regular">回答をお待ちください。</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (!$questions): ?>
                    <p class="main-text" id="emptyMessage">まだ公開されている質問はありません。</p>
                <?php endif; ?>
            </div>
            <input type="button" value="質問" class="big-button rounded box-shadow main-create-question-button"
                onclick="location.href='<?= e($askUrl) ?>'">
            <button type="button" class="main-button-primary subtitle-text box-shadow circle-create-question-button"
                onclick="location.href='<?= e($askUrl) ?>'"><img src="/src/img/add.svg" alt="add"></button>
        </div>
    </div>
</main>