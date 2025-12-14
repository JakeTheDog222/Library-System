<?php
require 'helpers.php';
require_once 'classes/Book.php';

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) header('Location: admin/dashboard.php');
    else header('Location: student/dashboard.php');
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') $error = 'Enter credentials';

    if (!isset($error)) {
        // Check credentials against database
        $stmt = $pdo->prepare('SELECT id, username, email, password, CONCAT(first_name, " ", COALESCE(middle_name, ""), " ", last_name) AS full_name, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'full_name' => $user['full_name'],
                'student_id' => null, // Will be populated if needed from user profile
                'course' => null // Will be populated if needed from user profile
            ];
            if ($user['role'] === 'admin') header('Location: admin/dashboard.php');
            else header('Location: student/dashboard.php');
            exit;
        } else {
            $error = 'Invalid credentials';
        }
    }
}

// Get featured books for carousel and shelf
$book = new Book($pdo);
$featuredBooks = $book->all(); // Get all books, we'll limit in display
$genres = $pdo->query('SELECT DISTINCT genre FROM books ORDER BY genre')->fetchAll(PDO::FETCH_COLUMN);

// Get some stats for the landing page
$totalBooks = $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn();
$totalUsers = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "student"')->fetchColumn();
$activeBorrowings = $pdo->query('SELECT COUNT(*) FROM borrow_history WHERE status = "borrowed"')->fetchColumn();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Library System - Login</title>
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
    .login-form {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
      position: relative;
      overflow: hidden;
      animation: glow 2s ease-in-out infinite alternate;
    }
    .login-form::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
      border-radius: 20px;
      z-index: -1;
    }
    @keyframes glow {
      from {
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      }
      to {
        box-shadow: 0 8px 32px rgba(255, 255, 255, 0.1);
      }
    }
    .form-header {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .title{
      font-family: 'Cormorant Garamond', serif;
      text-align: start;
      color: white;
      font-size: 1.5rem;
      margin-right: 77%;
    }
    .logo {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      width: 60px;
      height: 60px;
      margin-bottom: 0px;
      border-radius: 50%;
      object-fit: cover;
    }
    .login-form h2 {
      color: white;
      font-size: 2rem;
      right: 100px;
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
      padding: 15px 40px 15px 15px;
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
    .password-container {
      position: relative;
    }
    .password-toggle {
      position: absolute;
      right: 15px;
      top: 1%;
      transform: translateY(-15%);
      background: none;
      border: none;
      cursor: pointer;
      padding: 0;
      width: 25px;
      height: 25px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: opacity 0.3s ease;
    }
    .password-toggle:hover {
      opacity: 0.9;
    }
    .password-toggle img {
      width: 100%;
      height: 100%;
      object-fit: contain;
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
    .forgot-password {
      text-align: center;
      margin-top: 20px;
      position: relative;
    }
    .forgot-password::before {
      content: '';
      position: absolute;
      top: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80%;
      height: 1px;
      background: rgba(255, 255, 255, 0.3);
    }
    .forgot-password a {
      color: #4169e1;
      text-decoration: none;
      font-size: 0.9rem;
      transition: color 0.3s ease;
    }
    .forgot-password a:hover {
      color: #1e40af;
      text-decoration: underline;
    }
    .footer {
      padding: 10px 0;
      font-size: 0.8rem;
    }
    @media (max-width: 480px) {
      .login-form {
        padding: 30px 20px;
        max-width: 90%;
      }
      .login-form h2 {
        font-size: 1.5rem;
      }
    }
  </style>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const togglePassword = document.getElementById('togglePassword');
      const passwordInput = document.getElementById('password');
      const toggleIcon = document.getElementById('toggleIcon');

      togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        toggleIcon.src = type === 'password' ? 'image/visiblewhite.png' : 'image/invisiblewhite.png';
      });
    });
  </script>
</head>
<body>

  <!-- Header -->
  <header class="header">
    <img src="image\WMSU.png" alt="WMSU Logo" class="logo">
    <h1 class="title">Library Book Borrowing System</h1>
  </header>

  <!-- Main content -->
  <main>
    <form method="post" class="login-form">
      <div class="form-header">
        <img src="image\wmsulogo.png" alt="WMSU Logo" class="logo">
        <h2>LOGIN</h2>
      </div>

      <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <label for="email">Email</label>
      <input id="email" name="email" type="email" required>

      <label for="password">Password</label>
      <div class="password-container">
        <input id="password" name="password" type="password" required>
        <button type="button" id="togglePassword" class="password-toggle">
          <img id="toggleIcon" src="image/visiblewhite.png" alt="Toggle Password">
        </button>
      </div>

      <input type="hidden" name="login" value="1">

      <button type="submit">Login</button>
      <div class="forgot-password">
        <p><a href="forgot_password.php">Forgot Password?</a></p>
      </div>
    </form>
  </main>

  <!-- Footer -->
  <footer class="footer">
    © 2025 WMSU Library — All Rights Reserved
  </footer>

</body>
</html>
