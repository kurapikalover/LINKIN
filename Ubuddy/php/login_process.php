
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

            // Default destination
            $destination = null;

            // Check account type
            if ($user['user_type'] === 'System Admin') {

                $destination = 'admin.php';

            } elseif ($user['user_type'] === 'Student') {

                // Check whether the student is a committee admin
                $adminSql = "
                    SELECT c.club_id
                    FROM membership m
                    JOIN club c ON m.club_id = c.club_id
                    WHERE m.user_id = :user_id
                      AND m.membership_status = 'Enrolled'
                      AND m.is_admin = TRUE
                    ORDER BY c.club_name
                    LIMIT 1
                ";

                $adminStmt = $pdo->prepare($adminSql);
                $adminStmt->execute([
                    'user_id' => $user['user_id']
                ]);

                $adminClub = $adminStmt->fetch(PDO::FETCH_ASSOC);

                if ($adminClub) {
                    // Committee admin goes to their admin page
                    $destination = 'com-admin.php?id=' .
                        urlencode($adminClub['club_id']);
                } else {
                    // Regular student goes to homepage
                    $destination = 'homepage.php';
                }

            } else {
                $error = 'Your account type is not recognised.';
            }

            // Create login session and redirect
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
            // Invalid login details
            $_SESSION['login_error'] = 'Invalid email or password.';
            header('Location: login.php');
            exit;
        }
    }
}
?>
