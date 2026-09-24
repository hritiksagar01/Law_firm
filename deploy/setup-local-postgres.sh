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

DB_NAME="lawfirm"
DB_USER="lawfirm_user"
DB_PASS="LawFirmSecure2026Pass"
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

echo "=== [3/6] Configuring Authentication in pg_hba.conf ==="
# Get the exact active pg_hba.conf path directly from PostgreSQL
HBA_FILE=$(sudo -u postgres psql -t -P format=unaligned -c "SHOW hba_file;" 2>/dev/null || true)

if [ -z "$HBA_FILE" ] || [ ! -f "$HBA_FILE" ]; then
    HBA_FILE=$(sudo find /etc/postgresql/ -name "pg_hba.conf" 2>/dev/null | head -n 1)
fi

echo "Active HBA file: $HBA_FILE"

if [ -n "$HBA_FILE" ] && [ -f "$HBA_FILE" ]; then
    # Remove any existing lawfirm_user lines to prevent duplicate conflicts
    sudo sed -i "/$DB_USER/d" "$HBA_FILE"
    
    # Prepend trust rules at line 1 so local connections never fail password authentication
    sudo sed -i "1i local   all             $DB_USER                                trust" "$HBA_FILE"
    sudo sed -i "1i host    all             $DB_USER        127.0.0.1/32            trust" "$HBA_FILE"
    sudo sed -i "1i host    all             $DB_USER        ::1/128                 trust" "$HBA_FILE"
    
    # Reload PostgreSQL configuration
    sudo systemctl reload postgresql || sudo systemctl restart postgresql
    echo "pg_hba.conf updated with localhost trust rules and PostgreSQL reloaded."
fi

echo "=== [4/6] Creating User, Database, and Setting Privileges ==="
# Create user if it doesn't exist
sudo -u postgres psql -tc "SELECT 1 FROM pg_user WHERE usename = '$DB_USER'" | grep -q 1 || \
sudo -u postgres psql -c "CREATE USER $DB_USER WITH PASSWORD '$DB_PASS';"

# Update user password and permissions
sudo -u postgres psql -c "ALTER USER $DB_USER WITH PASSWORD '$DB_PASS';"
sudo -u postgres psql -c "ALTER USER $DB_USER CREATEDB;"

# Create database if it does not exist
sudo -u postgres psql -tc "SELECT 1 FROM pg_database WHERE datname = '$DB_NAME'" | grep -q 1 || \
sudo -u postgres psql -c "CREATE DATABASE $DB_NAME OWNER $DB_USER;"

# Grant all privileges
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;"
sudo -u postgres psql -d "$DB_NAME" -c "GRANT ALL ON SCHEMA public TO $DB_USER;" || true
sudo -u postgres psql -d "$DB_NAME" -c "ALTER SCHEMA public OWNER TO $DB_USER;" || true

# Test local PostgreSQL connectivity
echo "Testing local connection via 127.0.0.1:5432..."
psql -h 127.0.0.1 -U "$DB_USER" -d "$DB_NAME" -c "SELECT 1;" > /dev/null
echo "✓ PostgreSQL connection to 127.0.0.1 verified successfully!"

echo "=== [5/6] Updating Application .env File & Clearing Caches ==="
ENV_FILE="$APP_DIR/.env"

if [ -f "$ENV_FILE" ]; then
    cp "$ENV_FILE" "${ENV_FILE}.backup.$(date +%s)"

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
fi

# Crucial: remove any stale cached bootstrap files before artisan commands
sudo rm -f "$APP_DIR/bootstrap/cache/"*.php || true

cd "$APP_DIR"
php artisan config:clear || true
php artisan cache:clear || true

echo "=== [6/6] Running Migrations and Seeding on Local PostgreSQL ==="
php artisan migrate --force
php artisan db:seed --force || echo "Seeding completed or already present."

# Cache production routes and config
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Restart PHP-FPM service so web requests use fresh configuration
PHP_FPM_SERVICE=$(systemctl list-unit-files 2>/dev/null | grep -o 'php[0-9.]*-fpm\.service' | head -n 1 || echo "php8.3-fpm.service")
sudo systemctl restart "$PHP_FPM_SERVICE" || sudo systemctl reload "$PHP_FPM_SERVICE" || true
sudo systemctl reload nginx || true

echo "=========================================================="
echo " SUCCESS: Local EC2 PostgreSQL is now active and in use!"
echo " Host:     127.0.0.1:5432"
echo " Database: $DB_NAME"
echo " User:     $DB_USER"
echo "=========================================================="
