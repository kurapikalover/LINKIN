
<?php

// =========================
// SESSION AND FILES
// =========================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../php/com_admin_funct.php';


// =========================
// CHECK LOGIN
// =========================

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];


// =========================
// GET ADMIN CLUBS
// =========================

$adminClubs = getAdminClubs($pdo, $userId);

$allowedClubIds = array_map(
    'intval',
    array_column($adminClubs, 'club_id')
);


// =========================
// CHECK SELECTED CLUB
// =========================

$clubId = filter_input(INPUT_GET, 'club_id', FILTER_VALIDATE_INT);

if (!$clubId || $clubId <= 0) {
    header('Location: com-admin.php');
    exit;
}

// Make sure the user administers this club.
if (!in_array((int) $clubId, $allowedClubIds, true)) {
    http_response_code(403);
    exit('You do not have permission to add events for this club.');
}


// =========================
// GET CLUB DETAILS
// =========================

$stmt = $pdo->prepare("
    SELECT club_name
    FROM public.club
    WHERE club_id = :club_id
");

$stmt->execute([
    'club_id' => $clubId
]);

$club = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$club) {
    http_response_code(404);
    exit('Club not found.');
}


// =========================
// FORM VARIABLES
// =========================

$errors = [];

$eventName = '';
$description = '';
$startDate = '';
$endDate = '';
$location = '';


// =========================
// CSRF TOKEN
// =========================

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


// =========================
// HANDLE FORM SUBMISSION
// =========================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $eventName = trim($_POST['event_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $startDate = trim($_POST['start_date'] ?? '');
    $endDate = trim($_POST['end_date'] ?? '');
    $location = trim($_POST['location'] ?? '');


    // Validate CSRF token
    if (
        !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $errors[] = 'Invalid form submission. Please try again.';
    }


    // Validate event name
    if ($eventName === '') {
        $errors[] = 'Please enter an event name.';
    }


    // Validate dates
    $start = DateTime::createFromFormat('!Y-m-d', $startDate);
    $end = DateTime::createFromFormat('!Y-m-d', $endDate);

    if (!$start || $start->format('Y-m-d') !== $startDate) {
        $errors[] = 'Please enter a valid start date.';
    }

    if (!$end || $end->format('Y-m-d') !== $endDate) {
        $errors[] = 'Please enter a valid end date.';
    }

    if ($start && $end && $end < $start) {
        $errors[] = 'The end date cannot be earlier than the start date.';
    }


    // =========================
    // INSERT EVENT
    // =========================

    if (empty($errors)) {

        try {

            $stmt = $pdo->prepare("
                INSERT INTO public.event (
                    event_name,
                    description,
                    start_date,
                    end_date,
                    location,
                    event_status,
                    club_id
                )
                VALUES (
                    :event_name,
                    :description,
                    :start_date,
                    :end_date,
                    :location,
                    'upcoming',
                    :club_id
                )
            ");

            $_SESSION['success_message'] = 'Event added successfully!';

            $stmt->execute([
                'event_name' => $eventName,
                'description' => $description !== '' ? $description : null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'location' => $location !== '' ? $location : null,
                'club_id' => $clubId
            ]);

            header(
                'Location: com-admin.php?id=' . (int) $clubId . '&event_added=1'
            );

            exit;

        } catch (PDOException $e) {

            error_log($e->getMessage());

            $errors[] = 'Unable to add the event. Please try again.';
        }
    }
}


// =========================
// ESCAPE OUTPUT
// =========================

function e($value) {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Event | ÜBuddy</title>

    <?php pageIcons(); ?>

    <!-- Shared stylesheets -->
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/shared.css">

    <!-- Add Event stylesheet -->
    <link rel="stylesheet" href="../css/event-form.css">
</head>

<body>

    <?php
    pageHeader(
        $_SESSION['name'] ?? 'Student',
        $userId,
        $adminClubs
    );
    ?>

    <main class="page-content">

        <div class="event-form-container">

            <a
                href="com-admin.php?id=<?= (int) $clubId ?>"
                class="back-link"
            >
                &larr; Back to Dashboard
            </a>

            <h2>Add Event</h2>

            <p class="form-subtitle">
                Create a new event for <?= e($club['club_name']) ?>.
            </p>


            <!-- Error messages -->
            <?php if (!empty($errors)): ?>

                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?= e($error) ?></p>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>


            <!-- Add Event Form -->
            <form method="POST" action="">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($_SESSION['csrf_token']) ?>"
                >


                <!-- Event Name -->
                <div class="form-group">

                    <label for="event_name">
                        Event Name *
                    </label>

                    <input
                        type="text"
                        id="event_name"
                        name="event_name"
                        maxlength="150"
                        value="<?= e($eventName) ?>"
                        required
                    >

                </div>


                <!-- Description -->
                <div class="form-group">

                    <label for="description">
                        Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                    ><?= e($description) ?></textarea>

                </div>


                <!-- Start and End Dates -->
                <div class="form-row">

                    <div class="form-group">

                        <label for="start_date">
                            Start Date *
                        </label>

                        <input
                            type="date"
                            id="start_date"
                            name="start_date"
                            value="<?= e($startDate) ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="end_date">
                            End Date *
                        </label>

                        <input
                            type="date"
                            id="end_date"
                            name="end_date"
                            value="<?= e($endDate) ?>"
                            required
                        >

                    </div>

                </div>


                <!-- Location -->
                <div class="form-group">

                    <label for="location">
                        Location
                    </label>

                    <input
                        type="text"
                        id="location"
                        name="location"
                        maxlength="255"
                        value="<?= e($location) ?>"
                    >

                </div>


                <!-- Automatic Event Status -->
                <div class="event-status-note">

                    <span class="material-symbols-outlined">info</span>

                    <p>
                        Event status will automatically update based
                        on the start and end dates.
                    </p>

                </div>


                <!-- Form Buttons -->
                <div class="form-actions">

                    <a
                        href="com-admin.php?id=<?= (int) $clubId ?>"
                        class="cancel-button"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="submit-button"
                    >
                        Add Event
                    </button>

                </div>

            </form>

        </div>

    </main>

    <?php pageFooter(); ?>

</body>
</html>
