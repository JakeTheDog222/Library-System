<?php
require 'config.php';

try {
    // Fix student emails
    $pdo->prepare("UPDATE users SET email = 'student@wmsu.edu.ph' WHERE username = 'Student@wmsu.com'")->execute();
    $pdo->prepare("UPDATE users SET email = 'student1@wmsu.edu.ph' WHERE username = 'Student1@wmsu.com'")->execute();
    $pdo->prepare("UPDATE users SET email = 'kalifer@wmsu.edu.ph' WHERE username = 'kalifer@wmsu.edu.ph'")->execute();

    // Reset student passwords to match reset_admin.php
    $stud_pass = password_hash('student12345', PASSWORD_DEFAULT);
    $stud2_pass = password_hash('student22222', PASSWORD_DEFAULT);

    $pdo->prepare("UPDATE users SET password = ? WHERE email = 'student@wmsu.edu.ph'")->execute([$stud_pass]);
    $pdo->prepare("UPDATE users SET password = ? WHERE email = 'student1@wmsu.edu.ph'")->execute([$stud2_pass]);

    echo "Student emails and passwords fixed successfully!\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
