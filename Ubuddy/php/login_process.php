
<?php
session_start();

// Get any login error from the previous request
$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

require_once __DIR__ . '/db.php';

// Process the login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $enteredPassword = $_POST['password'] ?? '';

    if ($email === '' || $enteredPassword === '') {
        $error = 'Please enter your email and password.';
    } else {
        $sql = "
            SELECT user_id, name, email, password, user_type
            FROM public.users
            WHERE LOWER(email) = LOWER(:email)
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($enteredPassword, $user['password'])) {

            // Check the account type before creating a login session
            if ($user['user_type'] === 'System Admin') {
                $destination = 'admin.php';
            } elseif ($user['user_type'] === 'Student') {
                $destination = 'homepage.php';
            } else {
                $error = 'Your account type is not recognised.';
                $destination = null;
            }

            if ($destination !== null) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];

                header("Location: $destination");
                exit;
            }

        } else {
            // Store the error and return to the login page
            $_SESSION['login_error'] = 'Invalid email or password.';
            header('Location: login.php');
            exit;
        }
    }
}
?>
