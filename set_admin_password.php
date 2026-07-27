<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$response = $kernel->handle($request = new \Illuminate\Http\Request);

// Use Illuminate's hashing
$hash = app('hash')->make('admin123');
$user = \App\Models\User::where('email', 'admin@growfunder.local')->first();
if ($user) {
    $user->password = $hash;
    $user->save();
    echo "Password updated successfully for admin@growfunder.local\n";
    echo "New password: admin123\n";
} else {
    echo "User not found\n";
}
