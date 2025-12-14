<?php
require_once 'helpers.php';
require_once 'classes/EmailHelper.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $error = 'Please provide your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        // Check if user with email exists
        $stmt = $pdo->prepare('SELECT id, first_name, middle_name, last_name FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = 'No account found with that email address.';
        } else {
            // Construct full name
            $full_name = trim($user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . $user['last_name']);

            // Generate reset token and expiry (1 hour)
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Save token in password_reset_tokens table
            $stmt = $pdo->prepare('INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)');
            $stmt->execute([$user['id'], $token, $expires_at]);

            // Send password reset email with embedded form
            $emailHelper = new EmailHelper();
            $emailHelper->passwordResetFormEmail($email, $token, $full_name);

            $success = 'A password reset link has been sent to your email address.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Forgot Password - Library System</title>
  <link rel="stylesheet" href="assets/css/custom.css">
  <style>
    body {
      position: relative;
      margin: 0;
      padding: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    main {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100%;
    }
    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-image: url('image/WMSU1.jpg');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      filter: blur(10px);
      z-index: -1;
    }
    .forgot-form {
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.25);
      border-radius: 25px;
      padding: 40px;
      box-shadow:
        0 8px 32px rgba(0, 0, 0, 0.15),
        0 4px 16px rgba(255, 255, 255, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
      width: 100%;
      max-width: 400px;
      position: relative;
      overflow: hidden;
      animation: enhanced-glow 3s ease-in-out infinite alternate;
    }
    .forgot-form::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg,
        rgba(255, 255, 255, 0.15) 0%,
        rgba(255, 255, 255, 0.08) 25%,
        rgba(255, 255, 255, 0.05) 50%,
        rgba(255, 255, 255, 0.1) 75%,
        rgba(255, 255, 255, 0.12) 100%);
      border-radius: 25px;
      z-index: -1;
    }
    .forgot-form::after {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle,
        rgba(255, 255, 255, 0.1) 0%,
        transparent 50%,
        rgba(255, 255, 255, 0.05) 100%);
      opacity: 0.3;
      animation: texture-shift 8s ease-in-out infinite;
      z-index: -1;
    }
    @keyframes enhanced-glow {
      0% {
        box-shadow:
          0 8px 32px rgba(0, 0, 0, 0.15),
          0 4px 16px rgba(255, 255, 255, 0.1),
          inset 0 1px 0 rgba(255, 255, 255, 0.2);
      }
      50% {
        box-shadow:
          0 12px 48px rgba(0, 0, 0, 0.2),
          0 6px 24px rgba(255, 255, 255, 0.15),
          inset 0 1px 0 rgba(255, 255, 255, 0.3);
      }
      100% {
        box-shadow:
          0 16px 64px rgba(0, 0, 0, 0.25),
          0 8px 32px rgba(255, 255, 255, 0.2),
          inset 0 1px 0 rgba(255, 255, 255, 0.4);
      }
    }
    @keyframes texture-shift {
      0%, 100% {
        transform: translate(0, 0) rotate(0deg);
      }
      25% {
        transform: translate(10px, -10px) rotate(90deg);
      }
      50% {
        transform: translate(-5px, 5px) rotate(180deg);
      }
      75% {
        transform: translate(-10px, 10px) rotate(270deg);
      }
    }
    .form-header {
      text-align: center;
      margin-bottom: 30px;
      position: relative;
    }
    .back-arrow {
      position: absolute;
      left: 0;
      top: 0;
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      font-size: 24px;
      transition: color 0.3s ease;
    }
    .back-arrow:hover {
      color: white;
    }
    .logo {
      width: 60px;
      height: 60px;
      margin-bottom: 10px;
      border-radius: 50%;
      object-fit: cover;
    }
    .forgot-form h2 {
      color: #4169e1;
      font-size: 2rem;
      margin: 0;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }
    label {
      display: block;
      color: white;
      font-weight: 500;
      margin-bottom: 8px;
      font-size: 1rem;
    }
    input {
      width: 100%;
      padding: 15px;
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 10px;
      background: rgba(255, 255, 255, 0.1);
      color: white;
      font-size: 1rem;
      transition: all 0.3s ease;
      box-sizing: border-box;
    }
    input::placeholder {
      color: rgba(255, 255, 255, 0.7);
    }
    input:focus {
      border-color: rgba(255, 0, 0, 0.8);
      background: rgba(255, 255, 255, 0.2);
      outline: none;
      box-shadow: 0 0 10px rgba(255, 0, 0, 0.3);
      transition: all 0.3s ease;
    }
    input:hover {
      border-color: rgba(255, 0, 0, 0.6);
      transition: all 0.3s ease;
    }
    button {
      width: 100%;
      padding: 15px;
      border: none;
      border-radius: 10px;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
      color: white;
      font-size: 1.1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 20px;
      backdrop-filter: blur(5px);
    }
    button:hover {
      background: linear-gradient(135deg, rgba(255, 0, 0, 0.3), rgba(255, 0, 0, 0.2));
      transform: translateY(-2px);
      box-shadow: 0 4px 20px rgba(255, 0, 0, 0.2);
      transition: all 0.3s ease;
    }
    .error {
      color: #ff6b6b;
      font-size: 0.9rem;
      margin-bottom: 20px;
      padding: 10px;
      background: rgba(255, 0, 0, 0.1);
      border-radius: 8px;
      border-left: 3px solid #ff6b6b;
    }
    .success {
      color: #4CAF50;
      font-size: 0.9rem;
      margin-bottom: 20px;
      padding: 10px;
      background: rgba(76, 175, 80, 0.1);
      border-radius: 8px;
      border-left: 3px solid #4CAF50;
    }
    @media (max-width: 480px) {
      .forgot-form {
        padding: 30px 20px;
        max-width: 90%;
      }
      .forgot-form h2 {
        font-size: 1.5rem;
      }
    }
  </style>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

  <!-- Main content -->
  <main>
    <form method="post" action="forgot_password.php" class="forgot-form">
      <div class="form-header">
        <a href="index.php" class="back-arrow" title="Back to Login">←</a>
        <h2>Forgot Password</h2>
      </div>

      <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
      <?php endif; ?>

      <label for="username">Username</label>
      <input id="username" name="username" type="text" required>

      <label for="email">Email Address</label>
      <input id="email" name="email" type="email" required>

      <input type="hidden" name="forgot_password" value="1">

      <button type="submit">Send Reset Link</button>
    </form>
  </main>

</body>
</html>