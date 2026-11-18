
<?php

require_once __DIR__ . '/process_helper.php';

$audience = trim($_POST['audience'] ?? '');
$targetUserId = trim($_POST['notification_user_id'] ?? '');

$title = trim($_POST['notification_title'] ?? '');
$message = trim($_POST['notification_message'] ?? '');
$notificationType = trim($_POST['notification_type'] ?? '');
$linkUrl = trim($_POST['link_url'] ?? '');

try {

    /*
    |--------------------------------------------------------------------------
    | Validate audience
    |--------------------------------------------------------------------------
    */
    if (!in_array($audience, ['all', 'individual'], true)) {
        systemAdminProcessError(
            'Please select a valid notification audience.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Validate required fields
    |--------------------------------------------------------------------------
    */
    if (
        $title === '' ||
        $message === '' ||
        $notificationType === ''
    ) {
        systemAdminProcessError(
            'Please fill in the notification title, message, and type.'
        );
    }

    if (mb_strlen($title) > 255) {
        systemAdminProcessError(
            'Notification title cannot exceed 255 characters.'
        );
    }

    if (mb_strlen($notificationType) > 100) {
        systemAdminProcessError(
            'Notification type cannot exceed 100 characters.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Validate individual recipient
    |--------------------------------------------------------------------------
    */
    if ($audience === 'individual' && $targetUserId === '') {
        systemAdminProcessError(
            'Please select a user to receive the notification.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Validate optional link
    |--------------------------------------------------------------------------
    */
    if ($linkUrl !== '') {

        if (!filter_var($linkUrl, FILTER_VALIDATE_URL)) {
            systemAdminProcessError(
                'Please enter a valid notification link.'
            );
        }

        $scheme = strtolower(
            (string) parse_url($linkUrl, PHP_URL_SCHEME)
        );

        if (!in_array($scheme, ['http', 'https'], true)) {
            systemAdminProcessError(
                'Notification links must use HTTP or HTTPS.'
            );
        }

    } else {
        $linkUrl = null;
    }

    /*
    |--------------------------------------------------------------------------
    | Send notification
    |--------------------------------------------------------------------------
    */
    $sentCount = sendSystemAdminNotification(
        $pdo,
        $audience,
        $targetUserId,
        $title,
        $message,
        $notificationType,
        $linkUrl
    );

    if ($sentCount < 1) {

        if ($audience === 'individual') {
            systemAdminProcessError(
                'Unable to send the notification. Please check that the selected user exists.'
            );
        }

        systemAdminProcessError(
            'No notifications were sent. There may be no registered users.'
        );
    }

    if ($audience === 'all') {
        $_SESSION['system_admin_success'] =
            "Notification sent successfully to {$sentCount} users.";
    } else {
        $_SESSION['system_admin_success'] =
            'Notification sent successfully.';
    }

} catch (PDOException $e) {

    error_log(
        'Notification processing error: ' . $e->getMessage()
    );

    systemAdminProcessError(
        'Something went wrong while sending the notification.'
    );
}

systemAdminProcessRedirect();
