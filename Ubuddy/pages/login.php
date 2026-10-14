<?php
session_start();
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
} else {
    $error = '';
}

require_once __DIR__ . '/../php/db.php';

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
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];

            if ($user['user_type'] === 'System Admin') {
                header('Location: admin.php');
                exit;
            }

            if ($user['user_type'] === 'Student') {
                header('Location: homepage.php');
                exit;
            }

            $error = 'Your account type is not recognised.';
            session_unset();
        } else {
                $_SESSION['login_error'] = 'Invalid email or password.';
                header('Location: login.php');
                exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubuddy - Login</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/shared.css">
   
</head>

<body>

    <div class="login-page">

        <!-- Navy section -->
        <div class="brand-section">
            
            <div class = "brand-content">
                <h2>ÜBuddy</h2>
                <p class="tagline">Let Ubuddy be your buddy.</p>
                <p class="announcement">Club applications are open.<br>Sign up today!</p>
            </div>
            <svg class="wave-divider" viewBox="0 0 100 1000" preserveAspectRatio="none">
                <path d="M 50 0
                        C 10 150, 10 350, 50 500
                        C 90 650, 90 850, 50 1000
                        L 100 1000
                        L 100 0
                        Z">
                </path>
            </svg>


        </div>

        <!-- White section -->
        <div class="login-section">

            <div class="login-box">
                <h1>Login</h1>

                <?php if ($error !== ''): ?>
                    <p class="login-error" role="alert">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </p>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        required
                    >

                    <label for="password">Password</label>
                    <div class="password-container">
                        <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >

                     <button type="button" id="toggle-password">👁</button>

                    </div>
                    <div class="login-options">

                        <label class="remember-me">
                            <input type="checkbox" id="rememberMe">
                            <span>Remember me</span>
                        </label>

                        <a href="#" class="forgot-password">
                            Forgot password?
                        </a>

                        </div>
                    
                    <button type="submit" class="login-button">
                        LOGIN
                    </button>

                    <p class="signup-text">
                        Don't have an account?
                        <a href="#">Sign up</a>
                    </p>
                </form>
            </div>

        </div>

        

    </div>

    <script src="../js/script.js"></script>

</body>
</html>