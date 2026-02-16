<?php
// CITYBRIDGEBANK Logout
require_once 'config.php';

// Log logout before destroying session
if (isset($_SESSION['user_id'])) {
    Security::logAudit('user_logout', "User logged out: {$_SESSION['username']}", $_SESSION['user_id']);
} elseif (isset($_SESSION['admin_id'])) {
    Security::logAudit('admin_logout', "Admin logged out: {$_SESSION['admin_username']}", null, $_SESSION['admin_id']);
}

// Destroy session
$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

session_destroy();

// After destroying the session, start a new session so that flash
// messages can be stored when redirecting. Without starting a new
// session here, the redirectWithMessage() function will attempt to
// write to the $_SESSION array without an active session, resulting
// in flash messages not being saved for the next request.
session_start();

// Clear remember me cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 42000, '/');
}

// Redirect to home
// Use public index.php instead of root index (fix broken path)
redirectWithMessage('../public/index.php', 'You have been logged out successfully.', 'success');
?>