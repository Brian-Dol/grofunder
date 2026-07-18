<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

// Create super-admin user
$user = User::create([
    'name' => 'Admin',
    'email' => 'admin@growfunder.local',
    'password' => bcrypt('Growfunder@123'),
    'email_verified_at' => now(),
]);

echo "✅ Super-admin user created!\n";
echo "Email: " . $user->email . "\n";
echo "Password: Growfunder@123\n";
