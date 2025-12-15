<?php
require 'config.php';

try {
    // Drop the table if it exists
    $pdo->exec("DROP TABLE IF EXISTS notifications");

    // Recreate the table
    $createTableSQL = "
    CREATE TABLE notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type ENUM('approval', 'rejection', 'due_reminder', 'overdue', 'fine', 'reservation_available', 'cancellation') NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );
    ";
    $pdo->exec($createTableSQL);
    echo "Notifications table recreated successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
