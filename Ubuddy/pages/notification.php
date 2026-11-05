
<?php
session_start();

require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../php/stu_profile_functions.php';
require_once __DIR__ . '/../php/notification_functions.php';
require_once __DIR__ . '/../php/com_admin_funct.php';

// Check whether the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get clubs the user is a committee admin of.
$adminClubs = getAdminClubs($pdo, $userId);

// Retrieve the user's profile information.
$profile = getStudentProfile($pdo, $userId);

if (!$profile) {
    http_response_code(404);
    exit('User profile not found.');
}

// Retrieve notifications.
$notifications = [];
$loadError = false;

try {
    $notifications = getUserNotifications($pdo, $userId);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $loadError = true;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Notifications | ÜBuddy</title>

    <?php pageIcons(); ?>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/shared.css">
    <link rel="stylesheet" href="../css/notification.css">

    <!-- Notification interaction -->
    <script src="../js/notification.js" defer></script>
</head>

<body>

    <?php pageHeader($profile['name'], $profile['user_id'], $adminClubs); ?>

    <main class="page-content">

        <div class="notification-page">

            <?php backLink('homepage.php', 'Home'); ?>

            <h1 class="notifications-title">Notifications</h1>

            <div class="notifications-layout">

                <div class="notification-list">

                    <?php if ($loadError): ?>

                        <div class="empty-state">
                            <h3>Unable to load notifications</h3>
                            <p>Please try again later.</p>
                        </div>

                    <?php elseif (!empty($notifications)): ?>

                        <?php foreach ($notifications as $notification): ?>

                            <?php
                            $type = strtoupper(
                                trim($notification['notif_type'] ?? '')
                            );

                            $icon = getNotificationIcon($type);

                            $isRead = isNotificationRead(
                                $notification['is_read']
                            );

                            $displayDate = formatNotificationDate(
                                $notification['notif_date']
                            );

                            // Check whether the notification has a destination.
                            $hasLink = !empty($notification['link_url']);

                            // Notification handler URL.
                            $notificationUrl = 'open_notification.php?id='
                                . urlencode($notification['notif_id']);
                            ?>

                            <div class="info-notification <?= notificationEscape(strtolower($type)) ?>"
                                 data-notif-id="<?= notificationEscape($notification['notif_id']) ?>"
                                 data-is-read="<?= $isRead ? 'true' : 'false' ?>"
                                 tabindex="0"
                                 role="button"
                                 aria-pressed="<?= $isRead ? 'true' : 'false' ?>"
                                 aria-label="Mark notification as read: <?= notificationEscape($notification['title']) ?>">

                                <!-- Notification Icon -->
                                <span class="notification-icon material-symbols-outlined"
                                      aria-hidden="true">
                                    <?= notificationEscape($icon) ?>
                                </span>

                                <div class="notification-content">

                                    <div class="notification-header">

                                        <h2>
                                            <?= notificationEscape($notification['title']) ?>
                                        </h2>

                                        <span class="notification-time">
                                            <?= notificationEscape($displayDate) ?>
                                        </span>

                                    </div>

                                    <p>
                                        <?= notificationEscape($notification['message']) ?>
                                    </p>

                                </div>

                                <!-- Unread Indicator -->
                                <?php if (!$isRead): ?>
                                    <span class="badge"></span>
                                <?php endif; ?>

                                <!-- Navigation Arrow -->
                                <?php if ($hasLink): ?>
                                    <a href="<?= notificationEscape($notificationUrl) ?>"
                                       class="notification-arrow"
                                       aria-label="Open related page"
                                       title="Open related page">
                                        <span class="material-symbols-outlined">arrow_forward</span>
                                    </a>
                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <div class="empty-state">

                            <span class="material-symbols-outlined">
                                notifications
                            </span>

                            <h3>No notifications yet</h3>

                            <p>
                                You are all caught up!
                                New notifications will appear here.
                            </p>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </main>

    <?php pageFooter(); ?>

</body>
</html>
