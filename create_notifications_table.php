<?php
require 'config.php';

try {
    // Check if notifications table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
    $tableExists = $stmt->fetch();

    if (!$tableExists) {
        // Create the notifications table
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
        echo "Notifications table created successfully.\n";
    } else {
        echo "Notifications table already exists.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
