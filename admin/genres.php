<?php
require_once __DIR__ . '/../helpers.php';

if (!is_admin()) {
    header('Location: ../index.php');
    exit;
}

$genres = $pdo->query("
    SELECT
        b.genre,
        COUNT(DISTINCT b.id) AS book_count,
        COALESCE(SUM(bh.borrow_count), 0) AS borrow_count
    FROM books b
    LEFT JOIN (
        SELECT book_id, COUNT(*) AS borrow_count
        FROM borrow_history
        GROUP BY book_id
    ) bh ON b.id = bh.book_id
    WHERE b.genre IS NOT NULL AND b.genre != ''
    GROUP BY b.genre
    ORDER BY b.genre ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Handle AJAX request for books in a genre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_books'])) {
    $genre = trim($_POST['genre'] ?? '');
    if ($genre) {
        $books = $pdo->prepare("SELECT title, author FROM books WHERE genre = ? ORDER BY title ASC");
        $books->execute([$genre]);
        $bookList = $books->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($bookList);
    } else {
        echo json_encode([]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Genres</title>
    <link rel="stylesheet" href="../assets/css/custom.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f9fafb;
        }
        .genres-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            padding: 20px 0;
        }
        @media (max-width: 768px) {
            .genres-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
            }
        }
        @media (max-width: 480px) {
            .genres-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
        .genre-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 25px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            cursor: default;
            user-select: none;
        }
        .genre-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #d4af37, #ffd700);
        }
        .genre-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        .genre-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
            color: #333;
        }
        .genre-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, #d4af37, #ffd700);
            border-radius: 2px;
        }
        .genre-stats {
            font-size: 1rem;
        }
        .genre-stats p {
            margin: 12px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stat-label {
            font-weight: 500;
            color: #666;
        }
        .stat-value {
            font-weight: 600;
            color: #333;
        }
    </style>
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
                <h2>Book Genres</h2>
                <?php if (empty($genres)): ?>
                    <p>No genres found.</p>
                <?php else: ?>
                    <div class="genres-grid">
                        <?php foreach ($genres as $genre): ?>
                            <div class="genre-card" data-genre="<?= htmlspecialchars($genre['genre']) ?>">
                                <div class="genre-title"><?= htmlspecialchars($genre['genre']) ?></div>
                                <div class="genre-stats">
                                    <p><span class="stat-label">Books:</span> <span class="stat-value"><?= htmlspecialchars($genre['book_count']) ?></span></p>
                                    <p><span class="stat-label">Borrow Rate:</span> <span class="stat-value"><?= htmlspecialchars($genre['borrow_count']) ?></span></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <!-- Modal for displaying books in a genre -->
    <div id="booksModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Books in Genre</h2>
            <div id="booksList"></div>
        </div>
    </div>

    <footer>
        © 2025 WMSU Library — All Rights Reserved
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('booksModal');
            const modalTitle = document.getElementById('modalTitle');
            const booksList = document.getElementById('booksList');
            const closeBtn = document.querySelector('.close');

            // Add click event to genre cards
            document.querySelectorAll('.genre-card').forEach(card => {
                card.addEventListener('click', function() {
                    const genre = this.getAttribute('data-genre');
                    modalTitle.textContent = `Books in ${genre}`;
                    booksList.innerHTML = '<p>Loading...</p>';
                    modal.style.display = 'block';

                    // Fetch books via AJAX
                    const formData = new FormData();
                    formData.append('get_books', '1');
                    formData.append('genre', genre);

                    fetch('genres.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            booksList.innerHTML = '<ul>' + data.map(book =>
                                `<li><strong>${book.title}</strong> by ${book.author}</li>`
                            ).join('') + '</ul>';
                        } else {
                            booksList.innerHTML = '<p>No books found in this genre.</p>';
                        }
                    })
                    .catch(error => {
                        booksList.innerHTML = '<p>Error loading books.</p>';
                        console.error('Error:', error);
                    });
                });
            });

            // Close modal when clicking close button
            closeBtn.onclick = function() {
                modal.style.display = 'none';
            };

            // Close modal when clicking outside
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            };
        });
    </script>
</body>
</html>
