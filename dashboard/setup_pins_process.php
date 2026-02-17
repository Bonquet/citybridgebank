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

    if (!$changeMode) {
        $pinMap = [
            'authorization' => trim($_POST['authorization_pin'] ?? ''),
            'payment' => trim($_POST['payment_pin'] ?? ''),
            'secure_pass' => trim($_POST['secure_pass_pin'] ?? '')
        ];

        foreach ($pinMap as $type => $pin) {
            if (!preg_match('/^\d{4,6}$/', $pin)) {
                throw new RuntimeException(ucfirst(str_replace('_', ' ', $type)) . ' PIN must be 4-6 digits.');
            }
            $hash = Security::hashPIN($pin);
            $stmt = $db->prepare('INSERT INTO user_pins (user_id, pin_type, pin_hash, pin_plain, is_active, pin_created_at) VALUES (?, ?, ?, NULL, 1, NOW()) ON DUPLICATE KEY UPDATE pin_hash = VALUES(pin_hash), pin_plain = NULL, is_active = 1, pin_updated_at = NOW()');
            $stmt->execute([$_SESSION['user_id'], $type, $hash]);
        }
    }

    $transferHash = Security::hashPIN($transferPin);
    // Persist transfer PIN hash on all user_pins rows for this user (schema compatibility).
    $upd = $db->prepare('UPDATE user_pins SET transfer_pin_hash = ?, transfer_pin_created_at = NOW() WHERE user_id = ?');
    $upd->execute([$transferHash, $_SESSION['user_id']]);

    // If no rows existed (edge case), create authorization row to store transfer pin hash.
    if ($upd->rowCount() === 0) {
        $stmt = $db->prepare("INSERT INTO user_pins (user_id, pin_type, pin_hash, pin_plain, is_active, pin_created_at, transfer_pin_hash, transfer_pin_created_at) VALUES (?, 'authorization', ?, NULL, 1, NOW(), ?, NOW())");
        $stmt->execute([$_SESSION['user_id'], Security::hashPIN('0000'), $transferHash]);
    }

    Security::logAudit('transfer_pin_updated', 'User updated Transfer PIN', $_SESSION['user_id']);
    if (!$changeMode) {
        Security::logAudit('pins_initialized', 'User initialized authentication/payment/secure pins', $_SESSION['user_id']);
    }

    $db->commit();
    redirectWithMessage('security.php', $changeMode ? 'Transfer PIN updated successfully.' : 'PIN setup completed successfully.', 'success');
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log('setup_pins_process error: ' . $e->getMessage());
    redirectWithMessage('setup_pins.php' . ($changeMode ? '?change=1' : ''), 'Unable to save PIN settings. ' . $e->getMessage(), 'danger');
}
