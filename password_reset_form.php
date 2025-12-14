<?php
require_once __DIR__ . '/../helpers.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$token) {
        $error = 'Invalid password reset request.';
    } elseif ($new_password === '' || $confirm_password === '') {
        $error = 'Please fill out all fields.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if token exists and not expired
        $stmt = $pdo->prepare('SELECT user_id, expires_at FROM password_reset_tokens WHERE token = ?');
        $stmt->execute([$token]);
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tokenData) {
            $error = 'Invalid or expired password reset token.';
        } elseif (strtotime($tokenData['expires_at']) < time()) {
            // Token expired, delete it
            $deleteStmt = $pdo->prepare('DELETE FROM password_reset_tokens WHERE token = ?');
            $deleteStmt->execute([$token]);
            $error = 'Password reset token has expired. Please request a new password reset.';
        } else {
            // Update user's password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $result = $updateStmt->execute([$hashed_password, $tokenData['user_id']]);

            if ($result) {
                // Send password change notification email
                $emailHelper = new EmailHelper();
                $userStmt = $pdo->prepare('SELECT email, full_name FROM users WHERE id = ?');
                $userStmt->execute([$tokenData['user_id']]);
                $userInfo = $userStmt->fetch(PDO::FETCH_ASSOC);

                if ($userInfo) {
                    $emailHelper->passwordChangeNotificationEmail($userInfo['email'], $userInfo['full_name']);
                }

                // Delete used token
                $deleteStmt = $pdo->prepare('DELETE FROM password_reset_tokens WHERE token = ?');
                $deleteStmt->execute([$token]);
                $success = 'Your password has been reset successfully. You can now log in with your new password.';
            } else {
                $error = 'Failed to reset password. Please try again.';
            }
        }
    }
} else {
    // If not POST, redirect to forgot password
    header('Location: ../forgot_password.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Password Reset Result</title>
    <link rel="stylesheet" href="assets/css/custom.css" />
</head>
<body>
    <main class="content">
        <h2>Password Reset</h2>
        <?php if ($error): ?>
            <p style="color:red;"><?=htmlspecialchars($error)?></p>
            <p><a href="../forgot_password.php">Request New Password Reset</a></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p style="color:green;"><?=htmlspecialchars($success)?></p>
            <p><a href="../index.php">Go to Login</a></p>
        <?php endif; ?>
    </main>
</body>
</html>
