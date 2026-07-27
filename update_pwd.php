<?php
// Direct database update using PDO

// Read .env file line by line
$env_file = __DIR__ . '/.env';
$env_vars = [];

if (file_exists($env_file)) {
    foreach (file($env_file) as $line) {
        if (trim($line) && !str_starts_with(trim($line), '#')) {
            [$key, $val] = explode('=', $line, 2);
            $env_vars[trim($key)] = trim($val);
        }
    }
}

$host = $env_vars['DB_HOST'] ?? 'localhost';
$port = $env_vars['DB_PORT'] ?? '5432';
$db = $env_vars['DB_DATABASE'] ?? 'growfunder';
$user = $env_vars['DB_USERNAME'] ?? 'postgres';
$password = $env_vars['DB_PASSWORD'] ?? '';

try {
    // First, let's check what user exists
    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
    $pdo = new PDO($dsn, $user, $password ?: '');
    
    // List all users
    $stmt = $pdo->prepare('SELECT id, email, password FROM users LIMIT 10');
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Users in database:\n";
    foreach ($users as $u) {
        echo "  ID: {$u['id']}, Email: {$u['email']}\n";
    }
    
    // Generate a new hash using PHP's password_hash with BCRYPT
    // Laravel uses PASSWORD_BCRYPT with cost 12 by default
    $new_password = 'admin123';
    $hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
    
    echo "\nNew hash created: " . substr($hash, 0, 20) . "...\n";
    
    // Update the password
    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
    $result = $stmt->execute([$hash, 'admin@growfunder.local']);
    
    echo "Update result: " . ($result ? "success" : "failed") . "\n";
    echo "Rows affected: " . $stmt->rowCount() . "\n";
    
    // Verify the update
    $stmt = $pdo->prepare('SELECT password FROM users WHERE email = ?');
    $stmt->execute(['admin@growfunder.local']);
    $userRecord = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($userRecord) {
        echo "Updated password hash: " . substr($userRecord['password'], 0, 20) . "...\n";
        echo "\n✓ Admin password has been set to: admin123\n";
    } else {
        echo "✗ User not found after update\n";
    }
    
} catch (\PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}
