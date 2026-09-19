<?php
require_once __DIR__ . '/../models/User.php';
function dd($stuff)
{
    echo '<pre>';
    var_dump($stuff);
    echo '</pre>';
    die();
}

function sanitize($dirty)
{
    return htmlentities($dirty, ENT_QUOTES, "UTF-8");
}

function currentUser()
{

    if (isset($_SESSION['user_id'])) {
        $user = new User();
        $currentUser = $user->where(['user_id' => $_SESSION['user_id']]);
        return $currentUser[0];
    }
    return false;
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function setFlash($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
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

// // function generateCsrfToken()
// // {
// //     if (empty($_SESSION['csrf_token'])) {
// //         $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
// //     }

// //     return $_SESSION['csrf_token'];
// // }

// // function csrf_field()
// // {
// //     return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken()) . '">';
// // }

// function verifyCsrfToken($token)
// {
//     return; // isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string) $token);
// }

function requireLogin()
{
    if (!isLoggedIn()) {
        setFlash('error', 'Please log in to access that page.');
        header('Location: /user/login');
        exit;
    }
}

function requireGuest()
{
    if (isLoggedIn()) {
        header('Location: /dashboard');
        exit;
    }
}

function checkRegisterForm($post)
{
    $errors = [];

    if (empty($post['username'])) {
        $errors['username'] = "Username is required.";
    }

    if (empty($post['email'])) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }

    $user = new User();
    $existingUser = $user->where(['user_email' => trim($post['email'])]);
    if (!empty($existingUser)) {
        $errors['email'] = "Email is already registered.";
    }

    if (empty($post['password'])) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($post['password']) < 6) {
        $errors['password'] = "Password must be at least 6 characters long.";
    }

    if (empty($post['repassword'])) {
        $errors['repassword'] = "Confirm Password is required.";
    } elseif ($post['password'] !== $post['repassword']) {
        $errors['repassword'] = "Passwords do not match.";
    }

    return $errors;
}

function format_pretty_date($datetimeString)
{
    $dt = new DateTime($datetimeString);

    // Day with ordinal suffix
    $day = $dt->format('j');
    $suffix = 'th';

    if (!in_array(($day % 100), [11, 12, 13])) {
        switch ($day % 10) {
            case 1:
                $suffix = 'st';
                break;
            case 2:
                $suffix = 'nd';
                break;
            case 3:
                $suffix = 'rd';
                break;
        }
    }

    $dayWithSuffix = $day . $suffix;

    // Build final string
    return $dayWithSuffix . ' ' . $dt->format('F Y') . ' at ' . $dt->format('g:ia');
}