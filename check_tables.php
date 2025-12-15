<?php
require 'config.php';

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Existing tables in database '$DB_NAME':\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }

    // Check if notifications table exists
    if (in_array('notifications', $tables)) {
        echo "\nNotifications table exists.\n";
    } else {
        echo "\nNotifications table does NOT exist.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
