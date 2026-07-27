<?php
// Update password on Railway database

// Railway database connection details (from Dockerfile)
$host = 'postgres.railway.internal';
$port = '5432';
$db = 'railway';
$user = 'postgres';
$password = 'QQMChGefegtixvAHbSsUiJjnbkuEPGKm'; // From earlier configuration

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db sslmode=require";
    $pdo = new PDO($dsn, $user, $password);
    
    echo "✓ Connected to Railway database\n";
    
    // Generate password hash
    $new_password = 'admin123';
    $hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
    
    echo "Updating admin password...\n";
    
    // Update the password
    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
    $result = $stmt->execute([$hash, 'admin@growfunder.local']);
    
    echo "Rows affected: " . $stmt->rowCount() . "\n";
    
    if ($stmt->rowCount() > 0) {
        echo "\n✓ Admin password updated successfully on Railway!\n";
        echo "Email: admin@growfunder.local\n";
        echo "Password: admin123\n";
    } else {
        echo "✗ No rows updated. User may not exist.\n";
        
        // List users to debug
        $stmt = $pdo->prepare('SELECT id, email FROM users LIMIT 5');
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($users) {
            echo "\nUsers found:\n";
            foreach ($users as $u) {
                echo "  - {$u['email']}\n";
            }
        }
    }
    
} catch (\PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}
