
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../php/system_admin_funct.php';

/* =========================================
   CHECK LOGIN
========================================= */

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (string) $_SESSION['user_id'];

/* =========================================
   CHECK SYSTEM ADMIN ROLE
========================================= */

$admin = getSystemAdminDetails($pdo, $userId);

if (!$admin) {
    header('Location: homepage.php');
    exit;
}

/* =========================================
   CSRF TOKEN
========================================= */

if (empty($_SESSION['system_admin_csrf'])) {
    $_SESSION['system_admin_csrf'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['system_admin_csrf'];

/* =========================================
   FLASH MESSAGES
========================================= */

$successMessage = $_SESSION['system_admin_success'] ?? '';
$errorMessage = $_SESSION['system_admin_error'] ?? '';

unset(
    $_SESSION['system_admin_success'],
    $_SESSION['system_admin_error']
);

/* =========================================
   ESCAPE HELPER
========================================= */

function systemAdminEscape($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

/* =========================================
   CHECK ATTACHMENT FILE
========================================= */

function systemAdminAttachmentExists(string $filePath): bool
{
    $filePath = str_replace('\\', '/', trim($filePath));
    $filePath = ltrim($filePath, '/');

    if (
        $filePath === '' ||
        strpos($filePath, 'uploads/feedback/') !== 0
    ) {
        return false;
    }

    if (in_array('..', explode('/', $filePath), true)) {
        return false;
    }

    $uploadDirectory = realpath(
        __DIR__ . '/../uploads/feedback'
    );

    $absoluteFilePath = realpath(
        __DIR__ . '/../' . $filePath
    );

    if (
        $uploadDirectory === false ||
        $absoluteFilePath === false ||
        !is_file($absoluteFilePath)
    ) {
        return false;
    }

    return strpos(
        $absoluteFilePath,
        $uploadDirectory . DIRECTORY_SEPARATOR
    ) === 0;
}

/* =========================================
   GET FEEDBACK SEARCH AND STATUS
========================================= */

$search = trim($_GET['search'] ?? '');

$allowedFeedbackStatuses = [
    'Pending',
    'Reviewed',
    'Archived'
];

$status = trim($_GET['status'] ?? 'Pending');

if (!in_array($status, $allowedFeedbackStatuses, true)) {
    $status = 'Pending';
}

/* Get feedback for the selected status */

$feedbackList = getSystemAdminFeedback(
    $pdo,
    $search,
    $status
);

/* =========================================
   GET FEEDBACK ATTACHMENTS
========================================= */

/* Get all feedback IDs */

$feedbackIds = array_column($feedbackList, 'feedback_id');

/* Retrieve all attachments in one query */

$feedbackAttachmentsByFeedback =
    getFeedbackAttachmentsForFeedback($pdo, $feedbackIds);


/* =========================================
   GET FEEDBACK COUNTS
========================================= */

$feedbackCounts = getSystemAdminFeedbackCounts($pdo);


/* =========================================
   GET DATA FOR MANAGEMENT
========================================= */

$clubs = getSystemAdminClubs($pdo);
$accounts = getSystemAdminAccounts($pdo);
$categories = getSystemAdminCategories($pdo);
$notificationRecipients = getNotificationRecipients($pdo);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ÜBuddy - System Admin</title>

    <?php pageIcons(); ?>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/shared.css">
    <link rel="stylesheet" href="../css/system-admin.css">

    <script src="../js/system-admin.js" defer></script>

</head>

<body>

<?php
pageHeader(
    $admin['name'],
    $admin['user_id']
);
?>

<main class="page-content">

    <!-- =====================================
         PAGE TITLE
    ====================================== -->

    <h1 class="system-admin-title">
        System Admin
    </h1>


    <!-- =====================================
         SUCCESS / ERROR MESSAGES
    ====================================== -->

    <?php if ($successMessage !== ''): ?>

        <div class="success-message" role="status">
            <?= systemAdminEscape($successMessage) ?>
        </div>

    <?php endif; ?>

    <?php if ($errorMessage !== ''): ?>

        <div class="error-message" role="alert">
            <?= systemAdminEscape($errorMessage) ?>
        </div>

    <?php endif; ?>


    <!-- =====================================
         SYSTEM ADMIN PROFILE
    ====================================== -->

    <section class="admin-card">

        <span class="material-symbols-outlined system-admin-icon">
            admin_panel_settings
        </span>

        <div class="admin-details">

            <h3>
                <?= systemAdminEscape($admin['name']) ?>
            </h3>

            <p class="p1">
                <?= systemAdminEscape($admin['user_id']) ?>
            </p>

            <p class="p2">
                <?= systemAdminEscape(
                    $admin['faculty'] ?? 'Faculty not specified'
                ) ?>
            </p>

        </div>

    </section>


    <!-- =====================================
         SYSTEM MANAGEMENT
    ====================================== -->

    <section class="system-management">

        <div class="section-heading">

            <div>
                <h2>System Management</h2>
                <p>Manage accounts, categories, and system notifications.</p>
            </div>

            <span class="material-symbols-outlined section-icon">
                settings
            </span>

        </div>

        <div class="admin-actions">

            <button
                type="button"
                class="admin-action-btn"
                data-open-modal="accountModal"
            >

                <span class="material-symbols-outlined">
                    manage_accounts
                </span>

                <span class="action-text">
                    <strong>Account Management</strong>
                    <small>Add, edit, or delete user accounts</small>
                </span>

                <span class="material-symbols-outlined action-arrow">
                    arrow_forward
                </span>

            </button>


            <button
                type="button"
                class="admin-action-btn"
                data-open-modal="categoryModal"
            >

                <span class="material-symbols-outlined">
                    category
                </span>

                <span class="action-text">
                    <strong>Category Management</strong>
                    <small>Add, edit, or delete categories</small>
                </span>

                <span class="material-symbols-outlined action-arrow">
                    arrow_forward
                </span>

            </button>


            <button
                type="button"
                class="admin-action-btn"
                data-open-modal="notificationModal"
            >

                <span class="material-symbols-outlined">
                    notifications
                </span>

                <span class="action-text">
                    <strong>System Notifications</strong>
                    <small>Send notifications to users</small>
                </span>

                <span class="material-symbols-outlined action-arrow">
                    arrow_forward
                </span>

            </button>

        </div>

    </section>


    <!-- =====================================
         USER FEEDBACK
    ====================================== -->

    <section class="view-feedback">

        <div class="section-heading">

            <div>
                <h2>User Feedback</h2>
                <p>View and manage feedback submitted by students.</p>
            </div>

            <span class="material-symbols-outlined section-icon">
                forum
            </span>

        </div>


        <!-- FEEDBACK STATUS TABS -->

        <nav class="feedback-status-tabs" aria-label="Feedback status">

            <?php foreach ($allowedFeedbackStatuses as $tabStatus): ?>

                <a
                    class="feedback-status-tab <?= $status === $tabStatus ? 'active' : '' ?>"
                    href="?status=<?= urlencode($tabStatus) ?>&search=<?= urlencode($search) ?>"
                    <?= $status === $tabStatus ? 'aria-current="page"' : '' ?>
                >

                    <span class="feedback-tab-label">
                        <?= systemAdminEscape($tabStatus) ?>
                    </span>

                    <span class="feedback-tab-count">
                        <?= (int) $feedbackCounts[$tabStatus] ?>
                    </span>

                </a>

            <?php endforeach; ?>

        </nav>


        <!-- FEEDBACK SEARCH -->

        <form
            method="GET"
            action="system-admin.php"
            class="feedback-search"
        >

            <input
                type="hidden"
                name="status"
                value="<?= systemAdminEscape($status) ?>"
            >

            <span class="material-symbols-outlined" aria-hidden="true">
                search
            </span>

            <input
                type="text"
                name="search"
                placeholder="Search feedback..."
                value="<?= systemAdminEscape($search) ?>"
                aria-label="Search feedback"
            >

        </form>


        <?php if (!empty($feedbackList)): ?>

            <div class="feedback-list">

                <?php foreach ($feedbackList as $feedback): ?>

                    <?php

                    /* Get attachments for this feedback */

                    $feedbackAttachments =
                        $feedbackAttachmentsByFeedback[(int) $feedback['feedback_id']] ?? [];

                    $attachmentCount = count($feedbackAttachments);

                    $feedbackStatus =
                        $feedback['feedback_status'] ?? 'Pending';

                    $statusClass = strtolower(
                        str_replace(' ', '-', $feedbackStatus)
                    );

                    ?>

                    <article class="feedback-content">

                        <div class="feedback-top">

                            <h3>
                                Feedback #<?= (int) $feedback['feedback_id'] ?>
                            </h3>

                            <p class="feedback-date">

                                <?= !empty($feedback['feedback_date'])
                                    ? date(
                                        'd M Y, h:i A',
                                        strtotime($feedback['feedback_date'])
                                    )
                                    : 'Date unavailable'
                                ?>

                            </p>

                        </div>


                        <div class="feedback-details">

                            <strong class="feedback-title">
                                <?= systemAdminEscape(
                                    $feedback['feedback_title']
                                    ?: 'Untitled Feedback'
                                ) ?>
                            </strong>

                            <p class="feedback-text">
                                <?= nl2br(
                                    systemAdminEscape(
                                        $feedback['feedback_text']
                                    )
                                ) ?>
                            </p>

                            <p class="feedback-submitter">

                                Submitted by:

                                <?= systemAdminEscape(
                                    $feedback['student_name']
                                    ?? 'Unknown User'
                                ) ?>

                                (<?= systemAdminEscape(
                                    $feedback['user_id']
                                ) ?>)

                            </p>

                            <span class="feedback-status status-<?= systemAdminEscape($statusClass) ?>">
                                <?= systemAdminEscape($feedbackStatus) ?>
                            </span>

                        </div>


                        <!-- ATTACHMENTS -->

                        <?php if ($attachmentCount > 0): ?>

                            <details class="feedback-attachment-toggle">

                                <summary>

                                    <span class="attachment-summary-left">

                                        <span class="material-symbols-outlined attachment-main-icon">
                                            attach_file
                                        </span>

                                        <span class="attachment-summary-text">

                                            <strong>Attachments</strong>

                                            <small>
                                                <?= $attachmentCount ?>
                                                <?= $attachmentCount === 1 ? 'file' : 'files' ?>
                                            </small>

                                        </span>

                                    </span>

                                    <span class="material-symbols-outlined attachment-expand-icon">
                                        expand_more
                                    </span>

                                </summary>


                                <div class="feedback-attachment-list">

                                    <?php foreach ($feedbackAttachments as $attachment): ?>

                                        <?php

                                        $fileName =
                                            $attachment['file_name']
                                            ?? 'Unnamed file';

                                        $filePath =
                                            $attachment['file_path']
                                            ?? '';

                                        $extension = strtolower(
                                            pathinfo(
                                                $fileName,
                                                PATHINFO_EXTENSION
                                            )
                                        );

                                        $imageExtensions = [
                                            'jpg',
                                            'jpeg',
                                            'png',
                                            'gif',
                                            'webp'
                                        ];

                                        $isImage = in_array(
                                            $extension,
                                            $imageExtensions,
                                            true
                                        );

                                        $attachmentAvailable =
                                            $filePath !== '' &&
                                            systemAdminAttachmentExists($filePath);

                                        $browserFilePath = '';

                                        if ($attachmentAvailable) {
                                            $browserFilePath = '../' .
                                                ltrim(
                                                    str_replace('\\', '/', $filePath),
                                                    '/'
                                                );
                                        }

                                        if ($isImage) {
                                            $fileIcon = 'image';
                                        } elseif ($extension === 'pdf') {
                                            $fileIcon = 'picture_as_pdf';
                                        } elseif (
                                            in_array(
                                                $extension,
                                                ['doc', 'docx'],
                                                true
                                            )
                                        ) {
                                            $fileIcon = 'description';
                                        } else {
                                            $fileIcon = 'draft';
                                        }

                                        ?>


                                        <div class="feedback-attachment-item">

                                            <?php if ($isImage && $attachmentAvailable): ?>

                                                <a
                                                    href="<?= systemAdminEscape($browserFilePath) ?>"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="attachment-preview"
                                                    aria-label="Open <?= systemAdminEscape($fileName) ?>"
                                                >

                                                    <img
                                                        src="<?= systemAdminEscape($browserFilePath) ?>"
                                                        alt="<?= systemAdminEscape($fileName) ?>"
                                                        loading="lazy"
                                                        onerror="this.style.display='none'; this.parentElement.classList.add('attachment-image-missing'); this.parentElement.innerHTML='<span class=\'material-symbols-outlined\'>image_not_supported</span><small>Image unavailable</small>'; this.parentElement.removeAttribute('href');"
                                                    >

                                                </a>

                                            <?php elseif ($isImage): ?>

                                                <div class="attachment-preview attachment-image-missing">

                                                    <span class="material-symbols-outlined">
                                                        image_not_supported
                                                    </span>

                                                    <small>Image unavailable</small>

                                                </div>

                                            <?php else: ?>

                                                <span class="material-symbols-outlined attachment-file-icon">
                                                    <?= systemAdminEscape($fileIcon) ?>
                                                </span>

                                            <?php endif; ?>


                                            <div class="attachment-file-details">

                                                <strong>
                                                    <?= systemAdminEscape($fileName) ?>
                                                </strong>

                                                <small>
                                                    <?= $extension !== ''
                                                        ? strtoupper($extension) . ' file'
                                                        : 'Attachment'
                                                    ?>
                                                </small>

                                                <?php if (!$attachmentAvailable): ?>

                                                    <small class="attachment-missing-message">
                                                        File not found
                                                    </small>

                                                <?php endif; ?>

                                            </div>


                                            <?php if ($attachmentAvailable): ?>

                                                <a
                                                    href="<?= systemAdminEscape($browserFilePath) ?>"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="attachment-open-btn"
                                                    aria-label="Open <?= systemAdminEscape($fileName) ?>"
                                                >

                                                    <span class="material-symbols-outlined">
                                                        open_in_new
                                                    </span>

                                                </a>

                                            <?php else: ?>

                                                <span
                                                    class="attachment-open-btn attachment-open-disabled"
                                                    aria-label="File unavailable"
                                                    title="This file could not be found"
                                                >

                                                    <span class="material-symbols-outlined">
                                                        error_outline
                                                    </span>

                                                </span>

                                            <?php endif; ?>

                                        </div>

                                    <?php endforeach; ?>

                                </div>

                            </details>

                        <?php endif; ?>


                        <!-- FEEDBACK ACTIONS -->

                        <div class="feedback-actions">

                            <?php if ($feedbackStatus === 'Pending'): ?>

                                <form
                                    method="POST"
                                    action="../php/system_admin/feedback_process.php"
                                    class="feedback-action-form"
                                    data-confirm-action="review"
                                >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= systemAdminEscape($csrfToken) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="mark_feedback_reviewed"
                                    >

                                    <input
                                        type="hidden"
                                        name="feedback_id"
                                        value="<?= (int) $feedback['feedback_id'] ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="return_search"
                                        value="<?= systemAdminEscape($search) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="return_status"
                                        value="<?= systemAdminEscape($status) ?>"
                                    >

                                    <button type="submit" class="reviewed-btn">

                                        <span class="material-symbols-outlined">
                                            check_circle
                                        </span>

                                        Mark as Reviewed

                                    </button>

                                </form>

                            <?php endif; ?>


                            <?php if (
                                in_array(
                                    $feedbackStatus,
                                    ['Pending', 'Reviewed'],
                                    true
                                )
                            ): ?>

                                <form
                                    method="POST"
                                    action="../php/system_admin/feedback_process.php"
                                    class="feedback-action-form"
                                    data-confirm-action="archive"
                                >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= systemAdminEscape($csrfToken) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="archive_feedback"
                                    >

                                    <input
                                        type="hidden"
                                        name="feedback_id"
                                        value="<?= (int) $feedback['feedback_id'] ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="return_search"
                                        value="<?= systemAdminEscape($search) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="return_status"
                                        value="<?= systemAdminEscape($status) ?>"
                                    >

                                    <button type="submit" class="archive-btn">

                                        <span class="material-symbols-outlined">
                                            archive
                                        </span>

                                        Archive

                                    </button>

                                </form>

                            <?php endif; ?>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="no-feedback">

                <span class="material-symbols-outlined">
                    inbox
                </span>

                <?php if ($search !== ''): ?>

                    <p>
                        No feedback found matching
                        "<?= systemAdminEscape($search) ?>"
                        in the <?= systemAdminEscape($status) ?> section.
                    </p>

                <?php else: ?>

                    <p>
                        No <?= systemAdminEscape($status) ?> feedback available.
                    </p>

                <?php endif; ?>

            </div>

        <?php endif; ?>

    </section>


    <!-- =====================================
         FEEDBACK CONFIRMATION MODAL
    ====================================== -->

    <div
        class="confirmation-modal"
        id="feedbackConfirmModal"
        hidden
    >

        <div
            class="confirmation-modal-backdrop"
            data-confirm-cancel
        ></div>

        <section
            class="confirmation-modal-content"
            role="dialog"
            aria-modal="true"
            aria-labelledby="feedbackConfirmTitle"
            aria-describedby="feedbackConfirmMessage"
        >

            <div class="confirmation-modal-icon" id="feedbackConfirmIcon">

                <span class="material-symbols-outlined">
                    check_circle
                </span>

            </div>

            <h2 id="feedbackConfirmTitle">
                Confirm Action
            </h2>

            <p id="feedbackConfirmMessage">
                Are you sure you want to continue?
            </p>

            <div class="confirmation-modal-actions">

                <button
                    type="button"
                    class="confirmation-cancel-btn"
                    data-confirm-cancel
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="confirmation-submit-btn"
                    id="feedbackConfirmSubmit"
                >
                    Confirm
                </button>

            </div>

        </section>

    </div>


    <!-- =====================================
         ASSIGN ROLE
    ====================================== -->

    <section class="assign-role">

        <div class="section-heading">

            <div>
                <h2>Assign Role</h2>
                <p>Manage user access and administrative roles.</p>
            </div>

            <span class="material-symbols-outlined section-icon">
                manage_accounts
            </span>

        </div>


        <form
            method="POST"
            action="../php/system_admin/role_process.php"
            id="assignRoleForm"
            autocomplete="off"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= systemAdminEscape($csrfToken) ?>"
            >


            <!-- SEARCHABLE USER DROPDOWN -->

            <div class="field account-combobox-field">

                <label for="targetUserSearch">
                    User ID / Name
                </label>

                <input
                    type="hidden"
                    id="targetUserId"
                    name="target_user_id"
                >

                <div class="account-combobox">

                    <input
                        type="text"
                        id="targetUserSearch"
                        class="account-combobox-input"
                        placeholder="Search by name or user ID..."
                        autocomplete="off"
                        role="combobox"
                        aria-autocomplete="list"
                        aria-expanded="false"
                        aria-controls="roleUserOptions"
                        required
                    >

                    <span class="material-symbols-outlined account-combobox-icon">
                        expand_more
                    </span>

                    <div
                        class="account-combobox-options"
                        id="roleUserOptions"
                        role="listbox"
                        hidden
                    >

                        <?php foreach ($accounts as $account): ?>

                            <button
                                type="button"
                                class="account-combobox-option"
                                role="option"
                                data-value="<?= systemAdminEscape($account['user_id']) ?>"
                                data-name="<?= systemAdminEscape($account['name']) ?>"
                            >

                                <strong>
                                    <?= systemAdminEscape($account['name']) ?>
                                </strong>

                                <small>
                                    <?= systemAdminEscape($account['user_id']) ?>
                                </small>

                            </button>

                        <?php endforeach; ?>

                        <p class="account-combobox-empty" hidden>
                            No matching users found.
                        </p>

                    </div>

                </div>

            </div>


            <div class="role-club-row">

                <div class="field">

                    <label for="roleSelect">
                        Role
                    </label>

                    <select
                        id="roleSelect"
                        name="role"
                        required
                    >

                        <option value="" selected disabled>
                            Select Role
                        </option>

                        <option value="committee_admin">
                            Committee Admin
                        </option>

                        <option value="hep_admin">
                            HEP Admin
                        </option>

                        <option value="system_admin">
                            System Admin
                        </option>

                    </select>

                </div>


                <div class="field" id="clubField" hidden>

                    <label for="clubSelect">
                        Club
                    </label>

                    <select
                        id="clubSelect"
                        name="club_id"
                    >

                        <option value="" selected disabled>
                            Select Club
                        </option>

                        <?php foreach ($clubs as $club): ?>

                            <option value="<?= (int) $club['club_id'] ?>">
                                <?= systemAdminEscape($club['club_name']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

            </div>


            <button type="submit" class="confirm-btn">
                Confirm
            </button>

        </form>

    </section>


    <!-- =====================================
         ACCOUNT MANAGEMENT MODAL
    ====================================== -->

    <div class="management-modal" id="accountModal" hidden>

        <div class="management-modal-content">

            <div class="management-modal-header">

                <div>
                    <h2>Account Management</h2>
                    <p>Manage registered user accounts.</p>
                </div>

                <button
                    type="button"
                    class="modal-close-btn"
                    data-close-modal
                    aria-label="Close"
                >
                    <span class="material-symbols-outlined">close</span>
                </button>

            </div>


            <div data-action-panel>

                <div class="management-action-list">

                    <button
                        type="button"
                        class="management-option"
                        data-show-form="accountAddForm"
                    >
                        <span class="material-symbols-outlined">person_add</span>

                        <span>
                            <strong>Add Account</strong>
                            <small>Create a new user account</small>
                        </span>

                        <span class="material-symbols-outlined">arrow_forward</span>
                    </button>


                    <button
                        type="button"
                        class="management-option"
                        data-show-form="accountEditForm"
                    >
                        <span class="material-symbols-outlined">edit</span>

                        <span>
                            <strong>Edit Account</strong>
                            <small>Update existing account details</small>
                        </span>

                        <span class="material-symbols-outlined">arrow_forward</span>
                    </button>


                    <button
                        type="button"
                        class="management-option"
                        data-show-form="accountDeleteForm"
                    >
                        <span class="material-symbols-outlined">delete</span>

                        <span>
                            <strong>Delete Account</strong>
                            <small>Remove an existing account</small>
                        </span>

                        <span class="material-symbols-outlined">arrow_forward</span>
                    </button>

                </div>

            </div>


            <!-- ADD ACCOUNT FORM -->

            <div id="accountAddForm" data-form-panel hidden>

                <button
                    type="button"
                    class="modal-back-btn"
                    data-back-to-actions
                >
                    <span class="material-symbols-outlined">arrow_back</span>
                    Back
                </button>

                <h3>Add Account</h3>

                <form
                    method="POST"
                    action="../php/system_admin/account_process.php"
                    autocomplete="off"
                >

                    <input type="hidden" name="csrf_token" value="<?= systemAdminEscape($csrfToken) ?>">
                    <input type="hidden" name="action" value="add_account">

                    <div class="field">
                        <label for="addUserId">User ID</label>
                        <input type="text" id="addUserId" name="user_id" placeholder="Enter user ID" required>
                    </div>

                    <div class="field">
                        <label for="addName">Full Name</label>
                        <input type="text" id="addName" name="name" placeholder="Enter full name" required>
                    </div>

                    <div class="field">
                        <label for="addEmail">Email Address</label>
                        <input type="email" id="addEmail" name="email" placeholder="Enter email address" required>
                    </div>

                    <div class="field">
                        <label for="addPassword">Password</label>
                        <input type="password" id="addPassword" name="password" placeholder="Enter password" minlength="8" required>
                    </div>

                    <div class="field">
                        <label for="addFaculty">Faculty</label>
                        <input type="text" id="addFaculty" name="faculty" placeholder="Enter faculty (optional)">
                    </div>

                    <div class="field">
                        <label for="addUserType">Account Type</label>

                        <select id="addUserType" name="user_type" required>
                            <option value="">Select Account Type</option>
                            <option value="Student">Student</option>
                            <option value="Student Affairs">Student Affairs</option>
                            <option value="System Admin">System Admin</option>
                        </select>
                    </div>

                    <button type="submit" class="confirm-btn">
                        Create Account
                    </button>

                </form>

            </div>


            <!-- EDIT ACCOUNT FORM -->

            <div id="accountEditForm" data-form-panel hidden>

                <button
                    type="button"
                    class="modal-back-btn"
                    data-back-to-actions
                >
                    <span class="material-symbols-outlined">arrow_back</span>
                    Back
                </button>

                <h3>Edit Account</h3>

                <form
                    method="POST"
                    action="../php/system_admin/account_process.php"
                    id="accountEditFormSubmit"
                    autocomplete="off"
                >

                    <input type="hidden" name="csrf_token" value="<?= systemAdminEscape($csrfToken) ?>">
                    <input type="hidden" name="action" value="edit_account">

                    <input type="hidden" name="edit_user_id" id="editUserSelect">

                    <div class="field account-combobox-field">

                        <label for="editUserSearch">Select Account</label>

                        <div class="account-combobox">

                            <input
                                type="text"
                                id="editUserSearch"
                                class="account-combobox-input"
                                placeholder="Search by name or user ID..."
                                autocomplete="off"
                                role="combobox"
                                aria-autocomplete="list"
                                aria-expanded="false"
                                aria-controls="editUserOptions"
                                required
                            >

                            <span class="material-symbols-outlined account-combobox-icon">
                                expand_more
                            </span>

                            <div class="account-combobox-options" id="editUserOptions" role="listbox" hidden>

                                <?php foreach ($accounts as $account): ?>

                                    <button
                                        type="button"
                                        class="account-combobox-option"
                                        role="option"
                                        data-value="<?= systemAdminEscape($account['user_id']) ?>"
                                        data-name="<?= systemAdminEscape($account['name']) ?>"
                                        data-email="<?= systemAdminEscape($account['email']) ?>"
                                        data-faculty="<?= systemAdminEscape($account['faculty'] ?? '') ?>"
                                        data-user-type="<?= systemAdminEscape($account['user_type']) ?>"
                                    >

                                        <strong><?= systemAdminEscape($account['name']) ?></strong>
                                        <small><?= systemAdminEscape($account['user_id']) ?></small>

                                    </button>

                                <?php endforeach; ?>

                                <p class="account-combobox-empty" hidden>
                                    No matching accounts found.
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="field">
                        <label for="editName">Full Name</label>
                        <input type="text" id="editName" name="edit_name" autocomplete="off" required>
                    </div>

                    <div class="field">
                        <label for="editEmail">Email Address</label>
                        <input type="email" id="editEmail" name="edit_email" autocomplete="off" required>
                    </div>

                    <div class="field">
                        <label for="editPassword">New Password</label>
                        <input type="password" id="editPassword" name="edit_password" minlength="8" placeholder="Leave blank to keep current password" autocomplete="new-password">
                    </div>

                    <div class="field">
                        <label for="editFaculty">Faculty</label>
                        <input type="text" id="editFaculty" name="edit_faculty" autocomplete="off">
                    </div>

                    <div class="field">
                        <label for="editUserType">Account Type</label>

                        <select id="editUserType" name="edit_user_type" required>
                            <option value="Student">Student</option>
                            <option value="Student Affairs">Student Affairs</option>
                            <option value="System Admin">System Admin</option>
                        </select>
                    </div>

                    <button type="submit" class="confirm-btn">
                        Save Changes
                    </button>

                </form>

            </div>


            <!-- DELETE ACCOUNT FORM -->

            <div id="accountDeleteForm" data-form-panel hidden>

                <button type="button" class="modal-back-btn" data-back-to-actions>
                    <span class="material-symbols-outlined">arrow_back</span>
                    Back
                </button>

                <h3>Delete Account</h3>

                <p>
                    Select the account you want to delete.
                    This action cannot be undone.
                </p>

                <form
                    method="POST"
                    action="../php/system_admin/account_process.php"
                    id="accountDeleteFormSubmit"
                    autocomplete="off"
                >

                    <input type="hidden" name="csrf_token" value="<?= systemAdminEscape($csrfToken) ?>">
                    <input type="hidden" name="action" value="delete_account">

                    <input type="hidden" name="delete_user_id" id="deleteUserId">

                    <div class="field account-combobox-field">

                        <label for="deleteUserSearch">Select Account</label>

                        <div class="account-combobox">

                            <input
                                type="text"
                                id="deleteUserSearch"
                                class="account-combobox-input"
                                placeholder="Search by name or user ID..."
                                autocomplete="off"
                                role="combobox"
                                aria-autocomplete="list"
                                aria-expanded="false"
                                aria-controls="deleteUserOptions"
                                required
                            >

                            <span class="material-symbols-outlined account-combobox-icon">
                                expand_more
                            </span>

                            <div class="account-combobox-options" id="deleteUserOptions" role="listbox" hidden>

                                <?php foreach ($accounts as $account): ?>

                                    <?php if ((string) $account['user_id'] !== $userId): ?>

                                        <button
                                            type="button"
                                            class="account-combobox-option"
                                            role="option"
                                            data-value="<?= systemAdminEscape($account['user_id']) ?>"
                                            data-name="<?= systemAdminEscape($account['name']) ?>"
                                        >

                                            <strong><?= systemAdminEscape($account['name']) ?></strong>
                                            <small><?= systemAdminEscape($account['user_id']) ?></small>

                                        </button>

                                    <?php endif; ?>

                                <?php endforeach; ?>

                                <p class="account-combobox-empty" hidden>
                                    No matching accounts found.
                                </p>

                            </div>

                        </div>

                    </div>

                    <button type="submit" class="confirm-btn delete-confirm-btn">
                        Delete Account
                    </button>

                </form>

            </div>

        </div>

    </div>


    <!-- =====================================
         CATEGORY MANAGEMENT MODAL
    ====================================== -->

    <div class="management-modal" id="categoryModal" hidden>

        <div class="management-modal-content">

            <div class="management-modal-header">

                <div>
                    <h2>Category Management</h2>
                    <p>Manage club categories.</p>
                </div>

                <button type="button" class="modal-close-btn" data-close-modal aria-label="Close">
                    <span class="material-symbols-outlined">close</span>
                </button>

            </div>


            <div data-action-panel>

                <div class="management-action-list">

                    <button type="button" class="management-option" data-show-form="categoryAddForm">
                        <span class="material-symbols-outlined">add</span>
                        <span>
                            <strong>Add Category</strong>
                            <small>Create a new category</small>
                        </span>
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </button>

                    <button type="button" class="management-option" data-show-form="categoryEditForm">
                        <span class="material-symbols-outlined">edit</span>
                        <span>
                            <strong>Edit Category</strong>
                            <small>Rename an existing category</small>
                        </span>
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </button>

                    <button type="button" class="management-option" data-show-form="categoryDeleteForm">
                        <span class="material-symbols-outlined">delete</span>
                        <span>
                            <strong>Delete Category</strong>
                            <small>Remove an existing category</small>
                        </span>
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </button>

                </div>

            </div>


            <!-- ADD CATEGORY FORM -->

            <div id="categoryAddForm" data-form-panel hidden>

                <button type="button" class="modal-back-btn" data-back-to-actions>
                    <span class="material-symbols-outlined">arrow_back</span>
                    Back
                </button>

                <h3>Add Category</h3>

                <form method="POST" action="../php/system_admin/category_process.php">

                    <input type="hidden" name="csrf_token" value="<?= systemAdminEscape($csrfToken) ?>">
                    <input type="hidden" name="action" value="add_category">

                    <div class="field">
                        <label for="addCategoryName">Category Name</label>
                        <input type="text" id="addCategoryName" name="category_name" placeholder="Enter category name" maxlength="100" required>
                    </div>

                    <button type="submit" class="confirm-btn">
                        Add Category
                    </button>

                </form>

            </div>


            <!-- EDIT CATEGORY FORM -->

            <div id="categoryEditForm" data-form-panel hidden>

                <button type="button" class="modal-back-btn" data-back-to-actions>
                    <span class="material-symbols-outlined">arrow_back</span>
                    Back
                </button>

                <h3>Edit Category</h3>

                <form method="POST" action="../php/system_admin/category_process.php">

                    <input type="hidden" name="csrf_token" value="<?= systemAdminEscape($csrfToken) ?>">
                    <input type="hidden" name="action" value="edit_category">

                    <div class="field">

                        <label for="editCategorySelect">Select Category</label>

                        <select name="edit_category_id" id="editCategorySelect" required>
                            <option value="">Select Category</option>

                            <?php foreach ($categories as $category): ?>

                                <option
                                    value="<?= (int) $category['category_id'] ?>"
                                    data-name="<?= systemAdminEscape($category['category_name']) ?>"
                                >
                                    <?= systemAdminEscape($category['category_name']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="field">
                        <label for="editCategoryName">Category Name</label>
                        <input type="text" name="edit_category_name" id="editCategoryName" maxlength="100" required>
                    </div>

                    <button type="submit" class="confirm-btn">
                        Save Changes
                    </button>

                </form>

            </div>


            <!-- DELETE CATEGORY FORM -->

            <div id="categoryDeleteForm" data-form-panel hidden>

                <button type="button" class="modal-back-btn" data-back-to-actions>
                    <span class="material-symbols-outlined">arrow_back</span>
                    Back
                </button>

                <h3>Delete Category</h3>

                <p>
                    A category cannot be deleted if it is currently
                    assigned to a club.
                </p>

                <form
                    method="POST"
                    action="../php/system_admin/category_process.php"
                    id="categoryDeleteFormSubmit"
                >

                    <input type="hidden" name="csrf_token" value="<?= systemAdminEscape($csrfToken) ?>">
                    <input type="hidden" name="action" value="delete_category">

                    <div class="field">

                        <label for="deleteCategoryId">Select Category</label>

                        <select name="delete_category_id" id="deleteCategoryId" required>
                            <option value="">Select Category</option>

                            <?php foreach ($categories as $category): ?>

                                <option value="<?= (int) $category['category_id'] ?>">
                                    <?= systemAdminEscape($category['category_name']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <button type="submit" class="confirm-btn delete-confirm-btn">
                        Delete Category
                    </button>

                </form>

            </div>

        </div>

    </div>


    <!-- =====================================
         SYSTEM NOTIFICATION MODAL
    ====================================== -->

    <div class="management-modal" id="notificationModal" hidden>

        <div class="management-modal-content">

            <div class="management-modal-header">

                <div>
                    <h2>System Notifications</h2>
                    <p>Send announcements and notifications to users.</p>
                </div>

                <button type="button" class="modal-close-btn" data-close-modal aria-label="Close">
                    <span class="material-symbols-outlined">close</span>
                </button>

            </div>


            <div class="notification-form-panel">

                <form
                    method="POST"
                    action="../php/system_admin/notification_process.php"
                    id="notificationForm"
                >

                    <input type="hidden" name="csrf_token" value="<?= systemAdminEscape($csrfToken) ?>">
                    <input type="hidden" name="action" value="send_notification">


                    <div class="field">

                        <label for="notificationAudience">Send To</label>

                        <select name="audience" id="notificationAudience" required>
                            <option value="all">All Users</option>
                            <option value="individual">Specific User</option>
                        </select>

                    </div>


                    <div class="field" id="notificationUserField" hidden>

                        <label for="notificationUser">Select User</label>

                        <select name="notification_user_id" id="notificationUser">
                            <option value="">Select User</option>

                            <?php foreach ($notificationRecipients as $recipient): ?>

                                <option value="<?= systemAdminEscape($recipient['user_id']) ?>">
                                    <?= systemAdminEscape($recipient['name']) ?>
                                    (<?= systemAdminEscape($recipient['user_id']) ?>)
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="field">
                        <label for="notificationTitle">Title</label>
                        <input
                            type="text"
                            name="notification_title"
                            id="notificationTitle"
                            placeholder="Enter notification title"
                            maxlength="255"
                            required
                        >
                    </div>


                    <div class="field">
                        <label for="notificationMessage">Message</label>
                        <textarea
                            name="notification_message"
                            id="notificationMessage"
                            placeholder="Write your notification..."
                            rows="5"
                            required
                        ></textarea>
                    </div>


                    <div class="field">

                        <label for="notificationType">Notification Type</label>

                        <select name="notification_type" id="notificationType" required>
                            <option value="" selected disabled>
                                Select Notification Type
                            </option>
                            <option value="System">System</option>
                            <option value="Event">Event</option>
                            <option value="Announcement">Announcement</option>
                        </select>

                    </div>


                    <div class="field">
                        <label for="notificationLink">Link (Optional)</label>
                        <input
                            type="url"
                            name="link_url"
                            id="notificationLink"
                            placeholder="https://example.com"
                        >
                    </div>


                    <button type="submit" class="confirm-btn">
                        Send Notification
                    </button>

                </form>

            </div>

        </div>

    </div>

</main>

<?php pageFooter(); ?>

</body>
</html>
