<?php
require_once 'helpers.php';

try {
    // Check if full_name column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'full_name'");
    $stmt->execute();
    $fullNameColumn = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($fullNameColumn) {
        // Drop full_name column
        $pdo->exec("ALTER TABLE users DROP COLUMN full_name");

        echo "full_name column dropped successfully from users table.";
    } else {
        echo "full_name column does not exist.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
