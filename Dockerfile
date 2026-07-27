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

# Create nginx configuration file
RUN mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled && \
    cat > /etc/nginx/sites-available/default << 'EOF'
server {
    listen 0.0.0.0:${PORT:-80};
    server_name _;
    root /var/www/html/public;
    index index.php;

    error_log /var/log/nginx/error.log warn;
    access_log /var/log/nginx/access.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param HTTP_PROXY "";
        fastcgi_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        fastcgi_set_header X-Forwarded-Proto $scheme;
    }

    location ~ /\. {
        deny all;
    }
}
EOF
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Create entrypoint script
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
