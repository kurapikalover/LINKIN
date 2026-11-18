
<?php

require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/password_reset_functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot_password.php');
    exit;
}

if (!verifyResetCsrfToken()) {
    exit('Invalid request. Please try again.');
}

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

$error = '';
$success = false;

// Validate token format
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    $error = 'Invalid password reset link.';
}

// Validate password
elseif (strlen($password) < 8) {
    $error = 'Your password must contain at least 8 characters.';
}

elseif ($password !== $confirmPassword) {
    $error = 'Your passwords do not match.';
}

else {

    $tokenHash = hash('sha256', $token);

    try {
        $pdo->beginTransaction();

        // Check and lock the reset token
        $sql = "
            SELECT reset_id, user_id
            FROM public.password_resets
            WHERE token_hash = :token_hash
              AND expires_at > CURRENT_TIMESTAMP
              AND used_at IS NULL
            FOR UPDATE
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['token_hash' => $tokenHash]);

        $reset = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reset) {

            $pdo->rollBack();

            $error = 'This reset link has expired or has already been used.';

        } else {

            // Hash the new password
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Update the student's password
            $updateSql = "
                UPDATE public.users
                SET password = :password
                WHERE user_id = :user_id
            ";

            $updateStmt = $pdo->prepare($updateSql);

            $updateStmt->execute([
                'password' => $hashedPassword,
                'user_id' => $reset['user_id']
            ]);

            // Mark this reset token as used
            $usedSql = "
                UPDATE public.password_resets
                SET used_at = CURRENT_TIMESTAMP
                WHERE reset_id = :reset_id
            ";

            $usedStmt = $pdo->prepare($usedSql);

            $usedStmt->execute([
                'reset_id' => $reset['reset_id']
            ]);

            // Invalidate all other reset links for this user
            $invalidateSql = "
                UPDATE public.password_resets
                SET used_at = CURRENT_TIMESTAMP
                WHERE user_id = :user_id
                  AND used_at IS NULL
            ";

            $invalidateStmt = $pdo->prepare($invalidateSql);

            $invalidateStmt->execute([
                'user_id' => $reset['user_id']
            ]);

            $pdo->commit();

            $success = true;
        }

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log('Password update error: ' . $e->getMessage());

        $error = 'Something went wrong. Please try again later.';
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Updated | ÜBuddy</title>

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
            max-width: 430px;
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

        a {
            display: inline-block;
            margin-top: 15px;
            padding: 12px 25px;
            background: #426FB1;
            color: white;
            border-radius: 8px;
            text-decoration: none;
        }

        a:hover {
            background: #263F63;
        }

        .error {
            color: #C0392B;
        }
    </style>
</head>

<body>

    <div class="reset-container">

        <?php if ($success): ?>

            <h2>Password Updated!</h2>

            <p>
                Your password has been successfully changed.
                You can now log in using your new password.
            </p>

            <a href="login.php">Back to Login</a>

        <?php else: ?>

            <h2>Password Reset Failed</h2>

            <p class="error">
                <?= escapeResetOutput($error) ?>
            </p>

            <a href="forgot_password.php">Try Again</a>

        <?php endif; ?>

    </div>

</body>
</html>
