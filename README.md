# happimo（匿名質問箱）

## 起動
```
docker compose up --build   # http://localhost:8080
```
MySQL（utf8mb4_bin）に接続し、テーブルは初回アクセス時に自動作成されます。
メールは既定で `storage/mail.log` に書き出されます（実送信は `MAIL_DRIVER=mail`、`APP_URL` でメール内リンクのベースURLを指定）。

## DBなしで動かす（SQLite）
```
php -d extension=pdo_sqlite -S 127.0.0.1:8099 tests/router.php
```

## テスト
```
DB_DSN=sqlite:storage/test.sqlite php -d extension=pdo_sqlite -S 127.0.0.1:8099 tests/router.php
DB_DSN=sqlite:storage/test.sqlite php -d extension=pdo_sqlite tests/e2e.php
```
仕様は note.md を参照。

## 本番デプロイ
`docs/DEPLOY.md`（Ubuntu + Docker + Cloudflare Tunnel + SMTP）を参照。
