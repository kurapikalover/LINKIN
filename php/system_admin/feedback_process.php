
<?php

require_once __DIR__ . '/process_helper.php';

$action = $_POST['action'] ?? '';

$feedbackId = filter_input(
    INPUT_POST,
    'feedback_id',
    FILTER_VALIDATE_INT
);

$returnSearch = trim($_POST['return_search'] ?? '');
$returnStatus = trim($_POST['return_status'] ?? 'Pending');

$allowedStatuses = [
    'Pending',
    'Reviewed',
    'Archived'
];

if (!in_array($returnStatus, $allowedStatuses, true)) {
    $returnStatus = 'Pending';
}

$redirectParams = [
    'status' => $returnStatus
];

if ($returnSearch !== '') {
    $redirectParams['search'] = $returnSearch;
}

if (!$feedbackId || $feedbackId < 1) {

    systemAdminProcessError(
        'Invalid feedback selected.',
        $redirectParams
    );
}

try {

    if ($action === 'mark_feedback_reviewed') {

        $success = markSystemFeedbackReviewed(
            $pdo,
            $feedbackId
        );

        if (!$success) {

            systemAdminProcessError(
                'Unable to update feedback. It may already have been reviewed or archived.',
                $redirectParams
            );
        }

        $_SESSION['system_admin_success'] =
            'Feedback marked as reviewed.';

    } elseif ($action === 'archive_feedback') {

        $success = archiveSystemFeedback(
            $pdo,
            $feedbackId
        );

        if (!$success) {

            systemAdminProcessError(
                'Unable to archive feedback. It may already be archived.',
                $redirectParams
            );
        }

        $_SESSION['system_admin_success'] =
            'Feedback archived successfully.';

    } else {

        systemAdminProcessError(
            'Invalid feedback action.',
            $redirectParams
        );
    }

} catch (PDOException $e) {

    error_log(
        'Feedback processing error: ' . $e->getMessage()
    );

    systemAdminProcessError(
        'Something went wrong while processing the feedback.',
        $redirectParams
    );
}

systemAdminProcessRedirect($redirectParams);
