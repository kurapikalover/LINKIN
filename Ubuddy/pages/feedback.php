
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

// Retrieve the user's profile.
$profile = getStudentProfile($pdo, $userId);

if (!$profile) {
    http_response_code(404);
    exit('User profile not found.');
}

$feedbackSent = false;
$feedbackId = null;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $feedbackText = trim($_POST['feedback_text'] ?? '');

    // Check that feedback is not empty.
    if ($feedbackText === '') {
        $errorMessage = 'Please enter your feedback.';
    } else {

        $uploadedFiles = [];
        $savedFiles = [];

        try {

            // Upload folder.
            $uploadDirectory = dirname(__DIR__) . '/uploads/feedback/';

            if (!is_dir($uploadDirectory) || !is_writable($uploadDirectory)) {
                throw new RuntimeException('The feedback upload folder is unavailable.');
            }

            // Allowed file types and their extensions.
            $allowedTypes = [
                'image/jpeg'      => 'jpg',
                'image/png'       => 'png',
                'image/gif'       => 'gif',
                'image/webp'      => 'webp',
                'video/mp4'       => 'mp4',
                'video/webm'      => 'webm',
                'video/quicktime' => 'mov'
            ];

            $maxFileSize = 50 * 1024 * 1024; // 50 MB per file
            $maxFiles = 5;

            // Process uploaded files, if any.
            if (
                isset($_FILES['attachments']) &&
                is_array($_FILES['attachments']['name'])
            ) {

                $fileCount = count($_FILES['attachments']['name']);

                if ($fileCount > $maxFiles) {
                    throw new RuntimeException('You can upload a maximum of 5 files.');
                }

                $fileInfo = new finfo(FILEINFO_MIME_TYPE);

                for ($i = 0; $i < $fileCount; $i++) {

                    $fileError = $_FILES['attachments']['error'][$i];

                    // Skip empty file inputs.
                    if ($fileError === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }

                    if ($fileError !== UPLOAD_ERR_OK) {
                        throw new RuntimeException('There was a problem uploading a file.');
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
                        throw new RuntimeException('Unable to save an uploaded file.');
                    }

                    $savedFiles[] = $destination;

                    $uploadedFiles[] = [
                        'file_name' => $originalName,
                        'file_path' => 'uploads/feedback/' . $newFileName,
                        'file_type' => $mimeType
                    ];
                }
            }

            // Save feedback and attachment records together.
            $pdo->beginTransaction();

            // Insert feedback.
            $stmt = $pdo->prepare(
                'INSERT INTO feedback
                    (user_id, feedback_text)
                 VALUES
                    (:user_id, :feedback_text)
                 RETURNING feedback_id'
            );

            $stmt->execute([
                'user_id' => $userId,
                'feedback_text' => $feedbackText
            ]);

            $feedbackId = $stmt->fetchColumn();

            // Insert attachment information.
            if (!empty($uploadedFiles)) {

                $attachmentStmt = $pdo->prepare(
                    'INSERT INTO feedback_attachment
                        (feedback_id, file_name, file_path, file_type)
                     VALUES
                        (:feedback_id, :file_name, :file_path, :file_type)'
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

            // Remove uploaded files if the database operation fails.
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

// Display the shared page header.
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
// Display the shared page header.
pageHeader(
    $profile['name'],
    $profile['user_id'],
    $adminClubs
);
?>

<div class="page-content">

    <?php backLink('homepage.php', 'Home'); ?>

    <div class="feedback-page">

        <h1 class="feedback-title">User Feedback Form</h1>

        <small>All feedbacks will be sent anonymously</small>

        <?php if ($feedbackSent): ?>

            <!-- Feedback confirmation -->
            <div id="feedbackSent">

                <div class="content">

                    <p class="p1">Your feedback has been sent!</p>

                    <small>
                        Thank you, buddy, for helping us improve the website.
                    </small>

                    <p class="p2">
                        Feedback ID:
                        <span id="feedbackId">
                            <?= htmlspecialchars((string) $feedbackId, ENT_QUOTES, 'UTF-8') ?>
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

            <!-- Feedback error message -->
            <?php if (!empty($errorMessage)): ?>
                <p class="feedback-error">
                    <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <!-- Feedback form -->
            <form id="feedbackForm"
                  method="POST"
                  enctype="multipart/form-data">

                <textarea
                    id="typeFeedback"
                    name="feedback_text"
                    placeholder="Type here"
                    required><?= htmlspecialchars($_POST['feedback_text'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

                <div class="actions">

                    <div class="dropdown">

                        <button type="button" class="dropbtn">
                            Upload Files
                        </button>

                        <div class="dropdown-content">

                            <p>Select File Type:</p>

                            <button type="button" data-type="image/*">
                                Image
                            </button>

                            <button type="button" data-type="video/*">
                                Video
                            </button>

                        </div>

                    </div>

                    <button type="submit" class="btn btn-large">
                        Send
                    </button>

                </div>

                <input
                    type="file"
                    id="fileInput"
                    name="attachments[]"
                    accept="image/*,video/*"
                    multiple
                    hidden>

                <small id="fileName"></small>

            </form>

        <?php endif; ?>

    </div>

</div>

<?php pageFooter(); ?>

<script src="../js/feedback.js"></script>

</body>
</html>