#!/bin/sh
set -e
export PORT="${PORT:-10000}"
cd /var/www/html
php artisan config:clear || true
php artisan migrate --force
php artisan storage:link || true
exec apache2-foreground