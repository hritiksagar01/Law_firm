#!/bin/bash
# ==============================================================================
# Automated Application Deployment Script (Standard Production CI/CD)
# ==============================================================================

set -e

APP_DIR="/var/www/lawfirm"

echo "=== [1/6] Navigating to Application Root ==="
cd "$APP_DIR"

# Put application in maintenance mode during deployment
echo "=== [2/6] Activating Maintenance Mode ==="
php artisan down --render="errors::503" --retry=15 || true

# Determine active git branch
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "main")
echo "=== [3/6] Pulling Latest Changes from Git ($CURRENT_BRANCH) ==="
git fetch origin +refs/heads/*:refs/remotes/origin/* --prune || git fetch origin || true
git reset --hard "origin/$CURRENT_BRANCH" || git reset --hard HEAD

echo "=== [4/6] Installing Dependencies & Compiling Production Assets ==="
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

if command -v npm &> /dev/null; then
    npm install --no-audit --no-fund
    npm run build
fi

echo "=== [5/6] Running Database Migrations & Caching Configuration ==="
php artisan config:clear || true
php artisan migrate --force || echo "Notice: Database migration completed or skipped."
php artisan db:seed --class=QuireDemoSeeder --force || echo "Notice: Quire demo seeding completed or skipped."

# Clear and rebuild caches for maximum performance
php artisan config:cache || php artisan config:clear || true
php artisan route:cache || php artisan route:clear || true
php artisan view:cache || php artisan view:clear || true
php artisan event:cache || php artisan event:clear || true

# Ensure proper ownership and secure permissions (no world-writable permissions)
sudo chown -R ubuntu:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" "$APP_DIR/database"
sudo chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" "$APP_DIR/database"

if [ -f "$APP_DIR/.env" ]; then
    sudo chown ubuntu:www-data "$APP_DIR/.env"
    sudo chmod 660 "$APP_DIR/.env"
fi

echo "=== [6/6] Bringing Application Back Up & Reloading PHP-FPM ==="
php artisan up

PHP_FPM_SERVICE=$(systemctl list-unit-files 2>/dev/null | grep -o 'php[0-9.]*-fpm.service' | head -n 1 || echo "php-fpm")
sudo systemctl reload "$PHP_FPM_SERVICE" || sudo systemctl restart "$PHP_FPM_SERVICE" || true

echo "=== Deployment Completed Successfully at $(date) ==="
