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
RUN a2enmod rewrite headers

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

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache && chmod -R 755 storage bootstrap/cache

# Configure Apache to serve from /public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Add virtual host configuration with HTTPS detection
RUN echo '<VirtualHost *:80>' > /etc/apache2/sites-available/000-default.conf && \
    echo '    ServerName localhost' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    DocumentRoot /var/www/html/public' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    <Directory /var/www/html/public>' >> /etc/apache2/sites-available/000-default.conf && \
    echo '        Options Indexes FollowSymLinks' >> /etc/apache2/sites-available/000-default.conf && \
    echo '        AllowOverride All' >> /etc/apache2/sites-available/000-default.conf && \
    echo '        Require all granted' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    </Directory>' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    # Trust X-Forwarded-Proto from reverse proxy' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    SetEnvIf X-Forwarded-Proto https HTTPS=on' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    SetEnvIf X-Forwarded-Proto https REQUEST_SCHEME=https' >> /etc/apache2/sites-available/000-default.conf && \
    echo '</VirtualHost>'

# Create entrypoint script
RUN echo '#!/bin/bash' > /entrypoint.sh && \
    echo 'set -e' >> /entrypoint.sh && \
    echo 'echo "=== Growfunder Startup ===" ' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Ensure environment variables are set for Apache/PHP' >> /entrypoint.sh && \
    echo 'export APP_ENV=production' >> /entrypoint.sh && \
    echo 'export APP_URL=https://grofunder.onrender.com' >> /entrypoint.sh && \
    echo 'export APP_DEBUG=false' >> /entrypoint.sh && \
    echo 'export TRUSTED_PROXIES=*' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo 'echo "Environment: $APP_ENV"' >> /entrypoint.sh && \
    echo 'echo "App URL: $APP_URL"' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Ensure .env exists' >> /entrypoint.sh && \
    echo 'if [ ! -f .env ]; then cp .env.example .env; fi' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Update .env with correct values' >> /entrypoint.sh && \
    echo 'sed -i "s|^APP_URL=.*|APP_URL=https://grofunder.onrender.com|" .env' >> /entrypoint.sh && \
    echo 'sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env' >> /entrypoint.sh && \
    echo 'sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env' >> /entrypoint.sh && \
    echo 'grep -q "TRUSTED_PROXIES" .env || echo "TRUSTED_PROXIES=*" >> .env' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Clear all caches BEFORE config:cache' >> /entrypoint.sh && \
    echo 'rm -rf bootstrap/cache/*.php' >> /entrypoint.sh && \
    echo 'rm -rf storage/framework/cache/data/*' >> /entrypoint.sh && \
    echo 'rm -rf storage/framework/views/*' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Generate app key' >> /entrypoint.sh && \
    echo 'php artisan key:generate --force 2>/dev/null || true' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Explicitly clear routes cache' >> /entrypoint.sh && \
    echo 'php artisan route:clear 2>/dev/null || true' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Cache config with correct environment values' >> /entrypoint.sh && \
    echo 'php artisan config:cache' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Explicitly clear and rebuild views with correct config' >> /entrypoint.sh && \
    echo 'php artisan view:clear' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Run migrations' >> /entrypoint.sh && \
    echo 'php artisan migrate --force 2>/dev/null || true' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo 'echo "=== Starting Apache ===" ' >> /entrypoint.sh && \
    echo 'exec apache2-foreground' >> /entrypoint.sh && \
    chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]

EXPOSE 80

