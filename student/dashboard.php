    <?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../classes/Borrow.php';
require_once __DIR__ . '/../classes/Book.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Notification.php';
require_once __DIR__ . '/../classes/Fine.php';
require_once __DIR__ . '/../classes/Reservation.php';
if (!is_student()) { header('Location: index.php'); exit; }

$uid = $_SESSION['user']['id'];

// Handle AJAX request for statistics details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_stat'])) {
    header('Content-Type: application/json');
    try {
        global $pdo;
        $stat = trim($_POST['stat'] ?? '');
        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['error' => 'Session not found']);
            exit;
        }
        $uid = $_SESSION['user']['id'];

        // Handle different statistics requests
        switch ($stat) {
            case 'distinct_books':
                $stmt = $pdo->prepare('SELECT b.title, b.author, COUNT(*) as borrow_count FROM borrow_history bh JOIN books b ON bh.book_id = b.id WHERE bh.user_id = ? GROUP BY bh.book_id ORDER BY borrow_count DESC');
                $stmt->execute([$uid]);
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($result);
                break;

            case 'total_borrows':
                $stmt = $pdo->prepare('SELECT b.title, bh.borrow_date, bh.return_date, bh.status FROM borrow_history bh JOIN books b ON bh.book_id = b.id WHERE bh.user_id = ? ORDER BY bh.id DESC');
                $stmt->execute([$uid]);
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($result);
                break;

            case 'current_borrowed':
                $stmt = $pdo->prepare('SELECT b.title, bh.borrow_date, bh.due_date FROM borrow_history bh JOIN books b ON bh.book_id = b.id WHERE bh.user_id = ? AND bh.status IN (?, ?) ORDER BY bh.id DESC');
                $stmt->execute([$uid, 'pending', 'borrowed']);
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($result);
                break;

            case 'overdue_borrows':
                $stmt = $pdo->prepare('SELECT b.title, bh.due_date, DATEDIFF(CURDATE(), bh.due_date) as days_overdue FROM borrow_history bh JOIN books b ON bh.book_id = b.id WHERE bh.user_id = ? AND bh.status = ? AND bh.due_date < CURDATE() ORDER BY bh.due_date ASC');
                $stmt->execute([$uid, 'overdue']);
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($result);
                break;

            case 'most_borrowed_book':
                $stmt = $pdo->prepare('SELECT b.title, b.author, b.genre, COUNT(*) as borrow_count FROM borrow_history bh JOIN books b ON bh.book_id = b.id WHERE bh.user_id = ? GROUP BY bh.book_id ORDER BY borrow_count DESC LIMIT 1');
                $stmt->execute([$uid]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($result);
                break;

            case 'favorite_genre':
                $stmt = $pdo->prepare('SELECT b.genre, COUNT(*) as borrow_count FROM borrow_history bh JOIN books b ON bh.book_id = b.id WHERE bh.user_id = ? GROUP BY b.genre ORDER BY borrow_count DESC LIMIT 1');
                $stmt->execute([$uid]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result) {
                    // Get books in this genre
                    $booksStmt = $pdo->prepare('SELECT b.title, b.author, COUNT(*) as borrow_count FROM borrow_history bh JOIN books b ON bh.book_id = b.id WHERE bh.user_id = ? AND b.genre = ? GROUP BY bh.book_id ORDER BY borrow_count DESC');
                    $booksStmt->execute([$uid, $result['genre']]);
                    $result['books'] = $booksStmt->fetchAll(PDO::FETCH_ASSOC);
                }

                echo json_encode($result);
                break;

            default:
                echo json_encode(['error' => 'Invalid statistics type']);
                break;
        }
        } catch (Exception $e) {
            // Return JSON error response instead of HTML error page
            error_log("Statistics error: " . $e->getMessage());
            echo json_encode(['error' => 'Database error occurred while loading statistics: ' . $e->getMessage()]);
        }
    exit;
}

// mark overdues
(new Borrow($pdo))->checkAndMarkOverdue();
$uid = $_SESSION['user']['id'];
$my = $pdo->prepare('SELECT bh.*, b.title FROM borrow_history bh JOIN books b ON b.id=bh.book_id WHERE bh.user_id=? AND bh.status IN (?,?,?,?) ORDER BY bh.id DESC');
$my->execute([$uid, 'pending', 'borrowed', 'overdue', 'rejected']); $records = $my->fetchAll();
$books = (new Book($pdo))->all();
$stmt = $pdo->prepare('SELECT b.genre, COUNT(*) as count FROM borrow_history bh JOIN books b ON b.id = bh.book_id WHERE bh.user_id = ? GROUP BY b.genre ORDER BY count DESC');
$stmt->execute([$uid]);
$borrowedGenres = $stmt->fetchAll(PDO::FETCH_ASSOC);
$bookTitles = $pdo->query('SELECT title FROM books ORDER BY title')->fetchAll(PDO::FETCH_COLUMN);
$genres = $pdo->query('SELECT DISTINCT genre FROM books ORDER BY genre')->fetchAll(PDO::FETCH_COLUMN);
$blocked = (new User($pdo))->isBlocked($uid);

// Get notifications
$notification = new Notification($pdo);
$notifications = $notification->getUnread($uid);

// Get fines
$fine = new Fine($pdo);
$totalFines = $fine->getTotalPendingFines($uid);
$userFines = $fine->getUserFines($uid);
// Show both pending and paid fines for history

// Get reservations
$reservation = new Reservation($pdo);
$reservations = $reservation->getUserReservations($uid);

// Get borrow history records (needed for statistics calculation)
$historyStmt = $pdo->prepare('SELECT bh.*, b.title, b.author, b.genre, b.publication_date FROM borrow_history bh JOIN books b ON b.id = bh.book_id WHERE bh.user_id = ? ORDER BY bh.id DESC');
$historyStmt->execute([$uid]);
$historyRecords = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics (moved here after historyRecords is fetched)
$distinctBooksCount = count(array_unique(array_column($historyRecords, 'book_id')));
$totalBorrowsCount = count($historyRecords);
$currentBorrowedCount = count(array_filter($records, function($r) { return in_array($r['status'], ['pending', 'borrowed']); }));
$overdueCount = count(array_filter($records, function($r) { return $r['status'] === 'overdue'; }));

// Get most borrowed book
$mostBorrowedBook = $pdo->prepare('SELECT b.title, COUNT(*) as borrow_count FROM borrow_history bh JOIN books b ON bh.book_id = b.id WHERE bh.user_id = ? GROUP BY bh.book_id ORDER BY borrow_count DESC LIMIT 1');
$mostBorrowedBook->execute([$uid]);
$mostBorrowed = $mostBorrowedBook->fetch();

// Get most borrowed genre
$mostBorrowedGenre = $pdo->prepare('SELECT b.genre, COUNT(*) as genre_count FROM borrow_history bh JOIN books b ON bh.book_id = b.id WHERE bh.user_id = ? GROUP BY b.genre ORDER BY genre_count DESC LIMIT 1');
$mostBorrowedGenre->execute([$uid]);
$mostGenre = $mostBorrowedGenre->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="../assets/css/carousel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Entrance Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .animate-slide-in-left {
            animation: slideInLeft 0.8s ease-out forwards;
        }

        /* Staggered animation delays */
        .animate-delay-1 { animation-delay: 0.1s; }
        .animate-delay-2 { animation-delay: 0.2s; }
        .animate-delay-3 { animation-delay: 0.3s; }
        .animate-delay-4 { animation-delay: 0.4s; }
        .animate-delay-5 { animation-delay: 0.5s; }
        .animate-delay-6 { animation-delay: 0.6s; }

        /* Override to always show borrow buttons on bookshelf */
        #book-shelf .book-actions .borrow-btn {
            opacity: 1 !important;
            transform: none !important;
            pointer-events: auto !important;
        }

        /* Smooth Navigation Animations with Smoke Effect */
        header nav {
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
        }

        header nav .nav-links,
        header nav .nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        header nav .nav-links li,
        header nav .nav-actions li {
            position: relative;
        }

        .burger-menu {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .burger-menu:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                padding: 10px 15px;
                box-sizing: border-box;
                background-color: red !important;
            }

            header > div {
                flex-shrink: 0;
            }

            header nav {
                display: flex;
                align-items: center;
                gap: 10px;
                flex: 1;
                justify-content: flex-end;
                min-width: 0;
            }

            .burger-menu {
                display: block;
                font-size: 0.6rem !important;
                padding: 2px !important;
                flex-shrink: 0;
                margin-left: 10px;
            }

            header nav .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: rgba(0, 0, 0, 0.9);
                flex-direction: column;
                padding: 20px;
                border-radius: 0 0 10px 10px;
                z-index: 1000;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            }

            header nav .nav-links.nav-open {
                display: flex;
            }

            header nav .nav-links li {
                width: 100%;
                text-align: center;
            }

            header nav .nav-links li a {
                display: block;
                padding: 15px 20px;
                width: 100%;
                box-sizing: border-box;
            }

            header nav .nav-actions {
                display: flex;
                align-items: center;
                gap: 1px;
                flex-wrap: wrap;
                justify-content: flex-end;
                min-width: 0;
            }

            header nav .nav-actions li {
                flex-shrink: 0;
            }

            /* Adjust logo and title size on mobile */
            header > div img {
                width: 40px !important;
                height: 40px !important;
            }

            header > div h1 {
                font-size: 1.2rem !important;
            }

            /* Adjust notification bell, logout, and welcome text size on mobile */
            .notification-bell i {
                font-size: 1rem !important;
            }

            .logout {
                font-size: 0.8rem !important;
                padding: 6px 10px !important;
            }

            .welcome-text {
                font-size: 0.6rem !important;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 80px;
            }
        }

        /* Enlarge search bars, filters, buttons, and tables */
        .search-bar input,
        .search-bar select,
        .search-bar button,
        .btn {
            font-size: 1rem !important;
            padding: 12px 16px !important;
            height: auto !important;
            min-height: 40px !important;
        }

        .search-bar input {
            max-width: 250px !important;
            width: 250px !important;
        }

        .search-bar select {
            max-width: 150px !important;
            width: 150px !important;
        }

        .search-bar button,
        .btn {
            font-size: 1rem !important;
            padding: 12px 20px !important;
            min-width: auto !important;
            width: auto !important;
            max-width: 300px !important;
            white-space: nowrap !important;
        }

        /* Enlarge table sizes */
        table {
            font-size: 1rem !important;
        }

        table th,
        table td {
            padding: 12px 16px !important;
            font-size: 1rem !important;
        }

        /* Enlarge advanced search elements */
        #borrowedAdvancedSearch input,
        #borrowedAdvancedSearch button,
        #historyAdvancedSearch input,
        #historyAdvancedSearch button,
        #advancedSearch input,
        #advancedSearch button {
            font-size: 1rem !important;
            padding: 12px 16px !important;
            height: auto !important;
            min-height: 40px !important;
        }

        #borrowedAdvancedSearch input,
        #historyAdvancedSearch input,
        #advancedSearch input {
            width: 150px !important;
        }

        /* Enlarge modal content */
        .modal-content {
            font-size: 1.1rem !important;
        }

        .modal-content input,
        .modal-content button {
            font-size: 1rem !important;
            padding: 12px 16px !important;
        }

        /* Fix featured books text overflow */
        .book-title {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }

        .book-author {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }

        /* Enlarge notification dropdown */
        .notification-item {
            font-size: 1rem !important;
            padding: 12px 16px !important;
        }

        /* Enlarge fine modal */
        #fineModal div[style*="background: white"] {
            font-size: 1.1rem !important;
        }

        #fineModal input,
        #fineModal button {
            font-size: 1rem !important;
            padding: 12px 16px !important;
        }

        header nav .nav-links li a,
        header nav .nav-actions li a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        /* Smoke effect using multiple pseudo-elements */
        header nav .nav-links li a::before,
        header nav .nav-links li a::after,
        header nav .nav-actions li a::before,
        header nav .nav-actions li a::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            opacity: 0;
            transform: scale(0.8);
            transition: all 0.6s ease;
        }

        header nav .nav-links li a::after,
        header nav .nav-actions li a::after {
            background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, transparent 80%);
            animation: smoke-rise 2s ease-in-out infinite;
        }

        header nav .nav-links li a:hover::before,
        header nav .nav-actions li a:hover::before {
            opacity: 1;
            transform: scale(1.2);
            animation: smoke-puff 0.8s ease-out;
        }

        header nav .nav-links li a:hover::after,
        header nav .nav-actions li a:hover::after {
            opacity: 0.8;
            animation: smoke-drift 3s ease-in-out infinite;
        }

        header nav .nav-links li a:hover,
        header nav .nav-actions li a:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        header nav .nav-links li a:active,
        header nav .nav-actions li a:active {
            transform: translateY(0);
            transition: transform 0.1s ease;
        }

        /* Smoke animation keyframes */
        @keyframes smoke-puff {
            0% {
                opacity: 0;
                transform: scale(0.5) translateY(10px);
            }
            50% {
                opacity: 0.8;
                transform: scale(1.3) translateY(-5px);
            }
            100% {
                opacity: 0;
                transform: scale(1.5) translateY(-15px);
            }
        }

        @keyframes smoke-rise {
            0% {
                opacity: 0;
                transform: translateY(20px) scale(0.8);
            }
            50% {
                opacity: 0.6;
                transform: translateY(-10px) scale(1.2);
            }
            100% {
                opacity: 0;
                transform: translateY(-30px) scale(1.4);
            }
        }

        @keyframes smoke-drift {
            0% {
                opacity: 0.3;
                transform: translateX(-10px) translateY(5px) scale(0.9);
            }
            33% {
                opacity: 0.6;
                transform: translateX(5px) translateY(-8px) scale(1.1);
            }
            66% {
                opacity: 0.4;
                transform: translateX(10px) translateY(-15px) scale(1.2);
            }
            100% {
                opacity: 0.2;
                transform: translateX(-5px) translateY(-25px) scale(1.3);
            }
        }

        /* Special animation for logout button */
        .logout {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white !important;
            border: 2px solid transparent;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .logout:hover {
            background: linear-gradient(135deg, #c82333, #a02622);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
        }

        .logout:active {
            transform: translateY(0) scale(1);
            transition: transform 0.1s ease;
        }

        /* Notification bell animation */
        .notification-bell {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .notification-bell:hover {
            transform: scale(1.1);
        }

        .notification-bell i {
            transition: all 0.3s ease;
        }

        .notification-bell:hover i {
            color: #ffd700 !important;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
        }

        /* Smooth scroll behavior for navigation links */
        html {
            scroll-behavior: smooth;
        }

        /* Active navigation state */
        header nav ul li a[href^="#"]:active {
            animation: pulse 0.3s ease;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body class="student-page">
    <header class="no-left-radius animate-fade-in">
        <div style="display: flex; align-items: center; gap: 15px;">
            <img src="../image/WMSU.png" alt="WMSU Logo" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 3px solid white; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
            <h1 style="margin: 0; font-size: 1.8rem; font-weight: 600; color: white;">WMSU Library</h1>
        </div>
        <nav>
            <ul class="nav-links">
                <li><a href="#overview">Overview</a></li>
                <li><a href="#book-shelf">Featured Books</a></li>
                <li><a href="#borrowed">Borrowed Books</a></li>
                <li><a href="#borrow-history">Borrow History</a></li>
                <li><a href="#available">Available Books</a></li>
                <li><a href="#genres">Statistics</a></li>
            </ul>
            <ul class="nav-actions">
                <li class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['user']['full_name']) ?>!</li>
                <li>
                    <div class="notification-bell" style="position: relative; cursor: pointer;">
                        <i class="fas fa-bell fa-lg"></i>
                        <?php if (count($notifications) > 0): ?>
                            <span class="notification-count" style="
                                position: absolute;
                                top: -5px;
                                right: -5px;
                                background: red;
                                color: white;
                                font-size: 0.75rem;
                                font-weight: bold;
                                border-radius: 50%;
                                padding: 0 6px;
                                min-width: 20px;
                                height: 20px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                box-shadow: 0 0 5px rgba(0,0,0,0.3);
                                ">
                                <?= count($notifications) ?>
                            </span>
                        <?php endif; ?>
                        <div class="notification-dropdown" style="
                            display: none;
                            position: absolute;
                            top: 30px;
                            right: 0;
                            width: 500px;
                            max-height: 600px;
                            overflow-y: auto;
                            background: white;
                            border-radius: 10px;
                            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
                            z-index: 1500;
                            ">
                            <?php if(!empty($notifications)): ?>
                                <div style="padding: 10px; text-align: right; border-bottom: 1px solid #ddd;">
                                    <button onclick="markAllAsRead()" style="background: #800000; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">Mark All as Read</button>
                                </div>
                                <?php foreach($notifications as $n): ?>
                                    <div class="notification-item" id="notification-<?= $n['id'] ?>" style="margin: 5px 10px; padding: 10px; border-bottom: 1px solid #ddd; word-wrap: break-word; white-space: normal;">
                                        <div style="font-weight: bold; color: #800000; margin-bottom: 5px;"><?= ucfirst($n['type']) ?> - <?= htmlspecialchars($notification->getDescription($n['type'])) ?></div>
                                        <div style="margin-bottom: 5px;"><?= htmlspecialchars($n['message']) ?: 'No message content' ?></div>
                                        <div style="font-size: 0.8rem; color: #666; margin-bottom: 10px;">
                                            <span>Status: <strong style="color: #dc3545;">Unread</strong></span> |
                                            <span>Time: <?= date('M d, Y H:i', strtotime($n['created_at'])) ?></span>
                                        </div>
                                        <button onclick="markAsRead(<?= $n['id'] ?>)" style="
                                            color: #1e88e5;
                                            background: none;
                                            border: none;
                                            cursor: pointer;
                                            text-decoration: underline;
                                            font-size: 0.9rem;
                                            padding: 0;
                                        ">Mark as Read</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="padding: 10px; text-align: center; color: #666;">No new notifications</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
                <li><a href="../logout.php" class="logout">Logout</a></li>
            </ul>
            <button class="burger-menu" onclick="toggleMenu()">&#9776;</button>
        </nav>
    <style>
        /* Notification dropdown styling */
        .notification-bell:hover .notification-dropdown,
        .notification-bell.active .notification-dropdown {
            display: block !important;
        }

        .notification-bell i {
            color: white;
            transition: color 0.3s ease;
        }

        .notification-bell:hover i,
        .notification-bell.active i {
            color: var(--accent-gold);
        }

        .notification-count {
            font-family: Arial, sans-serif;
        }
    </style>
    <script>
        function toggleMenu() {
            const navLinks = document.querySelector('.nav-links');
            navLinks.classList.toggle('nav-open');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const bell = document.querySelector('.notification-bell');
            const dropdown = document.querySelector('.notification-dropdown');

            function updateNotificationCount(count) {
                const countSpan = document.querySelector('.notification-count');
                if (countSpan) {
                    if (count > 0) {
                        countSpan.textContent = count;
                        countSpan.style.display = 'flex';
                    } else {
                        countSpan.style.display = 'none';
                    }
                }
            }

            bell.addEventListener('click', (e) => {
                e.stopPropagation();
                bell.classList.toggle('active');
            });

            document.addEventListener('click', (e) => {
                if (!bell.contains(e.target)) {
                    bell.classList.remove('active');
                }
            });

            // Override markAsRead to update UI after marking read
            window.markAsRead = function(notificationId) {
                fetch('mark_read.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'notification_id=' + notificationId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.ok) {
                        const notificationDiv = document.getElementById('notification-' + notificationId);
                        if (notificationDiv) {
                            notificationDiv.remove();
                        }
                        // Update the notification count in badge
                        const notificationItems = document.querySelectorAll('.notification-item');
                        const newCount = notificationItems.length;
                        updateNotificationCount(newCount);

                        // If no notifications left, show "No new notifications" and hide the button
                        if (newCount === 0) {
                            const dropdown = document.querySelector('.notification-dropdown');
                            const markAllButton = document.querySelector('button[onclick="markAllAsRead()"]');
                            if (markAllButton) {
                                markAllButton.style.display = 'none';
                            }
                            const noNotifDiv = document.createElement('div');
                            noNotifDiv.style.padding = '10px';
                            noNotifDiv.style.textAlign = 'center';
                            noNotifDiv.style.color = '#666';
                            noNotifDiv.textContent = 'No new notifications';
                            dropdown.appendChild(noNotifDiv);
                        }
                    }
                })
                .catch(() => {});
            }

            window.markAllAsRead = function() {
                fetch('mark_all_read.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: ''
                })
                .then(response => response.json())
                .then(data => {
                    if (data.ok) {
                        // Hide all notification items
                        const notificationItems = document.querySelectorAll('.notification-item');
                        notificationItems.forEach(item => item.remove());

                        // Hide the "Mark All as Read" button
                        const markAllButton = document.querySelector('button[onclick="markAllAsRead()"]');
                        if (markAllButton) {
                            markAllButton.style.display = 'none';
                        }

                        // Update count to 0
                        updateNotificationCount(0);

                        // Show "No new notifications"
                        const dropdown = document.querySelector('.notification-dropdown');
                        const noNotifDiv = document.createElement('div');
                        noNotifDiv.style.padding = '10px';
                        noNotifDiv.style.textAlign = 'center';
                        noNotifDiv.style.color = '#666';
                        noNotifDiv.textContent = 'No new notifications';
                        dropdown.appendChild(noNotifDiv);
                    }
                })
                .catch(() => {});
            }
        });
    </script>
    </header>

    <main>
        <div class="content">
        <!-- Image Carousel Section -->
        <section class="carousel-section animate-fade-in-up animate-delay-2">
            <div class="carousel-container">
                <div class="carousel-slides">
                    <div class="carousel-slide active" style="background-image: url('../image/library.jpg');">
                        <div class="carousel-overlay">
                            <div class="carousel-content">
                                <h1 class="carousel-title">Welcome to WMSU Library</h1>
                                <p class="carousel-description">Discover a world of knowledge and explore our vast collection of books, journals, and digital resources.</p>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-slide" style="background-image: url('../image/WMSU1.jpg');">
                        <div class="carousel-overlay">
                            <div class="carousel-content">
                                <h1 class="carousel-title">Your Learning Journey Starts Here</h1>
                                <p class="carousel-description">Access educational materials, borrow books, and enhance your academic experience with our modern library system.</p>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-slide" style="background-image: url('../image/library.jpg');">
                        <div class="carousel-overlay">
                            <div class="carousel-content">
                                <h1 class="carousel-title">Connect & Learn</h1>
                                <p class="carousel-description">Join our community of learners, participate in events, and make the most of your time at WMSU Library.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-dots">
                    <span class="carousel-dot active" onclick="goToSlide(0)"></span>
                    <span class="carousel-dot" onclick="goToSlide(1)"></span>
                    <span class="carousel-dot" onclick="goToSlide(2)"></span>
                </div>
            </div>
        </section>

            <?php if (isset($showReviewModal) && $showReviewModal): ?>
            <div id="reviewModal" class="modal" style="display: block;">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h2>Submit a Review for <?= htmlspecialchars($bookToReview['title']) ?></h2>
                    <form id="reviewForm">
                        <input type="hidden" name="book_id" value="<?= $bookToReview['id'] ?>">
<label for="ratingSlider">Rating: <span id="ratingValue">3</span></label></form></div></div><?php endif; ?>
</main>

        <section id="overview" class="zigzag-section animate-fade-in-up animate-delay-3" style="min-height: 600px;">
            <div class="text">
                <h2>Library</h2>
                <p>The purpose of this Library Book Borrowing System is to provide WMSU students with an efficient and user-friendly platform to borrow, return, and manage books.</p>
                <p>It promotes fair access to educational resources, ensures accountability through fines and penalties for overdue items, and supports the academic community by facilitating easy book reservations and notifications.</p>
                <?php if($blocked): ?>
                    <div style="color: red; font-weight: bold;">Your account is blocked from borrowing due to overdue items.</div>
                <?php endif; ?>
                <?php if($totalFines > 0): ?>
                    <div style="color: orange; font-weight: bold;">You have outstanding fines: ₱<?= number_format($totalFines, 2) ?>. Borrowing is blocked until all fines are paid.</div>
                <?php endif; ?>
                <?php /* Notification list removed from below overview as per request */ ?>
            </div>
            <div class="image">
                <img src="../image/desginui.jpeg" alt="Design UI" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
            </div>
        </section>
        
        <!-- Interactive Book Shelf Section -->
        <section id="book-shelf" class="zigzag-section animate-fade-in-up animate-delay-4" style="position: relative; background-image: url('../image/backgroundstudent5.jpg'); background-size: cover; background-position: center;">
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; backdrop-filter: blur(3px); z-index: 1;"></div>
            <div style="position: relative; z-index: 2; width: 100%;">
                <div class="text">
                    <h2>Featured Books</h2>
                    <p>Discover our most popular and recently added books. Browse through our curated collection and find your next great read.</p>
                </div>
                <div class="library-shelf">
                    <?php
                    // Fetch featured books (recently added or popular)
                    $featuredBooks = $pdo->query('SELECT * FROM books WHERE deleted = 0 ORDER BY id DESC LIMIT 12')->fetchAll(PDO::FETCH_ASSOC);
                    // Duplicate books for infinite scroll effect
                    $allBooks = array_merge($featuredBooks, $featuredBooks, $featuredBooks);
                    $totalBooks = count($allBooks);
                    ?>
                    <div class="book-shelf-container" style="overflow-x: auto; width: 100%;">
                        <div class="book-shelf-horizontal" id="bookShelfSlider" style="display: flex; width: 100%;">
                            <script>console.log('Total books in shelf: <?php echo $totalBooks; ?>');</script>
                        <?php foreach($allBooks as $index => $book): ?>
                            <?php
                            // Check if user has pending request or borrowed this book
                            $hasRequest = $pdo->prepare('SELECT id, status FROM borrow_history WHERE user_id=? AND book_id=? AND status IN (?,?)');
                            $hasRequest->execute([$uid, $book['id'], 'pending', 'borrowed']);
                            $existing = $hasRequest->fetch();

                            // Calculate max additional copies user can borrow
                            $existingCopiesStmt = $pdo->prepare('SELECT SUM(copies_borrowed) as total_copies FROM borrow_history WHERE user_id=? AND book_id=? AND status IN (?,?)');
                            $existingCopiesStmt->execute([$uid, $book['id'], 'pending', 'borrowed']);
                            $existingCopies = intval($existingCopiesStmt->fetchColumn());
                            $maxAdditionalCopies = min(3 - $existingCopies, $book['available_copies']);

                            // Get average rating
                            require_once __DIR__ . '/../classes/Review.php';
                            $review = new Review($pdo);
                            $avgRating = $review->getAverageRating($book['id']);
                            ?>
                            <div class="book-card">
                                <div class="book-cover">
                                    <div class="book-placeholder">
                                        <i class="fas fa-book book-icon"></i>
                                        <div class="book-title-overlay"><?php echo htmlspecialchars(substr($book['title'], 0, 30)) . (strlen($book['title']) > 30 ? '...' : ''); ?></div>
                                        <?php if ($avgRating['total_reviews'] > 0): ?>
                                            <div class="book-rating">★<?php echo number_format($avgRating['avg_rating'], 1); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="book-info">
                                    <h4 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h4>
                                    <p class="book-author">by <?php echo htmlspecialchars($book['author']); ?></p>
                                    <div class="book-details">
                                        <span class="available-copies"><?php echo $book['available_copies']; ?> available</span>
                                        <span class="pub-date"><?php echo ($book['publication_date'] ? date('M d, Y', strtotime($book['publication_date'])) : 'N/A'); ?></span>
                                    </div>
                                    <div class="book-actions">
                                        <?php if($blocked): ?>
                                            <div class="borrow-status">Borrowing blocked</div>
                                        <?php elseif($totalFines>0): ?>
                                            <div class="borrow-status">Fines outstanding</div>
                                        <?php elseif($existing && ($existing['status'] == 'pending' || $existing['status'] == 'borrowed') && $maxAdditionalCopies > 0): ?>
                                            <form method='post' action='borrow.php' style='display:inline;'>
                                                <input type='hidden' name='book_id' value='<?= $book['id'] ?>'>
                                                <input type='hidden' name='copies' value='1'>
                                                <button type='submit' class='borrow-btn borrow-add'>Add Another</button>
                                            </form>
                                        <?php elseif($book['available_copies'] > 0): ?>
                                            <form method='post' action='borrow.php' style='display:inline;'>
                                                <input type='hidden' name='book_id' value='<?= $book['id'] ?>'>
                                                <input type='hidden' name='copies' value='1'>
                                                <button type='submit' class='borrow-btn borrow-request'>Borrow Now</button>
                                            </form>
                                        <?php else: ?>
                                            <button onclick="reserveBook(<?= $book['id'] ?>)" class="borrow-btn borrow-reserve">Reserve Book</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="image">
                    <!-- No image in this section -->
                </div>
            </div>
        </section>


        <section id="borrowed" class="zigzag-section animate-fade-in-up animate-delay-5">
            <div class="text" style="width: 100%;">
                <h2>Your Borrowed Books</h2>
                <div class="search-bar" style="display: flex; gap: 10px; align-items: center; margin-bottom: 15px; flex-wrap: wrap;">
                    <input type="text" id="borrowedSearchInput" placeholder="Search by title..." style="padding: 6px; border: 1px solid #ccc; border-radius: 5px; flex: 1; max-width: 300px;">
                    <select id="borrowedStatusFilter" style="padding: 6px; border: 1px solid #ccc; border-radius: 5px; max-width: 200px;">
                        <option value="">All Status</option>
                        <option value="borrowed">Borrowed</option>
                        <option value="pending">Pending</option>
                        <option value="overdue">Overdue</option>
                        <option value="returned">Returned</option>
                    </select>
                    <button onclick="toggleBorrowedAdvancedSearch()" class="btn btn-secondary btn-sm" style="padding: 10px 2px; font-size: 12px; width: 300px;">Advanced Search</button>
                    <a href="../reports/my_borrowed_books_pdf.php" class="btn" target="_blank" style="padding: 10px 2px; font-size: 12px; width: 300px;">Export to PDF</a>
                </div>
                <div id="borrowedAdvancedSearch" style="display: none; background: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                        <label for="borrowedYearFilter" style="font-weight: bold;">Borrow Year:</label>
                        <input type="number" id="borrowedYearFilter" placeholder="e.g., 2020" style="padding: 6px; border: 1px solid #ccc; border-radius: 5px; width: 120px;">
                        <button onclick="borrowedAdvancedSearch()" class="btn" style="padding: 10px 2px; font-size: 12px; width: 300px;">Search</button>
                    </div>
                    <div style="margin-top: 10px;">
                        <strong>Recommendations:</strong>
                        <div id="borrowedRecommendations" style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px;"></div>
                    </div>
                </div>
                <div class="table-container" style="max-height: 400px; overflow-y: auto;">
                    <table id="borrowedBooksTable">
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Borrowed</th>
                                <th>Due</th>
                                <th>Copies</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($records)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: #666; font-style: italic;">No borrowed books yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($records as $r): ?>
                                    <tr>
                                        <td>
                                            <?=htmlspecialchars($r['title'])?>
                                            <?php
                                            require_once __DIR__ . '/../classes/Review.php';
                                            $review = new Review($pdo);
                                            $avgRating = $review->getAverageRating($r['book_id']);
                                            if ($avgRating['total_reviews'] > 0): ?>
                                                <br><small style="color: #666;">★<?=number_format($avgRating['avg_rating'], 1)?> (<?=$avgRating['total_reviews']?> reviews)</small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?=$r['request_date'] ?: ($r['borrow_date'] ?: '-')?></td>
                                        <td><?=$r['due_date'] ?: '-'?></td>
                                        <td><?=$r['copies_borrowed']?></td>
                                        <td><?=$r['status']?></td>
                                        <td>
                                            <?php if($r['status']=='borrowed'): ?>
                                                <a class='btn btn-primary' href='return.php?id=<?=$r['id']?>' onclick='return confirm("Are you sure you want to return the book?")'>Return</a>
                                            <?php elseif($r['status']=='pending'): ?>
                                                <a class='btn btn-primary' href='cancel_request.php?id=<?=$r['id']?>' onclick='return confirm("Cancel this request?")' style='background-color: #dc3545; outline: none;'>Cancel</a>
                                            <?php elseif($r['status']=='rejected'): ?>
                                                -
                                            <?php elseif($r['status']=='overdue'): ?>
                                                -
                                            <?php else: echo '-'; endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div id="borrowedNoResults" style="display: none; text-align: center; padding: 20px; color: #666; font-style: italic;">No books found matching your search.</div>
                </div>
                <?php /* Removed stats cards from here as per user request */ ?>
            </div>
        </section>

        <section id="borrow-history" class="zigzag-section animate-fade-in-up animate-delay-6">
            <div class="text" style="width: 100%;">
                <h2>Borrowed History</h2>
                <div class="search-bar" style="display: flex; gap: 10px; align-items: center; margin-bottom: 15px;">
                    <input type="text" id="historySearchInput" placeholder="Search by title or author..." style="padding: 6px; border: 1px solid #ccc; border-radius: 5px; flex: 1; max-width: 300px;">
                    <select id="historyStatusFilter" style="padding: 6px; border: 1px solid #ccc; border-radius: 5px; max-width: 200px;">
                        <option value="">All Status</option>
                        <option value="borrowed">Borrowed</option>
                        <option value="pending">Pending</option>
                        <option value="overdue">Overdue</option>
                        <option value="returned">Returned</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <button onclick="toggleHistoryAdvancedSearch()" class="btn btn-secondary btn-sm" style="padding: 10px 2px; font-size: 12px; width: 300px;">Advanced Search</button>
                    <a href="../reports/my_borrowing_history_pdf.php" class="btn" target="_blank" style="padding: 10px 2px; font-size: 12px; width: 200px;">Export to PDF</a>
                </div>
                <div id="historyAdvancedSearch" style="display: none; background: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                        <label for="historyYearFilter" style="font-weight: bold;">Borrow Year:</label>
                        <input type="number" id="historyYearFilter" placeholder="e.g., 2020" style="padding: 6px; border: 1px solid #ccc; border-radius: 5px; width: 120px;">
                        <button onclick="historyAdvancedSearch()" class="btn" style="padding: 10px 2px; font-size: 12px; width: 300px;">Search</button>
                    </div>
                    <div style="margin-top: 10px;">
                        <strong>Recommendations:</strong>
                        <div id="historyRecommendations" style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px;"></div>
                    </div>
                </div>
                <div class="table-container" style="max-height: 400px; overflow-y: auto;">
                    <table id="borrowHistoryTable">
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Borrow Date</th>
                                <th>Return Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($historyRecords)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #666; font-style: italic;">No borrowing history yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($historyRecords as $r): ?>
                                    <tr>
                                        <td>
                                            <?=htmlspecialchars($r['title'])?>
                                            <?php
                                            require_once __DIR__ . '/../classes/Review.php';
                                            $review = new Review($pdo);
                                            $avgRating = $review->getAverageRating($r['book_id']);
                                            if ($avgRating['total_reviews'] > 0): ?>
                                                <br><small style="color: #666;">★<?=number_format($avgRating['avg_rating'], 1)?> (<?=$avgRating['total_reviews']?> reviews)</small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?=$r['borrow_date'] ?: '-'?></td>
                                        <td><?=$r['return_date'] ?: '-'?></td>
                                        <td><?=$r['status']?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div id="historyNoResults" style="display: none; text-align: center; padding: 20px; color: #666; font-style: italic;">No books found matching your search.</div>
                </div>
            </div>
        </section>

        <section id="fines" class="zigzag-section" style="display: <?= empty($userFines) ? 'none' : 'block' ?>;">
            <div class="text">
                <h2>Fines History</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($userFines as $f): ?>
                                <tr>
                                    <td><?=htmlspecialchars($f['title'])?></td>
                                    <td>₱<?=number_format($f['amount'], 2)?></td>
                                    <td><?=$f['status']?></td>
                                    <td>
                                        <?php if($f['status'] == 'paid'): ?>
                                            Paid: <?=date('M d, Y', strtotime($f['paid_at']))?>
                                        <?php else: ?>
                                            Created: <?=date('M d, Y', strtotime($f['created_at']))?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($f['status'] == 'pending'): ?>
                                            <button onclick="payFine(<?=$f['id']?>)" class="btn btn-success btn-sm">Pay Fine</button>
                                        <?php else: ?>
                                            <span style="color: #28a745; font-weight: bold;">✓ Paid</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="reservations" class="zigzag-section" style="display: <?= empty($reservations) ? 'none' : 'block' ?>;">
            <div class="text">
                <h2>My Reservations</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Reserved Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($reservations as $r): ?>
                                <tr>
                                    <td><?=htmlspecialchars($r['title'])?></td>
                                    <td><?=$r['reserved_at']?></td>
                                    <td><button onclick="cancelReservation(<?=$r['id']?>)" class="btn btn-danger btn-sm">Cancel</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="available" class="zigzag-section animate-fade-in-up animate-delay-4" style="position: relative; overflow: hidden; background: transparent;">
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: url('../image/backgroundstudent3.png'); background-size: cover; background-position: center; z-index: -1;"></div>
            <div class="text" style="background: rgba(255, 255, 255, 0.8); padding: 20px; border-radius: 8px; margin: 20px;">
                <h2>Available Books</h2>
                <div class="search-bar" style="display: flex; gap: 10px; align-items: center; margin-bottom: 15px;">
                    <input type="text" id="searchInput" placeholder="Search by title or author..." style="padding: 6px; border: 1px solid #ccc; border-radius: 5px; flex: 1; max-width: 300px;">
                    <select id="genreFilter" style="padding: 6px; border: 1px solid #ccc; border-radius: 5px; max-width: 200px;">
                        <option value="">All Genres</option>
                        <?php foreach($genres as $genre): ?>
                            <option value="<?=htmlspecialchars($genre)?>"><?=htmlspecialchars($genre)?></option>
                        <?php endforeach; ?>
                    </select>
                    <button onclick="toggleAdvancedSearch()" class="btn btn-secondary btn-sm" style="padding: 10px 2px; font-size: 12px; width: 300px;">Advanced Search</button>
                </div>
                <div id="advancedSearch" style="display: none; background: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                        <label for="yearFilter" style="font-weight: bold;">Publication Year:</label>
                        <input type="number" id="yearFilter" placeholder="e.g., 2020" style="padding: 6px; border: 1px solid #ccc; border-radius: 5px; width: 120px;">
                        <button onclick="advancedSearch()" class="btn" style="padding: 10px 2px; font-size: 12px; width: 300px;">Search</button>
                    </div>
                    <div style="margin-top: 10px;">
                        <strong>Recommendations:</strong>
                        <div id="recommendations" style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px;"></div>
                    </div>
                </div>
                <div class="table-container">
                    <table id="booksTable">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Genre</th>
                                <th>Publication Date</th>
                                <th>Available</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($books as $b): ?>
                                <?php
                                // Check if user has pending request or borrowed this book
                                $hasRequest = $pdo->prepare('SELECT id, status FROM borrow_history WHERE user_id=? AND book_id=? AND status IN (?,?)');
                                $hasRequest->execute([$uid, $b['id'], 'pending', 'borrowed']);
                                $existing = $hasRequest->fetch();

                                // Calculate max additional copies user can borrow
                                $existingCopiesStmt = $pdo->prepare('SELECT SUM(copies_borrowed) as total_copies FROM borrow_history WHERE user_id=? AND book_id=? AND status IN (?,?)');
                                $existingCopiesStmt->execute([$uid, $b['id'], 'pending', 'borrowed']);
                                $existingCopies = intval($existingCopiesStmt->fetchColumn());
                                $maxAdditionalCopies = min(3 - $existingCopies, $b['available_copies']);
                                ?>
                                <tr>
                                    <td>
                                        <?=htmlspecialchars($b['title'])?>
                                        <?php
                                        require_once __DIR__ . '/../classes/Review.php';
                                        $review = new Review($pdo);
                                        $avgRating = $review->getAverageRating($b['id']);
                                        if ($avgRating['total_reviews'] > 0): ?>
                                            <br><small style="color: #666;">★<?=number_format($avgRating['avg_rating'], 1)?> (<?=$avgRating['total_reviews']?> reviews)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?=htmlspecialchars($b['author'])?></td>
                                    <td><?=htmlspecialchars($b['genre'])?></td>
                                    <td><?=$b['publication_date'] ? date('M d, Y', strtotime($b['publication_date'])) : '-'?></td>
                                    <td><?=$b['available_copies']?></td>
                                    <td>
                                        <?php if(($existing && ($existing['status'] == 'pending' || $existing['status'] == 'borrowed') && $maxAdditionalCopies > 0)): ?>
                                            <form method='post' action='borrow.php' style='display:inline;'>
                                                <input type='hidden' name='book_id' value='<?=$b['id']?>'>
                                                <input type='number' name='copies' value='1' min='1' max='<?=$maxAdditionalCopies?>' style='width:70px;' required>
                                                <button type='submit' class='btn'>Add Another</button>
                                            </form>
                                        <?php elseif($b['available_copies']>0 && !$blocked && $totalFines == 0): ?>
                                            <form method='post' action='borrow.php' style='display:inline;'>
                                                <input type='hidden' name='book_id' value='<?=$b['id']?>'>
                                                <input type='number' name='copies' value='1' min='1' max='<?=$b['available_copies']?>' style='width:70px;' required>
                                                <button type='submit' class='btn'>Borrow Request</button>
                                            </form>
                                        <?php elseif($blocked): ?>
                                            <span>Borrowing blocked</span>
                                        <?php elseif($totalFines > 0): ?>
                                            <span>Fines outstanding</span>
                                        <?php else: ?>
                                            <span>Unavailable</span>
                                            <button onclick="reserveBook(<?=$b['id']?>)" class="btn btn-info btn-sm">Reserve</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div id="noResults" style="display: none; text-align: center; padding: 20px; color: #666; font-style: italic;">No books found matching your search.</div>
                </div>
            </div>
            <div class="image">
                <!-- No image in this section -->
            </div>
        </section>

        <section id="genres" class="zigzag-section">
            <div class="text">
                <h2>Statistics</h2>
                <p>View your borrowing statistics and insights.</p>
                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
                    <?php
                    // Calculate statistics
                    $distinctBooksCount = count(array_unique(array_column($historyRecords, 'book_id')));
                    $totalBorrowsCount = count($historyRecords);
                    $currentBorrowedCount = count(array_filter($records, function($r) { return in_array($r['status'], ['pending', 'borrowed']); }));
                    $overdueCount = count(array_filter($records, function($r) { return $r['status'] === 'overdue'; }));
                    ?>
                    <div class="stat-card" data-stat="distinct_books" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); cursor: pointer; transition: transform 0.2s;">
                        <div style="text-align: center;">
                            <i class="fas fa-book fa-2x" style="color: #800000; margin-bottom: 10px;"></i>
                            <h3 style="margin: 0; color: #800000;"><?php echo $distinctBooksCount; ?></h3>
                            <p style="margin: 5px 0; color: #666;">Distinct Books Borrowed</p>
                        </div>
                    </div>
                    <div class="stat-card" data-stat="total_borrows" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); cursor: pointer; transition: transform 0.2s;">
                        <div style="text-align: center;">
                            <i class="fas fa-history fa-2x" style="color: #800000; margin-bottom: 10px;"></i>
                            <h3 style="margin: 0; color: #800000;"><?php echo $totalBorrowsCount; ?></h3>
                            <p style="margin: 5px 0; color: #666;">Total Borrows</p>
                        </div>
                    </div>
                    <div class="stat-card" data-stat="current_borrowed" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); cursor: pointer; transition: transform 0.2s;">
                        <div style="text-align: center;">
                            <i class="fas fa-book-open fa-2x" style="color: #800000; margin-bottom: 10px;"></i>
                            <h3 style="margin: 0; color: #800000;"><?php echo $currentBorrowedCount; ?></h3>
                            <p style="margin: 5px 0; color: #666;">Current Borrowed Books</p>
                        </div>
                    </div>
                    <div class="stat-card" data-stat="overdue_borrows" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); cursor: pointer; transition: transform 0.2s;">
                        <div style="text-align: center;">
                            <i class="fas fa-exclamation-triangle fa-2x" style="color: #dc3545; margin-bottom: 10px;"></i>
                            <h3 style="margin: 0; color: #dc3545;"><?php echo $overdueCount; ?></h3>
                            <p style="margin: 5px 0; color: #666;">Overdue Books</p>
                        </div>
                    </div>
                    <div class="stat-card" data-stat="most_borrowed_book" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); cursor: pointer; transition: transform 0.2s;">
                        <div style="text-align: center;">
                            <i class="fas fa-trophy fa-2x" style="color: #ffc107; margin-bottom: 10px;"></i>
                            <h4 style="margin: 0; color: #800000; font-size: 1.1rem;"><?php echo $mostBorrowed ? htmlspecialchars(substr($mostBorrowed['title'], 0, 20)) . (strlen($mostBorrowed['title']) > 20 ? '...' : '') : 'None'; ?></h4>
                            <p style="margin: 5px 0; color: #666;">Most Borrowed Book</p>
                        </div>
                    </div>
                    <div class="stat-card" data-stat="favorite_genre" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); cursor: pointer; transition: transform 0.2s;">
                        <div style="text-align: center;">
                            <i class="fas fa-tags fa-2x" style="color: #28a745; margin-bottom: 10px;"></i>
                            <h4 style="margin: 0; color: #800000; font-size: 1.1rem;"><?php echo $mostGenre ? htmlspecialchars($mostGenre['genre']) : 'None'; ?></h4>
                            <p style="margin: 5px 0; color: #666;">Favorite Genre</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="image">
                <!-- No image in this section -->
            </div>
        </section>

        </div>

    <!-- Modal for displaying statistics details -->
    <div id="statsModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Statistics Details</h2>
            <div id="statsDetails"></div>
        </div>
    </div>

    </main>





    <!-- Carousel JavaScript -->
    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.carousel-dot');
        let slideInterval;

        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));

            currentSlide = (index + slides.length) % slides.length;
            slides[currentSlide].classList.add('active');
            dots[currentSlide].classList.add('active');
        }

        function changeSlide(direction) {
            showSlide(currentSlide + direction);
        }

        function goToSlide(index) {
            showSlide(index);
        }

        function startAutoSlide() {
            slideInterval = setInterval(() => {
                changeSlide(1);
            }, 3000);
        }

        function stopAutoSlide() {
            clearInterval(slideInterval);
        }

        // Infinite scrolling book shelf variables
        let bookShelfInterval;
        let scrollPosition = 0;
        let bookShelf, bookCards, cardWidth, totalCards, originalCards;
        let animationSpeed = 400; // pixels per frame - faster for visibility
        let isUserScrolling = false;
        let scrollTimeout;

        // Infinite scrolling book shelf functions
        function initBookShelf() {
            console.log('Initializing bookshelf');
            bookShelf = document.getElementById('bookShelfSlider');
            console.log('bookShelf element:', bookShelf);
            if (!bookShelf) {
                console.error('bookShelfSlider element not found!');
                return;
            }
            bookCards = document.querySelectorAll('.book-card');
            console.log('bookCards found:', bookCards.length);
            cardWidth = 300; // Approximate width of each book card including margin and gap
            totalCards = bookCards.length;
            originalCards = totalCards / 3; // Since we duplicated 3 times

            // Pause auto-play on hover and manual scroll
            bookShelf.addEventListener('mouseenter', stopBookShelfAutoPlay);
            bookShelf.addEventListener('mouseleave', startBookShelfAutoPlay);
            bookShelf.addEventListener('scroll', handleManualScroll);
            console.log('Event listeners added to bookshelf');
        }

        function handleManualScroll() {
            isUserScrolling = true;
            stopBookShelfAutoPlay();

            // Clear existing timeout
            if (scrollTimeout) {
                clearTimeout(scrollTimeout);
            }

            // Resume auto-play after user stops scrolling
            scrollTimeout = setTimeout(() => {
                isUserScrolling = false;
                startBookShelfAutoPlay();
            }, 2000); // Resume after 2 seconds of no scrolling
        }

        function startBookShelfAutoPlay() {
            if (bookShelf && !isUserScrolling) {
                console.log('Starting bookshelf animation');
                bookShelfInterval = setInterval(() => {
                    if (isUserScrolling) return;

                    bookShelf.scrollLeft += animationSpeed;

                    // Reset to beginning when reaching the end of original content
                    if (bookShelf.scrollLeft >= bookShelf.scrollWidth / 3) {
                        bookShelf.scrollLeft = 0;
                    }
                }, 50); // 50ms interval for smooth animation
            } else {
                console.log('Cannot start animation - bookShelf:', !!bookShelf, 'isUserScrolling:', isUserScrolling);
            }
        }

        function stopBookShelfAutoPlay() {
            if (bookShelfInterval) {
                cancelAnimationFrame(bookShelfInterval);
                bookShelfInterval = null;
            }
        }

        // Initialize everything
        document.addEventListener('DOMContentLoaded', () => {
            startAutoSlide();
            initBookShelf();
            startBookShelfAutoPlay();

            // Pause carousel on hover
            const carousel = document.querySelector('.carousel-section');
            carousel.addEventListener('mouseenter', stopAutoSlide);
            carousel.addEventListener('mouseleave', startAutoSlide);
        });
    </script>

    <script>
        const searchInput = document.getElementById('searchInput');
        const genreFilter = document.getElementById('genreFilter');
        const table = document.getElementById('booksTable');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        // Add event listener to close advanced search when clicking outside
        document.addEventListener('click', function(event) {
            const advancedSearch = document.getElementById('advancedSearch');
            const toggleButton = document.querySelector('button[onclick="toggleAdvancedSearch()"]');
            if (!advancedSearch.contains(event.target) && event.target !== toggleButton && advancedSearch.style.display === 'block') {
                advancedSearch.style.display = 'none';
            }
        });

        // Book titles for recommendations
        const bookTitles = <?php echo json_encode($bookTitles); ?>;
        const recommendationsDiv = document.getElementById('recommendations');

        function updateRecommendations() {
            const searchTerm = searchInput.value.toLowerCase();
            recommendationsDiv.innerHTML = '';
            if (searchTerm.length > 0) {
                const matches = bookTitles.filter(title => title.toLowerCase().includes(searchTerm)).slice(0, 5);
                matches.forEach(title => {
                    const button = document.createElement('button');
                    button.textContent = title;
                    button.className = 'btn btn-small';
                    button.onclick = () => {
                        searchInput.value = title;
                        filterTable();
                    };
                    recommendationsDiv.appendChild(button);
                });
            }
        }

        searchInput.addEventListener('input', updateRecommendations);

        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedGenre = genreFilter.value.toLowerCase();
            let visibleRows = 0;

            for (let i = 0; i < rows.length; i++) {
                const title = rows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
                const author = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
                const genre = rows[i].getElementsByTagName('td')[2] ? rows[i].getElementsByTagName('td')[2].textContent.toLowerCase() : '';

                const matchesSearch = title.includes(searchTerm) || author.includes(searchTerm);
                const matchesGenre = selectedGenre === '' || genre === selectedGenre;

                if (matchesSearch && matchesGenre) {
                    rows[i].style.display = '';
                    visibleRows++;
                } else {
                    rows[i].style.display = 'none';
                }
            }

            const noResults = document.getElementById('noResults');
            if (visibleRows === 0) {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        }

        if (searchInput) {
            searchInput.addEventListener('keyup', filterTable);
        }
        if (genreFilter) {
            genreFilter.addEventListener('change', filterTable);
        }

        function toggleAdvancedSearch() {
            const adv = document.getElementById('advancedSearch');
            adv.style.display = adv.style.display === 'none' ? 'block' : 'none';
        }

        function advancedSearch() {
            const year = document.getElementById('yearFilter').value;

            let visibleRows = 0;

            for (let i = 0; i < rows.length; i++) {
                const title = rows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
                const author = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
                const genre = rows[i].getElementsByTagName('td')[2] ? rows[i].getElementsByTagName('td')[2].textContent.toLowerCase() : '';
                const pubDate = rows[i].getElementsByTagName('td')[3].textContent.trim();
                const pubYear = pubDate !== '-' ? new Date(pubDate).getFullYear().toString() : '';

                const searchTerm = searchInput.value.toLowerCase();
                const selectedGenre = genreFilter.value.toLowerCase();

                const matchesSearch = title.includes(searchTerm) || author.includes(searchTerm);
                const matchesGenre = selectedGenre === '' || genre === selectedGenre;
                const matchesYear = year === '' || pubYear === year;

                if (matchesSearch && matchesGenre && matchesYear) {
                    rows[i].style.display = '';
                    visibleRows++;
                } else {
                    rows[i].style.display = 'none';
                }
            }

            const noResults = document.getElementById('noResults');
            if (visibleRows === 0) {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        }

        function reserveBook(bookId) {
            if (confirm('Reserve this book? You will be notified when it becomes available.')) {
                fetch('reserve.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'book_id=' + bookId
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.msg);
                    if (data.ok) location.reload();
                });
            }
        }

        function cancelReservation(reservationId) {
            if (confirm('Cancel this reservation?')) {
                fetch('cancel_reservation.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'reservation_id=' + reservationId
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.msg);
                    if (data.ok) location.reload();
                });
            }
        }

        function payFine(fineId) {
            console.log('Paying fine:', fineId);
            // Get fine amount from the table row
            const row = document.querySelector(`button[onclick="payFine(${fineId})"]`).closest('tr');
            const amountCell = row.querySelector('td:nth-child(2)');
            const amount = amountCell.textContent.trim();
            console.log('Fine amount:', amount);

            // Show confirmation modal
            showFineModal(fineId, amount, 'fine_id');
        }

        function showFineModal(id, amount, type) {
            console.log('Showing fine modal for id:', id, 'amount:', amount, 'type:', type);
            // Create modal HTML
            const modal = document.createElement('div');
            modal.id = 'fineModal';
            modal.style.cssText = `
                position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0,0,0,0.5); z-index: 1000; display: flex;
                align-items: center; justify-content: center;
            `;

            const now = new Date();
            const dateStr = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

            modal.innerHTML = `
                <div style="background: white; padding: 20px; border-radius: 8px; width: 400px; max-width: 90%;">
                    <h3 style="margin-top: 0; color: #800000;">Confirm Fine Payment</h3>
                    <p>Are you sure you want to pay this fine?</p>
                    <p style="font-size: 18px; font-weight: bold; color: #dc3545;">Amount: ${amount}</p>
                    <p>Payment Date: ${dateStr}</p>
                    <p>Status will be: Paid</p>
                    <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                        <button onclick="closeFineModal()" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                        <button onclick="confirmPayFine(${id}, '${type}')" style="padding: 8px 16px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">Pay Fine</button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            console.log('Modal appended to body');
        }

        function closeFineModal() {
            const modal = document.getElementById('fineModal');
            if (modal) {
                modal.remove();
            }
        }

        function confirmPayFine(id, type) {
            console.log('Confirming payment for id:', id, 'type:', type);
            const body = type + '=' + id;
            console.log('Request body:', body);

            fetch('pay_fine.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: body
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                closeFineModal();
                if (data.ok) {
                    alert(data.msg || 'Fine paid successfully!');
                    updateTableAfterPayment(id, type);
                } else {
                    alert(data.msg || 'Failed to pay fine');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                closeFineModal();
                alert('Error processing payment');
            });
        }

        function updateTableAfterPayment(id, type) {
            const now = new Date();
            const dateStr = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

            if (type === 'fine_id') {
                // Update Outstanding Fines table
                const button = document.querySelector(`button[onclick="payFine(${id})"]`);
                if (button) {
                    const row = button.closest('tr');
                    const statusCell = row.querySelector('td:nth-child(3)');
                    const actionCell = row.querySelector('td:nth-child(4)');

                    statusCell.textContent = 'paid';
                    actionCell.textContent = `Paid on ${dateStr}`;

                    // Disable the button to prevent double payment
                    button.disabled = true;
                    button.textContent = 'Paid';
                    button.style.backgroundColor = '#28a745';

                    // Check if all fines are now paid and update borrowing status
                    checkAndUpdateBorrowingStatus();
                }
            }
        }

        function checkAndUpdateBorrowingStatus() {
            // Check if there are any remaining pending fines
            const pendingButtons = document.querySelectorAll('button[onclick^="payFine("]:not([disabled])');
            if (pendingButtons.length === 0) {
                // All fines are paid - update the warning message and enable borrowing
                const warningDiv = document.querySelector('div[style*="color: orange"]');
                if (warningDiv) {
                    warningDiv.style.display = 'none';
                }

                // Re-enable borrow buttons for available books
                const borrowButtons = document.querySelectorAll('button[type="submit"]');
                borrowButtons.forEach(button => {
                    const form = button.closest('form');
                    if (form && form.action.includes('borrow.php')) {
                        const unavailableSpan = form.parentElement.querySelector('span');
                        if (unavailableSpan && unavailableSpan.textContent === 'Fines outstanding') {
                            unavailableSpan.style.display = 'none';
                            form.style.display = 'inline';
                        }
                    }
                });
            }
        }

        function updateBothTablesAfterPayment() {
            // Reload the page to refresh all data including fines totals and admin stats
            location.reload();
        }

        // Borrowed Books Search Functionality
        const borrowedSearchInput = document.getElementById('borrowedSearchInput');
        const borrowedStatusFilter = document.getElementById('borrowedStatusFilter');
        const borrowedTable = document.getElementById('borrowedBooksTable');
        const borrowedRows = borrowedTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        // Add event listener to close advanced search when clicking outside
        document.addEventListener('click', function(event) {
            const borrowedAdvancedSearch = document.getElementById('borrowedAdvancedSearch');
            const borrowedToggleButton = document.querySelector('button[onclick="toggleBorrowedAdvancedSearch()"]');
            if (!borrowedAdvancedSearch.contains(event.target) && event.target !== borrowedToggleButton && borrowedAdvancedSearch.style.display === 'block') {
                borrowedAdvancedSearch.style.display = 'none';
            }
        });

        // Book titles for borrowed books recommendations
        const borrowedBookTitles = <?php echo json_encode(array_column($records, 'title')); ?>;
        const borrowedRecommendationsDiv = document.getElementById('borrowedRecommendations');

        function updateBorrowedRecommendations() {
            const searchTerm = borrowedSearchInput.value.toLowerCase();
            borrowedRecommendationsDiv.innerHTML = '';
            if (searchTerm.length > 0) {
                const matches = borrowedBookTitles.filter(title => title.toLowerCase().includes(searchTerm)).slice(0, 5);
                matches.forEach(title => {
                    const button = document.createElement('button');
                    button.textContent = title;
                    button.className = 'btn btn-small';
                    button.onclick = () => {
                        borrowedSearchInput.value = title;
                        filterBorrowedTable();
                    };
                    borrowedRecommendationsDiv.appendChild(button);
                });
            }
        }

        if (borrowedSearchInput) {
            borrowedSearchInput.addEventListener('input', updateBorrowedRecommendations);
        }

        function filterBorrowedTable() {
            const searchTerm = borrowedSearchInput.value.toLowerCase();
            const selectedStatus = borrowedStatusFilter.value.toLowerCase();
            let visibleRows = 0;

            for (let i = 0; i < borrowedRows.length; i++) {
                const title = borrowedRows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
                const status = borrowedRows[i].getElementsByTagName('td')[4].textContent.toLowerCase();

                const matchesSearch = title.includes(searchTerm);
                const matchesStatus = selectedStatus === '' || status.includes(selectedStatus);

                if (matchesSearch && matchesStatus) {
                    borrowedRows[i].style.display = '';
                    visibleRows++;
                } else {
                    borrowedRows[i].style.display = 'none';
                }
            }

            const borrowedNoResults = document.getElementById('borrowedNoResults');
            if (visibleRows === 0) {
                borrowedNoResults.style.display = 'block';
            } else {
                borrowedNoResults.style.display = 'none';
            }
        }

        if (borrowedSearchInput) {
            borrowedSearchInput.addEventListener('keyup', filterBorrowedTable);
        }
        if (borrowedStatusFilter) {
            borrowedStatusFilter.addEventListener('change', filterBorrowedTable);
        }

        function toggleBorrowedAdvancedSearch() {
            const adv = document.getElementById('borrowedAdvancedSearch');
            adv.style.display = adv.style.display === 'none' ? 'block' : 'none';
        }

        function borrowedAdvancedSearch() {
            const year = document.getElementById('borrowedYearFilter').value;

            let visibleRows = 0;

            for (let i = 0; i < borrowedRows.length; i++) {
                const title = borrowedRows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
                const borrowDate = borrowedRows[i].getElementsByTagName('td')[1].textContent.trim();
                const borrowYear = borrowDate !== '-' ? new Date(borrowDate).getFullYear().toString() : '';
                const status = borrowedRows[i].getElementsByTagName('td')[4].textContent.toLowerCase();

                const searchTerm = borrowedSearchInput.value.toLowerCase();
                const selectedStatus = borrowedStatusFilter.value.toLowerCase();

                const matchesSearch = title.includes(searchTerm);
                const matchesStatus = selectedStatus === '' || status.includes(selectedStatus);
                const matchesYear = year === '' || borrowYear === year;

                if (matchesSearch && matchesStatus && matchesYear) {
                    borrowedRows[i].style.display = '';
                    visibleRows++;
                } else {
                    borrowedRows[i].style.display = 'none';
                }
            }

            const borrowedNoResults = document.getElementById('borrowedNoResults');
            if (visibleRows === 0) {
                borrowedNoResults.style.display = 'block';
            } else {
                borrowedNoResults.style.display = 'none';
            }
        }

        // Borrow History Search Functionality
        const historySearchInput = document.getElementById('historySearchInput');
        const historyGenreFilter = document.getElementById('historyGenreFilter');
        const historyTable = document.getElementById('borrowHistoryTable');
        const historyRows = historyTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        // Add event listener to close advanced search when clicking outside
        document.addEventListener('click', function(event) {
            const historyAdvancedSearch = document.getElementById('historyAdvancedSearch');
            const historyToggleButton = document.querySelector('button[onclick="toggleHistoryAdvancedSearch()"]');
            if (!historyAdvancedSearch.contains(event.target) && event.target !== historyToggleButton && historyAdvancedSearch.style.display === 'block') {
                historyAdvancedSearch.style.display = 'none';
            }
        });

        // Book titles for history recommendations
        const historyBookTitles = <?php echo json_encode(array_column($historyRecords, 'title')); ?>;
        const historyRecommendationsDiv = document.getElementById('historyRecommendations');

        function updateHistoryRecommendations() {
            const searchTerm = historySearchInput.value.toLowerCase();
            historyRecommendationsDiv.innerHTML = '';
            if (searchTerm.length > 0) {
                const matches = historyBookTitles.filter(title => title.toLowerCase().includes(searchTerm)).slice(0, 5);
                matches.forEach(title => {
                    const button = document.createElement('button');
                    button.textContent = title;
                    button.className = 'btn btn-small';
                    button.onclick = () => {
                        historySearchInput.value = title;
                        filterHistoryTable();
                    };
                    historyRecommendationsDiv.appendChild(button);
                });
            }
        }

        historySearchInput.addEventListener('input', updateHistoryRecommendations);

        function filterHistoryTable() {
            const searchTerm = historySearchInput.value.toLowerCase();
            const selectedStatus = historyStatusFilter.value.toLowerCase();
            let visibleRows = 0;

            for (let i = 0; i < historyRows.length; i++) {
                const title = historyRows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
                const author = historyRows[i].getElementsByTagName('td')[1] ? historyRows[i].getElementsByTagName('td')[1].textContent.toLowerCase() : '';
                const status = historyRows[i].getElementsByTagName('td')[3].textContent.toLowerCase();

                const matchesSearch = title.includes(searchTerm) || author.includes(searchTerm);
                const matchesStatus = selectedStatus === '' || status.includes(selectedStatus);

                if (matchesSearch && matchesStatus) {
                    historyRows[i].style.display = '';
                    visibleRows++;
                } else {
                    historyRows[i].style.display = 'none';
                }
            }

            const historyNoResults = document.getElementById('historyNoResults');
            if (visibleRows === 0) {
                historyNoResults.style.display = 'block';
            } else {
                historyNoResults.style.display = 'none';
            }
        }

        historySearchInput.addEventListener('keyup', filterHistoryTable);
        historyStatusFilter.addEventListener('change', filterHistoryTable);

        function toggleHistoryAdvancedSearch() {
            const adv = document.getElementById('historyAdvancedSearch');
            adv.style.display = adv.style.display === 'none' ? 'block' : 'none';
        }

        function historyAdvancedSearch() {
            const year = document.getElementById('historyYearFilter').value;

            let visibleRows = 0;

            for (let i = 0; i < historyRows.length; i++) {
                const title = historyRows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
                const borrowDate = historyRows[i].getElementsByTagName('td')[1].textContent.trim();
                const borrowYear = borrowDate !== '-' ? new Date(borrowDate).getFullYear().toString() : '';
                const status = historyRows[i].getElementsByTagName('td')[3].textContent.toLowerCase();

                const searchTerm = historySearchInput.value.toLowerCase();
                const selectedStatus = historyStatusFilter.value.toLowerCase();

                const matchesSearch = title.includes(searchTerm);
                const matchesStatus = selectedStatus === '' || status.includes(selectedStatus);
                const matchesYear = year === '' || borrowYear === year;

                if (matchesSearch && matchesStatus && matchesYear) {
                    historyRows[i].style.display = '';
                    visibleRows++;
                } else {
                    historyRows[i].style.display = 'none';
                }
            }

            const historyNoResults = document.getElementById('historyNoResults');
            if (visibleRows === 0) {
                historyNoResults.style.display = 'block';
            } else {
                historyNoResults.style.display = 'none';
            }
        }

        function markAsRead(notificationId) {
            console.log('Marking notification as read:', notificationId);
            fetch('mark_read.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'notification_id=' + notificationId
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.ok) {
                    const notificationDiv = document.getElementById('notification-' + notificationId);
                    if (notificationDiv) {
                        notificationDiv.style.display = 'none';
                        console.log('Notification hidden');
                    } else {
                        console.log('Notification div not found');
                    }

                    // Update notification count if needed
                    const notificationSection = document.querySelector('h3');
                    if (notificationSection) {
                        console.log('Notification section text:', notificationSection.textContent);
                        const countMatch = notificationSection.textContent.match(/(\d+)/);
                        if (countMatch) {
                            const currentCount = parseInt(countMatch[1]);
                            console.log('Current count:', currentCount);
                            if (currentCount > 1) {
                                notificationSection.textContent = notificationSection.textContent.replace(currentCount, currentCount - 1);
                                console.log('Updated count to:', currentCount - 1);
                            } else {
                                // Hide the entire notification section
                                const notificationContainer = notificationSection.closest('div');
                                if (notificationContainer) {
                                    notificationContainer.style.display = 'none';
                                    console.log('Notification section hidden');
                                }
                            }
                        } else {
                            console.log('No count match found');
                        }
                    } else {
                        console.log('Notification section h3 not found');
                    }
                } else {
                    alert('Failed to mark as read: ' + data.msg);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while marking the notification as read.');
            });
        }
    </script>

    <!-- Modal for displaying statistics details -->
    <div id="statsModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000;">
        <div class="modal-content" style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 600px; border-radius: 8px; overflow: auto;">
            <span class="close" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
            <h2 id="modalTitle">Statistics Details</h2>
            <div id="statsDetails"></div>
        </div>
    </div>

    <script>
        // Statistics modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('statsModal');
            const modalTitle = document.getElementById('modalTitle');
            const statsDetails = document.getElementById('statsDetails');
            const closeBtn = document.querySelector('#statsModal .close');

            // Check if modal elements exist
            if (!modal || !modalTitle || !statsDetails || !closeBtn) {
                console.error('Modal elements not found');
                return;
            }

            // Use event delegation for stat cards
            document.addEventListener('click', function(event) {
                const statCard = event.target.closest('.stat-card');
                if (statCard) {
                    event.preventDefault();
                    const stat = statCard.getAttribute('data-stat');
                    const statTitle = statCard.querySelector('p').textContent;
                    modalTitle.textContent = statTitle;
                    statsDetails.innerHTML = '<p>Loading...</p>';
                    modal.style.display = 'block';

                    // Fetch stats via AJAX
                    const params = new URLSearchParams();
                    params.append('get_stat', '1');
                    params.append('stat', stat);

                    console.log('Fetching stats for:', stat);
                    console.log('Request body:', params.toString());
                    console.log('URL:', window.location.pathname);

                    fetch(window.location.pathname, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: params.toString(),
                        credentials: 'include'
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Received data:', data);
                        // Check if response contains an error
                        if (data.error) {
                            statsDetails.innerHTML = `<p style="color: red;">${data.error}</p>`;
                            return;
                        }

                        let content = '';
                        switch (stat) {
                            case 'distinct_books':
                                if (data.length > 0) {
                                    content = '<ul>' + data.map(book =>
                                        `<li><strong>${book.title}</strong> by ${book.author} (Borrowed ${book.borrow_count} times)</li>`
                                    ).join('') + '</ul>';
                                } else {
                                    content = '<p>No books borrowed yet.</p>';
                                }
                                break;
                            case 'total_borrows':
                                if (data.length > 0) {
                                    content = '<div style="max-height: 400px; overflow-y: auto;"><table style="width: 100%; border-collapse: collapse;"><thead><tr><th style="border: 1px solid #ddd; padding: 8px;">Book</th><th style="border: 1px solid #ddd; padding: 8px;">Borrow Date</th><th style="border: 1px solid #ddd; padding: 8px;">Return Date</th><th style="border: 1px solid #ddd; padding: 8px;">Status</th></tr></thead><tbody>';
                                    data.forEach(borrow => {
                                        content += `<tr><td style="border: 1px solid #ddd; padding: 8px;">${borrow.title}</td><td style="border: 1px solid #ddd; padding: 8px;">${borrow.borrow_date || '-'}</td><td style="border: 1px solid #ddd; padding: 8px;">${borrow.return_date || '-'}</td><td style="border: 1px solid #ddd; padding: 8px;">${borrow.status}</td></tr>`;
                                    });
                                    content += '</tbody></table></div>';
                                } else {
                                    content = '<p>No borrowing history.</p>';
                                }
                                break;
                            case 'current_borrowed':
                                if (data.length > 0) {
                                    content = '<table style="width: 100%; border-collapse: collapse;"><thead><tr><th style="border: 1px solid #ddd; padding: 8px;">Book</th><th style="border: 1px solid #ddd; padding: 8px;">Borrow Date</th><th style="border: 1px solid #ddd; padding: 8px;">Due Date</th></tr></thead><tbody>';
                                    data.forEach(borrow => {
                                        content += `<tr><td style="border: 1px solid #ddd; padding: 8px;">${borrow.title}</td><td style="border: 1px solid #ddd; padding: 8px;">${borrow.borrow_date || '-'}</td><td style="border: 1px solid #ddd; padding: 8px;">${borrow.due_date || '-'}</td></tr>`;
                                    });
                                    content += '</tbody></table>';
                                } else {
                                    content = '<p>No current borrowed books.</p>';
                                }
                                break;
                            case 'overdue_borrows':
                                if (data.length > 0) {
                                    content = '<table style="width: 100%; border-collapse: collapse;"><thead><tr><th style="border: 1px solid #ddd; padding: 8px;">Book</th><th style="border: 1px solid #ddd; padding: 8px;">Due Date</th><th style="border: 1px solid #ddd; padding: 8px;">Days Overdue</th></tr></thead><tbody>';
                                    data.forEach(borrow => {
                                        content += `<tr><td style="border: 1px solid #ddd; padding: 8px;">${borrow.title}</td><td style="border: 1px solid #ddd; padding: 8px;">${borrow.due_date}</td><td style="border: 1px solid #ddd; padding: 8px;">${borrow.days_overdue}</td></tr>`;
                                    });
                                    content += '</tbody></table>';
                                } else {
                                    content = '<p>No overdue books.</p>';
                                }
                                break;
                            case 'most_borrowed_book':
                                if (data) {
                                    content = `<p><strong>Title:</strong> ${data.title}</p><p><strong>Author:</strong> ${data.author}</p><p><strong>Genre:</strong> ${data.genre}</p><p><strong>Times Borrowed:</strong> ${data.borrow_count}</p>`;
                                } else {
                                    content = '<p>No most borrowed book.</p>';
                                }
                                break;
                            case 'favorite_genre':
                                if (data) {
                                    content = `<p><strong>Genre:</strong> ${data.genre}</p><p><strong>Times Borrowed:</strong> ${data.borrow_count}</p>`;
                                    if (data.books && data.books.length > 0) {
                                        content += '<h4>Books in this genre:</h4><ul>';
                                        data.books.forEach(book => {
                                            content += `<li><strong>${book.title}</strong> by ${book.author} (Borrowed ${book.borrow_count} times)</li>`;
                                        });
                                        content += '</ul>';
                                    }
                                } else {
                                    content = '<p>No favorite genre.</p>';
                                }
                                break;
                        }
                        statsDetails.innerHTML = content;
                    })
                    .catch(error => {
                        statsDetails.innerHTML = '<p style="color: red;">Error loading statistics. Please try again later. Details: ' + error.message + '</p>';
                        console.error('Fetch error:', error);
                    });
                }
            });

            // Close modal when clicking close button
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    if (modal) modal.style.display = 'none';
                });
            }

            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (modal && event.target == modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>

    <footer>
        © 2025 WMSU Library — All Rights Reserved
    </footer>
</body>
</html>
