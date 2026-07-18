<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap($app);

$branches = DB::table('branches')->select('id', 'branch_name', 'organization_id')->get();
echo "Total branches: " . count($branches) . "\n";
foreach ($branches as $b) {
    echo "ID: {$b->id}, Name: {$b->branch_name}, Org: {$b->organization_id}\n";
}
?>
