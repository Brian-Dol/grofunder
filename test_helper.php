<?php

// Mock the config() function for testing
if (!function_exists('config')) {
    function config($key, $default = null) {
        if ($key === 'app.asset_url') {
            return null; // Will use the fallback
        }
        if ($key === 'app.url') {
            return 'https://grofunder.onrender.com';
        }
        return $default;
    }
}

require __DIR__ . '/app/helpers/HttpsAssetHelper.php';
echo asset_https('test.css') . "\n";
echo asset_https('css/app.css') . "\n";
echo asset_https('landingPage/css/style.css') . "\n";
?>
