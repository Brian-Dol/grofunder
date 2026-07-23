# Use PHP 8.3 with Apache - Clean, simplified Dockerfile
FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo pdo_pgsql intl gd exif zip

# Enable Apache modules
RUN a2dismod mpm_event && a2enmod mpm_prefork rewrite headers

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && apt-get install -y nodejs

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Build frontend assets
RUN npm install && npm run build

# DELETE any pre-generated cache files from local build
RUN rm -rf bootstrap/cache/*.php 2>/dev/null || true
RUN rm -rf storage/framework/cache/* 2>/dev/null || true  
RUN rm -rf storage/framework/views/* 2>/dev/null || true

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache && chmod -R 755 storage bootstrap/cache

# Configure Apache to serve from /public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Set X-Forwarded-Proto headers for reverse proxy
RUN echo 'SetEnvIf X-Forwarded-Proto https HTTPS=on' >> /etc/apache2/apache2.conf && \
    echo 'SetEnvIf X-Forwarded-Proto https REQUEST_SCHEME=https' >> /etc/apache2/apache2.conf

# Ensure environment variables are set for Apache/PHP
RUN echo '#!/bin/bash' > /entrypoint.sh && \
    echo 'set -e' >> /entrypoint.sh && \
    echo 'echo "=== Growfunder Startup ===" ' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Generate .env from environment variables' >> /entrypoint.sh && \
    echo 'cat > .env << EOF' >> /entrypoint.sh && \
    echo 'APP_NAME=${APP_NAME:-Growfunder}' >> /entrypoint.sh && \
    echo 'APP_ENV=${APP_ENV:-production}' >> /entrypoint.sh && \
    echo 'APP_DEBUG=${APP_DEBUG:-false}' >> /entrypoint.sh && \
    echo 'APP_KEY=${APP_KEY}' >> /entrypoint.sh && \
    echo 'APP_URL=${APP_URL}' >> /entrypoint.sh && \
    echo 'ASSET_URL=${ASSET_URL}' >> /entrypoint.sh && \
    echo 'TRUSTED_PROXIES=${TRUSTED_PROXIES:-*}' >> /entrypoint.sh && \
    echo 'LOG_CHANNEL=${LOG_CHANNEL:-stack}' >> /entrypoint.sh && \
    echo 'LOG_LEVEL=${LOG_LEVEL:-info}' >> /entrypoint.sh && \
    echo 'CACHE_DRIVER=${CACHE_DRIVER:-file}' >> /entrypoint.sh && \
    echo 'SESSION_DRIVER=${SESSION_DRIVER:-database}' >> /entrypoint.sh && \
    echo 'QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}' >> /entrypoint.sh && \
    echo 'DB_CONNECTION=${DB_CONNECTION:-pgsql}' >> /entrypoint.sh && \
    echo 'DB_HOST=${DB_HOST}' >> /entrypoint.sh && \
    echo 'DB_PORT=${DB_PORT:-5432}' >> /entrypoint.sh && \
    echo 'DB_DATABASE=${DB_DATABASE}' >> /entrypoint.sh && \
    echo 'DB_USERNAME=${DB_USERNAME}' >> /entrypoint.sh && \
    echo 'DB_PASSWORD=${DB_PASSWORD}' >> /entrypoint.sh && \
    echo 'MAIL_MAILER=${MAIL_MAILER:-log}' >> /entrypoint.sh && \
    echo 'EOF' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo 'echo "Environment configured:"' >> /entrypoint.sh && \
    echo 'echo "  APP_ENV: $APP_ENV"' >> /entrypoint.sh && \
    echo 'echo "  APP_URL: $APP_URL"' >> /entrypoint.sh && \
    echo 'echo "  DB_HOST: $DB_HOST"' >> /entrypoint.sh && \
    echo 'echo "  DB_DATABASE: $DB_DATABASE"' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Generate app key if not provided' >> /entrypoint.sh && \
    echo 'if [ -z "$APP_KEY" ]; then' >> /entrypoint.sh && \
    echo '  echo "Generating APP_KEY..."' >> /entrypoint.sh && \
    echo '  php artisan key:generate --force' >> /entrypoint.sh && \
    echo 'fi' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Cache configuration' >> /entrypoint.sh && \
    echo 'echo "Caching configuration..."' >> /entrypoint.sh && \
    echo 'rm -rf bootstrap/cache/*.php 2>/dev/null || true' >> /entrypoint.sh && \
    echo 'php artisan config:cache' >> /entrypoint.sh && \
    echo 'php artisan route:cache' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Migrations disabled to prevent memory exhaustion on free tier' >> /entrypoint.sh && \
    echo '# Run manually: php artisan migrate --force' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo 'echo "=== Starting Apache ===" ' >> /entrypoint.sh && \
    echo 'exec apache2-foreground' >> /entrypoint.sh && \
    chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]

EXPOSE 80

