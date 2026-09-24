# AWS EC2 Automated CI/CD Deployment Guide

This directory contains the automation scripts for deploying the Law Firm Management platform to an AWS EC2 or AWS Lightsail instance running Ubuntu 24.04 / 22.04 LTS.

---

## Architecture Overview

```
[Local Machine / Git Push]
         │
         ▼  (git push origin main)
[GitHub Repository]
         │
         ▼  (Triggers .github/workflows/deploy.yml)
[GitHub Actions Runner]
         │
         ▼  (SSH Connection via Secrets: EC2_HOST, EC2_USERNAME, EC2_SSH_KEY)
[AWS EC2 / Lightsail Server]
         │
         ├─► Pulls latest code (git reset --hard origin/main)
         ├─► composer install --no-dev --optimize-autoloader
         ├─► npm install && npm run build
         ├─► php artisan migrate --force
         ├─► Rebuilds caches (config, route, view)
         └─► Gracefully reloads PHP 8.3 FPM (Zero Downtime)
```

---

## 1. Initial One-Time EC2 Server Setup

### A. Launch Instance
1. In AWS Console, select **Asia Pacific (Mumbai) `ap-south-1`**.
2. Launch an **Ubuntu 24.04 LTS** instance:
   - Type: `t3.small` (2 GB RAM) or `t4g.small`.
   - Key Pair: Download the `.pem` private key file (e.g. `lawfirm-key.pem`).
3. Under **Security Groups**, allow inbound traffic on:
   - `SSH (22)`
   - `HTTP (80)`
   - `HTTPS (443)`
4. Allocate and attach an **Elastic IP** (static public IP) to the instance.

### B. Run the 1-Click Bootstrap Script
SSH into your instance from your terminal:
```bash
ssh -i /path/to/lawfirm-key.pem ubuntu@<YOUR_EC2_PUBLIC_IP>
```

Run the following commands to install PHP 8.3, Composer, Node.js 20, Nginx, and configure 2GB swap space:
```bash
# Clone the repo into /var/www/lawfirm
sudo git clone <YOUR_GITHUB_REPO_URL> /var/www/lawfirm
cd /var/www/lawfirm

# Run the bootstrap script
chmod +x deploy/setup-ec2.sh
sudo ./deploy/setup-ec2.sh
```

### C. Configure Local PostgreSQL Database (Recommended)
To run a high-performance local PostgreSQL database on your EC2 instance (zero latency, zero cloud costs):
```bash
cd /var/www/lawfirm
chmod +x deploy/setup-local-postgres.sh
sudo ./deploy/setup-local-postgres.sh
```
This automatically:
- Installs and activates PostgreSQL server locally on the EC2 machine.
- Creates the `lawfirm` database and `lawfirm_user`.
- Sets `.env` to `DB_CONNECTION=pgsql`, `DB_HOST=127.0.0.1`, `DB_PORT=5432`.
- Runs migrations (`php artisan migrate --force`) and seeds the demo data.
- Caches configuration for production performance.

### D. Configure Nginx & SSL
```bash
# Copy site configuration
sudo cp /var/www/lawfirm/deploy/nginx/lawfirm.conf /etc/nginx/sites-available/lawfirm

# Edit the domain name (replace server_name _ with your domain, e.g. chambers.sharmalegal.in)
sudo nano /etc/nginx/sites-available/lawfirm

# Enable site and disable default
sudo ln -s /etc/nginx/sites-available/lawfirm /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx

# Install free Let's Encrypt SSL certificate (Certbot)
sudo certbot --nginx -d yourdomain.com
```

---

## 2. Configure GitHub Secrets for CI/CD

Go to your GitHub repository -> **Settings** -> **Secrets and variables** -> **Actions** -> Click **New repository secret**:

| Secret Name | Value Description | Example |
|---|---|---|
| `EC2_HOST` | Your EC2 Public IP or Domain Name | `13.233.120.45` or `chambers.sharmalegal.in` |
| `EC2_USERNAME` | Ubuntu default SSH user | `ubuntu` |
| `EC2_SSH_KEY` | Entire content of your `.pem` private key | Open `.pem` in Notepad, copy entire text including `-----BEGIN RSA PRIVATE KEY-----` and `-----END RSA PRIVATE KEY-----` |
| `EC2_PORT` *(Optional)* | SSH Port (default is 22) | `22` |

---

## 3. How Automated Deployments Work

Every time you push changes to the `main` branch:
```bash
git add .
git commit -m "Add new court hearing workflow"
git push origin main
```
1. GitHub Actions automatically connects to your EC2 instance via SSH.
2. Runs `deploy/deploy.sh` automatically.
3. Updates code, compiles frontend assets with Vite, runs migrations, refreshes Laravel caches, and reloads PHP-FPM.
4. Your live website reflects the new code in under 60 seconds!
