
<?php
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/shared_functions.php';
require_once __DIR__ . '/../php/event_queries.php';
require_once __DIR__ . '/../php/event_functions.php';

// Get the event ID from the URL.
$eventId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Initialise the event variable.
$event = false;

// Retrieve the event if the ID is valid.
if ($eventId !== false && $eventId !== null && $eventId > 0) {
    $event = getEventById($pdo, $eventId);
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
</head>

<body>

<?php pageHeader(); ?>

<main class="page-content">

    <?php backLink('event_page.php', 'Back to Events'); ?>

    <?php if (!$event): ?>

        <!-- EVENT NOT FOUND -->
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

        <!-- EVENT DETAILS AND MAIN IMAGE -->
        <div class="event-layout">

            <!-- LEFT: EVENT INFORMATION -->
            <section class="event-information">

                <h1><?= escapeHTML($eventName) ?></h1>

                <!-- Event status -->
                <p class="event-status <?= escapeHTML($statusClass) ?>">
                    <?= escapeHTML(ucfirst($eventStatus)) ?>
                </p>

                <!-- Decorative circles -->
                <div class="event-circles" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <!-- Description -->
                <div class="event-info-section">
                    <h2>Description</h2>

                    <p>
                        <?= nl2br(escapeHTML($description)) ?>
                    </p>
                </div>

                <!-- Date -->
                <div class="event-info-section">
                    <h2>Date</h2>

                    <p>
                        <?= escapeHTML(
                            eventDateLabel($startDate, $endDate)
                        ) ?>
                    </p>
                </div>

                <!-- Location -->
                <div class="event-info-section">
                    <h2>Location</h2>

                    <p><?= escapeHTML($location) ?></p>
                </div>

                <!-- Hosting club -->
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

            <!-- RIGHT: MAIN IMAGE PLACEHOLDER -->
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

        <!-- EVENT GALLERY -->
        <?php require __DIR__ . '/event_gallery.php'; ?>

    <?php endif; ?>

</main>

<?php pageFooter(); ?>

</body>
</html>
