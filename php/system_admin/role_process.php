
<?php

require_once __DIR__ . '/process_helper.php';

$targetUserSearch = trim($_POST['target_user_id'] ?? '');
$role = trim($_POST['role'] ?? '');

$allowedRoles = [
    'committee_admin',
    'hep_admin',
    'system_admin'
];

if ($targetUserSearch === '') {
    systemAdminProcessError(
        'Please enter a user ID or name.'
    );
}

if (!in_array($role, $allowedRoles, true)) {
    systemAdminProcessError(
        'Please select a valid role.'
    );
}

try {

    // Find by exact user ID or full name.
    $targetUsers = findSystemAdminTargetUser(
        $pdo,
        $targetUserSearch
    );

    if (count($targetUsers) === 0) {
        systemAdminProcessError(
            'No matching user was found.'
        );
    }

    if (count($targetUsers) > 1) {
        systemAdminProcessError(
            'More than one user has that name. Please enter their user ID instead.'
        );
    }

    $targetUser = $targetUsers[0];
    $targetId = (string) $targetUser['user_id'];

    // Prevent changing your own role.
    if ($targetId === $userId) {
        systemAdminProcessError(
            'You cannot change your own role here.'
        );
    }

    $clubId = null;

    if ($role === 'committee_admin') {

        $clubId = filter_input(
            INPUT_POST,
            'club_id',
            FILTER_VALIDATE_INT
        );

        if (!$clubId || $clubId < 1) {
            systemAdminProcessError(
                'Please select a club.'
            );
        }
    }

    $updated = assignSystemAdminRole(
        $pdo,
        $targetId,
        $role,
        $clubId
    );

    if (!$updated) {

        if ($role === 'committee_admin') {
            systemAdminProcessError(
                'The user must already be a member of the selected club before becoming a Committee Admin.'
            );
        }

        systemAdminProcessError(
            'The user role could not be updated.'
        );
    }

    $_SESSION['system_admin_success'] =
        'User role updated successfully.';

} catch (PDOException $e) {

    error_log(
        'Assign role error: ' . $e->getMessage()
    );

    systemAdminProcessError(
        'Something went wrong while assigning the role.'
    );
}

systemAdminProcessRedirect();
