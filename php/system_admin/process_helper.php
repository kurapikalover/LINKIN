
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../system_admin_funct.php';

/*
|--------------------------------------------------------------------------
| Redirect back to System Admin page
|--------------------------------------------------------------------------
*/
function systemAdminProcessRedirect(array $params = []): void
{
    $url = '../../pages/system-admin.php';

    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    header('Location: ' . $url);
    exit;
}

/*
|--------------------------------------------------------------------------
| Set error message and redirect
|--------------------------------------------------------------------------
*/
function systemAdminProcessError(
    string $message,
    array $params = []
): void {
    $_SESSION['system_admin_error'] = $message;
    systemAdminProcessRedirect($params);
}

/*
|--------------------------------------------------------------------------
| Check login
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../pages/login.php');
    exit;
}

$userId = (string) $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Check System Admin access
|--------------------------------------------------------------------------
*/
$admin = getSystemAdminDetails($pdo, $userId);

if (!$admin) {
    header('Location: ../../pages/homepage.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Only allow POST requests
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    systemAdminProcessRedirect();
}

/*
|--------------------------------------------------------------------------
| Validate CSRF token
|--------------------------------------------------------------------------
*/
$submittedToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['system_admin_csrf'] ?? '';

if (
    empty($submittedToken) ||
    empty($sessionToken) ||
    !hash_equals($sessionToken, $submittedToken)
) {
    systemAdminProcessError(
        'Your session has expired. Please try again.'
    );
}
