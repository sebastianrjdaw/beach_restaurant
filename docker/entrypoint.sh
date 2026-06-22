#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

if [ ! -x node_modules/.bin/vite ]; then
    npm install
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

php artisan config:clear

until mysqladmin ping -h"${DB_HOST:-mysql}" -P"${DB_PORT:-3306}" -u"${DB_USERNAME:-laravel}" -p"${DB_PASSWORD:-secret}" --silent; do
    echo "Waiting for MySQL..."
    sleep 2
done

php artisan migrate --force
php artisan cache:clear
php artisan filament:assets
php artisan db:seed --force
php artisan db:seed --class=AdminUserSeeder --force

if [ "${1:-start}" = "start" ]; then
    rm -f public/hot
    mkdir -p storage/logs

    if [ "${VITE_DEV_SERVER:-false}" = "true" ]; then
        npm run dev -- --host 0.0.0.0 > storage/logs/vite.log 2>&1 &

        for _ in {1..30}; do
            if [ -f public/hot ]; then
                break
            fi

            sleep 1
        done

        if [ ! -f public/hot ]; then
            echo "Vite dev server did not create public/hot. Building static assets instead."
            cat storage/logs/vite.log || true
            npm run build
        fi
    else
        npm run build
    fi

    exec php artisan serve --host=0.0.0.0 --port=8000
fi

exec "$@"
