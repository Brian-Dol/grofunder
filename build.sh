#!/bin/bash
# Build script for Render.com

# Clean old cache
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes.php
rm -f bootstrap/cache/views.php
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/views/*

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies and build frontend assets
npm install
npm run build

# Generate app key if needed
php artisan key:generate --force 2>/dev/null || true

# Clear caches
php artisan cache:clear 2>/dev/null || true
php artisan config:clear 2>/dev/null || true

# Cache config only (NOT routes - let them be discovered dynamically)
php artisan config:cache 2>/dev/null || true

# Generate app key if not set
php artisan key:generate --force || true

# Clear and cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (will fail if DB not configured yet)
php artisan migrate --force || true
