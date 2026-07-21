#!/bin/bash
# Build script for Render.com
set -e

echo "=== Build Script Started ==="

# Clean old cache files only once
echo "Cleaning old cache files..."
rm -rf bootstrap/cache/*.php 2>/dev/null || true
rm -rf storage/framework/cache/data/* 2>/dev/null || true
rm -rf storage/framework/views/* 2>/dev/null || true

# Install PHP dependencies
echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Install Node dependencies and build frontend assets
echo "Building frontend assets..."
npm install
npm run build

echo "=== Build Script Completed ==="
echo "Note: Caching and migrations will run in the Dockerfile entrypoint for production readiness"
