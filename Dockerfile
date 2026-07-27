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

# Create .env if it doesn't exist
if [ ! -f .env ]; then
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
    echo "[STARTUP] Created .env file"
fi

# Get PORT from Railway environment
PORT=${PORT:-8000}

echo "[STARTUP] Starting PHP development server"
echo "[STARTUP] Listening on 0.0.0.0:$PORT"
echo "[STARTUP] Document root: /app/public"

# Start PHP development server with proper router
exec php -S 0.0.0.0:$PORT -t /app/public /app/public/router.php
EOFSTART

RUN chmod +x /start.sh

EXPOSE 8000
CMD ["sh", "/start.sh"]

