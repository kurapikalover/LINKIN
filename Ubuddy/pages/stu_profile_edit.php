
<?php
session_start();

require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../php/stu_profile_functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$userId = $_SESSION['user_id'];
$error = '';

$profile = getStudentProfile($pdo, $userId);

if (!$profile) {
    http_response_code(404);
    exit('Student profile not found.');
}

// Handle email updates.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $email = trim($_POST['email'] ?? '');

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Invalid request. Please refresh and try again.';
    } elseif (
        $email === '' ||
        strlen($email) > 254 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            updateStudentEmail($pdo, $userId, $email);

            $_SESSION['profile_message'] =
                'Your profile has been updated.';

            header('Location: stu_profile.php');
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'Unable to update your profile. Please try again.';
        }
    }
}

pageHeader($profile['name'], $profile['user_id']);
?>

<?php pageIcons(); ?>

<link rel="stylesheet" href="../css/base.css">
<link rel="stylesheet" href="../css/shared.css">
<link rel="stylesheet" href="../css/profile-common.css">
<link rel="stylesheet" href="../css/stu_profile_edit.css">

<main class="page-content profile-edit-page">

    <h1 class="profile-page-title">Edit Profile</h1>
    <p class="profile-page-subtitle">
        View your information and update your email address.
    </p>

    <section class="profile-section profile-edit-card">

        <?php if ($error !== ''): ?>
            <div class="notice error-notice" role="alert">
                <?= profileEscape($error) ?>
            </div>
        <?php endif; ?>

        <h2 class="section-heading">Personal Information</h2>

        <div class="information-grid">

            <div class="information-item">
                <span class="information-label">Full Name</span>
                <p><?= profileEscape($profile['name']) ?></p>
            </div>

            <div class="information-item">
                <span class="information-label">Student ID</span>
                <p><?= profileEscape($profile['user_id']) ?></p>
            </div>

            <div class="information-item">
                <span class="information-label">Faculty</span>
                <p><?= profileEscape($profile['faculty']) ?></p>
            </div>

        </div>

        <form
            method="POST"
            action="stu_profile_edit.php"
            class="profile-form"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= profileEscape($_SESSION['csrf_token']) ?>"
            >

            <div class="form-group">
                <label for="email">Email Address</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    maxlength="254"
                    required
                    value="<?= profileEscape(
                        $_POST['email'] ?? $profile['email']
                    ) ?>"
                >
            </div>

            <div class="form-actions">
                <a href="stu_profile.php" class="btn btn-secondary">
                    Cancel
                </a>

                <button type="submit" class="btn">
                    Save Changes
                </button>
            </div>

        </form>

    </section>

</main>

<?php pageFooter(); ?>
