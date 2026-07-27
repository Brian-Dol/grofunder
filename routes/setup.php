<?php
// routes/web.php - add this to an existing route file or create new routes

Route::post('/admin/setup/create-admin', function () {
    // Simple security check - only allow from localhost or with a token
    $token = request('token');
    if ($token !== env('SETUP_TOKEN', 'setup123')) {
        abort(401, 'Unauthorized');
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
