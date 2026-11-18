
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate a CSRF token
function getResetCsrfToken(): string
{
    if (empty($_SESSION['reset_csrf_token'])) {
        $_SESSION['reset_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['reset_csrf_token'];
}

// Verify a submitted CSRF token
function verifyResetCsrfToken(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['reset_csrf_token'])
        && hash_equals(
            $_SESSION['reset_csrf_token'],
            $_POST['csrf_token']
        );
}

// Escape output before displaying it in HTML
function escapeResetOutput($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
