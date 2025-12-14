<?php
require_once __DIR__ . '/../helpers.php';

if (!is_admin()) {
    header('Location: ../index.php');
    exit;
}

$students = $pdo->query("SELECT id, email, CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) AS full_name, role FROM users ORDER BY role, CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Accounts</title>
    <link rel="stylesheet" href="../assets/css/custom.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body>
   <header class="no-left-radius">
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
            <section class="dashboard-section">
                <h2>Students List <a href="create_student.php" style="font-size: 0.8em; margin-left: 10px; text-decoration: none; color: #800000; font-weight: bold;"><i class="fas fa-user-plus"></i> Create Student</a></h2>
                <?php if (empty($students)): ?>
                    <p>No students found.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Email</th>
                                    <th>Full Name</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($student['id']) ?></td>
                                        <td><?= htmlspecialchars($student['email']) ?></td>
                                        <td><?= htmlspecialchars($student['full_name']) ?></td>
                                        <td><?= htmlspecialchars($student['role']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <footer>
        © 2025 WMSU Library — All Rights Reserved
    </footer>
</body>
</html>
