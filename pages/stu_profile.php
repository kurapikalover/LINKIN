
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

// Retrieve profile information.
$profile = getStudentProfile($pdo, $userId);

if (!$profile) {
    http_response_code(404);
    exit('Student profile not found.');
}

// Retrieve the student's related information.
$clubs = getStudentClubs($pdo, $userId);
$applications = getRecentApplications($pdo, $userId);
$achievements = getStudentAchievements($pdo, $userId);

// Only display approved and pending achievements.
$visibleAchievements = array_values(array_filter(
    $achievements,
    function ($achievement) {
        $status = strtolower(trim($achievement['achievement_status']));

        return in_array($status, ['approved', 'pending'], true);
    }
));

// Get unique achievement categories for the filter.
$achievementCategories = [];

foreach ($visibleAchievements as $achievement) {
    $category = trim($achievement['achievement_type']);

    if ($category !== '') {
        $achievementCategories[$category] = $category;
    }
}

sort($achievementCategories, SORT_NATURAL | SORT_FLAG_CASE);

// Display the shared page header.
pageHeader($profile['name'], $profile['user_id'],$adminClubs);
?>

<?php pageIcons(); ?>

<link rel="stylesheet" href="../css/base.css">
<link rel="stylesheet" href="../css/shared.css">
<link rel="stylesheet" href="../css/profile-common.css">
<link rel="stylesheet" href="../css/stu_profile.css">

<div class="profile-page">

    <!-- PAGE TITLE -->
    <div class="profile-page-title">
        <div>
            <h1>My Profile</h1>
        </div>
    </div>

    <!-- PROFILE SUMMARY -->
    <section class="profile-card">

        <div class="profile-avatar">
            <?= profileEscape(
                strtoupper(substr(trim($profile['name']), 0, 1))
            ) ?>
        </div>

        <div class="profile-summary">
            <h2><?= profileEscape($profile['name']) ?></h2>
            <p><?= profileEscape($profile['user_id']) ?></p>
            <p><?= profileEscape($profile['faculty']) ?></p>
        </div>

    </section>

    <!-- PERSONAL INFORMATION -->
    <section class="profile-section">

        <div class="section-heading">
            <h2>Personal Information</h2>
        </div>

        <div class="information-grid">

            <div class="information-item">
                <span>Full Name</span>
                <p><?= profileEscape($profile['name']) ?></p>
            </div>

            <div class="information-item">
                <span>Student ID</span>
                <p><?= profileEscape($profile['user_id']) ?></p>
            </div>

            <div class="information-item">
                <span>Email Address</span>
                <p><?= profileEscape($profile['email']) ?></p>
            </div>

            <div class="information-item">
                <span>Faculty</span>
                <p><?= profileEscape($profile['faculty']) ?></p>
            </div>

        </div>

    </section>

    <!-- MY CLUBS -->
    <section class="profile-section">

        <div class="section-heading">
            <h2>My Clubs</h2>
        </div>

        <?php if (!empty($clubs)): ?>

            <div class="profile-list">

                <?php foreach ($clubs as $club): ?>

                    <div class="profile-list-card club-card">

                        <!-- Club Logo -->
                        <div class="club-logo">
                            <span class="material-symbols-outlined"
                                  aria-hidden="true">groups</span>
                        </div>

                        <!-- Club Name and Position -->
                        <div class="club-info">
                            <h3>
                                <?= profileEscape($club['club_name']) ?>
                            </h3>

                            <p>
                                <?= profileEscape($club['role'] ?? 'Member') ?>
                            </p>
                        </div>

                        <!-- Joined Date and Status -->
                        <div class="club-details">

                            <span class="club-joined-date">
                                Joined:
                                <?= !empty($club['join_date'])
                                    ? date('d M Y', strtotime($club['join_date']))
                                    : 'N/A' ?>
                            </span>

                            <span class="club-status">
                                <?= profileEscape($club['membership_status']) ?>
                            </span>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty-state">
                <span class="material-symbols-outlined empty-state-icon"
                      aria-hidden="true">groups</span>

                <h3>No clubs yet</h3>
                <p>You have not joined any clubs yet.</p>
            </div>

        <?php endif; ?>

    </section>

    <!-- MY APPLICATIONS -->
    <section class="profile-section">

        <div class="section-heading">
            <h2>My Applications</h2>

            <a href="my_applications.php" class="text-link">
                View All
            </a>
        </div>

        <?php if (!empty($applications)): ?>

            <div class="profile-list">

                <?php foreach ($applications as $application): ?>

                    <div class="profile-list-card">

                        <div>
                            <h3>
                                <?= profileEscape($application['club_name']) ?>
                            </h3>

                            <p>Club Application</p>
                        </div>

                        <span class="achievement-status <?= profileEscape(
                            profileStatusClass($application['application_status'])
                        ) ?>">
                            <?= profileEscape($application['application_status']) ?>
                        </span>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty-state">
                <span class="material-symbols-outlined empty-state-icon"
                      aria-hidden="true">description</span>

                <h3>No applications yet</h3>
                <p>You have no club applications yet.</p>
            </div>

        <?php endif; ?>

    </section>

    <!-- MY ACHIEVEMENTS -->
    <section class="profile-section">

        <div class="section-heading achievement-heading">
            <div>
                <h2>My Achievements</h2>
                <p>Share your academic, club, and personal achievements.</p>
            </div>

            <a href="achievement_submit.php" class="btn">
                + Add Achievement
            </a>
        </div>

        <?php if (!empty($visibleAchievements)): ?>

            <!-- ACHIEVEMENT SEARCH AND FILTER -->
            <div class="achievement-controls">

                <div class="achievement-search">
                    <span class="material-symbols-outlined"
                          aria-hidden="true">search</span>

                    <input
                        type="text"
                        id="achievementSearch"
                        placeholder="Search achievements..."
                        aria-label="Search achievements"
                    >
                </div>

                <div class="achievement-filter">
                    <select
                        id="achievementCategory"
                        aria-label="Filter achievements by category"
                    >
                        <option value="all">All Categories</option>

                        <?php foreach ($achievementCategories as $category): ?>
                            <option value="<?= profileEscape(
                                strtolower($category)
                            ) ?>">
                                <?= profileEscape($category) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

            </div>

            <!-- ACHIEVEMENT COUNT -->
            <p class="achievement-count">
                Showing
                <span id="achievementVisibleCount">
                    <?= count($visibleAchievements) ?>
                </span>
                of <?= count($visibleAchievements) ?> achievements
            </p>

            <!-- SCROLLABLE ACHIEVEMENT LIST -->
            <div class="achievement-scroll">

                <div class="achievement-list" id="achievementList">

                    <?php foreach ($visibleAchievements as $achievement): ?>

                        <div
                            class="achievement-card"
                            data-title="<?= profileEscape(
                                strtolower($achievement['achievement_title'])
                            ) ?>"
                            data-category="<?= profileEscape(
                                strtolower($achievement['achievement_type'])
                            ) ?>"
                        >

                            <div class="achievement-card-top">

                                <div>
                                    <h3>
                                        <?= profileEscape(
                                            $achievement['achievement_title']
                                        ) ?>
                                    </h3>

                                    <p class="achievement-meta">
                                        <span class="achievement-category">
                                            <?= profileEscape(
                                                $achievement['achievement_type']
                                            ) ?>
                                        </span>

                                        <?php if (!empty(
                                            $achievement['achievement_date']
                                        )): ?>
                                            <span class="achievement-date">
                                                ·
                                                <?= profileEscape(
                                                    $achievement['achievement_date']
                                                ) ?>
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                </div>

                                <span class="achievement-status <?= profileEscape(
                                    profileStatusClass(
                                        $achievement['achievement_status']
                                    )
                                ) ?>">
                                    <?= profileEscape(
                                        $achievement['achievement_status']
                                    ) ?>
                                </span>

                            </div>

                            <?php if (!empty(
                                $achievement['achievement_description']
                            )): ?>

                                <p>
                                    <?= nl2br(profileEscape(
                                        $achievement['achievement_description']
                                    )) ?>
                                </p>

                            <?php endif; ?>

                            <?php if (!empty($achievement['evidence_url'])): ?>

                                <p class="achievement-evidence">
                                    <a
                                        href="<?= profileEscape(
                                            $achievement['evidence_url']
                                        ) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        View Evidence
                                    </a>
                                </p>

                            <?php endif; ?>

                        </div>

                    <?php endforeach; ?>

                </div>

                <!-- No search results message -->
                <div class="empty-state achievement-no-results"
                     id="achievementNoResults"
                     hidden>

                    <h3>No achievements found</h3>
                    <p>Try changing your search or category filter.</p>

                </div>

            </div>

        <?php else: ?>

            <div class="empty-state">
                <span class="material-symbols-outlined empty-state-icon"
                      aria-hidden="true">emoji_events</span>

                <h3>No achievements yet</h3>
                <p>You have not submitted any approved or pending achievements yet.</p>
                <p>Add an achievement to have it reviewed.</p>
            </div>

        <?php endif; ?>

    </section>

</div>

<!-- ACHIEVEMENT SEARCH AND FILTER SCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.getElementById('achievementSearch');
    const categoryFilter = document.getElementById('achievementCategory');
    const achievementCards = document.querySelectorAll(
        '.achievement-card'
    );
    const visibleCount = document.getElementById(
        'achievementVisibleCount'
    );
    const noResults = document.getElementById(
        'achievementNoResults'
    );
    const achievementList = document.getElementById(
        'achievementList'
    );

    // Stop if the student has no achievements.
    if (!searchInput || !categoryFilter || !achievementList) {
        return;
    }

    function filterAchievements() {

        const searchValue = searchInput.value
            .trim()
            .toLowerCase();

        const selectedCategory = categoryFilter.value;

        let visibleAchievements = 0;

        achievementCards.forEach(function (card) {

            const title = card.dataset.title;
            const category = card.dataset.category;

            const matchesSearch = title.includes(searchValue);

            const matchesCategory =
                selectedCategory === 'all' ||
                category === selectedCategory;

            if (matchesSearch && matchesCategory) {
                card.style.display = '';
                visibleAchievements++;
            } else {
                card.style.display = 'none';
            }

        });

        visibleCount.textContent = visibleAchievements;

        noResults.hidden = visibleAchievements !== 0;
        achievementList.hidden = visibleAchievements === 0;
    }

    searchInput.addEventListener('input', filterAchievements);
    categoryFilter.addEventListener('change', filterAchievements);

});
</script>

<?php
// Display the shared page footer.
pageFooter();
?>
