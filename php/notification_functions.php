
<?php

// Escape output before displaying it.
function notificationEscape($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

// Notification icons.
function getNotificationIcon($type): string
{
    $notificationIcons = [
        'EVENT'       => 'calendar_month',
        'MEMBERSHIP'  => 'groups_2',
        'INTERVIEW'   => 'frame_person_mic',
        'APPLICATION' => 'document_scanner',
        'FEEDBACK'    => 'feedback'
    ];

    $type = strtoupper(trim($type));

    return $notificationIcons[$type] ?? 'notifications';
}

// Retrieve notifications belonging to a user.
function getUserNotifications(PDO $pdo, $userId): array
{
    $stmt = $pdo->prepare(
        'SELECT
            notif_id,
            user_id,
            title,
            message,
            notif_date,
            notif_type,
            is_read,
            link_url
         FROM notification
         WHERE user_id = :user_id
         ORDER BY notif_date DESC'
    );

    $stmt->execute([
        'user_id' => $userId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Count unread notifications belonging to a user.
function getUnreadNotificationCount(PDO $pdo, $userId): int
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM notification
         WHERE user_id = :user_id
         AND is_read = FALSE'
    );

    $stmt->execute([
        'user_id' => $userId
    ]);

    return (int) $stmt->fetchColumn();
}

// Mark one notification as read.
function markNotificationAsRead(PDO $pdo, $notifId, $userId): void
{
    $stmt = $pdo->prepare(
        'UPDATE notification
         SET is_read = TRUE
         WHERE notif_id = :notif_id
         AND user_id = :user_id'
    );

    $stmt->execute([
        'notif_id' => $notifId,
        'user_id' => $userId
    ]);
}

// Check whether a notification has been read.
function isNotificationRead($value): bool
{
    return in_array(
        strtolower((string) $value),
        ['1', 't', 'true', 'yes'],
        true
    );
}

// Format notification date.
function formatNotificationDate($date): string
{
    $notificationDate = new DateTimeImmutable($date);
    $now = new DateTimeImmutable();

    $difference = $now->getTimestamp()
        - $notificationDate->getTimestamp();

    return $difference < 60
        ? 'Now'
        : $notificationDate->format('d/m/Y');
}

?>
