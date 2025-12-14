<?php
require_once __DIR__ . '/../includes/helpers.php';

if (!isset($_GET['token'])) {
    die('Invalid verification link.');
}

$token = $_GET['token'];

// The $pdo object is available from helpers.php -> config.php

// Find the token and user
$stmt = $pdo->prepare('SELECT user_id FROM email_verification_tokens WHERE token = ?');
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('Invalid or expired verification token.');
}

$userId = $user['user_id'];

// Update the user record to set email_verified = TRUE
$updateStmt = $pdo->prepare('UPDATE users SET email_verified = TRUE WHERE id = ?');
$updateStmt->execute([$userId]);

// Delete the verification token
$deleteStmt = $pdo->prepare('DELETE FROM email_verification_tokens WHERE user_id = ?');
$deleteStmt->execute([$userId]);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Email Verification</title>
    <link rel="stylesheet" href="assets/css/custom.css" />
</head>
<body>
    <header>
        <h1>WMSU Library Email Verification</h1>
    </header>
    <main>
        <section class="notification">
            <h2>Email Verified Successfully</h2>
            <p>Your email address has been verified. You can now log in to your account.</p>
            <a href="../index.php" class="btn">Go to Login</a>
        </section>
    </main>
</body>
</html>