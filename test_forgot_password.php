<?php
require_once 'helpers.php';

echo "Testing forgot password functionality...\n\n";

try {
    // Test the query that was failing
    $stmt = $pdo->prepare('SELECT id, full_name FROM users WHERE username = ? AND email = ?');
    $stmt->execute(['admin', 'admin@wmsu.edu.ph']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "✅ Query successful! Found user: " . $user['full_name'] . "\n";
    } else {
        echo "❌ Query executed but no user found with those credentials.\n";
    }

    // Check if email column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'email'");
    $stmt->execute();
    $emailColumn = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($emailColumn) {
        echo "✅ Email column exists in users table.\n";
        echo "Column details: " . json_encode($emailColumn) . "\n";
    } else {
        echo "❌ Email column does not exist in users table.\n";
    }

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";
?>
