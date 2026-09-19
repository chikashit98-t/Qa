# 本番デプロイ手順（Ubuntu 26.04 LTS）

構成: `Cloudflare → cloudflared(Tunnel) → web(Apache+PHP) → db(MySQL)`。サーバーの80/443は開けず、外向き接続のみです。HTTPSはCloudflareが終端します。

## 0. 事前に用意するもの
- Cloudflareで管理しているドメインと、Zero Trust の Tunnel（Networks > Tunnels）
  - Tunnel の「Public Hostname」に `qa.example.com` → サービス `HTTP` / `web:80` を追加する
  - Tunnel のトークンを控える（`.env` の `TUNNEL_TOKEN` に設定）
- 送信用SMTPの情報（メール確認コード・再設定・引き継ぎメールに必須）
  - 自前のMTAは避け、SendGrid / Amazon SES / Gmail(アプリパスワード) 等のSMTPを使う
  - 送信元ドメインにSPF / DKIM を設定しないと迷惑メール扱いになりやすい

## 1. サーバーの準備
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y docker.io docker-compose-v2 ufw git
sudo systemctl enable --now docker

# ファイアウォール（SSHのみ。Tunnelは外向き接続なので80/443は不要）
sudo ufw allow OpenSSH
sudo ufw enable
```
`docker compose version` が動かない場合は、Docker公式の手順（docs.docker.com/engine/install/ubuntu）で導入してください。

## 2. コードの配置
```bash
git clone <リポジトリURL> ~/happimo && cd ~/happimo
# gitを使わない場合: 手元から rsync -av --exclude storage --exclude .git ./ user@server:~/happimo/
```

## 3. 設定（.env）
```bash
cp .env.example .env
chmod 600 .env
nano .env     # DOMAIN, TUNNEL_TOKEN, DB_PASSWORD, DB_ROOT_PASSWORD, MAIL_* を設定
```
- パスワードは十分に長いランダム値にする（`openssl rand -base64 24`）。
- 値に `$` を含める場合は `$$` と書く。

## 4. 起動
```bash
docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml ps
docker compose -f docker-compose.prod.yml logs -f cloudflared web   # Tunnel接続（Registered tunnel connection）・エラーの確認
```
`https://<DOMAIN>/` を開いて確認。テーブルは初回アクセス時に自動作成されます。
動作確認: 質問箱を作成 → 確認コードのメールが届くか → ログイン → 質問投稿 → 回答。

## 5. 運用
### 更新
```bash
cd ~/happimo && git pull
docker compose -f docker-compose.prod.yml up -d --build
```
### バックアップ（毎日3時の例）
```bash
mkdir -p ~/backup
( crontab -l 2>/dev/null; echo '0 3 * * * cd ~/happimo && . ./.env && docker compose -f docker-compose.prod.yml exec -T db mysqldump -uroot -p"$DB_ROOT_PASSWORD" --single-transaction happimo | gzip > ~/backup/happimo_$(date +\%F).sql.gz && find ~/backup -mtime +14 -delete' ) | crontab -
```
復元: `gunzip -c backup.sql.gz | docker compose -f docker-compose.prod.yml exec -T db mysql -uroot -p"$DB_ROOT_PASSWORD" happimo`
※ `.env` に `$$` を使った場合、cron内の `$DB_ROOT_PASSWORD` は展開後の値が異なるので注意。

### DBを覗く
```bash
docker compose -f docker-compose.prod.yml exec db mysql -uroot -p happimo
```
### ログイン制限の手動解除
`DELETE FROM login_attempts;`

## 6. 注意点
- `APP_URL` は `https://<DOMAIN>` に固定されます（メール内リンクのホスト偽装対策）。
- クライアントIPは Cloudflare の `CF-Connecting-IP` から取得します（ログイン試行制限で使用）。webコンテナのポートは公開しないでください（公開すると、このヘッダーを偽装されます）。
- `TUNNEL_TOKEN` は秘密情報です。漏れた場合は Zero Trust の Tunnel 画面でトークンを再発行してください。
- `db` / `appstorage` は Docker ボリュームです。`docker compose down -v` を本番で実行するとデータが消えます。
