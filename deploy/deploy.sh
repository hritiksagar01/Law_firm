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

# Self-healing safeguard: Ensure SESSION_DRIVER=file so database drops never crash sessions
if [ -f "$APP_DIR/.env" ]; then
    sed -i 's/^SESSION_DRIVER=database/SESSION_DRIVER=file/' "$APP_DIR/.env"
    # Auto-convert direct Supabase IPv6 host to IPv4 pooler if present
    sed -i 's/db\.vfeqqwdewvqpjieqktml\.supabase\.co/aws-0-ap-south-1.pooler.supabase.com/g' "$APP_DIR/.env"
    sed -i 's/DB_USERNAME=postgres$/DB_USERNAME=postgres.vfeqqwdewvqpjieqktml/g' "$APP_DIR/.env"
fi

echo "=== [5/6] Running Database Migrations & Rebuilding Caches ==="
php artisan config:clear || true
php artisan migrate --force || echo "Notice: Database migration skipped or database currently unreachable."

php artisan config:cache || php artisan config:clear || true
php artisan route:cache || php artisan route:clear || true
php artisan view:cache || php artisan view:clear || true
php artisan event:cache || php artisan event:clear || true

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
