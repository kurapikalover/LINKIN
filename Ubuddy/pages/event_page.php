
<?php
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../php/shared_functions.php';
require_once __DIR__ . '/../php/event_functions.php';
require_once __DIR__ . '/../php/event_queries.php';

$events = [];
$error = '';

try {
    $events = getAllEvents($pdo);
} catch (PDOException $e) {
    error_log('Failed to load events: ' . $e->getMessage());
    $error = 'Unable to load events right now. Please try again later.';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ÜBuddy | Events</title>

    <?php pageIcons(); ?>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/shared.css">
    <link rel="stylesheet" href="../css/event_page.css">
    <link rel="stylesheet" href="../css/filter.css">
</head>

<body>

    <?php pageHeader(); ?>

    <div class="sheet">

        <main class="page-content">

            <div class="events-page">

                <?php backLink('homepage.php', 'Home'); ?>

                <h1 class="events-title">Events</h1>

                <!-- Search bar -->
                <div class="search-bar">

                    <div class="search-input">

                        <span class="material-symbols-outlined search-icon">
                            search
                        </span>

                        <input
                            type="text"
                            id="searchInput"
                            placeholder="Search events or clubs"
                            aria-label="Search events or clubs"
                        >

                    </div>

                    <button
                        type="button"
                        id="toggleFilter"
                        class="filter-btn material-symbols-outlined"
                        title="Show filters"
                        aria-label="Show filters"
                        aria-expanded="false"
                        aria-controls="filterPanel"
                    >
                        filter_alt
                    </button>

                </div>

                <!-- Filters -->
                <?php
                $filterPage = 'events';
                require __DIR__ . '/../php/filter.php';
                ?>

                <!-- Event list -->
                <?php if ($error !== ''): ?>

                    <p class="event-message">
                        <?= escapeHtml($error) ?>
                    </p>

                <?php elseif (empty($events)): ?>

                    <p class="event-message">
                        No events available at the moment.
                    </p>

                <?php else: ?>

                    <div class="event-grid">

                        <?php foreach ($events as $event): ?>

                            <a
                                href="event_detail.php?id=<?= urlencode((string) $event['event_id']) ?>"
                                class="event-card"
                                data-search="<?= escapeHtml(
                                    $event['event_name'] . ' ' .
                                    ($event['club_name'] ?? '')
                                ) ?>"
                                data-status="<?= escapeHtml(
                                    strtolower($event['event_status'] ?? '')
                                ) ?>"
                                data-start-date="<?= escapeHtml(
                                    $event['start_date'] ?? ''
                                ) ?>"
                                aria-label="View details for <?= escapeHtml($event['event_name']) ?>"
                            >

                                <!-- Event image -->
                                <div class="event-card-image">

                                    <?php if (!empty($event['event_image'])): ?>

                                        <img
                                            src="<?= escapeHtml($event['event_image']) ?>"
                                            alt="<?= escapeHtml($event['event_name']) ?>"
                                        >

                                    <?php else: ?>

                                        <div class="event-image-placeholder">
                                            Event Image
                                        </div>

                                    <?php endif; ?>

                                </div>

                                <!-- Event information -->
                                <div class="event-card-content">

                                    <h2 class="event-name">
                                        <?= escapeHtml($event['event_name']) ?>
                                    </h2>

                                    <p class="event-club-name">
                                        <?= escapeHtml(
                                            $event['club_name'] ?? 'Unknown club'
                                        ) ?>
                                    </p>

                                    <div class="event-status-row">

                                        <span class="event-status <?= escapeHtml(
                                            eventStatusClass($event['event_status'] ?? '')
                                        ) ?>">
                                            <?= escapeHtml(
                                                ucfirst($event['event_status'] ?? 'Unknown')
                                            ) ?>
                                        </span>

                                    </div>

                                </div>

                            </a>

                        <?php endforeach; ?>

                    </div>

                    <!-- Displayed when search or filters find no events -->
                    <p id="noResults" class="event-message" hidden>
                        No matching events found.
                    </p>

                <?php endif; ?>

            </div>

        </main>

    </div>

    <?php pageFooter(); ?>

    <script src="../js/event_page.js"></script>

</body>
</html>
