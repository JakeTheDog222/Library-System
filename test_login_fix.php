<?php
require 'config.php';

// Test admin login
$email = 'angelobadi124@gmail.com';
$password = 'admin123';

echo "Testing login with email: $email, password: $password\n";

$stmt = $pdo->prepare('SELECT id, username, email, password, full_name, role FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    echo "User found: " . $user['full_name'] . " (" . $user['email'] . ")\n";
    if (password_verify($password, $user['password'])) {
        echo "Password verification successful!\n";
        echo "Login should work now.\n";
    } else {
        echo "Password verification failed.\n";
    }
} else {
    echo "User not found with email: $email\n";
}
?>
