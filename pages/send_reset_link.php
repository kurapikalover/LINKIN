
<?php

require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/password_reset_functions.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot_password.php');
    exit;
}

// Check CSRF token
if (!verifyResetCsrfToken()) {
    exit('Invalid request. Please return to the forgot password page.');
}

$email = strtolower(trim($_POST['email'] ?? ''));

$message = 'If an account exists with that email address, a reset link has been generated.';

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $message = 'Please enter a valid email address.';
} else {

    // Find the account
    $sql = "
        SELECT user_id
        FROM public.users
        WHERE LOWER(email) = :email
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {

        try {
            // Generate a secure random token
            $token = bin2hex(random_bytes(32));

            // Store only the token hash
            $tokenHash = hash('sha256', $token);

            // Reset link expires after 30 minutes
            $expiresAt = date('Y-m-d H:i:sP', time() + 1800);

            $pdo->beginTransaction();

            // Invalidate previous unused reset links
            $invalidateSql = "
                UPDATE public.password_resets
                SET used_at = CURRENT_TIMESTAMP
                WHERE user_id = :user_id
                  AND used_at IS NULL
            ";

            $invalidateStmt = $pdo->prepare($invalidateSql);
            $invalidateStmt->execute([
                'user_id' => $user['user_id']
            ]);

            // Insert new reset token
            $insertSql = "
                INSERT INTO public.password_resets
                    (user_id, token_hash, expires_at)
                VALUES
                    (:user_id, :token_hash, :expires_at)
            ";

            $insertStmt = $pdo->prepare($insertSql);

            $insertStmt->execute([
                'user_id' => $user['user_id'],
                'token_hash' => $tokenHash,
                'expires_at' => $expiresAt
            ]);

            $pdo->commit();

            // LOCAL TESTING ONLY
            // Replace this with email sending when deploying.
            $resetLink = 'http://localhost/Ubuddy/pages/reset_password.php?token='
                . urlencode($token);

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log('Password reset error: ' . $e->getMessage());

            $message = 'Something went wrong. Please try again later.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset | ÜBuddy</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/shared.css">

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
            max-width: 480px;
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        h2 {
            color: #172A46;
        }

        p {
            color: #858D99;
            font-size: 14px;
            line-height: 1.7;
        }

        .reset-link {
            display: block;
            overflow-wrap: anywhere;
            background: #EAF0F8;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            color: #263F63;
            font-size: 13px;
        }

        .reset-link a {
            color: #426FB1;
        }

        .back-link {
            display: inline-block;
            margin-top: 15px;
            color: #426FB1;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="reset-container">

        <h2>Password Reset</h2>

        <p>
            <?= escapeResetOutput($message) ?>
        </p>

        <?php if (isset($resetLink)): ?>

            <p>
                Since ÜBuddy is currently in local testing mode,
                your reset link is displayed below instead of being
                sent through email.
            </p>

            <div class="reset-link">
                <a href="<?= escapeResetOutput($resetLink) ?>">
                    Open Password Reset Link
                </a>
            </div>

            <p>
                This link will expire in 30 minutes and can only
                be used once.
            </p>

        <?php endif; ?>

        <a href="login.php" class="back-link">
            Back to Login
        </a>

    </div>

</body>
</html>
