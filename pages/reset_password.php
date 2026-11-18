
<?php

require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/password_reset_functions.php';

$token = $_GET['token'] ?? '';
$error = '';

$resetId = null;

// Check token format
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    $error = 'This password reset link is invalid.';
} else {

    $tokenHash = hash('sha256', $token);

    $sql = "
        SELECT reset_id
        FROM public.password_resets
        WHERE token_hash = :token_hash
          AND expires_at > CURRENT_TIMESTAMP
          AND used_at IS NULL
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['token_hash' => $tokenHash]);

    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($reset) {
        $resetId = $reset['reset_id'];
    } else {
        $error = 'This reset link has expired or has already been used.';
    }
}

$csrfToken = getResetCsrfToken();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | ÜBuddy</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/shared.css">

    <!-- Material Symbols for password visibility icons -->
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"
        rel="stylesheet"
    >

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #F5F7FB;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .reset-container {
            width: 90%;
            max-width: 430px;
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        h2 {
            color: #172A46;
            text-align: center;
        }

        p {
            color: #858D99;
            font-size: 14px;
            text-align: center;
        }

        /* Password input wrapper */
        .password-wrapper {
            position: relative;
            width: 100%;
        }

        /* Password inputs */
        .password-wrapper input {
            width: 100%;
            box-sizing: border-box;
            padding: 13px 45px 13px 13px;
            border: 1px solid #C7D4E5;
            border-radius: 8px;
            margin-top: 15px;
            font-family: inherit;
        }

        /* Show/hide password button */
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-30%);
            width: auto;
            padding: 5px;
            margin: 0;
            background: none;
            border: none;
            color: #858D99;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-password:hover {
            background: none;
            color: #172A46;
        }

        .toggle-password span {
            font-size: 21px;
        }

        /* Update password button */
        .submit-btn {
            width: 100%;
            padding: 13px;
            background: #426FB1;
            color: white;
            border: none;
            border-radius: 8px;
            margin-top: 20px;
            cursor: pointer;
            font-family: inherit;
            font-weight: 600;
        }

        .submit-btn:hover {
            background: #263F63;
        }

        .error {
            color: #C0392B;
            text-align: center;
            font-size: 14px;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            color: #426FB1;
            text-align: center;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="reset-container">

        <h2>Reset Password</h2>

        <?php if ($error !== ''): ?>

            <p class="error">
                <?= escapeResetOutput($error) ?>
            </p>

            <a href="forgot_password.php" class="back-link">
                Request a new reset link
            </a>

        <?php else: ?>

            <p>Enter your new password below.</p>

            <form action="update_password.php" method="POST">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escapeResetOutput($csrfToken) ?>"
                >

                <input
                    type="hidden"
                    name="token"
                    value="<?= escapeResetOutput($token) ?>"
                >

                <!-- New Password -->
                <div class="password-wrapper">

                    <input
                        type="password"
                        id="newPassword"
                        name="password"
                        placeholder="New password"
                        minlength="8"
                        required
                    >

                    <button
                        type="button"
                        class="toggle-password"
                        onclick="togglePassword('newPassword', this)"
                        aria-label="Show password"
                    >
                        <span class="material-symbols-outlined">
                            visibility
                        </span>
                    </button>

                </div>

                <!-- Confirm Password -->
                <div class="password-wrapper">

                    <input
                        type="password"
                        id="confirmPassword"
                        name="confirm_password"
                        placeholder="Confirm new password"
                        minlength="8"
                        required
                    >

                    <button
                        type="button"
                        class="toggle-password"
                        onclick="togglePassword('confirmPassword', this)"
                        aria-label="Show password"
                    >
                        <span class="material-symbols-outlined">
                            visibility
                        </span>
                    </button>

                </div>

                <button type="submit" class="submit-btn">
                    Update Password
                </button>

            </form>

        <?php endif; ?>

    </div>

    <script>
        function togglePassword(inputId, button) {

            const input = document.getElementById(inputId);
            const icon = button.querySelector('span');

            if (input.type === 'password') {

                input.type = 'text';
                icon.textContent = 'visibility_off';

                button.setAttribute('aria-label', 'Hide password');

            } else {

                input.type = 'password';
                icon.textContent = 'visibility';

                button.setAttribute('aria-label', 'Show password');

            }
        }
    </script>

</body>
</html>
