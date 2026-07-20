<?php
use App\Http\Controllers\{
    BorrowersController,
    SubscriptionsController,
    CustomerStatementController,
    BorrowerApplicationController,
    LoanApplicationController,
    DirectDebitMandateController,
    PayslipController,
    DocumentController
};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



Route::get('/', function () {
    return view('welcome');
});

// Debug endpoint - direct inline definition
Route::get('/debug-config', function () {
    return response()->json([
        'environment_vars' => [
            'APP_ENV' => env('APP_ENV'),
            'APP_URL' => env('APP_URL'),
            'ASSET_URL' => env('ASSET_URL'),
            'APP_DEBUG' => env('APP_DEBUG'),
            'TRUSTED_PROXIES' => env('TRUSTED_PROXIES'),
        ],
        'config_values' => [
            'app.env' => config('app.env'),
            'app.url' => config('app.url'),
            'app.asset_url' => config('app.asset_url'),
            'app.debug' => config('app.debug'),
        ],
        'url_helpers' => [
            'URL::to("test")' => \Illuminate\Support\Facades\URL::to('test'),
            'URL::asset("css/test.css")' => \Illuminate\Support\Facades\URL::asset('css/test.css'),
            'asset("css/test.css")' => asset('css/test.css'),
            'URL::getScheme()' => \Illuminate\Support\Facades\URL::getScheme(),
            'URL::getRootUrl()' => \Illuminate\Support\Facades\URL::getRootUrl(),
        ],
        'server_variables' => [
            'HTTPS' => $_SERVER['HTTPS'] ?? 'not set',
            'REQUEST_SCHEME' => $_SERVER['REQUEST_SCHEME'] ?? 'not set',
            'SERVER_PORT' => $_SERVER['SERVER_PORT'] ?? 'not set',
            'X-Forwarded-Proto' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'not set',
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'not set',
        ],
        'request_helpers' => [
            'request()->secure()' => request()->secure() ? 'true' : 'false',
            'request()->getScheme()' => request()->getScheme(),
            'request()->getRootUrl()' => request()->getRootUrl(),
        ],
    ]);
});

// Test asset_https helper
Route::get('/test-https', function () {
    $assetHttpsExists = function_exists('asset_https');
    $result = [
        'env' => app()->environment(),
        'app_url' => config('app.url'),
        'asset_https_exists' => $assetHttpsExists,
        'asset_https_callable' => is_callable('asset_https'),
        'request_scheme' => \Request::getScheme(),
        'is_https' => \Request::isSecure(),
        'server_https' => $_SERVER['HTTPS'] ?? 'not set',
        'x_forwarded_proto' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'not set',
    ];
    
    if ($assetHttpsExists) {
        $result['asset_https_tests'] = [
            'test.css' => asset_https('test.css'),
            'landingPage/css/style.css' => asset_https('landingPage/css/style.css'),
            'icon.png' => asset_https('icon.png'),
        ];
    } else {
        $result['error'] = 'asset_https function does not exist!';
    }
    
    return response()->json($result);
});

// Simple test for middleware - returns HTML with HTTP URLs that middleware should convert
Route::get('/test-middleware', function () {
    return '<html><body><h1>Test</h1><p>Visit <a href="http://grofunder.onrender.com/css/test.css">styles</a></p></body></html>';
});

// Diagnostic endpoint
require __DIR__ . '/diagnostic.php';

// Debug endpoints
require __DIR__ . '/debug.php';

Route::get('/subscription/{amount}', function ($amount) {
    return view('gateways.lenco.lencoPayments', ['amount' => decrypt($amount)]);
})->name('subscription.lenco');

Route::post('completeSubscription/{amount}', [SubscriptionsController::class, 'completeSubscription'])
    ->name('completeSubscription');



Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('borrower', BorrowersController::class);
});

Route::get('/statement/{record}', [CustomerStatementController::class, 'download'])->name('statement.download');

Route::get('/payslip/{payslip}/download', [\App\Http\Controllers\PayslipController::class, 'download'])
    ->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
    ->name('payslip.download');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/borrower-application/{id}/preview', [BorrowerApplicationController::class, 'preview'])->name('borrower.application.preview');
    Route::get('/borrower-application/{id}/download', [BorrowerApplicationController::class, 'download'])->name('borrower.application.download');
    Route::get('/loan-application/{id}/preview', [LoanApplicationController::class, 'preview'])->name('loan.application.preview');
    Route::get('/loan-application/{id}/download', [LoanApplicationController::class, 'download'])->name('loan.application.download');
    Route::get('/direct-debit-mandate/{id}/preview', [DirectDebitMandateController::class, 'preview'])->name('direct.debit.mandate.preview');
    Route::get('/direct-debit-mandate/{id}/download', [DirectDebitMandateController::class, 'download'])->name('direct.debit.mandate.download');

    // Document routes
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('{document}/download', [DocumentController::class, 'download'])->name('download');
        Route::get('{document}/view', [DocumentController::class, 'view'])->name('view');
        Route::delete('{document}', [DocumentController::class, 'delete'])->name('delete');
    });
});

