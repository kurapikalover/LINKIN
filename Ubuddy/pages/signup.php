<?php

require_once __DIR__ . '/../php/signup_process.php';
require_once __DIR__ . '/../php/shared_functions.php'; //for escapeHTML()
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubuddy - SignUp</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/shared.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <div class="signup-page">

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
        <div class="signup-section">
            <div class="signup-box">
                <h1>Sign Up</h1>
                <h3>Please enter all the details</h3>

                <!-- NEW -->
                <!-- Server-side errors (same .login-error style as the login page) -->
                <?php foreach ($errors as $err): ?>
                    <p class= "login-error" role= "alert"><?= escapeHTML($err) ?></p>
                <?php endforeach; ?>

                <!-- novalidate: the JS messages are used instead of browser popups -->
                <form id="signupForm" method="POST" action="signup.php" novalidate>
                    <!-- NEW -->
                    <input type="hidden" name="csrf" value="<?= escapeHTML($_SESSION['csrf']) ?>">

                    <label for="full_name">Full Name</label>
                    <!-- added value="escapeHTML($old['name']) ?>" -->
                    <input type="text" id="full_name" name="full_name" placeholder="Enter your full name" value="<?= escapeHTML($old['name']) ?>" required>

                    <label for="email">Email</label>
                    <!-- added like previous tpi for email -->
                    <input type="email" id="email" name="email" placeholder="Enter your email" value="<?= escapeHTML($old['name']) ?>" required>

                    <label for="password">Password</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password"
                            placeholder="Enter your password" autocomplete="new-password" required>
                         <button type="button" id="toggle-password" aria-label="toggle-password">👁</button>
                    </div>

                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                        placeholder="Re-enter your password" autocomplete="new-password" required>

                    <p class="form-message" id="matchMessage" aria-live="polite"></p>

                    <button type="submit" class="signup-button">SIGN UP</button>

                    <p class="login-text">
                        Logging back in the account?
                        <a href="login.php">Login</a>
                    </p>
                 </form>
             </div>
         </div>
    </div>

    <script src="../js/sign-up.js"></script>

</body>
</html>

