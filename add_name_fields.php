<?php
require_once 'helpers.php';

try {
    // Check if first_name column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'first_name'");
    $stmt->execute();
    $firstNameColumn = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$firstNameColumn) {
        // Add name fields
        $pdo->exec("ALTER TABLE users
            ADD COLUMN first_name VARCHAR(50) NOT NULL AFTER password,
            ADD COLUMN middle_name VARCHAR(50) AFTER first_name,
            ADD COLUMN last_name VARCHAR(50) NOT NULL AFTER middle_name,
            DROP COLUMN full_name VARCHAR(100) NOT NULL AFTER last_name");

        // Update existing users with name data
        $pdo->prepare("UPDATE users SET first_name = 'Administrator', last_name = '', full_name = 'Administrator' WHERE username = 'admin'")->execute();
        $pdo->prepare("UPDATE users SET first_name = 'Test', last_name = 'Student', full_name = 'Test Student' WHERE username = 'student'")->execute();

        echo "Name fields added successfully to users table.";
    } else {
        echo "Name fields already exist.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
