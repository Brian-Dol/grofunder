#!/bin/sh
set -ex

echo "===== GROWFUNDER APPLICATION STARTUP ====="

# Create .env file using simple direct substitution
export PORT=${PORT:-8080}

# Generate .env with environment variables
cat > .env <<'EOF'
APP_NAME=Growfunder
APP_ENV=production
APP_DEBUG=false
APP_KEY=BASE64_KEY_PLACEHOLDER
APP_URL=https://web-production-848ef.up.railway.app
ASSET_URL=
TRUSTED_PROXIES=*
LOG_CHANNEL=stack
LOG_LEVEL=info
CACHE_DRIVER=file
SESSION_DRIVER=database
QUEUE_CONNECTION=sync
DB_CONNECTION=pgsql
DB_HOST=postgres.railway.internal
DB_PORT=5432
DB_DATABASE=railway
DB_USERNAME=postgres
DB_PASSWORD=QQMChGefegtixvAHbSsUiJjnbkuEPGKm
MAIL_MAILER=log
EOF

# Replace placeholders with actual values
sed -i "s|BASE64_KEY_PLACEHOLDER|${APP_KEY}|g" .env

echo "✓ Environment file created"

# Verify key database variables
echo "  DB_HOST: $(grep DB_HOST .env)"
echo "  DB_DATABASE: $(grep DB_DATABASE .env)"
echo "  PORT: $PORT"
echo ""

# Cache Laravel config
echo "Caching Laravel configuration..."
php artisan config:cache --quiet
php artisan route:cache --quiet
echo "✓ Configuration cached"
echo ""

# Start PHP-FPM daemon
echo "Starting PHP-FPM..."
php-fpm -D

# Wait for socket
sleep 2

# Verify PHP-FPM is running
if ! pidof php-fpm > /dev/null; then
    echo "ERROR: PHP-FPM failed to start!"
    exit 1
fi
echo "✓ PHP-FPM started (pid: $(pidof php-fpm))"
echo ""

# Create nginx configuration
echo "Generating nginx configuration for port $PORT..."
mkdir -p /var/log/nginx

# Use simple port substitution without sed
cat > /etc/nginx/nginx.conf << EOF
user nobody;
worker_processes auto;
error_log /var/log/nginx/error.log warn;
pid /var/run/nginx.pid;

events { worker_connections 512; }

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    log_format main '\$remote_addr - \$remote_user [\$time_local] "\$request" \$status \$body_bytes_sent "\$http_referer" "\$http_user_agent"';
    access_log /var/log/nginx/access.log main;
    sendfile on;
    keepalive_timeout 65;

    server {
        listen 0.0.0.0:$PORT;
        server_name _;
        root /var/www/html/public;
        index index.php;

        location / {
            try_files \$uri \$uri/ /index.php?\$query_string;
        }

        location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
            fastcgi_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
            fastcgi_set_header X-Forwarded-Proto \$scheme;
        }

        location ~ /\. { deny all; }
    }
}
EOF

echo "✓ nginx configuration generated"

# Test nginx
echo "Validating nginx configuration..."
if ! nginx -t; then
    echo "ERROR: nginx configuration invalid!"
    exit 1
fi
echo "✓ nginx configuration valid"
echo ""

# Start nginx in foreground
echo "===== Starting nginx on port $PORT ====="
exec nginx -g "daemon off;"
