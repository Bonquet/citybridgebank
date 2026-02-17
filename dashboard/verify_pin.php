<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!Security::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$type = Security::sanitizeInput($_POST['type'] ?? '');
$pin = trim($_POST['pin'] ?? '');
$allowed = ['transfer', 'authorization', 'payment', 'secure_pass'];
if (!in_array($type, $allowed, true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid PIN type.']);
    exit;
}

$pattern = $type === 'transfer' ? '/^\d{4}$/' : '/^\d{4,6}$/';
if (!preg_match($pattern, $pin)) {
    echo json_encode(['success' => false, 'message' => 'Invalid PIN format.']);
    exit;
}

$_SESSION['pin_attempts'] = $_SESSION['pin_attempts'] ?? 0;
if ($_SESSION['pin_attempts'] >= MAX_PIN_ATTEMPTS) {
    echo json_encode(['success' => false, 'message' => 'Too many incorrect PIN attempts.']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    if ($type === 'transfer') {
        $stmt = $db->prepare('SELECT transfer_pin_hash FROM user_pins WHERE user_id = ? AND transfer_pin_hash IS NOT NULL LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $hash = $stmt->fetchColumn();
        if (!$hash) {
            echo json_encode(['success' => false, 'message' => 'Transfer PIN not set. Please set it in Security settings.', 'requires_setup' => true]);
            exit;
        }

        if (!Security::verifyPIN($pin, $hash)) {
            $_SESSION['pin_attempts']++;
            Security::logAudit('pin_validation_failed', 'Invalid transfer PIN', $_SESSION['user_id'], null, null, 'transfer', 'failure');
            echo json_encode(['success' => false, 'message' => 'Invalid Transfer PIN.']);
            exit;
        }

        Security::logAudit('pin_validated', 'Transfer PIN validated', $_SESSION['user_id'], null, null, 'transfer', 'success');
        echo json_encode(['success' => true]);
        exit;
    }

    $stmt = $db->prepare('SELECT pin_hash, is_active FROM user_pins WHERE user_id = ? AND pin_type = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id'], $type]);
    $row = $stmt->fetch();
    if (!$row) {
        echo json_encode(['success' => false, 'message' => ucfirst($type) . ' PIN not configured.']);
        exit;
    }
    if ((int)$row['is_active'] !== 1) {
        echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_',' ', $type)) . ' PIN stage is currently disabled by admin support.']);
        exit;
    }

    if (!Security::verifyPIN($pin, $row['pin_hash'])) {
        $_SESSION['pin_attempts']++;
        Security::logAudit('pin_validation_failed', "Invalid {$type} PIN", $_SESSION['user_id'], null, null, $type, 'failure');
        echo json_encode(['success' => false, 'message' => 'Invalid PIN.']);
        exit;
    }

    Security::logAudit('pin_validated', ucfirst($type) . ' PIN validated', $_SESSION['user_id'], null, null, $type, 'success');
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    error_log('verify_pin error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Verification failed.']);
}
