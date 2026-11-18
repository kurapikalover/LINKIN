
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/club_functions.php';
require_once __DIR__ . '/../php/club_queries.php';
require_once __DIR__ . '/../php/shared_functions.php';
require_once __DIR__ . '/../php/com_admin_funct.php';

// Get logged-in user
$userId = $_SESSION['user_id'] ?? null;

// Get clubs the user is a Committee Admin of
$adminClubs = [];

if ($userId !== null) {
    $adminClubs = getAdminClubs($pdo, $userId);
}

// Read club ID from URL
$clubId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$club = false;
$socialLinks = [];
$categories = [];
$isClubAdmin = false;

$errorMessage = '';
$successMessage = '';

// Check whether the user is an admin of this specific club
if ($clubId && $clubId > 0) {
    foreach ($adminClubs as $adminClub) {
        if ((int)$adminClub['club_id'] === (int)$clubId) {
            $isClubAdmin = true;
            break;
        }
    }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Load club details
if ($clubId && $clubId > 0) {
    $club = getClubById($pdo, $clubId);

    if ($club) {
        $socialLinks = getClubSocialLinks($pdo, $clubId);
    }
}

// Load categories for the edit form
if ($isClubAdmin && $club) {
    $categoryStmt = $pdo->query("
        SELECT category_id, category_name
        FROM public.category
        ORDER BY category_name
    ");

    $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);
}


// ======================================
// HANDLE CLUB DETAILS UPDATE
// ======================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $club) {

    // Check Committee Admin permission again
    if (!$isClubAdmin) {
        http_response_code(403);
        exit('You are not authorised to edit this club.');
    }

    // Check CSRF token
    $submittedToken = $_POST['csrf_token'] ?? '';

    if (
        !is_string($submittedToken) ||
        !hash_equals($_SESSION['csrf_token'], $submittedToken)
    ) {
        $errorMessage = 'Invalid request. Please refresh the page and try again.';
    }

    // Get submitted values
    $clubName = trim($_POST['club_name'] ?? '');
    $categoryId = filter_var(
        $_POST['category_id'] ?? '',
        FILTER_VALIDATE_INT
    );

    $description = trim($_POST['description'] ?? '');
    $activities = trim($_POST['activities'] ?? '');
    $meetingFrequency = trim($_POST['meeting_frequency'] ?? '');
    $expRequirement = trim($_POST['exp_requirement'] ?? '');

    $membershipFeeInput = trim($_POST['membership_fee'] ?? '');
    $feeCurrency = strtoupper(trim($_POST['fee_currency'] ?? ''));

    // Validate club name
    if ($errorMessage === '' && $clubName === '') {
        $errorMessage = 'Please enter a club name.';
    }

    // Validate category
    $validCategoryIds = array_column($categories, 'category_id');

    if (
        $errorMessage === '' &&
        (!$categoryId || !in_array($categoryId, $validCategoryIds))
    ) {
        $errorMessage = 'Please select a valid category.';
    }

    // Validate membership fee
    $membershipFee = null;

    if ($errorMessage === '' && $membershipFeeInput !== '') {

        if (
            !is_numeric($membershipFeeInput) ||
            (float)$membershipFeeInput < 0
        ) {
            $errorMessage = 'Please enter a valid membership fee.';
        } else {
            $membershipFee = (float)$membershipFeeInput;
        }
    }

    // Validate currency
    if (
        $errorMessage === '' &&
        !preg_match('/^[A-Z]{3}$/', $feeCurrency)
    ) {
        $errorMessage = 'Please enter a valid three-letter currency code.';
    }

    // Get social media links
    $submittedSocialLinks = $_POST['social_links'] ?? [];

    $allowedPlatforms = [
        'instagram',
        'tiktok',
        'facebook',
        'youtube',
        'twitter',
        'linkedin',
        'whatsapp'
    ];

    $cleanSocialLinks = [];

    if (!is_array($submittedSocialLinks)) {
        $errorMessage = 'Invalid social media information.';
    }

    if ($errorMessage === '') {
        foreach ($submittedSocialLinks as $social) {

            if (!is_array($social)) {
                $errorMessage = 'Invalid social media information.';
                break;
            }

            $platform = strtolower(trim($social['platform'] ?? ''));
            $socialName = trim($social['social_media_name'] ?? '');
            $accountUrl = trim($social['account_url'] ?? '');

            // Skip completely empty rows
            if ($platform === '' && $socialName === '' && $accountUrl === '') {
                continue;
            }

            if (!in_array($platform, $allowedPlatforms, true)) {
                $errorMessage = 'Please select a valid social media platform.';
                break;
            }

            if (
                $accountUrl === '' ||
                !filter_var($accountUrl, FILTER_VALIDATE_URL)
            ) {
                $errorMessage = 'Please enter a valid social media URL.';
                break;
            }

            $urlScheme = strtolower(
                parse_url($accountUrl, PHP_URL_SCHEME) ?? ''
            );

            if (!in_array($urlScheme, ['http', 'https'], true)) {
                $errorMessage = 'Social media links must use HTTP or HTTPS.';
                break;
            }

            $cleanSocialLinks[] = [
                'platform' => $platform,
                'social_media_name' => $socialName,
                'account_url' => $accountUrl
            ];
        }
    }

    // Save changes
    if ($errorMessage === '') {

        try {
            $pdo->beginTransaction();

            // Update club information
            $updateClub = $pdo->prepare("
                UPDATE public.club
                SET
                    club_name = :club_name,
                    category_id = :category_id,
                    description = :description,
                    activities = :activities,
                    meeting_frequency = :meeting_frequency,
                    exp_requirement = :exp_requirement,
                    membership_fee = :membership_fee,
                    fee_currency = :fee_currency
                WHERE club_id = :club_id
            ");

            $updateClub->execute([
                'club_name' => $clubName,
                'category_id' => $categoryId,
                'description' => $description,
                'activities' => $activities,
                'meeting_frequency' => $meetingFrequency,
                'exp_requirement' => $expRequirement,
                'membership_fee' => $membershipFee,
                'fee_currency' => $feeCurrency,
                'club_id' => $clubId
            ]);

            // Replace the club's social media links
            $deleteSocial = $pdo->prepare("
                DELETE FROM public.club_social_media
                WHERE club_id = :club_id
            ");

            $deleteSocial->execute([
                'club_id' => $clubId
            ]);

            if (!empty($cleanSocialLinks)) {

                $insertSocial = $pdo->prepare("
                    INSERT INTO public.club_social_media
                    (
                        club_id,
                        platform,
                        social_media_name,
                        account_url
                    )
                    VALUES
                    (
                        :club_id,
                        :platform,
                        :social_media_name,
                        :account_url
                    )
                ");

                foreach ($cleanSocialLinks as $social) {
                    $insertSocial->execute([
                        'club_id' => $clubId,
                        'platform' => $social['platform'],
                        'social_media_name' => $social['social_media_name'],
                        'account_url' => $social['account_url']
                    ]);
                }
            }

            $pdo->commit();

            // Redirect to prevent form resubmission
            header("Location: club.php?id=" . $clubId . "&updated=1");
            exit;

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log($e->getMessage());
            $errorMessage = 'Something went wrong while saving the club details.';
        }
    }
}


// Show success message after redirect
if (isset($_GET['updated']) && $_GET['updated'] === '1') {
    $successMessage = 'Club details have been updated successfully.';
}

// Reload latest club information
if ($clubId && $clubId > 0) {
    $club = getClubById($pdo, $clubId);

    if ($club) {
        $socialLinks = getClubSocialLinks($pdo, $clubId);
    }
}

// Prepare displayed activities
$activities = [];

if ($club) {
    $activities = getClubActivities($club['activities']);
}

// Use submitted values again if validation fails
$formClub = $club ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $errorMessage !== '') {
    $formClub = array_merge($formClub, [
        'club_name' => $_POST['club_name'] ?? '',
        'category_id' => $_POST['category_id'] ?? '',
        'description' => $_POST['description'] ?? '',
        'activities' => $_POST['activities'] ?? '',
        'meeting_frequency' => $_POST['meeting_frequency'] ?? '',
        'exp_requirement' => $_POST['exp_requirement'] ?? '',
        'membership_fee' => $_POST['membership_fee'] ?? '',
        'fee_currency' => $_POST['fee_currency'] ?? 'BND'
    ]);

    $socialLinks = $_POST['social_links'] ?? [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ÜBuddy</title>

    <?php pageIcons(); ?>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/shared.css">
    <link rel="stylesheet" href="../css/club.css">

</head>

<body>

<?php
pageHeader(
    $_SESSION['name'] ?? 'Student',
    $userId,
    $adminClubs
);
?>

<div class="sheet">
    <main class="page-content">

        <?php
        $from = $_GET['from'] ?? '';

        $backUrl = 'homepage.php';
        $backLabel = 'Back to Home';

        if ($from === 'clubs') {
            $backUrl = 'club_page.php';
            $backLabel = 'Back to Clubs';
        } elseif ($from === 'com-admin') {
            $clubIdForBack = (int) ($_GET['admin_club_id'] ?? 0);

            $backUrl = 'com-admin.php?id=' . $clubIdForBack;
            $backLabel = 'Back to Committee Admin';
        }

        backLink($backUrl, $backLabel);
        ?>

        <?php if (!$club): ?>

            <div class="club-layout">
                <section class="club-information">
                    <h1>Club not found</h1>
                    <p>This club does not exist or the link is invalid.</p>
                    <a href="club_page.php">Browse all clubs</a>
                </section>
            </div>

        <?php else: ?>

            <?php
            $categoryName = $club['category_name'] ?? 'Uncategorised';
            $categoryClass = getClubCategoryClass($categoryName);
            ?>

            <?php if ($successMessage !== ''): ?>
                <div class="club-alert success-alert">
                    <?= escapeHTML($successMessage) ?>
                </div>
            <?php endif; ?>

            <?php if ($errorMessage !== ''): ?>
                <div class="club-alert error-alert">
                    <?= escapeHTML($errorMessage) ?>
                </div>
            <?php endif; ?>

            <div class="club-layout">

                <!-- CLUB INFORMATION -->
                <section class="club-information">

                    <div class="club-heading">

                        <div>
                            <h1><?= escapeHTML($club['club_name']) ?></h1>

                            <p class="category <?= escapeHTML($categoryClass) ?>">
                                <?= escapeHTML($categoryName) ?>
                            </p>
                        </div>

                        <?php if ($isClubAdmin): ?>
                            <button
                                type="button"
                                class="edit-club-button"
                                id="openEditModal"
                            >
                                <i class="fa-solid fa-pen"></i>
                                Edit Club Details
                            </button>
                        <?php endif; ?>

                    </div>

                    <div class="club-circles">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>

                    <!-- ABOUT -->
                    <div class="info-section">
                        <h2>About</h2>

                        <p>
                            <?= nl2br(escapeHTML(
                                $club['description']
                                ?: 'No description available yet.'
                            )) ?>
                        </p>
                    </div>

                    <!-- ACTIVITIES -->
                    <div class="info-section">
                        <h2>Activities</h2>

                        <?php if (!empty($activities)): ?>
                            <ul>
                                <?php foreach ($activities as $activity): ?>
                                    <li><?= escapeHTML($activity) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No activities listed yet.</p>
                        <?php endif; ?>
                    </div>

                    <!-- MEETING FREQUENCY -->
                    <div class="info-section">
                        <h2>Meeting Frequency</h2>
                        <p>
                            <?= escapeHTML(
                                $club['meeting_frequency'] ?? 'Not specified'
                            ) ?>
                        </p>
                    </div>

                    <!-- EXPERIENCE REQUIREMENT -->
                    <div class="info-section">
                        <h2>Experience Requirement</h2>
                        <p>
                            <?= escapeHTML(
                                $club['exp_requirement'] ?? 'Not specified'
                            ) ?>
                        </p>
                    </div>

                    <!-- MEMBERSHIP FEE -->
                    <div class="info-section">
                        <h2>Fee</h2>
                        <p>
                            <?= escapeHTML(formatClubFee(
                                $club['membership_fee'],
                                $club['fee_currency']
                            )) ?>
                        </p>
                    </div>

                    <!-- SOCIAL MEDIA -->
                    <?php if (!empty($socialLinks)): ?>
                        <div class="info-section">
                            <h2>Social Media</h2>

                            <div class="social-buttons">

                                <?php foreach ($socialLinks as $social): ?>
                                    <a
                                        class="social-button"
                                        href="<?= escapeHTML($social['account_url']) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <i
                                            class="<?= escapeHTML(
                                                getClubSocialIcon($social['platform'])
                                            ) ?>"
                                            aria-hidden="true"
                                        ></i>

                                        <span class="social-button-text">
                                            <span class="social-platform">
                                                <?= escapeHTML($social['platform']) ?>
                                            </span>

                                            <?php if (!empty($social['social_media_name'])): ?>
                                                <span class="social-username">
                                                    <?= escapeHTML(
                                                        $social['social_media_name']
                                                    ) ?>
                                                </span>
                                            <?php endif; ?>
                                        </span>
                                    </a>
                                <?php endforeach; ?>

                            </div>
                        </div>
                    <?php endif; ?>

                </section>

                <!-- RIGHT SIDEBAR -->
                <aside class="club-sidebar">

                    <div class="club-image">
                        <span>Club Image</span>
                    </div>

                    <button
                        type="button"
                        class="join-button"
                        disabled
                        title="Application functionality has not been connected yet"
                    >
                        JOIN CLUB
                    </button>

                    <div class="contact-card">
                        <h2>Contact Information</h2>
                        <p>Contact information has not been added yet.</p>
                    </div>

                </aside>

            </div>


            <!-- ======================================
                 EDIT CLUB MODAL
            ======================================= -->

            <?php if ($isClubAdmin): ?>

                <div class="edit-modal-overlay" id="editClubModal">

                    <div
                        class="edit-modal"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="editModalTitle"
                    >

                        <div class="edit-modal-header">
                            <div>
                                <h2 id="editModalTitle">Edit Club Details</h2>
                                <p>Update your club's information.</p>
                            </div>

                            <button
                                type="button"
                                class="close-modal-button"
                                id="closeEditModal"
                                aria-label="Close"
                            >
                                &times;
                            </button>
                        </div>

                        <form method="POST" id="editClubForm">

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= escapeHTML($_SESSION['csrf_token']) ?>"
                            >

                            <div class="edit-modal-body">

                                <!-- CLUB NAME -->
                                <div class="form-group">
                                    <label for="club_name">Club Name</label>

                                    <input
                                        type="text"
                                        id="club_name"
                                        name="club_name"
                                        value="<?= escapeHTML($formClub['club_name'] ?? '') ?>"
                                        required
                                    >
                                </div>

                                <!-- CATEGORY -->
                                <div class="form-group">
                                    <label for="category_id">Category</label>

                                    <select
                                        id="category_id"
                                        name="category_id"
                                        required
                                    >
                                        <option value="">Select a category</option>

                                        <?php foreach ($categories as $category): ?>
                                            <option
                                                value="<?= escapeHTML($category['category_id']) ?>"
                                                <?= (string)($formClub['category_id'] ?? '') === (string)$category['category_id'] ? 'selected' : '' ?>
                                            >
                                                <?= escapeHTML($category['category_name']) ?>
                                            </option>
                                        <?php endforeach; ?>

                                    </select>
                                </div>

                                <!-- DESCRIPTION -->
                                <div class="form-group">
                                    <label for="description">About</label>

                                    <textarea
                                        id="description"
                                        name="description"
                                        rows="4"
                                    ><?= escapeHTML($formClub['description'] ?? '') ?></textarea>
                                </div>

                                <!-- ACTIVITIES -->
                                <div class="form-group">
                                    <label for="activities">Activities</label>

                                    <textarea
                                        id="activities"
                                        name="activities"
                                        rows="4"
                                        placeholder="Enter each activity on a new line"
                                    ><?= escapeHTML($formClub['activities'] ?? '') ?></textarea>

                                    <small>Enter each activity on a separate line.</small>
                                </div>

                                <!-- MEETING FREQUENCY -->
                                <div class="form-group">
                                    <label for="meeting_frequency">Meeting Frequency</label>

                                    <input
                                        type="text"
                                        id="meeting_frequency"
                                        name="meeting_frequency"
                                        value="<?= escapeHTML($formClub['meeting_frequency'] ?? '') ?>"
                                        placeholder="e.g. Every Friday"
                                    >
                                </div>

                                <!-- EXPERIENCE REQUIREMENT -->
                                <div class="form-group">
                                    <label for="exp_requirement">Experience Requirement</label>

                                    <textarea
                                        id="exp_requirement"
                                        name="exp_requirement"
                                        rows="2"
                                        placeholder="Describe any experience required"
                                    ><?= escapeHTML($formClub['exp_requirement'] ?? '') ?></textarea>
                                </div>

                                <!-- MEMBERSHIP FEE -->
                                <div class="form-group">
                                    <label>Membership Fee</label>

                                    <div class="fee-input-group">

                                        <input
                                            type="number"
                                            name="membership_fee"
                                            min="0"
                                            step="0.01"
                                            value="<?= escapeHTML($formClub['membership_fee'] ?? '') ?>"
                                            placeholder="0.00"
                                        >

                                        <input
                                            type="text"
                                            name="fee_currency"
                                            maxlength="3"
                                            value="<?= escapeHTML($formClub['fee_currency'] ?? 'BND') ?>"
                                            placeholder="BND"
                                        >

                                    </div>
                                </div>

                                <!-- SOCIAL MEDIA -->
                                <div class="form-group">

                                    <div class="social-form-heading">
                                        <label>Social Media Links</label>

                                        <button
                                            type="button"
                                            class="add-social-button"
                                            id="addSocialLink"
                                        >
                                            + Add Link
                                        </button>
                                    </div>

                                    <div id="socialLinksContainer">

                                        <?php foreach ($socialLinks as $index => $social): ?>

                                            <div class="social-edit-row">

                                                <select name="social_links[<?= $index ?>][platform]" required>
                                                    <?php
                                                    $platforms = [
                                                        'instagram',
                                                        'tiktok',
                                                        'facebook',
                                                        'youtube',
                                                        'twitter',
                                                        'linkedin',
                                                        'whatsapp'
                                                    ];
                                                    ?>

                                                    <?php foreach ($platforms as $platform): ?>
                                                        <option
                                                            value="<?= $platform ?>"
                                                            <?= strtolower($social['platform']) === $platform ? 'selected' : '' ?>
                                                        >
                                                            <?= ucfirst($platform) ?>
                                                        </option>
                                                    <?php endforeach; ?>

                                                </select>

                                                <input
                                                    type="text"
                                                    name="social_links[<?= $index ?>][social_media_name]"
                                                    value="<?= escapeHTML($social['social_media_name'] ?? '') ?>"
                                                    placeholder="Username"
                                                >

                                                <input
                                                    type="url"
                                                    name="social_links[<?= $index ?>][account_url]"
                                                    value="<?= escapeHTML($social['account_url'] ?? '') ?>"
                                                    placeholder="https://..."
                                                    required
                                                >

                                                <button
                                                    type="button"
                                                    class="remove-social-button"
                                                    aria-label="Remove social media link"
                                                >
                                                    &times;
                                                </button>

                                            </div>

                                        <?php endforeach; ?>

                                    </div>

                                    <p class="form-help">
                                        Add the social media accounts your club uses.
                                    </p>

                                </div>

                            </div>

                            <!-- MODAL FOOTER -->
                            <div class="edit-modal-footer">

                                <button
                                    type="button"
                                    class="cancel-edit-button"
                                    id="cancelEdit"
                                >
                                    Cancel
                                </button>

                                <button
                                    type="submit"
                                    class="save-edit-button"
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
</div>

<?php pageFooter(); ?>

<script src="../js/club.js"></script>

</body>
</html>
