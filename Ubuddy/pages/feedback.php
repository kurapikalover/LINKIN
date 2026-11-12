
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

// Retrieve user information.
$adminClubs = getAdminClubs($pdo, $userId);
$profile = getStudentProfile($pdo, $userId);

if (!$profile) {
    http_response_code(404);
    exit('User profile not found.');
}

// Available feedback categories.
$feedbackCategories = [
    'Website Issues & Suggestions',
    'Club Complaints & Concerns',
    'Club Feedback'
];

$feedbackSent = false;
$feedbackId = null;
$errorMessage = '';

// Preserve submitted values.
$selectedCategory = $_POST['recipient_type'] ?? '';
$feedbackTitle = $_POST['feedback_title'] ?? '';
$feedbackText = $_POST['feedback_text'] ?? '';

// Retrieve clubs the student has joined.
// This includes current and previous memberships,
// provided previous membership records are retained.
$clubStmt = $pdo->prepare(
    'SELECT DISTINCT c.club_id, c.club_name
     FROM membership m
     INNER JOIN club c ON m.club_id = c.club_id
     WHERE m.user_id = :user_id
     ORDER BY c.club_name'
);

$clubStmt->execute([
    'user_id' => $userId
]);

$studentClubs = $clubStmt->fetchAll(PDO::FETCH_ASSOC);

$hasStudentClubs = !empty($studentClubs);

// Handle feedback submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $feedbackTitle = trim($feedbackTitle);
    $feedbackText = trim($feedbackText);
    $selectedCategory = trim($selectedCategory);

    $recipientClubId = null;

    // Validate feedback category.
    if (!in_array($selectedCategory, $feedbackCategories, true)) {
        $errorMessage = 'Please select a valid feedback category.';
    }

    // Validate feedback title.
    elseif ($feedbackTitle === '') {
        $errorMessage = 'Please enter a feedback title.';
    }

    elseif (mb_strlen($feedbackTitle) > 150) {
        $errorMessage = 'The feedback title must not exceed 150 characters.';
    }

    // Validate feedback description.
    elseif ($feedbackText === '') {
        $errorMessage = 'Please enter your feedback.';
    }

    // Validate club selection for Club Feedback.
    elseif ($selectedCategory === 'Club Feedback') {

        if (!$hasStudentClubs) {
            $errorMessage = 'You need to join a club before submitting Club Feedback.';
        } else {

            $recipientClubId = filter_var(
                $_POST['recipient_club_id'] ?? null,
                FILTER_VALIDATE_INT
            );

            $validClubIds = array_map(
                'intval',
                array_column($studentClubs, 'club_id')
            );

            if (
                $recipientClubId === false ||
                !in_array($recipientClubId, $validClubIds, true)
            ) {
                $errorMessage = 'Please select a club you have joined.';
            }
        }
    }

    // Continue only if the form is valid.
    if ($errorMessage === '') {

        $uploadedFiles = [];
        $savedFiles = [];

        try {

            // Upload folder.
            $uploadDirectory = dirname(__DIR__) . '/uploads/feedback/';

            // Allowed file types and extensions.
            $allowedTypes = [
                'image/jpeg'      => 'jpg',
                'image/png'       => 'png',
                'image/gif'       => 'gif',
                'image/webp'      => 'webp',
                'video/mp4'       => 'mp4',
                'video/webm'      => 'webm',
                'video/quicktime' => 'mov'
            ];

            $maxFileSize = 50 * 1024 * 1024;
            $maxFiles = 5;

            // Process uploaded files, if any.
            if (
                isset($_FILES['attachments']) &&
                is_array($_FILES['attachments']['name'])
            ) {

                $fileCount = count($_FILES['attachments']['name']);

                if ($fileCount > $maxFiles) {
                    throw new RuntimeException(
                        'You can upload a maximum of 5 files.'
                    );
                }

                if (!is_dir($uploadDirectory) || !is_writable($uploadDirectory)) {
                    throw new RuntimeException(
                        'The feedback upload folder is unavailable.'
                    );
                }

                $fileInfo = new finfo(FILEINFO_MIME_TYPE);

                for ($i = 0; $i < $fileCount; $i++) {

                    $fileError = $_FILES['attachments']['error'][$i];

                    // Skip empty file inputs.
                    if ($fileError === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }

                    if ($fileError !== UPLOAD_ERR_OK) {
                        throw new RuntimeException(
                            'There was a problem uploading a file.'
                        );
                    }

                    $temporaryPath = $_FILES['attachments']['tmp_name'][$i];
                    $originalName = basename($_FILES['attachments']['name'][$i]);
                    $fileSize = $_FILES['attachments']['size'][$i];

                    if ($fileSize > $maxFileSize) {
                        throw new RuntimeException(
                            'Each uploaded file must be 50 MB or smaller.'
                        );
                    }

                    // Detect the actual file type.
                    $mimeType = $fileInfo->file($temporaryPath);

                    if (!isset($allowedTypes[$mimeType])) {
                        throw new RuntimeException(
                            'Only supported image and video files are allowed.'
                        );
                    }

                    // Generate a unique filename.
                    $extension = $allowedTypes[$mimeType];
                    $newFileName = bin2hex(random_bytes(16)) . '.' . $extension;

                    $destination = $uploadDirectory . $newFileName;

                    if (!move_uploaded_file($temporaryPath, $destination)) {
                        throw new RuntimeException(
                            'Unable to save an uploaded file.'
                        );
                    }

                    $savedFiles[] = $destination;

                    $uploadedFiles[] = [
                        'file_name' => $originalName,
                        'file_path' => 'uploads/feedback/' . $newFileName,
                        'file_type' => $mimeType
                    ];
                }
            }

            // Save feedback and attachments together.
            $pdo->beginTransaction();

            // Insert feedback record.
            $stmt = $pdo->prepare(
                'INSERT INTO feedback
                    (
                        user_id,
                        feedback_title,
                        feedback_text,
                        recipient_type,
                        recipient_club_id
                    )
                 VALUES
                    (
                        :user_id,
                        :feedback_title,
                        :feedback_text,
                        :recipient_type,
                        :recipient_club_id
                    )
                 RETURNING feedback_id'
            );

            $stmt->execute([
                'user_id' => $userId,
                'feedback_title' => $feedbackTitle,
                'feedback_text' => $feedbackText,
                'recipient_type' => $selectedCategory,
                'recipient_club_id' => $recipientClubId
            ]);

            $feedbackId = $stmt->fetchColumn();

            // Insert attachment records.
            if (!empty($uploadedFiles)) {

                $attachmentStmt = $pdo->prepare(
                    'INSERT INTO feedback_attachment
                        (
                            feedback_id,
                            file_name,
                            file_path,
                            file_type
                        )
                     VALUES
                        (
                            :feedback_id,
                            :file_name,
                            :file_path,
                            :file_type
                        )'
                );

                foreach ($uploadedFiles as $file) {

                    $attachmentStmt->execute([
                        'feedback_id' => $feedbackId,
                        'file_name' => $file['file_name'],
                        'file_path' => $file['file_path'],
                        'file_type' => $file['file_type']
                    ]);
                }
            }

            $pdo->commit();

            $feedbackSent = true;

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            // Remove uploaded files if submission fails.
            foreach ($savedFiles as $filePath) {
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            error_log($e->getMessage());

            $errorMessage = 'Unable to submit your feedback. Please try again.';
        }
    }
}

// Reopen the modal if the submitted form has errors.
$showForm = (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    !$feedbackSent
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ÜBuddy - Feedback</title>

    <?php pageIcons(); ?>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/shared.css">
    <link rel="stylesheet" href="../css/feedback.css">
</head>

<body>

<?php
pageHeader(
    $profile['name'],
    $profile['user_id'],
    $adminClubs
);
?>

<div class="page-content">

    <?php backLink('homepage.php', 'Home'); ?>

    <div class="feedback-page">

        <h1 class="feedback-title">Give Feedback</h1>

        <small>
            Your feedback helps us improve ÜBuddy.
        </small>

        <?php if ($feedbackSent): ?>

            <!-- Feedback confirmation -->
            <div id="feedbackSent">

                <div class="content">

                    <p class="p1">Your feedback has been sent!</p>

                    <small>
                        Thank you, buddy, for helping us improve ÜBuddy.
                    </small>

                    <p class="p2">
                        Feedback ID:
                        <span id="feedbackId">
                            <?= htmlspecialchars(
                                (string) $feedbackId,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </span>
                    </p>

                </div>

                <div class="actions">

                    <button type="button" class="btn btn-large" disabled>
                        Sent!
                    </button>

                </div>

            </div>

        <?php else: ?>

            <!-- Error message -->
            <?php if (!empty($errorMessage)): ?>
                <p class="feedback-error">
                    <?= htmlspecialchars(
                        $errorMessage,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </p>
            <?php endif; ?>

            <!-- Button to open feedback modal -->
            <button
                type="button"
                id="openFeedbackModal"
                class="give-feedback-btn"
            >
                Give Feedback
            </button>

            <!-- Feedback Modal -->
            <div
                id="feedbackModal"
                class="feedback-modal"
                <?= $showForm ? '' : 'hidden' ?>
            >

                <div
                    class="feedback-modal-content"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="modalTitle"
                >

                    <!-- Close button -->
                    <button
                        type="button"
                        id="closeFeedbackModal"
                        class="close-modal-btn"
                        aria-label="Close feedback form"
                    >
                        &times;
                    </button>


                    <!-- STEP 1: Select feedback category -->
                    <section
                        id="categoryStep"
                        <?= $showForm ? 'hidden' : '' ?>
                    >

                        <h2 id="modalTitle">
                            What would you like to give feedback about?
                        </h2>

                        <p>Select a category to continue.</p>

                        <div class="category-options">

                            <button
                                type="button"
                                class="category-btn"
                                data-category="Website Issues & Suggestions"
                            >
                                <span class="category-name">
                                    Website Issues & Suggestions
                                </span>

                                <small>
                                    Report website problems or share suggestions.
                                </small>
                            </button>


                            <button
                                type="button"
                                class="category-btn"
                                data-category="Club Complaints & Concerns"
                            >
                                <span class="category-name">
                                    Club Complaints & Concerns
                                </span>

                                <small>
                                    Share concerns about club activities or management.
                                </small>
                            </button>


                            <button
                                type="button"
                                class="category-btn"
                                data-category="Club Feedback"
                                <?= !$hasStudentClubs ? 'disabled' : '' ?>
                            >
                                <span class="category-name">
                                    Club Feedback
                                </span>

                                <small>
                                    <?php if ($hasStudentClubs): ?>
                                        Share suggestions, questions or feedback about a club.
                                    <?php else: ?>
                                        Join a club before submitting Club Feedback.
                                    <?php endif; ?>
                                </small>
                            </button>

                        </div>

                    </section>


                    <!-- STEP 2: Feedback form -->
                    <section
                        id="formStep"
                        <?= $showForm ? '' : 'hidden' ?>
                    >

                        <button
                            type="button"
                            id="backToCategories"
                            class="back-category-btn"
                        >
                            &larr; Change category
                        </button>

                        <h2 id="selectedCategoryTitle">
                            <?= htmlspecialchars(
                                $selectedCategory,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </h2>

                        <form
                            id="feedbackForm"
                            method="POST"
                            enctype="multipart/form-data"
                        >

                            <!-- Selected category -->
                            <input
                                type="hidden"
                                id="recipientType"
                                name="recipient_type"
                                value="<?= htmlspecialchars(
                                    $selectedCategory,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >


                            <!-- Club selection -->
                            <div
                                id="clubSelection"
                                <?= $selectedCategory === 'Club Feedback' ? '' : 'hidden' ?>
                            >

                                <label for="recipientClub">
                                    Select Club
                                </label>

                                <?php if (!empty($studentClubs)): ?>

                                    <select
                                        id="recipientClub"
                                        name="recipient_club_id"
                                        <?= $selectedCategory === 'Club Feedback' ? 'required' : '' ?>
                                    >

                                        <option value="">
                                            -- Select a club --
                                        </option>

                                        <?php foreach ($studentClubs as $club): ?>

                                            <option
                                                value="<?= (int) $club['club_id'] ?>"
                                                <?= (
                                                    isset($_POST['recipient_club_id']) &&
                                                    (int) $_POST['recipient_club_id'] === (int) $club['club_id']
                                                ) ? 'selected' : '' ?>
                                            >
                                                <?= htmlspecialchars(
                                                    $club['club_name'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                <?php else: ?>

                                    <p>
                                        You have not joined any clubs yet, so you cannot
                                        submit Club Feedback.
                                    </p>

                                <?php endif; ?>

                            </div>


                            <!-- Feedback title -->
                            <label for="feedbackTitle">
                                Feedback Title
                            </label>

                            <input
                                type="text"
                                id="feedbackTitle"
                                name="feedback_title"
                                maxlength="150"
                                placeholder="Enter a short title"
                                value="<?= htmlspecialchars(
                                    $feedbackTitle,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                required
                            >


                            <!-- Feedback description -->
                            <label for="typeFeedback">
                                Description
                            </label>

                            <textarea
                                id="typeFeedback"
                                name="feedback_text"
                                placeholder="Describe your feedback here..."
                                required
                            ><?= htmlspecialchars(
                                $feedbackText,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?></textarea>


                            <!-- Upload files -->
                            <div class="actions">

                                <div class="dropdown">

                                    <button
                                        type="button"
                                        class="dropbtn"
                                    >
                                        Upload Files
                                    </button>

                                    <div class="dropdown-content">

                                        <p>Select File Type:</p>

                                        <button
                                            type="button"
                                            data-type="image/*"
                                        >
                                            Image
                                        </button>

                                        <button
                                            type="button"
                                            data-type="video/*"
                                        >
                                            Video
                                        </button>

                                    </div>

                                </div>

                                <button
                                    type="submit"
                                    class="btn btn-large"
                                    id="submitFeedback"
                                >
                                    Send
                                </button>

                            </div>


                            <input
                                type="file"
                                id="fileInput"
                                name="attachments[]"
                                accept="image/*,video/*"
                                multiple
                                hidden
                            >

                            <small id="fileName"></small>

                            <small class="upload-note">
                                You can upload up to 5 images or videos.
                                Each file must be 50 MB or smaller.
                            </small>

                        </form>

                    </section>

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>

<?php pageFooter(); ?>

<script src="../js/feedback.js"></script>

</body>
</html>
