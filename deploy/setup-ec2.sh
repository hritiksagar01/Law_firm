#!/bin/bash
# ==============================================================================
# LexisCore Law Firm Management — EC2 / Ubuntu 24.04 Initial Bootstrap Script
# ==============================================================================
# Run this once on your fresh Ubuntu EC2 / Lightsail instance:
#   chmod +x setup-ec2.sh && sudo ./setup-ec2.sh
# ==============================================================================

set -e

echo "=== [1/8] Setting Up Swap Space (Prevents OOM on small instances) ==="
if [ ! -f /swapfile ]; then
    sudo fallocate -l 2G /swapfile
    sudo chmod 600 /swapfile
    sudo mkswap /swapfile
    sudo swapon /swapfile
    echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
    echo "2GB Swap file activated."
else
    echo "Swap file already exists."
fi

echo "=== [2/8] Updating System & Installing Essentials ==="
sudo apt-get update -y
sudo apt-get upgrade -y
sudo apt-get install -y curl git unzip zip software-properties-common ca-certificates lsb-release certbot python3-certbot-nginx sqlite3

echo "=== [3/8] Installing PHP 8.3 & Required Extensions ==="
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update -y
sudo apt-get install -y php8.3 php8.3-fpm php8.3-cli php8.3-common php8.3-curl \
                        php8.3-mbstring php8.3-xml php8.3-zip php8.3-bcmath \
                        php8.3-sqlite3 php8.3-pgsql php8.3-mysql php8.3-intl \
                        php8.3-redis php8.3-opcache

echo "=== [4/8] Installing Composer ==="
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod +x /usr/local/bin/composer
    echo "Composer installed successfully."
fi

echo "=== [5/8] Installing Node.js 20 LTS & NPM ==="
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
    sudo apt-get install -y nodejs
    echo "Node $(node -v) and NPM $(npm -v) installed."
fi

echo "=== [6/8] Installing and Configuring Nginx ==="
sudo apt-get install -y nginx

# Allow ubuntu user to reload php-fpm without password (for CI/CD)
echo "ubuntu ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload php8.3-fpm" | sudo tee /etc/sudoers.d/php-fpm-reload
sudo chmod 0440 /etc/sudoers.d/php-fpm-reload

echo "=== [7/8] Preparing Application Directory /var/www/lawfirm ==="
sudo mkdir -p /var/www/lawfirm
sudo chown -R ubuntu:www-data /var/www/lawfirm
sudo chmod -R 775 /var/www/lawfirm

echo "=== [8/8] Completed Bootstrap Successfully! ==="
echo "Next steps:"
echo "1. Clone your repo: git clone <REPO_URL> /var/www/lawfirm"
echo "2. Copy deploy/nginx/lawfirm.conf to /etc/nginx/sites-available/lawfirm"
echo "3. Run: sudo ln -s /etc/nginx/sites-available/lawfirm /etc/nginx/sites-enabled/"
echo "4. Run: sudo nginx -t && sudo systemctl reload nginx"
