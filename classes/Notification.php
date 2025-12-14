<?php
require_once __DIR__ . '/Database.php';

class Notification extends Database {
    private $typeDescriptions = [
        'review' => 'A new review has been submitted for a book.',
        'approval' => 'Your borrow request has been approved.',
        'return' => 'A book has been returned successfully.',
        'overdue' => 'A borrowed book is overdue.',
        'reminder' => 'Reminder about an upcoming due date.',
        'rejection' => 'Your borrow request has been rejected.',
        'cancellation' => 'Your borrow request has been cancelled.',
        'fine' => 'A fine has been added for an overdue book.'
    ];

    public function __construct($pdo = null) {
        parent::__construct($pdo);
    }

    public function getDescription($type) {
        return $this->typeDescriptions[$type] ?? 'Notification';
    }

    public function create($user_id, $type, $message) {
        $stmt = $this->pdo->prepare('INSERT INTO notifications (user_id, type, message) VALUES (?, ?, ?)');
        return $stmt->execute([$user_id, $type, $message]);
    }

    public function getUnread($user_id) {
        $stmt = $this->pdo->prepare('SELECT * FROM notifications WHERE user_id = ? AND is_read = FALSE ORDER BY created_at DESC');
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsRead($notification_id, $user_id) {
        error_log("Notification::markAsRead called with id=$notification_id, user_id=$user_id");
        $stmt = $this->pdo->prepare('UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?');
        $result = $stmt->execute([$notification_id, $user_id]);
        $affected = $stmt->rowCount();
        error_log("Update result: $result, affected rows: $affected");
        return $result && $affected > 0;
    }

    public function getAll($user_id, $limit = 50) {
        $stmt = $this->pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?');
        $stmt->execute([$user_id, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAllAsRead($user_id) {
        $stmt = $this->pdo->prepare('UPDATE notifications SET is_read = TRUE WHERE user_id = ? AND is_read = FALSE');
        $result = $stmt->execute([$user_id]);
        $affected = $stmt->rowCount();
        return $result && $affected > 0;
    }
}
?>
