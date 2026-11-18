
<?php
$eventName = $event['event_name'] ?? 'Untitled Event';
$eventStatus = $event['event_status'] ?? 'Unknown';

$description = $event['description']
    ?: 'No description available yet.';

$location = $event['location']
    ?: 'Location not specified';

$clubName = $event['club_name']
    ?? 'Unknown club';

$startDate = $event['start_date'] ?? null;
$endDate = $event['end_date'] ?? null;
?>

<div class="event-layout">

    <!-- LEFT: EVENT INFORMATION -->
    <section class="event-information">

        <h1><?= escapeHTML($eventName) ?></h1>

        <!-- Event status -->
        <p class="event-status <?= escapeHTML(eventStatusClass($eventStatus)) ?>">
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
            <p><?= nl2br(escapeHTML($description)) ?></p>
        </div>

        <!-- Date -->
        <div class="event-info-section">
            <h2>Date</h2>
            <p>
                <?= escapeHTML(eventDateLabel($startDate, $endDate)) ?>
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
