
<?php

require_once __DIR__ . '/../php/password_reset_functions.php';

$csrfToken = getResetCsrfToken();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | ÜBuddy</title>

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

        .reset-container h2 {
            color: #172A46;
            margin-bottom: 10px;
        }

        .reset-container p {
            color: #858D99;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .reset-container input {
            width: 100%;
            box-sizing: border-box;
            padding: 13px;
            border: 1px solid #C7D4E5;
            border-radius: 8px;
            margin-bottom: 18px;
            font-family: inherit;
        }

        .reset-container button {
            width: 100%;
            padding: 13px;
            background: #426FB1;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: inherit;
            font-weight: 600;
        }

        .reset-container button:hover {
            background: #263F63;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #426FB1;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="reset-container">

        <h2>Forgot Password?</h2>

        <p>
            Enter the email address registered to your ÜBuddy account.
            We'll generate a password reset link for you.
        </p>

        <form action="send_reset_link.php" method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= escapeResetOutput($csrfToken) ?>"
            >

            <input
                type="email"
                name="email"
                placeholder="Enter your email address"
                required
            >

            <button type="submit">
                Generate Reset Link
            </button>

        </form>

        <a href="login.php" class="back-link">
            Back to Login
        </a>

    </div>

</body>
</html>
