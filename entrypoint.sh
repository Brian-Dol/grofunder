#!/bin/sh
set -e

echo "[$(date +'%Y-%m-%d %H:%M:%S')] Starting Growfunder..."
echo "[$(date +'%Y-%m-%d %H:%M:%S')] PWD: $(pwd)"
echo "[$(date +'%Y-%m-%d %H:%M:%S')] PORT: ${PORT:-8080}"

# Generate .env
echo "[$(date +'%Y-%m-%d %H:%M:%S')] Creating .env..."
cat > .env << EOF
APP_NAME=Growfunder
APP_ENV=production  
APP_DEBUG=false
APP_KEY=${APP_KEY}
APP_URL=${APP_URL:-https://web-production-848ef.up.railway.app}
DB_CONNECTION=pgsql
DB_HOST=${DB_HOST:-postgres.railway.internal}
DB_PORT=${DB_PORT:-5432}
DB_DATABASE=${DB_DATABASE:-railway}
DB_USERNAME=${DB_USERNAME:-postgres}
DB_PASSWORD=${DB_PASSWORD}
SESSION_DRIVER=database
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr
EOF

echo "[$(date +'%Y-%m-%d %H:%M:%S')] Caching config..."
php artisan config:cache 2>&1 || true
php artisan route:cache 2>&1 || true

echo "[$(date +'%Y-%m-%d %H:%M:%S')] Starting PHP-FPM..."
php-fpm -D 2>&1
sleep 1

echo "[$(date +'%Y-%m-%d %H:%M:%S')] Checking PHP-FPM..."
if pidof php-fpm > /dev/null; then
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] ✓ PHP-FPM running"
else
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] ✗ PHP-FPM not running!"
    exit 1
fi

PORT=${PORT:-8080}

echo "[$(date +'%Y-%m-%d %H:%M:%S')] Creating nginx config for port $PORT..."
mkdir -p /var/log/nginx

cat > /etc/nginx/nginx.conf << 'EOF'
user nobody;
worker_processes 1;
error_log /dev/stderr info;
pid /var/run/nginx.pid;

events {
    worker_connections 128;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    access_log /dev/stdout;
    sendfile on;
    keepalive_timeout 65;

    server {
        listen 0.0.0.0:LISTEN_PORT;
        server_name _;
        root /app/public;
        index index.php;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param PATH_INFO $fastcgi_path_info;
            fastcgi_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            fastcgi_set_header X-Forwarded-Proto $scheme;
        }

        location ~ /\. {
            deny all;
        }
    }
}
EOF

sed -i "s/LISTEN_PORT/$PORT/g" /etc/nginx/nginx.conf

echo "[$(date +'%Y-%m-%d %H:%M:%S')] Validating nginx config..."
if nginx -t 2>&1; then
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] ✓ nginx config valid"
else
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] ✗ nginx config invalid!"
    exit 1
fi

echo "[$(date +'%Y-%m-%d %H:%M:%S')] ===== STARTING SERVICES ====="
echo "[$(date +'%Y-%m-%d %H:%M:%S')] nginx will listen on 0.0.0.0:$PORT"
echo "[$(date +'%Y-%m-%d %H:%M:%S')] PHP-FPM running on 127.0.0.1:9000"

exec nginx -g "daemon off;"
