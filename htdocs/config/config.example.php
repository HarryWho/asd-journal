<?php
// ============================================
// Site configuration - TEMPLATE
// ============================================
// Copy this file to config.php (same folder) and fill in real values.
// config.php itself is in .gitignore and must NEVER be committed -
// it holds real database credentials.

if ($_SERVER['HTTP_HOST'] == 'your-local-dev-host.local') {
    define('DB_HOST', '127.0.0.1');
    define('DB_NAME', 'asd_journal');
    define('DB_USER', 'root');
    define('DB_PASS', 'your-local-password-here');
    define('BASE_URL', 'http://your-local-dev-host.local/');
    define('APP_DEBUG', true);
} else {
    define('DB_HOST', 'your-live-db-host-here');
    define('DB_NAME', 'your-live-db-name-here');
    define('DB_USER', 'your-live-db-user-here');
    define('DB_PASS', 'your-live-db-password-here');
    define('BASE_URL', 'https://your-live-domain-here/');
    define('APP_DEBUG', false);
}

define('SITE_NAME', 'Me and my AI');

if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../storage/logs/error.log');
}

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') == 443);

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

// ============================================
// CSRF protection
// ============================================
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    $expected  = $_SESSION['csrf_token'] ?? '';

    if ($expected === '' || !hash_equals($expected, $submitted)) {
        http_response_code(403);
        die('Your form session expired or was submitted from an untrusted source. Please go back and try again.');
    }
}
