<?php
require_once '../includes/config.php';

if (!Security::isAdminLoggedIn()) {
    redirectWithMessage('login.php', 'Please login first.', 'danger');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('users.php', 'Invalid request.', 'danger');
}
if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirectWithMessage('users.php', 'Security verification failed.', 'danger');
}

$userId = (int)($_POST['user_id'] ?? 0);
$reason = trim(Security::sanitizeInput($_POST['reason'] ?? ''));
$forceReset = isset($_POST['force_reset']) ? 1 : 0;

if ($userId <= 0 || $reason === '') {
    redirectWithMessage('users.php', 'User and reason are required.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('SELECT username, email FROM users WHERE user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) {
        redirectWithMessage('users.php', 'User not found.', 'danger');
    }

    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', time() + 3600);

    $stmt = $db->prepare('UPDATE users SET reset_token = ?, reset_token_expiry = ?, force_password_reset = ? WHERE user_id = ?');
    $stmt->execute([$token, $expiry, $forceReset, $userId]);

    Security::logAudit('admin_password_reset_initiated', 'Admin initiated password reset. Reason: ' . $reason, $userId, $_SESSION['admin_id'], null, null, 'success');
    Security::logAdminAction($_SESSION['admin_id'], 'admin_password_reset_initiated', 'Password reset initiated for user #' . $userId . '. Force reset=' . $forceReset . '. Reason: ' . $reason, $userId);

    $resetUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/public/reset_password.php?token=' . urlencode($token);
    redirectWithMessage('users.php', 'Password reset initiated successfully. Reset link: ' . $resetUrl, 'success');
} catch (Throwable $e) {
    error_log('reset_user_password error: ' . $e->getMessage());
    redirectWithMessage('users.php', 'Could not initiate password reset.', 'danger');
}
