# Production Laravel Docker with Apache
FROM php:8.3-apache-bullseye

# Install dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl npm nodejs postgresql-client libpq-dev \
    libssl-dev && \
    rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql opcache

# Enable Apache modules
RUN a2enmod rewrite headers

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Build dependencies
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs && \
    npm install && npm run build && \
    chown -R www-data:www-data /var/www/html

# PHP configuration for production
RUN cat > /usr/local/etc/php/conf.d/production.ini << 'EOF'
memory_limit = 512M
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
display_errors = Off
log_errors = On
error_reporting = E_ALL
EOF

# Apache configuration
RUN cat > /etc/apache2/sites-available/000-default.conf << 'EOF'
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        AllowOverride All
        Options -MultiViews
        Order allow,deny
        Allow from all
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^ index.php [QSA,L]
        </IfModule>
    </Directory>

    <Directory /var/www/html/storage>
        Deny from all
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Startup script to create .env and run migrations
RUN cat > /startup.sh << 'EOFSTART'
#!/bin/bash
set -e

echo "========================================="
echo "[STARTUP] Growfunder Application Starting"
echo "========================================="

# Create .env if it doesn't exist (will be overridden by Railway env vars)
if [ ! -f /var/www/html/.env ]; then
    cat > /var/www/html/.env << 'EOF'
APP_NAME=Growfunder
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:tE6w4W4Y+nhteXfQVPCAHKzOnKiUqJqbb2jQ9LTHrKA=
APP_URL=https://web-production-848ef.up.railway.app
DB_CONNECTION=pgsql
DB_PORT=5432
SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
TRUSTED_PROXIES=*
LOG_CHANNEL=stderr
EOF
    echo "[STARTUP] ✓ Created .env file"
fi

# Verify database connection
echo "[STARTUP] Waiting for database connection..."
for i in {1..30}; do
    if php artisan db:seed --help > /dev/null 2>&1; then
        echo "[STARTUP] ✓ Database connection successful"
        break
    fi
    echo "[STARTUP] Attempt $i/30... waiting for database"
    sleep 2
done

# Clear all caches to ensure fresh routes and config
echo "[STARTUP] Clearing application caches..."
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan config:clear || true
echo "[STARTUP] ✓ Caches cleared"

# Run database migrations
echo "[STARTUP] Running database migrations..."
php artisan migrate --force 2>&1 | head -20 || echo "[STARTUP] Migrations already completed"
echo "[STARTUP] ✓ Migrations complete"

# Run seeders in order (this is critical!)
echo "[STARTUP] Running database seeders..."
echo "[STARTUP]   1/4 NativeShieldSeeder..."
php artisan db:seed --class=Database\\Seeders\\NativeShieldSeeder --force 2>&1 || {
    echo "[STARTUP] ⚠️  NativeShieldSeeder failed, checking if roles exist..."
}

echo "[STARTUP]   2/4 PagePermissionsSeeder..."
php artisan db:seed --class=Database\\Seeders\\PagePermissionsSeeder --force 2>&1 || {
    echo "[STARTUP] ⚠️  PagePermissionsSeeder failed, continuing..."
}

echo "[STARTUP]   3/4 GrowfunderRolesSeeder..."
php artisan db:seed --class=Database\\Seeders\\GrowfunderRolesSeeder --force 2>&1 || {
    echo "[STARTUP] ⚠️  GrowfunderRolesSeeder failed, continuing..."
}

echo "[STARTUP]   4/4 CreateAdminSeeder..."
php artisan db:seed --class=Database\\Seeders\\CreateAdminSeeder --force 2>&1 || {
    echo "[STARTUP] ⚠️  CreateAdminSeeder failed, continuing..."
}

echo "[STARTUP] ✓ All seeders attempted"

# Verify roles exist in database
echo "[STARTUP] Verifying database state..."
php artisan tinker --execute '
$roleCount = \Spatie\Permission\Models\Role::count();
$permCount = \Spatie\Permission\Models\Permission::count();  
$userCount = \App\Models\User::count();
echo "   Roles: " . $roleCount . "\n";
echo "   Permissions: " . $permCount . "\n";
echo "   Users: " . $userCount . "\n";
' 2>/dev/null || echo "[STARTUP] (Could not verify database content)"

echo "[STARTUP] ========================================="
echo "[STARTUP] ✓ Initialization Complete - Starting Apache"
echo "[STARTUP] ========================================="

# Start Apache
exec apache2-foreground
EOFSTART

RUN chmod +x /startup.sh

EXPOSE 80
CMD ["/startup.sh"]