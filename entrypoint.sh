#!/bin/sh
set -e

echo "=== Growfunder Application Startup ==="
echo ""

# Generate .env file from environment variables
cat > .env << EOF
APP_NAME=${APP_NAME:-Growfunder}
APP_ENV=${APP_ENV:-production}
APP_DEBUG=${APP_DEBUG:-false}
APP_KEY=${APP_KEY}
APP_URL=${APP_URL}
ASSET_URL=${ASSET_URL}
TRUSTED_PROXIES=${TRUSTED_PROXIES:-*}
LOG_CHANNEL=${LOG_CHANNEL:-stack}
LOG_LEVEL=${LOG_LEVEL:-info}
CACHE_DRIVER=${CACHE_DRIVER:-file}
SESSION_DRIVER=${SESSION_DRIVER:-database}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}
DB_CONNECTION=${DB_CONNECTION:-pgsql}
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT:-5432}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}
MAIL_MAILER=${MAIL_MAILER:-log}
EOF

echo "✓ Environment configured"
echo "  APP_ENV: $APP_ENV"
echo "  APP_URL: $APP_URL"
echo "  DB_HOST: $DB_HOST"
echo "  DB_DATABASE: $DB_DATABASE"
echo ""

# Generate nginx config from the PORT environment variable
export PORT=${PORT:-80}

sed -i "s|\${PORT:-80}|$PORT|g" /etc/nginx/sites-available/default

echo "✓ nginx configured to listen on 0.0.0.0:$PORT"
echo ""

# Cache Laravel configuration
echo "Caching Laravel configuration..."
php artisan config:cache
php artisan route:cache
echo "✓ Configuration cached"
echo ""

# Start PHP-FPM in the background
echo "Starting PHP-FPM..."
php-fpm -D
echo "✓ PHP-FPM started (PID: $(pgrep -f 'php-fpm.*master' || echo 'unknown'))"
echo ""

# Give PHP-FPM time to open the socket
sleep 2

# Start nginx in the foreground (Docker will monitor this)
echo "Starting nginx..."
echo "✓ Ready to handle requests"
echo ""
exec nginx -g "daemon off;"
