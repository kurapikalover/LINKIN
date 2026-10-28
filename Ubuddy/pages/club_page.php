
<?php
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/shared_functions.php';
require_once __DIR__ . '/../php/club_functions.php';
require_once __DIR__ . '/../php/club_queries.php';

$clubs = [];
$error = '';

try {
    $clubs = getAllClubs($pdo);
} catch (PDOException $e) {
    error_log('Failed to load clubs: ' . $e->getMessage());
    $error = 'Unable to load clubs right now. Please try again later.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ÜBuddy | Clubs</title>

    <?php pageIcons(); ?>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/shared.css">
    <link rel="stylesheet" href="../css/club_page.css">
    <link rel="stylesheet" href="../css/filter.css">
</head>

<body>

    <?php pageHeader(); ?>

    <main class="page-content">
        <div class="clubs-page">

            <?php backLink('homepage.php', 'Home'); ?>

            <h1 class="clubs-title">Clubs</h1>

            <div class="search-bar">
                <div class="search-input">
                    <span class="material-symbols-outlined search-icon">
                        search
                    </span>

                    <input
                        type="text"
                        id="searchInput"
                        placeholder="Search clubs"
                        aria-label="Search clubs"
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

            <?php
            $filterPage = 'clubs';
            require __DIR__ . '/../php/filter.php';
            ?>

            <?php if ($error !== ''): ?>

                <p class="club-message">
                    <?= escapeHtml($error) ?>
                </p>

            <?php elseif (empty($clubs)): ?>

                <p class="club-message">
                    No clubs available at the moment.
                </p>

            <?php else: ?>

                <div class="club-grid">

                    <?php foreach ($clubs as $club): ?>

                        <?php
                        $clubName = $club['club_name'];
                        $categoryName = $club['category_name']
                            ?? 'Uncategorised';

                        $categoryClass = getClubCategoryClass(
                            $club['category_name']
                        );
                        ?>

                        <a
                            href="club.php?id=<?= (int) $club['club_id'] ?>"
                            class="club-card"
                            data-category="<?= escapeHtml($categoryName) ?>"
                            data-search="<?= escapeHtml(
                                $clubName . ' ' . $categoryName
                            ) ?>"
                        >

                            <div class="club-card-image">
                                <div class="club-image-placeholder">
                                    Club Image
                                </div>
                            </div>

                            <div class="club-card-content">

                                <h2 class="club-name">
                                    <?= escapeHtml($clubName) ?>
                                </h2>

                                <p class="category <?= escapeHtml($categoryClass) ?>">
                                    <?= escapeHtml($categoryName) ?>
                                </p>

                            </div>

                        </a>

                    <?php endforeach; ?>

                </div>

                <p id="noResults" class="club-message" hidden>
                    No matching clubs found.
                </p>

            <?php endif; ?>

        </div>
    </main>

    <?php pageFooter(); ?>

    <script src="../js/club_page.js"></script>

</body>
</html>
