#!/bin/sh
set -ex

echo "===== GROWFUNDER APPLICATION STARTUP ====="
echo ""

# Create .env from environment variables
echo "Generating .env file..."
cat > .env << 'ENVEND'
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
ENVEND

# Expand environment variables in .env
sed -i "s|\${APP_NAME:-Growfunder}|${APP_NAME:-Growfunder}|g" .env
sed -i "s|\${APP_ENV:-production}|${APP_ENV:-production}|g" .env
sed -i "s|\${APP_DEBUG:-false}|${APP_DEBUG:-false}|g" .env
sed -i "s|\${APP_KEY}|${APP_KEY}|g" .env
sed -i "s|\${APP_URL}|${APP_URL}|g" .env
sed -i "s|\${ASSET_URL}|${ASSET_URL}|g" .env
sed -i "s|\${TRUSTED_PROXIES:-\*}|${TRUSTED_PROXIES:-*}|g" .env
sed -i "s|\${LOG_CHANNEL:-stack}|${LOG_CHANNEL:-stack}|g" .env
sed -i "s|\${LOG_LEVEL:-info}|${LOG_LEVEL:-info}|g" .env
sed -i "s|\${CACHE_DRIVER:-file}|${CACHE_DRIVER:-file}|g" .env
sed -i "s|\${SESSION_DRIVER:-database}|${SESSION_DRIVER:-database}|g" .env
sed -i "s|\${QUEUE_CONNECTION:-sync}|${QUEUE_CONNECTION:-sync}|g" .env
sed -i "s|\${DB_CONNECTION:-pgsql}|${DB_CONNECTION:-pgsql}|g" .env
sed -i "s|\${DB_HOST}|${DB_HOST}|g" .env
sed -i "s|\${DB_PORT:-5432}|${DB_PORT:-5432}|g" .env
sed -i "s|\${DB_DATABASE}|${DB_DATABASE}|g" .env
sed -i "s|\${DB_USERNAME}|${DB_USERNAME}|g" .env
sed -i "s|\${DB_PASSWORD}|${DB_PASSWORD}|g" .env
sed -i "s|\${MAIL_MAILER:-log}|${MAIL_MAILER:-log}|g" .env

echo "✓ Environment file created"
echo ""

# Get PORT from Railway or use 8080
PORT=${PORT:-8080}
echo "Listening on port: $PORT"
echo ""

# Cache Laravel configuration
echo "Caching Laravel configuration..."
php artisan config:cache
php artisan route:cache
echo "✓ Configuration cached"
echo ""

# Start PHP-FPM
echo "Starting PHP-FPM daemon..."
php-fpm -D
sleep 1
echo "✓ PHP-FPM started"
echo ""

# Generate nginx.conf
echo "Generating nginx configuration for port $PORT..."
mkdir -p /var/log/nginx /run/nginx

cat > /etc/nginx/nginx.conf <<'NGINXEND'
user nobody;
worker_processes auto;
error_log /var/log/nginx/error.log warn;
pid /var/run/nginx.pid;

events { worker_connections 512; }

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    log_format main '$remote_addr - $remote_user [$time_local] "$request" $status $body_bytes_sent "$http_referer" "$http_user_agent"';
    access_log /var/log/nginx/access.log main;
    sendfile on;
    keepalive_timeout 65;

    server {
        listen 0.0.0.0:PORT_PLACEHOLDER;
        server_name _;
        root /var/www/html/public;
        index index.php;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            fastcgi_set_header X-Forwarded-Proto $scheme;
        }

        location ~ /\. { deny all; }
    }
}
NGINXEND

sed -i "s|PORT_PLACEHOLDER|$PORT|g" /etc/nginx/nginx.conf
echo "✓ nginx configuration generated"
echo ""

# Validate nginx config
echo "Validating nginx configuration..."
nginx -t
echo ""

# Start nginx
echo "===== Starting nginx ====="
exec nginx -g "daemon off;"
