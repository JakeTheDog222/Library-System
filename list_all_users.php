<?php
require_once __DIR__ . '/../includes/config.php';

echo "Current users in the system:\n\n";

$stmt = $pdo->query('SELECT id, username, email, CONCAT(first_name, " ", COALESCE(middle_name, ""), " ", last_name) AS full_name, role FROM users ORDER BY role, id');
$users = $stmt->fetchAll();

foreach ($users as $user) {
    echo "ID: {$user['id']}\n";
    echo "Username: {$user['username']}\n";
    echo "Email: {$user['email']}\n";
    echo "Full Name: {$user['full_name']}\n";
    echo "Role: {$user['role']}\n";
    echo "Password: (use reset_admin.php to see default passwords)\n";
    echo "------------------------\n";
}
?>