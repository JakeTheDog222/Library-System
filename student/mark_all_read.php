<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../classes/Notification.php';

if (!is_student()) {
    header('Location: ../index.php');
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user']['id'];

    $notification = new Notification($pdo);
    $result = $notification->markAllAsRead($user_id);

    if ($result) {
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'msg' => 'Failed to mark all notifications as read']);
    }
    exit;
}

echo json_encode(['ok' => false, 'msg' => 'Invalid request']);
?>
