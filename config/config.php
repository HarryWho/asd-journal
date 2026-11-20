<?php
// ============================================
// Site configuration
// ============================================
// !! URGENT !! The DB_PASS and FTP password below were exposed in a public
// git commit. If you haven't already, rotate BOTH your database password
// and your FTP password in the InfinityFree control panel RIGHT NOW, using
// two DIFFERENT passwords - then paste the new DB password in below.
// This file is in .gitignore and will never be committed again.

if ($_SERVER['HTTP_HOST'] == 'willthisdo42web.local') {
    define('DB_HOST', '127.0.0.1');
    define('DB_NAME', 'asd_journal');
    define('DB_USER', 'root');       // change for production
    define('DB_PASS', 'bill42');           // change for production

    define('BASE_URL', 'http://willthisdo42web.local/'); // update when you have a real domain
    // --- Environment ---
    // Set this to true only while actively developing locally.
    // On the live server, this MUST be false - it stops PHP from showing
    // stack traces / file paths / DB details to visitors when something breaks.
    define('APP_DEBUG', true);
} else {
    define('DB_HOST', 'sql309.infinityfree.com');
    define('DB_NAME', 'if0_38995519_asd_journal');
    define('DB_USER', 'if0_38995519');       // change for production
    define('DB_PASS', 'SallyRose27');
    define('BASE_URL', 'https://willthisdo.42web.io/'); // update when you have a real domain
    // --- Environment ---
    // Set this to true only while actively developing locally.
    // On the live server, this MUST be false - it stops PHP from showing
    // stack traces / file paths / DB details to visitors when something breaks.
    define('APP_DEBUG', false);
}
// --- Database ---


// --- Site ---
define('SITE_NAME', 'Me and my AI'); // rename however you like


if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../storage/logs/error.log');
}

// --- Sessions (needed for login state) ---
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') == 443);

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $isHttps,   // cookie only sent over HTTPS, when available
        'httponly' => true,       // JavaScript can't read the session cookie
        'samesite' => 'Lax',      // blocks the cookie being sent from most cross-site requests
    ]);

    session_start();
}

// ============================================
// CSRF protection
// ============================================
// Every state-changing form (create/update/delete/approve/etc.) must include
// csrf_field() and every matching controller action must call verify_csrf()
// before doing anything - this stops another website from tricking your
// browser into submitting actions on your site while you're logged in.

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
