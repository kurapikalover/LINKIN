
<?php

require_once __DIR__ . '/process_helper.php';

/*
|--------------------------------------------------------------------------
| Allowed account types
|--------------------------------------------------------------------------
*/
$allowedUserTypes = [
    'Student',
    'Student Affairs',
    'System Admin'
];

$action = trim($_POST['action'] ?? '');


try {

    /*
    |--------------------------------------------------------------------------
    | ADD ACCOUNT
    |--------------------------------------------------------------------------
    */
    if ($action === 'add_account') {

        $newUserId = trim($_POST['user_id'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $faculty = trim($_POST['faculty'] ?? '');
        $userType = trim($_POST['user_type'] ?? '');

        // Validate required fields
        if (
            $newUserId === '' ||
            $name === '' ||
            $email === '' ||
            $password === '' ||
            $userType === ''
        ) {
            systemAdminProcessError(
                'Please fill in all required account fields.'
            );
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            systemAdminProcessError(
                'Please enter a valid email address.'
            );
        }

        if (!in_array($userType, $allowedUserTypes, true)) {
            systemAdminProcessError(
                'Please select a valid account type.'
            );
        }

        if (strlen($password) < 8) {
            systemAdminProcessError(
                'Password must contain at least 8 characters.'
            );
        }

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $success = addSystemAdminAccount($pdo, [
            'user_id'  => $newUserId,
            'name'     => $name,
            'email'    => $email,
            'password' => $hashedPassword,
            'faculty'  => $faculty !== '' ? $faculty : null,
            'user_type' => $userType
        ]);

        if (!$success) {
            systemAdminProcessError(
                'Unable to create the account.'
            );
        }

        $_SESSION['system_admin_success'] =
            'Account created successfully.';


    /*
    |--------------------------------------------------------------------------
    | EDIT ACCOUNT
    |--------------------------------------------------------------------------
    */
    } elseif ($action === 'edit_account') {

        $editUserId = trim($_POST['edit_user_id'] ?? '');
        $name = trim($_POST['edit_name'] ?? '');
        $email = trim($_POST['edit_email'] ?? '');
        $password = $_POST['edit_password'] ?? '';
        $faculty = trim($_POST['edit_faculty'] ?? '');
        $userType = trim($_POST['edit_user_type'] ?? '');

        if (
            $editUserId === '' ||
            $name === '' ||
            $email === '' ||
            $userType === ''
        ) {
            systemAdminProcessError(
                'Please fill in all required account fields.'
            );
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            systemAdminProcessError(
                'Please enter a valid email address.'
            );
        }

        if (!in_array($userType, $allowedUserTypes, true)) {
            systemAdminProcessError(
                'Please select a valid account type.'
            );
        }

        // Prevent the currently logged-in System Admin
        // from removing their own System Admin access.
        if (
            $editUserId === $userId &&
            $userType !== 'System Admin'
        ) {
            systemAdminProcessError(
                'You cannot remove your own System Admin access.'
            );
        }

        $hashedPassword = null;

        if ($password !== '') {

            if (strlen($password) < 8) {
                systemAdminProcessError(
                    'New password must contain at least 8 characters.'
                );
            }

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );
        }

        $success = updateSystemAdminAccount(
            $pdo,
            $editUserId,
            [
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
                'faculty' => $faculty !== '' ? $faculty : null,
                'user_type' => $userType
            ]
        );

        if (!$success) {
            systemAdminProcessError(
                'Unable to update the account. Please check the user ID.'
            );
        }

        $_SESSION['system_admin_success'] =
            'Account updated successfully.';


    /*
    |--------------------------------------------------------------------------
    | DELETE ACCOUNT
    |--------------------------------------------------------------------------
    */
    } elseif ($action === 'delete_account') {

        $deleteUserId = trim($_POST['delete_user_id'] ?? '');

        if ($deleteUserId === '') {
            systemAdminProcessError(
                'Please select an account to delete.'
            );
        }

        // Prevent deleting the currently logged-in account.
        if ($deleteUserId === $userId) {
            systemAdminProcessError(
                'You cannot delete your own account.'
            );
        }

        $success = deleteSystemAdminAccount(
            $pdo,
            $deleteUserId
        );

        if (!$success) {
            systemAdminProcessError(
                'Unable to delete the account. It may no longer exist.'
            );
        }

        $_SESSION['system_admin_success'] =
            'Account deleted successfully.';


    } else {

        systemAdminProcessError(
            'Invalid account action.'
        );
    }

} catch (PDOException $e) {

    error_log(
        'Account processing error: ' . $e->getMessage()
    );

    // PostgreSQL unique violation
    if ($e->getCode() === '23505') {
        systemAdminProcessError(
            'That User ID or email address is already in use.'
        );
    }

    // PostgreSQL foreign key violation
    if ($e->getCode() === '23503') {
        systemAdminProcessError(
            'This account is linked to other records and cannot be deleted.'
        );
    }

    systemAdminProcessError(
        'Something went wrong while processing the account.'
    );
}

systemAdminProcessRedirect();
