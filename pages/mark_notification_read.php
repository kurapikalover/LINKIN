
<?php
session_start();

require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/notification_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$notifId = filter_input(INPUT_POST, 'notif_id', FILTER_VALIDATE_INT);

if (!$notifId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid notification ID.']);
    exit;
}

try {
    markNotificationAsRead($pdo, $notifId, $_SESSION['user_id']);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to update notification.']);
}
