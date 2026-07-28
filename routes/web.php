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

// TEMPORARY DEBUG ROUTE - TEST DATABASE QUERY
Route::get('/debug-borrowers', function () {
    try {
        $count = \App\Models\Borrower::count();
        $borrowers = \App\Models\Borrower::limit(5)->get(['id', 'first_name', 'last_name', 'email']);
        
        return response()->json([
            'success' => true,
            'total_borrowers' => $count,
            'sample_borrowers' => $borrowers->toArray(),
            'user_authenticated' => auth()->check(),
            'user_id' => auth()->id(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});
    $logPath = storage_path('logs/laravel.log');
    if (!file_exists($logPath)) {
        return response('Log file not found', 404);
    }
    $logContent = file_get_contents($logPath);
    $lines = array_slice(explode("\n", $logContent), -200);
    return response(
        '<pre style="background:#000;color:#0f0;padding:20px;font-family:monospace;white-space:pre-wrap;word-wrap:break-word;font-size:12px;">' . 
        htmlspecialchars(implode("\n", $lines)) . 
        '</pre>',
        200,
        ['Content-Type' => 'text/html; charset=UTF-8']
    );
});

// TEMPORARY ROUTE - RUN SEEDERS (ENSURE ROLES/PERMISSIONS IN DATABASE)
Route::get('/run-seeders', function () {
    $token = request('token');
    if ($token !== env('SETUP_TOKEN', 'setup123')) {
        return response()->json(['error' => 'Unauthorized - token invalid'], 401);
    }
    
    try {
        $results = [];
        
        $results[] = "▶ Running NativeShieldSeeder...";
        \Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\NativeShieldSeeder']);
        $results[] = \Artisan::output();
        
        $results[] = "▶ Running PagePermissionsSeeder...";
        \Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PagePermissionsSeeder']);
        $results[] = \Artisan::output();
        
        $results[] = "▶ Running GrowfunderRolesSeeder...";
        \Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\GrowfunderRolesSeeder']);
        $results[] = \Artisan::output();
        
        $results[] = "▶ Running CreateAdminSeeder...";
        \Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\CreateAdminSeeder']);
        $results[] = \Artisan::output();
        
        return response()->json([
            'success' => true,
            'message' => 'All seeders executed successfully',
            'output' => implode("\n", $results)
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

Route::get('/', function () {
    return view('welcome');
});

// Setup route for creating admin
Route::post('/admin/setup/create-admin', function () {
    $token = request('token');
    if ($token !== env('SETUP_TOKEN', 'setup123')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    try {
        \Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\CreateAdminSeeder']);
        return response()->json([
            'success' => true,
            'message' => 'Admin user created successfully',
            'email' => 'admin@growfunder.local',
            'password' => 'admin123'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

// Diagnostic endpoint
require __DIR__ . '/diagnostic.php';

// Debug endpoints - only in local environment
if (app()->environment('local', 'staging')) {
    require __DIR__ . '/debug.php';
}

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

