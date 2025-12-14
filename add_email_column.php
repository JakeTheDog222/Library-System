<?php
require_once 'helpers.php';

try {
    // Check if email column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'email'");
    $stmt->execute();
    $emailColumn = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$emailColumn) {
        // Add email column
        $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(100) UNIQUE NOT NULL");

        // Update existing users with email addresses
        $pdo->prepare("UPDATE users SET email = 'admin@wmsu.edu.ph' WHERE username = 'admin'")->execute();
        $pdo->prepare("UPDATE users SET email = 'student@wmsu.edu.ph' WHERE username = 'student'")->execute();

        echo "Email column added successfully to users table.";
    } else {
        echo "Email column already exists.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
