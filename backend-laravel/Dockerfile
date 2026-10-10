# ---------- Composer dependencies ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

# ---------- PHP-FPM application (also used by the queue worker) ----------
FROM php:8.3-fpm-alpine AS app

RUN apk add --no-cache bash mysql-client libpng-dev libjpeg-turbo-dev freetype-dev $PHPIZE_DEPS linux-headers \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install pdo_mysql sockets bcmath pcntl gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS linux-headers

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN { echo "upload_max_filesize=12M"; echo "post_max_size=14M"; echo "memory_limit=256M"; } \
    > /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor

RUN composer dump-autoload --optimize --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod +x docker/entrypoint.sh

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["php-fpm"]

# ---------- Nginx origin (serves public/ and proxies PHP to the app) ----------
FROM nginx:1.27-alpine AS web
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public /var/www/html/public
