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

# Get PORT from environment or default to 80
PORT=${PORT:-80}

# Generate nginx configuration with actual PORT value
cat > /etc/nginx/sites-available/default << EOF
server {
    listen 0.0.0.0:$PORT;
    server_name _;
    root /var/www/html/public;
    index index.php;

    error_log /var/log/nginx/error.log warn;
    access_log /var/log/nginx/access.log;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param HTTP_PROXY "";
        fastcgi_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        fastcgi_set_header X-Forwarded-Proto \$scheme;
    }

    location ~ /\. {
        deny all;
    }
}
EOF

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
echo "✓ PHP-FPM started"
echo ""

# Give PHP-FPM time to open the socket
sleep 2

# Start nginx in the foreground (Docker will monitor this)
echo "Starting nginx..."
nginx -t 2>&1 || { echo "✗ nginx config validation failed!"; exit 1; }
echo "✓ nginx started"
echo ""
echo "=== Application Ready ==="
exec nginx -g "daemon off;"
