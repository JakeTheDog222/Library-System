<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../classes/EmailHelper.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        // Check if user with email exists
        $stmt = $pdo->prepare('SELECT id, full_name FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = 'No account found with that email address.';
        } else {
            // Generate reset token and expiry (1 hour)
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Save token in password_reset_tokens table
            $stmt = $pdo->prepare('INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)');
            $stmt->execute([$user['id'], $token, $expires_at]);

            // Send password reset email
            $emailHelper = new EmailHelper();
            $resetLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .
                "://{$_SERVER['HTTP_HOST']}/password_reset.php?token={$token}";

            $emailHelper->passwordResetEmail($email, $resetLink, $user['full_name']);

            $success = 'A password reset link has been sent to your email address.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Password Reset Request</title>
    <link rel="stylesheet" href="../assets/css/custom.css" />
</head>
<body>
    <main class="content">
        <h2>Password Reset Request</h2>
        <?php if ($error): ?>
            <p style="color:red;"><?=htmlspecialchars($error)?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p style="color:green;"><?=htmlspecialchars($success)?></p>
        <?php endif; ?>
        <form method="post">
            <label for="email">Enter your email address:</label><br />
            <input type="email" id="email" name="email" required /><br /><br />
            <button type="submit">Send Reset Link</button>
        </form>
        <p><a href="index.php">Back to Login</a></p>
    </main>
</body>
</html>
