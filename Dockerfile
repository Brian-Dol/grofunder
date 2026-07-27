# Use PHP 8.3 FPM with Alpine
FROM php:8.3-fpm-alpine

# Install system dependencies
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

WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Build frontend assets
RUN npm install && npm run build

# Clean up cache
RUN rm -rf bootstrap/cache/*.php 2>/dev/null || true && \
    rm -rf storage/framework/cache/* 2>/dev/null || true && \
    rm -rf storage/framework/views/* 2>/dev/null || true

# Set permissions
RUN chown -R nobody:nobody storage bootstrap/cache && chmod -R 755 storage bootstrap/cache

# Create nginx directory (config will be generated at runtime)
RUN mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled

# Create entrypoint script
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
