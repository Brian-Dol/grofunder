#!/usr/bin/env php
<?php
define('LARAVEL_START', microtime(true));

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArrayInput([
        'command' => 'db:seed',
    ]),
    new Symfony\Component\Console\Output\BufferedOutput
);

// Now try to use tinker to set password
\Artisan::call('db:table', ['--name' => 'users', '--orderBy' => 'email']);

// Alternative: direct approach
try {
    $user = \App\Models\User::where('email', 'admin@growfunder.local')->first();
    if ($user) {
        $user->password = bcrypt('admin123');
        $user->save();
        echo "✓ Admin password set to: admin123\n";
    } else {
        echo "✗ Admin user not found\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
