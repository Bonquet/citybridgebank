<?php
require_once '../includes/config.php';
if (!Security::isLoggedIn()) { redirectWithMessage('../public/login.php', 'Please login first.', 'danger'); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirectWithMessage('security.php', 'Invalid request.', 'danger');
}
$current = $_POST['current_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';
if ($new === '' || strlen($new) < 8 || $new !== $confirm) {
    redirectWithMessage('security.php', 'Password validation failed.', 'danger');
}
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('SELECT password_hash FROM users WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$hash = $stmt->fetchColumn();
if (!$hash || !Security::verifyPassword($current, $hash)) {
    redirectWithMessage('security.php', 'Current password is incorrect.', 'danger');
}
$upd = $db->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?');
$upd->execute([Security::hashPassword($new), $_SESSION['user_id']]);
Security::logAudit('password_change', 'User changed password', $_SESSION['user_id']);
redirectWithMessage('security.php', 'Password updated successfully.', 'success');
