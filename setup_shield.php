<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Run shield:generate --all with admin panel selected
$code = $kernel->call('shield:generate', ['--all' => true]);

echo "✅ Shield permissions generated for admin panel!\n";
