<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap($app);

// Create a default branch
$branch = DB::table('branches')->insertGetId([
    'branch_name' => 'Main Branch',
    'street' => 'Main Street',
    'address' => 'Main Address',
    'mobile' => '+1234567890',
    'email' => 'main@growfunder.local',
    'city' => 'Main City',
    'province' => 'Main Province',
    'branch_manager' => 1,  // Admin user
    'zipcode' => '00000',
    'added_by' => 1,  // Admin user
    'organization_id' => 1,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);

echo "Branch created with ID: {$branch}\n";

// Verify it was created
$branches = DB::table('branches')->get();
echo "Total branches now: " . count($branches) . "\n";
foreach ($branches as $b) {
    echo "ID: {$b->id}, Name: {$b->branch_name}\n";
}
?>
