<?php
require 'config.php';

try {
    // Read the schema.sql file
    $schema = file_get_contents('sql/schema.sql');

    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $schema)));

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }

    echo "Database schema created successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
