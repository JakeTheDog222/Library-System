<?php
require 'config.php';

try {
    $stmt = $pdo->query('SELECT id, username, email, password FROM users WHERE role = "admin"');
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo "Admin ID: " . $admin['id'] . "\n";
        echo "Username: " . $admin['username'] . "\n";
        echo "Email: " . $admin['email'] . "\n";
        echo "Password hash: " . $admin['password'] . "\n";

        // Test password verification
        $testPassword = 'admin123';
        if (password_verify($testPassword, $admin['password'])) {
            echo "Password 'admin123' is correct\n";
        } else {
            echo "Password 'admin123' is incorrect\n";
        }
    } else {
        echo "No admin user found.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
