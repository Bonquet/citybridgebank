<?php
require_once '../includes/config.php';
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login first.', 'danger');
}

Security::logAudit('user_pin_change_blocked', 'User attempted to access admin-managed PIN change page', $_SESSION['user_id']);
redirectWithMessage('security.php', 'Authentication, Payment, and Secure PINs are admin-managed. Please open a support ticket for assistance.', 'info');
