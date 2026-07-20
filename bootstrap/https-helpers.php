<?php

/**
 * HTTPS Helper Registration - Pre-Bootstrap
 * 
 * This file doesn't override functions (can't do that in PHP once defined).
 * Instead, it ensures environment variables are set correctly for Laravel to read.
 * 
 * The real fix happens through the service providers and Dockerfile.
 */

// Verify environment is set for HTTPS production deployment
if (empty(getenv('APP_URL'))) {
    putenv('APP_URL=https://grofunder.onrender.com');
}

if (empty(getenv('APP_ENV'))) {
    putenv('APP_ENV=production');
}

// Ensure $_ENV array is populated (some systems use getenv() and some use $_ENV)
if (empty($_ENV['APP_URL'])) {
    $_ENV['APP_URL'] = 'https://grofunder.onrender.com';
}

if (empty($_ENV['APP_ENV'])) {
    $_ENV['APP_ENV'] = 'production';
}



