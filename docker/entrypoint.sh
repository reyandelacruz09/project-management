#!/bin/sh

set -e

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

if [ -z "$(ls -A database/*.sqlite 2>/dev/null)" ]; then
    touch database/database.sqlite
fi

if [ "$1" = "php-fpm" ]; then
    php artisan migrate --force --no-interaction
    php artisan storage:link --force --no-interaction || true
fi

exec "$@"