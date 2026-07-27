<?php
/**
 * Laravel PHP Development Server Router
 * Serves static files directly, routes everything else to index.php
 */

$file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// If the requested file exists and is not a directory, serve it
if (file_exists($file) && !is_dir($file)) {
    return false; // Let PHP serve the file
}

// Otherwise, route to index.php (Laravel app)
require __DIR__ . '/index.php';
