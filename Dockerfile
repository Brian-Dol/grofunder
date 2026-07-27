# PHP 8.3 with nginx and supervisord
FROM php:8.3-fpm-alpine

RUN apk add --no-cache nginx supervisor curl npm nodejs git postgresql-client libpq-dev

RUN docker-php-ext-install pdo pdo_pgsql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs && \
    npm install && npm run build

# Create directories
RUN mkdir -p /var/log/supervisor /var/log/nginx /run/nginx

# Create supervisord config
RUN cat > /etc/supervisord.conf << 'EOF'
[supervisord]
nodaemon=true
logfile=/dev/stdout
logfile_maxbytes=0

[program:php-fpm]
command=php-fpm -F
autostart=true
autorestart=true
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
EOF

# Create entrypoint
RUN cat > /entrypoint.sh << 'EOF'
#!/bin/sh
set -e

echo "Creating .env..."
cat > .env << ENVEOF
APP_NAME=Growfunder
APP_ENV=production
APP_DEBUG=false
APP_KEY=${APP_KEY}
APP_URL=https://web-production-848ef.up.railway.app
DB_CONNECTION=pgsql
DB_HOST=${DB_HOST:-postgres.railway.internal}
DB_PORT=5432
DB_DATABASE=${DB_DATABASE:-railway}
DB_USERNAME=${DB_USERNAME:-postgres}
DB_PASSWORD=${DB_PASSWORD}
SESSION_DRIVER=database
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr
ENVEOF

echo "Caching config..."
php artisan config:cache
php artisan route:cache

PORT=${PORT:-8080}

echo "Creating nginx config..."
cat > /etc/nginx/nginx.conf << 'NGINXEOF'
user nobody;
worker_processes auto;
error_log /dev/stderr warn;
pid /var/run/nginx.pid;

events { worker_connections 128; }

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    access_log /dev/stdout;
    sendfile on;
    keepalive_timeout 65;

    server {
        listen 0.0.0.0:PORT;
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
            fastcgi_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            fastcgi_set_header X-Forwarded-Proto $scheme;
        }

        location ~ /\. { deny all; }
    }
}
NGINXEOF

sed -i "s/PORT/$PORT/g" /etc/nginx/nginx.conf

echo "Starting supervisord..."
exec supervisord -c /etc/supervisord.conf
EOF

chmod +x /entrypoint.sh

EXPOSE 8080
ENTRYPOINT ["/entrypoint.sh"]

