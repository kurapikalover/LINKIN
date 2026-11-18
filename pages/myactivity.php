
<?php
session_start();

require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../php/stu_profile_functions.php';
require_once __DIR__ . '/../php/com_admin_funct.php';

// Check whether the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get clubs the user is a committee admin of.
$adminClubs = getAdminClubs($pdo, $userId);

// Retrieve the user's profile.
$profile = getStudentProfile($pdo, $userId);

if (!$profile) {
    http_response_code(404);
    exit('User profile not found.');
}


// Escape output safely.
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Format dates.
function formatActivityDate($date) {
    if (empty($date)) {
        return 'Date unavailable';
    }

    return date('d F Y', strtotime($date));
}


// Retrieve the student's active memberships.
$membershipStmt = $pdo->prepare(
    "SELECT
        m.membership_id,
        m.club_id,
        m.position_id,
        m.membership_type,
        m.join_date,
        m.membership_status,
        m.is_admin,
        c.club_name,
        p.position_name
    FROM membership m
    INNER JOIN club c
        ON m.club_id = c.club_id
    LEFT JOIN position p
        ON m.position_id = p.position_id
    WHERE m.user_id = :user_id
      AND m.membership_status = 'Enrolled'
    ORDER BY m.join_date DESC"
);

$membershipStmt->execute([
    'user_id' => $userId
]);

$memberships = $membershipStmt->fetchAll(PDO::FETCH_ASSOC);


// Retrieve events from clubs the student belongs to.
$eventStmt = $pdo->prepare(
    "SELECT DISTINCT
        e.event_id,
        e.event_name,
        e.description,
        e.start_date,
        e.end_date,
        e.location,
        e.event_status,
        c.club_name
    FROM event e
    INNER JOIN club c
        ON e.club_id = c.club_id
    INNER JOIN membership m
        ON e.club_id = m.club_id
    WHERE m.user_id = :user_id
      AND m.membership_status = 'Enrolled'
    ORDER BY e.start_date ASC"
);

$eventStmt->execute([
    'user_id' => $userId
]);

$events = $eventStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ÜBuddy - My Activity</title>

    <?php pageIcons(); ?>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/shared.css">
    <link rel="stylesheet" href="../css/myactivity.css">
</head>

<body>

<?php
// Display the shared page header.
pageHeader(
    $profile['name'],
    $profile['user_id'],
    $adminClubs
);
?>

<main class="page-content">

    <?php backLink('homepage.php', 'Home'); ?>

    <div class="myactivity-page">

        <h1 class="myactivity-title">My Activity</h1>

        <div class="container">

            <!-- CLUBS AND EVENTS -->
            <div class="clubsandevents-layout">

                <h2>Clubs and Events</h2>

                <div class="clubsandevents-list">

                    <?php if (empty($events)): ?>

                        <p>You have no club events to display.</p>

                    <?php else: ?>

                        <?php foreach ($events as $event): ?>

                            <div class="info-section">

                                <div class="inline-row">

                                    <h3><?= e($event['club_name']) ?></h3>

                                    <span class="<?= e(strtolower($event['event_status'])) ?>">
                                        <?= e($event['event_status']) ?>
                                    </span>

                                </div>

                                <div class="details-section">

                                    <p><?= e($event['event_name']) ?></p>

                                    <small>

                                        <?= e(formatActivityDate($event['start_date'])) ?>

                                        <?php if (
                                            !empty($event['end_date']) &&
                                            $event['end_date'] !== $event['start_date']
                                        ): ?>

                                            - <?= e(formatActivityDate($event['end_date'])) ?>

                                        <?php endif; ?>

                                        <br>

                                        <?= e($event['location']) ?>

                                    </small>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>

            </div>


            <!-- MEMBERSHIPS -->
            <div class="memberships-layout">

                <h2>Memberships</h2>

                <div class="memberships-list">

                    <?php if (empty($memberships)): ?>

                        <p>You have no active club memberships.</p>

                    <?php else: ?>

                        <?php foreach ($memberships as $membership): ?>

                            <div class="memberships-info">

                                <div class="logo-container">
                                    <!-- Club logo can be added here later. -->
                                </div>

                                <div class="details-section">

                                    <h3><?= e($membership['club_name']) ?></h3>

                                    <p>
                                        <?= e(ucfirst($membership['membership_type'])) ?>
                                    </p>

                                    <?php if (
                                        strtolower($membership['membership_type']) === 'committee'
                                        && !empty($membership['position_name'])
                                    ): ?>

                                        <p class="p1">
                                            <?= e($membership['position_name']) ?>
                                        </p>

                                    <?php endif; ?>

                                    <p class="p2">
                                        Joined <?= e(formatActivityDate($membership['join_date'])) ?>
                                    </p>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</main>

<?php pageFooter(); ?>

</body>
</html>
