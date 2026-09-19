FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install pdo_mysql mbstring \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite remoteip headers

COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini "$PHP_INI_DIR/conf.d/99-app.ini"

# 本番用: コードをイメージに含める（開発時はdocker-compose.ymlのボリュームで上書きされる）
COPY app /var/www/html/app
COPY public /var/www/html/public
RUN mkdir -p /var/www/html/storage && chown -R www-data:www-data /var/www/html/storage
