<?php
require 'config.php';

try {
    // Delete existing admin
    $pdo->exec("DELETE FROM users WHERE role = 'admin'");

    // Reset auto increment
    $pdo->exec("ALTER TABLE users AUTO_INCREMENT = 1");

    // Hash passwords
    $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
    $stud_pass = password_hash('student12345', PASSWORD_DEFAULT);
    $stud2_pass = password_hash('student22222', PASSWORD_DEFAULT);

    // Insert new admin with email
    $pdo->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, 'admin')")
        ->execute(['Admin', $admin_pass, 'Administrator', 'angelobadi124@gmail.com']);

    // Insert students
    $pdo->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, 'student')")
        ->execute(['Student@wmsu.com', $stud_pass, 'Student', 'student@wmsu.edu.ph']);
    $pdo->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, 'student')")
        ->execute(['Student1@wmsu.com', $stud2_pass, 'Student1', 'student1@wmsu.edu.ph']);

    echo "Admin reset complete. Admin email: angelobadi124@gmail.com, password: admin123\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
