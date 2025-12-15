<?php
require 'config.php';

try {
    // Repair the notifications table
    $pdo->exec("REPAIR TABLE notifications");
    echo "Notifications table repaired.\n";

    // Test the query
    $borrow = ['user_id' => 1, 'id' => 123];
    $check = $pdo->prepare("SELECT id FROM notifications WHERE user_id = ? AND type = ? AND message LIKE ?");
    $result = $check->execute([$borrow['user_id'], 'overdue', "%{$borrow['id']}%"]);

    if ($result) {
        echo "PASS: Query executed successfully after repair.\n";
    } else {
        echo "FAIL: Query still fails after repair.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
