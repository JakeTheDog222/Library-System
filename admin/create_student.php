<?php
require_once __DIR__ . '/../helpers.php';

if (!is_admin()) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($username === '' || $email === '' || $first_name === '' || $last_name === '' || $password === '' || $confirm_password === '') {
        $error = 'All fields are required except middle name.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if username or email exists
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Username or email already exists.';
        } else {
            // Insert new user with email
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, email, first_name, middle_name, last_name, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $result = $stmt->execute([$username, $email, $first_name, $middle_name, $last_name, $hashed_password, 'student']);
            if ($result) {
                // Generate email verification token and send verification email
                $userId = $pdo->lastInsertId();
                $token = bin2hex(random_bytes(32));

                $stmtToken = $pdo->prepare('INSERT INTO email_verification_tokens (user_id, token) VALUES (?, ?)');
                $stmtToken->execute([$userId, $token]);

                require_once __DIR__ . '/../classes/EmailHelper.php';
                $emailHelper = new EmailHelper();

                $verificationLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .
                    "://{$_SERVER['HTTP_HOST']}/verify_email.php?token={$token}";

                $full_name = trim($first_name . ' ' . ($middle_name ? $middle_name . ' ' : '') . $last_name);
                $emailHelper->verificationEmail($email, $verificationLink, $full_name);

                $success = 'Student account created successfully. A verification email has been sent.';
            } else {
                $error = 'Failed to create student account.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Create Student Account</title>
    <link rel="stylesheet" href="../assets/css/custom.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .password-container {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 7%;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .password-toggle img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
    </style>
</head>
<body>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            toggleIcon.src = type === 'password' ? '../image/visibleblack.png' : '../image/invisibleblack.png';
        });

        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const toggleConfirmIcon = document.getElementById('toggleConfirmIcon');

        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            toggleConfirmIcon.src = type === 'password' ? '../image/visibleblack.png' : '../image/invisibleblack.png';
        });
    });
</script>
<header class="no-left-radius animate-fade-in">
        <h1><img src="../image/WMSU.png" alt="WMSU Logo" style="border-radius: 50%;">Library Book Borrowing System</h1>
        <nav>
            <ul>
                <li>Welcome, Admin!</li>
                <li><a href="../logout.php" class="logout">Logout</a></li>
            </ul>
        </nav>
    </header>

<main>
    <aside class="sidebar">
            <h2>Admin Menu</h2>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                <li><a href="books.php"><i class="fas fa-book"></i>Manage Books</a></li>
                <li><a href="borrow_history.php"><i class="fas fa-history"></i>Borrow History</a></li>
                <li><a href="pending_requests.php"><i class="fas fa-clock"></i>Pending Requests</a></li>
                <li><a href="fines.php"><i class="fas fa-money-bill-wave"></i>Fines Management</a></li>
                <li><a href="genres.php"><i class="fas fa-tags"></i>Genres</a></li>
                <li><a href="students.php"><i class="fas fa-users"></i>List of Account</a></li>
            </ul>
        </aside>

    <div class="content">
        <section class="dashboard-section animate-fade-in-up animate-delay-1">
            <h2>Create Student Account</h2>
            <?php if ($error): ?>
                <p style="color:red;"><?=htmlspecialchars($error)?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p style="color:green;"><?=htmlspecialchars($success)?></p>
            <?php endif; ?>
            <form method="post">
                <label for="username">Username:</label><br />
                <input type="text" id="username" name="username" required /><br /><br />

                <label for="email">Email Address:</label><br />
                <input type="email" id="email" name="email" required /><br /><br />

                <label for="first_name">First Name:</label><br />
                <input type="text" id="first_name" name="first_name" required /><br /><br />

                <label for="middle_name">Middle Name (optional):</label><br />
                <input type="text" id="middle_name" name="middle_name" /><br /><br />

                <label for="last_name">Last Name:</label><br />
                <input type="text" id="last_name" name="last_name" required /><br /><br />

                <label for="password">Password:</label><br />
                <div class="password-container">
                    <input type="password" id="password" name="password" required />
                    <button type="button" id="togglePassword" class="password-toggle">
                        <img id="toggleIcon" src="../image/visibleblack.png" alt="Toggle visibility" />
                    </button>
                </div><br /><br />

                <label for="confirm_password">Confirm Password:</label><br />
                <div class="password-container">
                    <input type="password" id="confirm_password" name="confirm_password" required />
                    <button type="button" id="toggleConfirmPassword" class="password-toggle">
                        <img id="toggleConfirmIcon" src="../image/visibleblack.png" alt="Toggle visibility" />
                    </button>
                </div><br /><br />

                <button type="submit">Create Account</button>
            </form>
        </section>
    </div>
</main>

<footer>
    © 2025 WMSU Library — All Rights Reserved
</footer>
</body>
</html>
