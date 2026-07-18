# Use official PHP image with Apache
FROM php:8.3-apache AS builder

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        gd \
        intl \
        exif \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy application files
COPY . .

# Copy .env.example to .env for defaults
RUN cp .env.example .env

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Install Node dependencies and build frontend assets
RUN npm install && npm run build

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 755 storage bootstrap/cache

# Configure Apache
COPY public /var/www/html/public
RUN echo "<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>" > /etc/apache2/conf-available/laravel.conf \
    && echo "SetEnvIf X-Forwarded-Proto https HTTPS=on" >> /etc/apache2/conf-available/laravel.conf \
    && echo "SetEnvIf X-Forwarded-Proto https HTTP_X_FORWARDED_PROTO=https" >> /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel \
    && sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Set app key and run migrations on startup
RUN echo '#!/bin/bash\n\
set -e\n\
# Clean all cache directories\n\
rm -rf bootstrap/cache/*.php\n\
rm -rf storage/framework/cache/data/*\n\
rm -rf storage/framework/views/*\n\
# Ensure APP_URL defaults to HTTPS if not set\n\
APP_URL=${APP_URL:-https://grofunder.onrender.com}\n\
# Update or create .env with proper values\n\
sed -i "s|^APP_URL=.*|APP_URL=$APP_URL|" .env\n\
echo "TRUSTED_PROXIES=*" >> .env || true\n\
# Rebuild cache\n\
php artisan config:cache 2>/dev/null || true\n\
php artisan route:cache 2>/dev/null || true\n\
php artisan migrate --force 2>/dev/null || true\n\
apache2-foreground' > /entrypoint.sh && \
    chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]

EXPOSE 80
