# note

## mysql

datatype = utf8mb4 utf8mb4_bin 大文字、小文字を分けて保存する

## 質問箱管理URL

ランダ(64桁くらい)な文字列。Google Driveの共有みたいに管理者がパス、メールを登録。管理者がパスを変更できるようにする。

者を変えたい時、前の管理者が後の管理者にURL送る形にする。有効期限は10分とかにする。

## config

## フォーム送信（validation.js）

data-validate付きフォームはvalidation.jsがfetchで送信する（GETはクエリ文字列、それ以外はJSONボディ）。サーバーは以下を返す想定:
- 成功: Response::redirect()で本物のHTTPリダイレクト（303）を返す。fetchが自動で追いかけるので、JS側はresponse.redirectedを見てlocation.href = response.urlで遷移する（JSONのredirectフィールドは使わない）
- 失敗: `{ "errors": { フィールド名: [{ "code": "namespace.rule" }] } }` → クライアント側と同じ showErrors() でエラー表示

エラー系のステータスは400番台にする。
- 422: 入力値バリデーションエラー
- 401: 未ログイン（dashboard等）
- 400: トークン無効/期限切れ（パスワード再設定・引き継ぎ等）
- 404: ルート不一致
- 303: 成功時のリダイレクト（Response::redirectのデフォルト）

## pages

質問のstatusは3種類、すべて管理者が手動設定（自動遷移なし）:
- public: 公開
- publicoff: 非公開
- pending: 回答待ち

管理者ログインはPHPセッションで保持（$_SESSIONにbox_idを保存、ブラウザを閉じると切れる想定）。

ログインはID（＝boxID）＋パスワード方式。admin_loginはどこからでも到達可（ヘッダーの「管理者ログイン」ボタンは全ページ共通で復活）。dashboardの64桁URLはブックマーク用の利便性リンクであり、セキュリティの主体ではない（無くてもID+パスワードでログイン可能）。

boxID変更＝ログインIDの変更を兼ねる。

### layout（layout.php）

全ページ共通。ヘッダー（ロゴ→homeのみ）とエラーポップアップダイアログ（共通コンポーネント）。

アクセス制御: なし（誰でも表示可）

### home（home.php）

役割: トップページ。サービス説明と質問箱作成への導線。

アクセス制御: 誰でも

入力: なし

DB操作: なし

遷移: 「質問箱作成」→createBox1、「管理者ログイン」→admin_login

### createBox1（createBox.php）

役割: 質問箱作成 手順1（タイトル・URL指定）

アクセス制御: 誰でも

入力: boxTitle（必須、8〜20文字）, boxID（任意、10〜50文字、半角英数記号）

DB操作: なし（この時点では未保存。createBox2の入力とまとめてINSERT）

遷移: 「次へ」→createBox2（入力値を引き継ぐ）、「戻る」→home

備考: boxID未入力時はランダムな文字列を自動生成

### createBox2（createBox2.php）

役割: 質問箱作成 手順2（管理者のパスワード・メールアドレス登録）

アクセス制御: 誰でも（createBox1からの遷移前提）

入力: password, passwordConfirm, email

DB操作: box新規作成（boxTitle, boxID, passwordのハッシュ, email, dashboardトークンをINSERT）、確認コード発行

遷移: 確認コードをメール送信→emailConfirmation

### box（box.php）

役割: 質問箱の公開一覧。質問の閲覧・投稿。

アクセス制御: 誰でも（boxIDでアクセス）

表示対象: statusがpublicの質問のみ

入力: 検索語・ソート条件（GETパラメータ、サーバー側でSQL絞り込み）

DB操作: 質問一覧のSELECT（boxIDからbox_idを特定し、status=publicのものを検索・ソート条件付きで取得）

遷移: 「質問」ボタン→createQuestion

### createQuestion（createQuestion.php）

役割: 質問の投稿

アクセス制御: 誰でも

入力: questionTitle（必須、〜50文字）, content（必須、〜200文字）

DB操作: 質問をINSERT（該当box_idに紐付け、初期status=pending）

遷移: 投稿後→box

### admin_login（admin_login.php）

役割: 管理者ログイン。どこからでも到達可能。

アクセス制御: 誰でも（ID・パスワードを知っている前提）

入力: boxID, password

DB操作: boxIDからboxを特定し、passwordのハッシュ照合

遷移: 成功→dashboard（$_SESSIONにbox_idを保存）

### dashboard（dashboard.php）

役割: 管理者用ダッシュボード。質問への回答編集、box設定変更。

アクセス制御: 要ログイン（$_SESSIONのbox_id必須。未ログインならadmin_loginへリダイレクト）

表示対象: box内の全質問（status問わず）

質問カード編集フォーム:
- 入力: answerTitle（必須、〜50文字）, content（必須、〜200文字）, status（public/publicoff/pending、手動切替ボタン）
- DB操作: 該当質問をUPDATE
- 削除ボタン: 確認ダイアログを挟んでソフトデリート（DBからは消さず、非表示フラグ/deletedステータス等で管理画面・公開一覧の両方から除外）

設定ダイアログ:
- 表示: box URL（boxID）、dashboard URL（ブックマーク用の利便性リンク、コピー可）
- 入力: boxTitle, boxID（変更。ログインIDも同時に変わる）
- DB操作: box情報をUPDATE
- 「パスワード変更」「メールアドレス変更」ボタン: その場でフォーム入力させず、登録済みメールアドレス宛にURL付きメールを送信するのみ（案内メッセージ表示）。実際の変更は専用ページで行う
- 「次の管理者に引き継ぐ」ボタン: transferAdminへ遷移
- ログアウト: セッション破棄→home

### passwordChange（passwordChange.php）

役割: パスワード再設定。dashboard設定内の「パスワード変更」ボタン押下で登録メールに送られるURL（トークン付き）からのみ到達。

アクセス制御: URLのトークンが有効な場合のみ（トークンはメール送信時にサーバー側で発行・検証）

入力: password（新パスワード、8文字以上）, passwordConfirm

DB操作: トークン検証後、該当boxのpasswordハッシュをUPDATE

遷移: 変更成功→admin_login（再ログインさせる）

### emailChange（emailChange.php）

役割: メールアドレス変更 手順1。dashboard設定内の「メールアドレス変更」ボタン押下で現登録メールに送られるURL（トークン付き）からのみ到達し、新しいメールアドレスを入力する。

アクセス制御: URLのトークンが有効な場合のみ

入力: email（新しいメールアドレス）

DB操作: なし（この時点では未保存。新アドレス宛に確認コードを発行）

遷移: 送信後→emailConfirmation（新アドレス宛の確認コードを入力）

「質問箱を開く」ボタン: box.php（公開一覧）を新しいタブで開く

### transferAdmin（transferAdmin.php）

役割: 管理者引き継ぎ 手順1。dashboard設定内の「次の管理者に引き継ぐ」ボタンから遷移し、次の管理者のメールアドレスを入力する。

アクセス制御: 要ログイン（現管理者本人のみ）

入力: email（次の管理者のメールアドレス）

DB操作: なし（この時点では未保存。次の管理者宛に10分間有効な引き継ぎ用URL＋トークンを発行）

遷移: 送信後→dashboardに戻る（案内メッセージ表示）

### transferAccept（transferAccept.php）

役割: 管理者引き継ぎ 手順2。次の管理者がメール内URL（トークン付き、10分で失効）から到達し、新しいパスワードを設定して引き継ぎを確定する。

アクセス制御: URLのトークンが有効な場合のみ

入力: password（新パスワード、8文字以上）, passwordConfirm

DB操作: トークン検証後、該当boxのpasswordハッシュをUPDATE、登録メールアドレスを次の管理者のものに更新。完了と同時に旧パスワードは無効化

遷移: 完了→admin_login（新しいID・パスワードで再ログイン）

### emailConfirm（emailConfirmation.php）

役割: メールアドレス確認。box新規作成時・メールアドレス変更時の2フローで共通利用。

アクセス制御: 誰でも（直前のフローで発行されたコードを知っている前提）

入力: emailCode（8桁）

DB操作: コード照合（一致すれば該当フローを確定：box作成完了／email更新）

遷移: 確認成功→admin_login。「確認コード再送」ボタンあり
