#!/usr/bin/env bash
set -e

cd /var/www/html

[ -f .env ] || cp .env.example .env

# app and queue-worker must share one APP_KEY: generate it once into a
# shared volume (noclobber makes the create atomic) and export it.
if [ -z "$APP_KEY" ]; then
    KEY_FILE=storage/keys/app_key
    mkdir -p storage/keys
    (set -o noclobber; echo "base64:$(head -c 32 /dev/urandom | base64)" > "$KEY_FILE") 2>/dev/null || true
    export APP_KEY="$(cat "$KEY_FILE")"
fi

# Only the main app container runs one-off setup tasks.
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
    until mysqladmin ping -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" --skip-ssl --silent; do
        sleep 2
    done

    php artisan migrate --force
    php artisan db:seed --force
    php artisan storage:link || true
    # Drop catalog cache entries that may have been built by a previous release
    php artisan cache:clear
    php artisan config:cache
    php artisan route:cache
fi

mkdir -p storage/app/public/user/images
chown -R www-data:www-data storage bootstrap/cache

exec "$@"
