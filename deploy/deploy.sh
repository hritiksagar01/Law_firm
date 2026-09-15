#!/bin/bash
# ==============================================================================
# Automated Application Deployment Script (Called by GitHub Actions or manually)
# ==============================================================================

set -e

APP_DIR="/var/www/lawfirm"

echo "=== [1/6] Navigating to Application Root ==="
cd "$APP_DIR"

# Put application in maintenance mode during deployment
echo "=== [2/6] Activating Maintenance Mode ==="
php artisan down --render="errors::503" --retry=15 || true

echo "=== [3/6] Pulling Latest Changes from Git (main) ==="
git fetch origin main
git reset --hard origin/main

echo "=== [4/6] Installing Dependencies & Compiling Production Assets ==="
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

if command -v npm &> /dev/null; then
    npm install --no-audit --no-fund
    npm run build
fi

echo "=== [5/6] Running Database Migrations & Rebuilding Caches ==="
php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Ensure storage and database permissions remain correct
sudo chown -R ubuntu:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" "$APP_DIR/database"
sudo chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" "$APP_DIR/database"
if [ -f "$APP_DIR/.env" ]; then
    sudo chmod 664 "$APP_DIR/.env"
fi

echo "=== [6/6] Bringing Application Back Up & Reloading PHP-FPM ==="
php artisan up

PHP_FPM_SERVICE=$(systemctl list-unit-files | grep -o 'php[0-9.]*-fpm.service' | head -n 1 || echo "php-fpm")
sudo systemctl reload "$PHP_FPM_SERVICE" || sudo systemctl restart "$PHP_FPM_SERVICE" || true

echo "=== Deployment Completed Successfully at $(date) ==="
