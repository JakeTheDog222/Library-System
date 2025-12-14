<?php
require_once __DIR__ . '/../helpers.php';

$error = '';
$success = '';
$showForm = false;

$token = $_GET['token'] ?? '';

if (!$token) {
    die('Invalid password reset link.');
}

// Check if token exists and not expired
$stmt = $pdo->prepare('SELECT user_id, expires_at FROM password_reset_tokens WHERE token = ?');
$stmt->execute([$token]);
$tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tokenData) {
    die('Invalid or expired password reset token.');
}

if (strtotime($tokenData['expires_at']) < time()) {
    // Token expired, delete it
    $deleteStmt = $pdo->prepare('DELETE FROM password_reset_tokens WHERE token = ?');
    $deleteStmt->execute([$token]);
    die('Password reset token has expired. Please request a new password reset.');
}

$showForm = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password === '' || $confirm_password === '') {
        $error = 'Please fill out all fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Update user's password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
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
            $success = 'Your password has been reset successfully. You can now log in.';
            $showForm = false;
        } else {
            $error = 'Failed to reset password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reset Password</title>
    <link rel="stylesheet" href="../assets/css/custom.css" />
</head>
<body>
    <main class="content">
        <h2>Reset Password</h2>
        <?php if ($error): ?>
            <p style="color:red;"><?=htmlspecialchars($error)?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p style="color:green;"><?=htmlspecialchars($success)?></p>
            <p><a href="index.php">Go to Login</a></p>
        <?php endif; ?>
        <?php if ($showForm): ?>
            <form method="post">
                <label for="password">New Password:</label><br />
                <input type="password" id="password" name="password" required /><br /><br />
                <label for="confirm_password">Confirm New Password:</label><br />
                <input type="password" id="confirm_password" name="confirm_password" required /><br /><br />
                <button type="submit">Reset Password</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
