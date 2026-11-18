
<?php
session_start();

require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/notification_functions.php';

// Check whether the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$notifId = $_GET['id'] ?? null;

// Check whether a valid notification ID was provided.
if (!$notifId || !ctype_digit((string) $notifId)) {
    header('Location: notification.php');
    exit;
}

try {
    // Retrieve the notification belonging to this user.
    $stmt = $pdo->prepare(
        'SELECT link_url
         FROM notification
         WHERE notif_id = :notif_id
         AND user_id = :user_id'
    );

    $stmt->execute([
        'notif_id' => $notifId,
        'user_id' => $userId
    ]);

    $notification = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$notification) {
        header('Location: notification.php');
        exit;
    }

    // Mark the notification as read.
    markNotificationAsRead($pdo, $notifId, $userId);

    // Get the notification destination.
    $link = trim($notification['link_url'] ?? '');

    // Only allow safe relative page paths.
    if (
        !empty($link) &&
        !str_contains($link, '://') &&
        !str_starts_with($link, '//') &&
        !str_starts_with($link, '/') &&
        !str_contains($link, '..') &&
        !str_contains($link, '\\')
    ) {
        header('Location: ' . $link);
        exit;
    }

    // Return to notifications if there is no valid destination.
    header('Location: notification.php');
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());

    header('Location: notification.php');
    exit;
}
