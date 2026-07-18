#!/bin/bash
# Build script for Render.com

# Clean old cache
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/views/*

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies and build frontend assets
npm install
npm run build

# Generate app key if not set
php artisan key:generate --force || true

# Clear and cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (will fail if DB not configured yet)
php artisan migrate --force || true
