# Multi-stage Dockerfile for Laravel 11/12 with PHP 8.3 & FrankenPHP
FROM dunglas/frankenphp:1-php8.3 AS runner

# Install system dependencies & PHP extensions
RUN install-php-extensions \
    pdo_pgsql \
    pdo_sqlite \
    bcmath \
    intl \
    opcache \
    zip

# Set working directory
WORKDIR /app

# Copy Composer dependencies first for cache efficiency
COPY composer.json composer.lock ./

# Install production PHP dependencies
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Copy application files
COPY . .

# Run composer post-autoload-dump scripts
RUN composer dump-autoload --optimize

# Ensure storage and bootstrap permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache && \
    chmod -R 775 /app/storage /app/bootstrap/cache

# Environment defaults for production
ENV SERVER_NAME=":80"
ENV APP_ENV="production"
ENV APP_DEBUG="false"

# Healthcheck
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost:80/up || exit 1

EXPOSE 80

# Production startup script running migrations and serving
CMD php artisan optimize:clear && \
    php artisan package:discover && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan migrate --force && \
    frankenphp run --config /etc/caddy/Caddyfile
