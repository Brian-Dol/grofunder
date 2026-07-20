<?php
// Direct PHP check - bypasses Laravel routing entirely

$output = [
    'timestamp' => date('Y-m-d H:i:s'),
    'environment_vars' => [
        'APP_ENV' => getenv('APP_ENV'),
        'APP_URL' => getenv('APP_URL'),
        'ASSET_URL' => getenv('ASSET_URL'),
        'APP_DEBUG' => getenv('APP_DEBUG'),
        'TRUSTED_PROXIES' => getenv('TRUSTED_PROXIES'),
    ],
    'server_variables' => [
        'HTTPS' => $_SERVER['HTTPS'] ?? 'not set',
        'REQUEST_SCHEME' => $_SERVER['REQUEST_SCHEME'] ?? 'not set',
        'SERVER_PORT' => $_SERVER['SERVER_PORT'] ?? 'not set',
        'X-Forwarded-Proto' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'not set',
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'not set',
        'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? 'not set',
    ],
];

// Try to load Laravel config if available
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    
    $output['laravel_config'] = [
        'app.env' => config('app.env'),
        'app.url' => config('app.url'),
        'app.asset_url' => config('app.asset_url'),
        'app.debug' => config('app.debug'),
    ];
    
    $output['url_helpers'] = [
        'URL::to("test")' => \Illuminate\Support\Facades\URL::to('test'),
        'URL::asset("css/test.css")' => \Illuminate\Support\Facades\URL::asset('css/test.css'),
        'asset("css/test.css")' => asset('css/test.css'),
        'URL::getScheme()' => \Illuminate\Support\Facades\URL::getScheme(),
        'URL::getRootUrl()' => \Illuminate\Support\Facades\URL::getRootUrl(),
    ];
    
    $output['request_helpers'] = [
        'request()->secure()' => request()->secure() ? 'true' : 'false',
        'request()->getScheme()' => request()->getScheme(),
        'request()->getRootUrl()' => request()->getRootUrl(),
    ];
} catch (Exception $e) {
    $output['laravel_error'] = $e->getMessage();
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
