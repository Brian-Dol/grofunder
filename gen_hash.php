<?php
// Simple password hash generator
$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
echo "Hash: " . $hash . "\n";
echo "Use this in your database\n";
