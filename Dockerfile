# Ultra-minimal Laravel Docker
FROM php:8.3-cli-alpine

RUN apk add --no-cache git curl npm nodejs postgresql-client libpq-dev

RUN docker-php-ext-install pdo pdo_pgsql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs && \
    npm install && npm run build

RUN cat > /start.sh << 'EOFSTART'
#!/bin/sh

# Create .env
cat > .env << 'EOF'
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
EOF

echo "[STARTUP] Config caching..."
php artisan config:cache || echo "[WARN] config:cache failed but continuing"
php artisan route:cache || echo "[WARN] route:cache failed but continuing"

# Get PORT from Railway environment or default to 8000
PORT=${PORT:-8000}
echo "[STARTUP] Listening on 0.0.0.0:$PORT with docroot /app/public"

# Replace shell with PHP server (this exec never returns)
exec php -S 0.0.0.0:$PORT -t /app/public
EOFSTART

chmod +x /start.sh

EXPOSE 8000
CMD ["sh", "/start.sh"]

