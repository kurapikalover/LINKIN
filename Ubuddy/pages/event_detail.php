<?php
session_start();

require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/shared_functions.php';
require_once __DIR__ . '/../php/event_queries.php';
require_once __DIR__ . '/../php/event_functions.php';
require_once __DIR__ . '/../php/com_admin_funct.php';

// Get logged-in user.
$userId = $_SESSION['user_id'] ?? null;

// Get clubs managed by this user.
$adminClubs = [];

if ($userId !== null) {
    $adminClubs = getAdminClubs($pdo, $userId);
}

// Get event ID.
$eventId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$event = false;
$errors = [];
$successMessage = null;
$isClubAdmin = false;

// Retrieve event.
if ($eventId !== false && $eventId !== null && $eventId > 0) {
    $event = getEventById($pdo, $eventId);
}

// Check whether the user administers this event's club.
if ($event && $userId !== null) {
    foreach ($adminClubs as $adminClub) {
        if ((int) $adminClub['club_id'] === (int) $event['club_id']) {
            $isClubAdmin = true;
            break;
        }
    }
}

// Read success message after redirect.
$successMessage = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);

// Default form values.
$formEventName = $event['event_name'] ?? '';
$formDescription = $event['description'] ?? '';
$formStartDate = $event['start_date'] ?? '';
$formEndDate = $event['end_date'] ?? '';
$formLocation = $event['location'] ?? '';

// Generate CSRF token.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle event update.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $event && $isClubAdmin) {

    $formEventName = trim($_POST['event_name'] ?? '');
    $formDescription = trim($_POST['description'] ?? '');
    $formStartDate = trim($_POST['start_date'] ?? '');
    $formEndDate = trim($_POST['end_date'] ?? '');
    $formLocation = trim($_POST['location'] ?? '');

    $cancelEvent = isset($_POST['cancel_event'])
        && $_POST['cancel_event'] === '1';

    // Check CSRF token.
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    // Validate event name.
    if ($formEventName === '') {
        $errors[] = 'Please enter an event name.';
    }

    // Validate dates.
    $startDateObject = DateTime::createFromFormat('!Y-m-d', $formStartDate);
    $endDateObject = DateTime::createFromFormat('!Y-m-d', $formEndDate);

    if (
        !$startDateObject ||
        $startDateObject->format('Y-m-d') !== $formStartDate
    ) {
        $errors[] = 'Please enter a valid start date.';
    }

    if (
        !$endDateObject ||
        $endDateObject->format('Y-m-d') !== $formEndDate
    ) {
        $errors[] = 'Please enter a valid end date.';
    }

    if (
        $startDateObject &&
        $endDateObject &&
        $endDateObject < $startDateObject
    ) {
        $errors[] = 'The end date cannot be earlier than the start date.';
    }

    // Save changes.
    if (empty($errors)) {

        try {
            $newStatus = $cancelEvent ? 'cancelled' : 'upcoming';

            $sql = "
                UPDATE public.event
                SET
                    event_name = :event_name,
                    description = :description,
                    start_date = :start_date,
                    end_date = :end_date,
                    location = :location,
                    event_status = :event_status
                WHERE event_id = :event_id
                  AND club_id = :club_id
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'event_name' => $formEventName,
                'description' => $formDescription !== ''
                    ? $formDescription
                    : null,
                'start_date' => $formStartDate,
                'end_date' => $formEndDate,
                'location' => $formLocation !== ''
                    ? $formLocation
                    : null,
                'event_status' => $newStatus,
                'event_id' => $eventId,
                'club_id' => $event['club_id']
            ]);

            $_SESSION['success_message'] = 'Event updated successfully!';

            header('Location: event_detail.php?id=' . (int) $eventId);
            exit;

        } catch (PDOException $e) {
            $errors[] = 'Unable to update the event. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        <?= $event
            ? escapeHTML($event['event_name']) . ' | ÜBuddy'
            : 'Event Details | ÜBuddy'
        ?>
    </title>

    <?php pageIcons(); ?>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/shared.css">
    <link rel="stylesheet" href="../css/event_detail.css">

    <script src="../js/event_detail.js" defer></script>
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

    <?php
    $from = $_GET['from'] ?? '';

    $backUrl = 'event_page.php';
    $backLabel = 'Back to Events';

    if ($from === 'com-admin') {
        $adminClubId = (int) ($_GET['admin_club_id'] ?? 0);

        $backUrl = 'com-admin.php?id=' . $adminClubId;
        $backLabel = 'Back to Committee Admin';
    }

    backLink($backUrl, $backLabel);
    ?>

    <?php if (!$event): ?>

        <section class="event-not-found">
            <h1>Event not found</h1>

            <p>
                This event does not exist or the link is invalid.
            </p>

            <a href="event_page.php">Browse all events</a>
        </section>

    <?php else: ?>

        <?php
        $eventName = $event['event_name'] ?? 'Untitled Event';
        $eventStatus = $event['event_status'] ?? 'Unknown';
        $statusClass = eventStatusClass($eventStatus);

        $description = $event['description']
            ?: 'No description available yet.';

        $startDate = $event['start_date'] ?? null;
        $endDate = $event['end_date'] ?? null;

        $location = $event['location']
            ?: 'Location not specified';

        $clubName = $event['club_name']
            ?? 'Unknown club';
        ?>

        <?php if ($successMessage): ?>
            <div class="event-success" id="eventSuccess">
                <span><?= escapeHTML($successMessage) ?></span>

                <button
                    type="button"
                    class="close-success"
                    aria-label="Close notification"
                    onclick="document.getElementById('eventSuccess').remove()"
                >
                    &times;
                </button>
            </div>
        <?php endif; ?>

        <div class="event-layout">

            <section class="event-information">

                <div class="event-title-row">
                    <h1><?= escapeHTML($eventName) ?></h1>

                    <?php if ($isClubAdmin): ?>
                        <button
                            type="button"
                            class="edit-event-btn"
                            id="openEditEvent"
                        >
                            <span class="material-symbols-outlined">
                                edit
                            </span>
                            Edit Event
                        </button>
                    <?php endif; ?>
                </div>

                <p class="event-status <?= escapeHTML($statusClass) ?>">
                    <?= escapeHTML(ucfirst($eventStatus)) ?>
                </p>

                <div class="event-circles" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <div class="event-info-section">
                    <h2>Description</h2>

                    <p>
                        <?= nl2br(escapeHTML($description)) ?>
                    </p>
                </div>

                <div class="event-info-section">
                    <h2>Date</h2>

                    <p>
                        <?= escapeHTML(
                            eventDateLabel($startDate, $endDate)
                        ) ?>
                    </p>
                </div>

                <div class="event-info-section">
                    <h2>Location</h2>

                    <p><?= escapeHTML($location) ?></p>
                </div>

                <div class="event-info-section">
                    <h2>Hosted by</h2>

                    <?php if (!empty($event['club_id'])): ?>

                        <a
                            class="event-host-link"
                            href="club.php?id=<?= urlencode((string) $event['club_id']) ?>"
                        >
                            <?= escapeHTML($clubName) ?>
                        </a>

                    <?php else: ?>

                        <p><?= escapeHTML($clubName) ?></p>

                    <?php endif; ?>
                </div>

            </section>

            <aside class="event-sidebar">

                <div class="event-main-image">

                    <div class="event-image-placeholder">
                        <span class="material-symbols-outlined" aria-hidden="true">
                            event
                        </span>

                        <span>Event image coming soon</span>
                    </div>

                </div>

            </aside>

        </div>

        <?php require __DIR__ . '/event_gallery.php'; ?>


        <?php if ($isClubAdmin): ?>

            <!-- EDIT EVENT MODAL -->
            <div
                class="event-modal <?= !empty($errors) ? 'is-open' : '' ?>"
                id="editEventModal"
                aria-hidden="<?= !empty($errors) ? 'false' : 'true' ?>"
            >

                <div class="event-modal-content">

                    <div class="event-modal-header">
                        <div>
                            <h2>Edit Event</h2>
                            <p>Update your event information.</p>
                        </div>

                        <button
                            type="button"
                            class="event-modal-close"
                            id="closeEditEvent"
                            aria-label="Close modal"
                        >
                            &times;
                        </button>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="event-form-errors">
                            <?php foreach ($errors as $error): ?>
                                <p><?= escapeHTML($error) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= escapeHTML($_SESSION['csrf_token']) ?>"
                        >

                        <div class="event-form-group">
                            <label for="event_name">Event Name</label>

                            <input
                                type="text"
                                id="event_name"
                                name="event_name"
                                value="<?= escapeHTML($formEventName) ?>"
                                required
                            >
                        </div>

                        <div class="event-form-group">
                            <label for="description">Description</label>

                            <textarea
                                id="description"
                                name="description"
                                rows="4"
                            ><?= escapeHTML($formDescription) ?></textarea>
                        </div>

                        <div class="event-form-row">

                            <div class="event-form-group">
                                <label for="start_date">Start Date</label>

                                <input
                                    type="date"
                                    id="start_date"
                                    name="start_date"
                                    value="<?= escapeHTML($formStartDate) ?>"
                                    required
                                >
                            </div>

                            <div class="event-form-group">
                                <label for="end_date">End Date</label>

                                <input
                                    type="date"
                                    id="end_date"
                                    name="end_date"
                                    value="<?= escapeHTML($formEndDate) ?>"
                                    required
                                >
                            </div>

                        </div>

                        <div class="event-form-group">
                            <label for="location">Location</label>

                            <input
                                type="text"
                                id="location"
                                name="location"
                                value="<?= escapeHTML($formLocation) ?>"
                            >
                        </div>

                        <div class="event-cancel-option">
                            <label>
                                <input
                                    type="checkbox"
                                    name="cancel_event"
                                    value="1"
                                    <?= $eventStatus === 'cancelled' ? 'checked' : '' ?>
                                >

                                <span>Mark this event as cancelled</span>
                            </label>

                            <p>
                                Uncheck this option to restore the event.
                                Its status will then follow the event dates.
                            </p>
                        </div>

                        <div class="event-modal-actions">
                            <button
                                type="button"
                                class="event-cancel-btn"
                                id="cancelEditEvent"
                            >
                                Cancel
                            </button>

                            <button
                                type="submit"
                                class="event-save-btn"
                            >
                                Save Changes
                            </button>
                        </div>

                    </form>

                </div>

            </div>

        <?php endif; ?>

    <?php endif; ?>

</main>

<?php pageFooter(); ?>

</body>
</html>