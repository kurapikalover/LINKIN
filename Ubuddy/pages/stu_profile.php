
<?php
session_start();

require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../php/stu_profile_functions.php';

// Check whether the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

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

// Display the shared page header.
pageHeader($profile['name'], $profile['user_id']);
?>

<?php pageIcons(); ?>

<link rel="stylesheet" href="../css/base.css">
<link rel="stylesheet" href="../css/shared.css">
<link rel="stylesheet" href="../css/profile-common.css">
<link rel="stylesheet" href="../css/stu_profile.css">


<div class="profile-page">

    <div class="profile-page-title">
        <div>
            <h1>My Profile</h1>
            <p>Manage your personal information and achievements.</p>
        </div>

        <a href="stu_profile_edit.php" class="btn btn-secondary">
            Edit Profile
        </a>
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

                    <div class="profile-list-card">
                        <div>
                            <h3>
                                <?= profileEscape($club['club_name']) ?>
                            </h3>

                            <p>
                                <?= profileEscape($club['role']) ?>
                            </p>
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
                            profileStatusClass($application['status'])
                        ) ?>">
                            <?= profileEscape($application['status']) ?>
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

        <div class="section-heading">
            <div>
                <h2>My Achievements</h2>
                <p>Share your academic, club, and personal achievements.</p>
            </div>

            <a href="achievement_submit.php" class="btn">
                + Add Achievement
            </a>
        </div>

        <?php if (!empty($achievements)): ?>

            <div class="achievement-list">

                <?php foreach ($achievements as $achievement): ?>

                    <div class="achievement-card">

                        <div class="achievement-card-top">

                            <div>
                                <h3>
                                    <?= profileEscape(
                                        $achievement['achievement_title']
                                    ) ?>
                                </h3>

                                <p class="achievement-meta">
                                    <?= profileEscape(
                                        $achievement['achievement_type']
                                    ) ?>

                                    <?php if (!empty(
                                        $achievement['achievement_date']
                                    )): ?>
                                        ·
                                        <?= profileEscape(
                                            $achievement['achievement_date']
                                        ) ?>
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

                        <?php if (
                            strtolower(
                                trim($achievement['achievement_status'])
                            ) === 'rejected'
                            && !empty($achievement['review_feedback'])
                        ): ?>

                            <div class="review-reason">
                                <strong>Feedback:</strong>

                                <?= profileEscape(
                                    $achievement['review_feedback']
                                ) ?>
                            </div>

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

        <?php else: ?>

            <div class="empty-state">
                <span class="material-symbols-outlined empty-state-icon"
                      aria-hidden="true">emoji_events</span>

                <h3>No achievements yet</h3>
                <p>You have not submitted any achievements yet.</p>
                <p>Add an achievement to have it reviewed.</p>
            </div>

        <?php endif; ?>

    </section>

</div>

<?php
// Display the shared page footer.
pageFooter();
?>
