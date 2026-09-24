#!/bin/bash
# ==============================================================================
# Setup Local PostgreSQL on EC2 / Ubuntu 24.04 / 22.04 LTS
# ==============================================================================
# Usage (run on your EC2 instance via SSH):
#   cd /var/www/lawfirm
#   chmod +x deploy/setup-local-postgres.sh
#   sudo ./deploy/setup-local-postgres.sh
# ==============================================================================

set -e

DB_NAME="${1:-lawfirm}"
DB_USER="${2:-lawfirm_user}"
DB_PASS="${3:-LawFirmSecure2026!#}"
APP_DIR="/var/www/lawfirm"

echo "=========================================================="
echo " Starting Local PostgreSQL Setup on EC2"
echo " Database: $DB_NAME"
echo " User:     $DB_USER"
echo " Host:     127.0.0.1:5432"
echo "=========================================================="

echo "=== [1/6] Installing PostgreSQL Server & Contrib Packages ==="
sudo apt-get update -y
sudo apt-get install -y postgresql postgresql-contrib

echo "=== [2/6] Ensuring PostgreSQL Service is Running & Enabled ==="
sudo systemctl enable postgresql
sudo systemctl start postgresql

echo "=== [3/6] Provisioning Local Database and User ==="
sudo -u postgres psql -tc "SELECT 1 FROM pg_user WHERE usename = '$DB_USER'" | grep -q 1 || \
sudo -u postgres psql -c "CREATE USER $DB_USER WITH PASSWORD '$DB_PASS';"

# Update password if user already exists
sudo -u postgres psql -c "ALTER USER $DB_USER WITH PASSWORD '$DB_PASS';"

# Create database if it does not exist
sudo -u postgres psql -tc "SELECT 1 FROM pg_database WHERE datname = '$DB_NAME'" | grep -q 1 || \
sudo -u postgres psql -c "CREATE DATABASE $DB_NAME OWNER $DB_USER;"

# Grant full database and schema privileges
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;"
sudo -u postgres psql -d "$DB_NAME" -c "GRANT ALL ON SCHEMA public TO $DB_USER;"
sudo -u postgres psql -d "$DB_NAME" -c "ALTER SCHEMA public OWNER TO $DB_USER;"

echo "=== [4/6] Updating Application .env File ==="
ENV_FILE="$APP_DIR/.env"

if [ -f "$ENV_FILE" ]; then
    cp "$ENV_FILE" "${ENV_FILE}.backup.$(date +%s)"
    echo "Backed up current .env file."

    set_env_var() {
        local key="$1"
        local val="$2"
        local file="$3"
        if grep -q "^${key}=" "$file"; then
            sed -i "s|^${key}=.*|${key}=${val}|" "$file"
        elif grep -q "^# *${key}=" "$file"; then
            sed -i "s|^# *${key}=.*|${key}=${val}|" "$file"
        else
            echo "${key}=${val}" >> "$file"
        fi
    }

    set_env_var "DB_CONNECTION" "pgsql" "$ENV_FILE"
    set_env_var "DB_HOST" "127.0.0.1" "$ENV_FILE"
    set_env_var "DB_PORT" "5432" "$ENV_FILE"
    set_env_var "DB_DATABASE" "$DB_NAME" "$ENV_FILE"
    set_env_var "DB_USERNAME" "$DB_USER" "$ENV_FILE"
    set_env_var "DB_PASSWORD" "$DB_PASS" "$ENV_FILE"

    echo "Application .env configured for local PostgreSQL."
else
    echo "Warning: $ENV_FILE not found. Skipping auto-edit of .env."
fi

echo "=== [5/6] Running Migrations and Seeding on Local PostgreSQL ==="
cd "$APP_DIR"
php artisan config:clear || true
php artisan migrate --force
php artisan db:seed --class=QuireDemoSeeder --force || echo "Seeding completed or already present."

echo "=== [6/6] Rebuilding Production Cache & Reloading PHP-FPM ==="
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

PHP_FPM_SERVICE=$(systemctl list-unit-files 2>/dev/null | grep -o 'php[0-9.]*-fpm.service' | head -n 1 || echo "php8.3-fpm")
sudo systemctl reload "$PHP_FPM_SERVICE" || sudo systemctl restart "$PHP_FPM_SERVICE" || true

echo "=========================================================="
echo " SUCCESS: Local EC2 PostgreSQL is now active and in use!"
echo " URL: http://127.0.0.1:5432/$DB_NAME"
echo "=========================================================="
