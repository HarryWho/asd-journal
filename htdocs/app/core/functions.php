<?php
// ============================================
// General-purpose helper functions
// ============================================
// Keep this file to small, self-contained utilities that don't depend on
// a specific model's shape. Anything tied to users/auth belongs on
// Controller.php instead (see isLoggedIn/isAdmin/requireLogin there),
// since that's what the rest of the app already uses and expects.

// Quick debug dump - prints a variable and stops execution.
// Handy while building something; remove the call once you're done,
// don't leave it running on the live site.
function dd($stuff)
{
    echo '<pre>';
    var_dump($stuff);
    echo '</pre>';
    die();
}

// Escapes a string for safe HTML output. Equivalent to htmlspecialchars()
// with ENT_QUOTES - use this (or htmlspecialchars directly, same effect)
// any time you're echoing user-provided text into a page.
function sanitize($dirty)
{
    return htmlentities($dirty, ENT_QUOTES, "UTF-8");
}

// One-time "flash" messages - set one before a redirect, read it (and it
// clears itself) on the next page load. Good for "Comment approved" or
// "Post deleted" style confirmations. Not wired into any view yet -
// call setFlash() before a redirect() and getFlash() in a layout to use it.
function setFlash($type, $message)
{
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

function getFlash()
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}