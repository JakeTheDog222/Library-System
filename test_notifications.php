<?php
require 'config.php';

try {
    // Show current database
    $stmt = $pdo->query("SELECT DATABASE()");
    $currentDb = $stmt->fetchColumn();
    echo "Current database: $currentDb\n";
    echo "Configured database: $DB_NAME\n";

    // Test if notifications table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
    $tableExists = $stmt->fetch();

    if (!$tableExists) {
        echo "FAIL: Notifications table does not exist.\n";
        exit(1);
    }

    echo "PASS: Notifications table exists.\n";

    // Test the query from Borrow.php line 177
    // Simulate some data
    $borrow = ['user_id' => 1, 'id' => 123];
    $check = $pdo->prepare("SELECT id FROM notifications WHERE user_id = ? AND type = ? AND message LIKE ?");
    $result = $check->execute([$borrow['user_id'], 'overdue', "%{$borrow['id']}%"]);

    if ($result) {
        echo "PASS: Query executed successfully.\n";
    } else {
        echo "FAIL: Query failed.\n";
    }

    echo "Test completed.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
