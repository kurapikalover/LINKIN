
<?php
session_start();

require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/functions.php';

// Check whether the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Generate a CSRF token.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

// Allowed achievement types.
$allowedTypes = [
    'Academic',
    'Sports',
    'Leadership',
    'Community',
    'Arts',
    'Other'
];

// Handle form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Invalid request. Please refresh and try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = trim($_POST['achievement_type'] ?? '');
        $date = $_POST['achievement_date'] ?? '';

        // Validate the date.
        $parsedDate = DateTime::createFromFormat('!Y-m-d', $date);

        $validDate = $parsedDate !== false
            && $parsedDate->format('Y-m-d') === $date
            && $date <= date('Y-m-d');

        // Validate all required fields.
        if (
            $title === '' ||
            $description === '' ||
            mb_strlen($title) > 120 ||
            mb_strlen($description) > 1000 ||
            !in_array($type, $allowedTypes, true) ||
            !$validDate
        ) {
            $error = 'Please check all fields and try again.';
        } else {
            try {
                // Insert the achievement.
                // achievement_id is generated automatically.
                // achievement_status defaults to Pending.
                $stmt = $pdo->prepare(
                    'INSERT INTO achievement (
                        user_id,
                        achievement_title,
                        achievement_description,
                        achievement_date,
                        achievement_type
                    )
                    VALUES (
                        :user_id,
                        :title,
                        :description,
                        :achievement_date,
                        :achievement_type
                    )'
                );

                $stmt->execute([
                    'user_id' => $_SESSION['user_id'],
                    'title' => $title,
                    'description' => $description,
                    'achievement_date' => $date,
                    'achievement_type' => $type
                ]);

                $_SESSION['profile_message'] =
                    'Achievement submitted for review.';

                // Rotate the token after a successful submission.
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                header('Location: stu_profile.php');
                exit;

            } catch (PDOException $e) {
                error_log($e->getMessage());

                $error = 'Unable to submit the achievement right now.';
            }
        }
    }
}

// Escape values before displaying them.
function achievementEscape($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

pageHeader();
?>

<?php pageIcons(); ?>

<link rel="stylesheet" href="../css/shared.css">
<link rel="stylesheet" href="../css/base.css">
<link rel="stylesheet" href="../css/profile-common.css">
<link rel="stylesheet" href="../css/achievement_submit.css">

<main class="page-content">

    <h1 class="profile-page-title">Add Achievement</h1>

    <section class="profile-section">

        <p class="section-description">
            Provide the details of your achievement. Your submission
            will be reviewed before it is verified.
        </p>

        <?php if ($error !== ''): ?>
            <div class="notice error-notice" role="alert">
                <?= achievementEscape($error) ?>
            </div>
        <?php endif; ?>

        <form
            method="POST"
            action="achievement_submit.php"
            class="achievement-form"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= achievementEscape($_SESSION['csrf_token']) ?>"
            >

            <!-- ACHIEVEMENT TITLE -->
            <div class="form-group">
                <label for="title">Achievement Title *</label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    maxlength="120"
                    required
                    value="<?= achievementEscape(
                        $_POST['title'] ?? ''
                    ) ?>"
                >
            </div>

            <!-- ACHIEVEMENT TYPE -->
            <div class="form-group">
                <label for="achievement_type">Category *</label>

                <select
                    id="achievement_type"
                    name="achievement_type"
                    required
                >
                    <option value="">Select a category</option>

                    <?php foreach ($allowedTypes as $item): ?>
                        <option
                            value="<?= achievementEscape($item) ?>"
                            <?= (
                                ($_POST['achievement_type'] ?? '') === $item
                            ) ? 'selected' : '' ?>
                        >
                            <?= achievementEscape($item) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <!-- ACHIEVEMENT DATE -->
            <div class="form-group">
                <label for="achievement_date">Achievement Date *</label>

                <input
                    type="date"
                    id="achievement_date"
                    name="achievement_date"
                    max="<?= date('Y-m-d') ?>"
                    required
                    value="<?= achievementEscape(
                        $_POST['achievement_date'] ?? ''
                    ) ?>"
                >
            </div>

            <!-- DESCRIPTION -->
            <div class="form-group">
                <label for="description">Description *</label>

                <textarea
                    id="description"
                    name="description"
                    rows="5"
                    maxlength="1000"
                    required
                ><?= achievementEscape(
                    $_POST['description'] ?? ''
                ) ?></textarea>
            </div>

            <p class="form-note">
                Your submission will remain Pending until reviewed.
            </p>

            <!-- FORM ACTIONS -->
            <div class="form-actions">

                <button type="submit" class="btn">
                    Submit for Review
                </button>

                <a
                    href="stu_profile.php"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>

            </div>

        </form>

    </section>

</main>

<?php pageFooter(); ?>
