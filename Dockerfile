# Use PHP 8.3 FPM with nginx - Clean, modern approach
FROM php:8.3-fpm-alpine

# Install system dependencies (Alpine Linux)
RUN apk add --no-cache \
    nginx \
    postgresql-client \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    icu-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-configure intl && \
    docker-php-ext-install pdo pdo_pgsql intl gd exif zip

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
RUN chown -R nobody:nobody storage bootstrap/cache && chmod -R 755 storage bootstrap/cache

# Configure nginx
RUN mkdir -p /etc/nginx/sites-available && \
    echo 'server {' > /etc/nginx/sites-available/default && \
    echo '    listen 80;' >> /etc/nginx/sites-available/default && \
    echo '    server_name _;' >> /etc/nginx/sites-available/default && \
    echo '    root /var/www/html/public;' >> /etc/nginx/sites-available/default && \
    echo '    index index.php;' >> /etc/nginx/sites-available/default && \
    echo '    location / {' >> /etc/nginx/sites-available/default && \
    echo '        try_files $uri $uri/ /index.php?$query_string;' >> /etc/nginx/sites-available/default && \
    echo '    }' >> /etc/nginx/sites-available/default && \
    echo '    location ~ \.php$ {' >> /etc/nginx/sites-available/default && \
    echo '        fastcgi_pass 127.0.0.1:9000;' >> /etc/nginx/sites-available/default && \
    echo '        fastcgi_index index.php;' >> /etc/nginx/sites-available/default && \
    echo '        include fastcgi_params;' >> /etc/nginx/sites-available/default && \
    echo '        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;' >> /etc/nginx/sites-available/default && \
    echo '        set_real_ip_from 0.0.0.0/0;' >> /etc/nginx/sites-available/default && \
    echo '        real_ip_header X-Forwarded-For;' >> /etc/nginx/sites-available/default && \
    echo '    }' >> /etc/nginx/sites-available/default && \
    echo '}' >> /etc/nginx/sites-available/default && \
    mkdir -p /etc/nginx/sites-enabled && \
    ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Create startup script
RUN echo '#!/bin/sh' > /entrypoint.sh && \
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
    echo '# Cache configuration' >> /entrypoint.sh && \
    echo 'echo "Caching configuration..."' >> /entrypoint.sh && \
    echo 'rm -rf bootstrap/cache/*.php 2>/dev/null || true' >> /entrypoint.sh && \
    echo 'php artisan config:cache' >> /entrypoint.sh && \
    echo 'php artisan route:cache' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo 'echo "=== Starting nginx + php-fpm ===" ' >> /entrypoint.sh && \
    echo 'nginx' >> /entrypoint.sh && \
    echo 'exec php-fpm' >> /entrypoint.sh && \
    chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]

EXPOSE 80 9000

