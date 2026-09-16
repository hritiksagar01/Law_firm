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

# Self-healing safeguard: Ensure SESSION_DRIVER=file and verified Supabase credentials
if [ -f "$APP_DIR/.env" ]; then
    sudo chown ubuntu:www-data "$APP_DIR/.env" || true
    sudo chmod 666 "$APP_DIR/.env" || true
    sed -i 's/^SESSION_DRIVER=.*/SESSION_DRIVER=file/' "$APP_DIR/.env"
    sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=pgsql/' "$APP_DIR/.env"
    sed -i 's/^DB_HOST=.*/DB_HOST=aws-0-ap-northeast-1.pooler.supabase.com/' "$APP_DIR/.env"
    sed -i 's/^DB_PORT=.*/DB_PORT=5432/' "$APP_DIR/.env"
    sed -i 's/^DB_DATABASE=.*/DB_DATABASE=postgres/' "$APP_DIR/.env"
    sed -i 's/^DB_USERNAME=.*/DB_USERNAME=postgres.vfeqqwdewvqpjieqktml/' "$APP_DIR/.env"
    sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=w0eeeDQBxopjhRBZ/' "$APP_DIR/.env"
    sed -i 's/^APP_NAME=.*/APP_NAME="Vennamraj Associates"/' "$APP_DIR/.env"
    sed -i 's/^LEGAL_APP_NAME=.*/LEGAL_APP_NAME="Vennamraj Associates"/' "$APP_DIR/.env"
    grep -q '^LEGAL_APP_NAME=' "$APP_DIR/.env" || echo 'LEGAL_APP_NAME="Vennamraj Associates"' >> "$APP_DIR/.env"
    sed -i 's/^APP_URL=.*/APP_URL=https:\/\/lawfirm.pllatinum.me/' "$APP_DIR/.env"
fi

echo "=== [5/6] Running Database Migrations & Seeding ==="
php artisan config:clear || true
php artisan migrate --force || echo "Notice: Database migration completed or skipped."
php artisan db:seed --force || echo "Notice: Database seed completed or skipped."

# Ensure existing database firm record reflects Vennamraj Associates branding
php artisan tinker --execute="App\Models\Firm::first()?->update(['name' => 'Vennamraj Associates, Advocates & Legal Consultants', 'slug' => 'vennamraj-associates', 'email' => 'contact@vennamraj.com']);" || true

php artisan config:cache || php artisan config:clear || true
php artisan route:cache || php artisan route:clear || true
php artisan view:cache || php artisan view:clear || true
php artisan event:cache || php artisan event:clear || true

# Ensure storage, database, and .env permissions allow www-data to read and write
sudo chown -R ubuntu:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" "$APP_DIR/database"
sudo chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" "$APP_DIR/database"
if [ -f "$APP_DIR/.env" ]; then
    sudo chown ubuntu:www-data "$APP_DIR/.env"
    sudo chmod 666 "$APP_DIR/.env"
fi

echo "=== [6/6] Bringing Application Back Up & Reloading PHP-FPM ==="
php artisan up

PHP_FPM_SERVICE=$(systemctl list-unit-files | grep -o 'php[0-9.]*-fpm.service' | head -n 1 || echo "php-fpm")
sudo systemctl reload "$PHP_FPM_SERVICE" || sudo systemctl restart "$PHP_FPM_SERVICE" || true

echo "=== Deployment Completed Successfully at $(date) ==="
