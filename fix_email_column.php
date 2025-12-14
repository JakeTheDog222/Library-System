<?php
require_once 'config.php';

try {
    // Check current users table structure
    echo "Checking users table structure...\n";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "Column: {$col['Field']} - {$col['Type']} - {$col['Null']} - {$col['Key']}\n";
    }

    // Check if email column exists
    $emailExists = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'email') {
            $emailExists = true;
            break;
        }
    }

    if (!$emailExists) {
        echo "Email column does not exist. Adding it...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(100)");
    }

    // Update existing users with emails based on their usernames
    echo "Updating existing users with emails...\n";
    $pdo->prepare("UPDATE users SET email = CONCAT(username, '@wmsu.edu.ph') WHERE email IS NULL OR email = ''")->execute();

    // Make email column NOT NULL and UNIQUE
    echo "Making email column NOT NULL and UNIQUE...\n";
    $pdo->exec("ALTER TABLE users MODIFY COLUMN email VARCHAR(100) NOT NULL UNIQUE");

    echo "Email column fixed successfully!\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
