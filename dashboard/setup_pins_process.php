<?php
require_once '../includes/config.php';
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login first.', 'danger');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('setup_pins.php', 'Invalid request.', 'danger');
}
if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirectWithMessage('setup_pins.php', 'Security verification failed.', 'danger');
}

$changeMode = ($_POST['change_mode'] ?? '0') === '1';
$transferPin = trim($_POST['transfer_pin'] ?? '');
$transferPinConfirm = trim($_POST['transfer_pin_confirm'] ?? '');

if (!preg_match('/^\d{4}$/', $transferPin) || $transferPin !== $transferPinConfirm) {
    redirectWithMessage('setup_pins.php' . ($changeMode ? '?change=1' : ''), 'Transfer PIN must be 4 digits and match confirmation.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    $transferHash = Security::hashPIN($transferPin);

    $upd = $db->prepare('UPDATE user_pins SET transfer_pin_hash = ?, transfer_pin_created_at = NOW() WHERE user_id = ?');
    $upd->execute([$transferHash, $_SESSION['user_id']]);

    if ($upd->rowCount() === 0) {
        $placeholderPin = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $stmt = $db->prepare("INSERT INTO user_pins (user_id, pin_type, pin_hash, pin_plain, is_active, pin_created_at, transfer_pin_hash, transfer_pin_created_at) VALUES (?, 'authorization', ?, NULL, 0, NOW(), ?, NOW())");
        $stmt->execute([$_SESSION['user_id'], Security::hashPIN($placeholderPin), $transferHash]);
    }

    Security::logAudit('transfer_pin_updated', 'User updated Transfer PIN', $_SESSION['user_id'], null, null, 'transfer');

    $db->commit();
    redirectWithMessage('security.php', $changeMode ? 'Transfer PIN updated successfully.' : 'Transfer PIN setup completed successfully.', 'success');
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log('setup_pins_process error: ' . $e->getMessage());
    redirectWithMessage('setup_pins.php' . ($changeMode ? '?change=1' : ''), 'Unable to save Transfer PIN right now.', 'danger');
}
