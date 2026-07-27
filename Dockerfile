# Minimal Laravel Docker - use built-in PHP server
FROM php:8.3-fpm-alpine

RUN apk add --no-cache git curl npm nodejs postgresql-client libpq-dev

RUN docker-php-ext-install pdo pdo_pgsql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs && npm install && npm run build

RUN cat > /start.sh << 'STARTEOF'
#!/bin/sh
set -e

echo "Creating .env..."
cat > .env << 'ENVEOF'
APP_NAME=Growfunder
APP_ENV=production
APP_DEBUG=true
APP_KEY=base64:tE6w4W4Y+nhteXfQVPCAHKzOnKiUqJqbb2jQ9LTHrKA=
APP_URL=https://web-production-848ef.up.railway.app
DB_CONNECTION=pgsql
DB_HOST=postgres.railway.internal
DB_PORT=5432
DB_DATABASE=railway
DB_USERNAME=postgres
DB_PASSWORD=QQMChGefegtixvAHbSsUiJjnbkuEPGKm
SESSION_DRIVER=database
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
TRUSTED_PROXIES=*
LOG_CHANNEL=stderr
ENVEOF

echo "Caching Laravel config..."
php artisan config:cache
php artisan route:cache

PORT=${PORT:-8000}
echo "Starting Laravel on 0.0.0.0:$PORT..."
php artisan serve --host=0.0.0.0 --port=$PORT
STARTEOF

chmod +x /start.sh

EXPOSE 8000
CMD ["/start.sh"]

