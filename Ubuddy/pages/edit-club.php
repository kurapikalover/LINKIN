
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../php/com_admin_funct.php';
require_once __DIR__ . '/../php/club_queries.php';


// =========================
// CHECK LOGIN
// =========================

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];


// =========================
// CHECK CLUB ADMIN ACCESS
// =========================

$adminClubs = getAdminClubs($pdo, $userId);

$allowedClubIds = array_map(
    'intval',
    array_column($adminClubs, 'club_id')
);

$clubId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$clubId || !in_array((int) $clubId, $allowedClubIds, true)) {
    header('Location: homepage.php');
    exit();
}

$clubId = (int) $clubId;


// =========================
// GET CLUB INFORMATION
// =========================

$clubStmt = $pdo->prepare(
    'SELECT *
     FROM public.club
     WHERE club_id = :club_id'
);

$clubStmt->execute([
    'club_id' => $clubId
]);

$club = $clubStmt->fetch(PDO::FETCH_ASSOC);

if (!$club) {
    header('Location: com-admin.php');
    exit();
}


// =========================
// GET CATEGORIES
// =========================

$categoryStmt = $pdo->query(
    'SELECT category_id, category_name
     FROM public.category
     ORDER BY category_name'
);

$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);


// =========================
// GET SOCIAL MEDIA LINKS
// =========================

$socialStmt = $pdo->prepare(
    'SELECT platform, social_media_name, account_url
     FROM public.club_social_media
     WHERE club_id = :club_id
     ORDER BY platform'
);

$socialStmt->execute([
    'club_id' => $clubId
]);

$socialLinks = $socialStmt->fetchAll(PDO::FETCH_ASSOC);


// =========================
// INITIALISE VARIABLES
// =========================

$errorMessage = '';
$successMessage = '';

$formData = $club;
$formSocialLinks = $socialLinks;


// =========================
// HANDLE FORM SUBMISSION
// =========================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $formData = [
        'club_name' => trim($_POST['club_name'] ?? ''),
        'category_id' => $_POST['category_id'] ?? '',
        'description' => trim($_POST['description'] ?? ''),
        'activities' => trim($_POST['activities'] ?? ''),
        'meeting_frequency' => trim($_POST['meeting_frequency'] ?? ''),
        'exp_requirement' => trim($_POST['exp_requirement'] ?? ''),
        'membership_fee' => trim($_POST['membership_fee'] ?? ''),
        'fee_currency' => strtoupper(trim($_POST['fee_currency'] ?? 'BND'))
    ];

    $formSocialLinks = $_POST['social_links'] ?? [];

    // Validate club name.
    if ($formData['club_name'] === '') {

        $errorMessage = 'Please enter the club name.';

    } elseif (mb_strlen($formData['club_name']) > 150) {

        $errorMessage = 'Club name must not exceed 150 characters.';

    }

    // Validate category.
    elseif (
        filter_var(
            $formData['category_id'],
            FILTER_VALIDATE_INT
        ) === false ||
        !in_array(
            (int) $formData['category_id'],
            array_map('intval', array_column($categories, 'category_id')),
            true
        )
    ) {

        $errorMessage = 'Please select a valid category.';

    }

    // Validate membership fee.
    elseif (
        $formData['membership_fee'] !== '' &&
        (
            !is_numeric($formData['membership_fee']) ||
            (float) $formData['membership_fee'] < 0
        )
    ) {

        $errorMessage = 'Please enter a valid membership fee.';

    }

    // Validate currency.
    elseif (!preg_match('/^[A-Z]{3}$/', $formData['fee_currency'])) {

        $errorMessage = 'Please enter a valid three-letter currency code.';

    }


    // Validate social media links.
    if ($errorMessage === '' && !is_array($formSocialLinks)) {
        $errorMessage = 'Invalid social media information.';
    }

    if ($errorMessage === '') {

        foreach ($formSocialLinks as $link) {

            if (!is_array($link)) {
                $errorMessage = 'Invalid social media information.';
                break;
            }

            $platform = trim($link['platform'] ?? '');
            $accountName = trim($link['social_media_name'] ?? '');
            $url = trim($link['account_url'] ?? '');

            // Ignore completely empty rows.
            if (
                $platform === '' &&
                $accountName === '' &&
                $url === ''
            ) {
                continue;
            }

            if ($platform === '' || $url === '') {
                $errorMessage = 'Please provide both a platform and URL for each social media link.';
                break;
            }

            if (
                !filter_var($url, FILTER_VALIDATE_URL) ||
                !in_array(
                    strtolower((string) parse_url($url, PHP_URL_SCHEME)),
                    ['http', 'https'],
                    true
                )
            ) {
                $errorMessage = 'Please enter a valid social media URL.';
                break;
            }
        }
    }


    // =========================
    // SAVE CHANGES
    // =========================

    if ($errorMessage === '') {

        try {

            $pdo->beginTransaction();

            // Update club information.
            $updateStmt = $pdo->prepare(
                'UPDATE public.club
                 SET
                    club_name = :club_name,
                    category_id = :category_id,
                    description = :description,
                    activities = :activities,
                    meeting_frequency = :meeting_frequency,
                    exp_requirement = :exp_requirement,
                    membership_fee = :membership_fee,
                    fee_currency = :fee_currency
                 WHERE club_id = :club_id'
            );

            $updateStmt->execute([
                'club_name' => $formData['club_name'],
                'category_id' => (int) $formData['category_id'],
                'description' => $formData['description'],
                'activities' => $formData['activities'],
                'meeting_frequency' => $formData['meeting_frequency'],
                'exp_requirement' => $formData['exp_requirement'],
                'membership_fee' => (
                    $formData['membership_fee'] === ''
                    ? null
                    : $formData['membership_fee']
                ),
                'fee_currency' => $formData['fee_currency'],
                'club_id' => $clubId
            ]);


            // Remove existing social media records.
            $deleteSocialStmt = $pdo->prepare(
                'DELETE FROM public.club_social_media
                 WHERE club_id = :club_id'
            );

            $deleteSocialStmt->execute([
                'club_id' => $clubId
            ]);


            // Insert updated social media records.
            $insertSocialStmt = $pdo->prepare(
                'INSERT INTO public.club_social_media
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
                    )'
            );

            foreach ($formSocialLinks as $link) {

                $platform = trim($link['platform'] ?? '');
                $accountName = trim($link['social_media_name'] ?? '');
                $url = trim($link['account_url'] ?? '');

                if ($platform === '' && $url === '') {
                    continue;
                }

                $insertSocialStmt->execute([
                    'club_id' => $clubId,
                    'platform' => strtolower($platform),
                    'social_media_name' => $accountName,
                    'account_url' => $url
                ]);
            }

            $pdo->commit();

            // Redirect to prevent duplicate submissions.
            header(
                'Location: edit-club.php?id=' .
                $clubId .
                '&success=1'
            );

            exit();

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log($e->getMessage());

            $errorMessage = 'Unable to save your changes. Please try again.';
        }
    }
}


// =========================
// SUCCESS MESSAGE
// =========================

if (isset($_GET['success'])) {
    $successMessage = 'Club details have been updated successfully!';
}


// =========================
// HELPER
// =========================

function e($value)
{
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
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ÜBuddy - Edit Club Details</title>

    <?php pageIcons(); ?>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/shared.css">
    <link rel="stylesheet" href="../css/edit-club.css">

    <script src="../js/edit-club.js" defer></script>

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

    <?php backLink('com-admin.php?id=' . $clubId, 'Back to Dashboard'); ?>

    <div class="edit-club-page">

        <h1>Edit Club Details</h1>

        <p class="edit-club-subtitle">
            Update your club's information.
        </p>


        <?php if ($errorMessage !== ''): ?>

            <div class="form-message error-message">
                <?= e($errorMessage) ?>
            </div>

        <?php endif; ?>


        <?php if ($successMessage !== ''): ?>

            <div class="form-message success-message">
                <?= e($successMessage) ?>
            </div>

        <?php endif; ?>


        <form
            method="POST"
            action="edit-club.php?id=<?= $clubId ?>"
            id="editClubForm"
        >


            <!-- CLUB INFORMATION -->

            <section class="edit-section">

                <h2>Club Information</h2>

                <div class="form-group">

                    <label for="clubName">
                        Club Name
                    </label>

                    <input
                        type="text"
                        id="clubName"
                        name="club_name"
                        maxlength="150"
                        value="<?= e($formData['club_name']) ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="category">
                        Club Category
                    </label>

                    <select
                        id="category"
                        name="category_id"
                        required
                    >

                        <option value="">
                            Select a category
                        </option>

                        <?php foreach ($categories as $category): ?>

                            <option
                                value="<?= (int) $category['category_id'] ?>"
                                <?= (
                                    (string) $formData['category_id'] ===
                                    (string) $category['category_id']
                                ) ? 'selected' : '' ?>
                            >
                                <?= e($category['category_name']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

            </section>


            <!-- ABOUT THE CLUB -->

            <section class="edit-section">

                <h2>About the Club</h2>

                <div class="form-group">

                    <label for="description">
                        Club Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        placeholder="Describe your club..."
                    ><?= e($formData['description']) ?></textarea>

                </div>


                <div class="form-group">

                    <label for="activities">
                        Club Activities
                    </label>

                    <textarea
                        id="activities"
                        name="activities"
                        rows="5"
                        placeholder="Enter each activity on a separate line."
                    ><?= e($formData['activities']) ?></textarea>

                    <small>
                        Enter each activity on a new line.
                    </small>

                </div>

            </section>


            <!-- MEMBERSHIP INFORMATION -->

            <section class="edit-section">

                <h2>Membership Information</h2>

                <div class="form-group">

                    <label for="meetingFrequency">
                        Meeting Frequency
                    </label>

                    <input
                        type="text"
                        id="meetingFrequency"
                        name="meeting_frequency"
                        value="<?= e($formData['meeting_frequency']) ?>"
                        placeholder="e.g. Weekly, Monthly"
                    >

                </div>


                <div class="form-group">

                    <label for="experienceRequirement">
                        Experience Requirement
                    </label>

                    <textarea
                        id="experienceRequirement"
                        name="exp_requirement"
                        rows="3"
                        placeholder="Describe the experience required..."
                    ><?= e($formData['exp_requirement']) ?></textarea>

                </div>


                <div class="fee-fields">

                    <div class="form-group">

                        <label for="membershipFee">
                            Membership Fee
                        </label>

                        <input
                            type="number"
                            id="membershipFee"
                            name="membership_fee"
                            min="0"
                            step="0.01"
                            value="<?= e($formData['membership_fee']) ?>"
                            placeholder="0.00"
                        >

                    </div>


                    <div class="form-group">

                        <label for="feeCurrency">
                            Currency
                        </label>

                        <input
                            type="text"
                            id="feeCurrency"
                            name="fee_currency"
                            maxlength="3"
                            value="<?= e($formData['fee_currency'] ?? 'BND') ?>"
                            placeholder="BND"
                        >

                    </div>

                </div>

            </section>


            <!-- SOCIAL MEDIA -->

            <section class="edit-section">

                <div class="section-heading">

                    <div>
                        <h2>Social Media</h2>

                        <p>
                            Manage your club's social media accounts.
                        </p>
                    </div>

                    <button
                        type="button"
                        id="addSocialLink"
                        class="add-social-btn"
                    >
                        + Add Link
                    </button>

                </div>


                <div id="socialLinksContainer">

                    <?php if (!empty($formSocialLinks)): ?>

                        <?php foreach ($formSocialLinks as $index => $social): ?>

                            <div class="social-link-row">

                                <div class="form-group">

                                    <label>Platform</label>

                                    <select
                                        name="social_links[<?= $index ?>][platform]"
                                    >

                                        <option value="">Select platform</option>

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

                                        if (
                                            !in_array(
                                                strtolower($social['platform']),
                                                $platforms,
                                                true
                                            )
                                        ) {
                                            $platforms[] = strtolower($social['platform']);
                                        }
                                        ?>

                                        <?php foreach ($platforms as $platform): ?>

                                            <option
                                                value="<?= e($platform) ?>"
                                                <?= strtolower($social['platform']) === $platform ? 'selected' : '' ?>
                                            >
                                                <?= e(ucfirst($platform)) ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>


                                <div class="form-group">

                                    <label>Account Name</label>

                                    <input
                                        type="text"
                                        name="social_links[<?= $index ?>][social_media_name]"
                                        value="<?= e($social['social_media_name']) ?>"
                                        placeholder="@username"
                                    >

                                </div>


                                <div class="form-group">

                                    <label>Account URL</label>

                                    <input
                                        type="url"
                                        name="social_links[<?= $index ?>][account_url]"
                                        value="<?= e($social['account_url']) ?>"
                                        placeholder="https://..."
                                    >

                                </div>


                                <button
                                    type="button"
                                    class="remove-social-btn"
                                >
                                    Remove
                                </button>

                            </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>


                <p class="social-empty-message"
                   id="socialEmptyMessage"
                   <?= !empty($formSocialLinks) ? 'hidden' : '' ?>
                >
                    No social media links added yet.
                </p>

            </section>


            <!-- FORM ACTIONS -->

            <div class="edit-form-actions">

                <a
                    href="com-admin.php?id=<?= $clubId ?>"
                    class="cancel-btn"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="save-btn"
                >
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</main>


<?php pageFooter(); ?>

</body>
</html>
