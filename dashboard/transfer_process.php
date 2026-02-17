<?php
require_once '../includes/config.php';
if (!Security::isLoggedIn()) { redirectWithMessage('../public/login.php', 'Please login to make transfers.', 'danger'); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirectWithMessage('transfer.php', 'Invalid request method.', 'danger'); }
if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) { redirectWithMessage('transfer.php', 'Security verification failed.', 'danger'); }

$transferType = Security::sanitizeInput($_POST['transfer_type'] ?? '');
$recipientAccount = Security::sanitizeInput($_POST['recipient_account'] ?? '');
$recipientBank = Security::sanitizeInput($_POST['recipient_bank'] ?? '');
$amount = (float)str_replace(',', '', $_POST['amount'] ?? '0');
$description = Security::sanitizeInput($_POST['description'] ?? '');
$transferPin = trim($_POST['transfer_pin'] ?? '');
$authPin = trim($_POST['authorization_pin'] ?? '');
$paymentPin = trim($_POST['payment_pin'] ?? '');
$securePin = trim($_POST['secure_pass_pin'] ?? '');

if (!in_array($transferType, ['internal','external','wire'], true) || strlen($recipientAccount) < 6 || $amount < MIN_TRANSFER_AMOUNT || strlen($description) < 5) {
    redirectWithMessage('transfer.php', 'Please review transfer details.', 'danger');
}
if (!preg_match('/^\d{4}$/', $transferPin) || !preg_match('/^\d{4,6}$/', $authPin) || !preg_match('/^\d{4,6}$/', $paymentPin) || !preg_match('/^\d{4,6}$/', $securePin)) {
    redirectWithMessage('transfer.php', 'PIN verification failed. Please try again.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('SELECT account_balance, account_status FROM users WHERE user_id = ? FOR UPDATE');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user || $user['account_status'] !== 'active') { redirectWithMessage('index.php', 'Your account is not active.', 'danger'); }

    $pinStmt = $db->prepare('SELECT pin_type, pin_hash, is_active, transfer_pin_hash FROM user_pins WHERE user_id = ?');
    $pinStmt->execute([$_SESSION['user_id']]);
    $pins = $pinStmt->fetchAll();
    $pinMap = [];
    $transferHash = null;
    foreach ($pins as $p) {
        $pinMap[$p['pin_type']] = $p;
        if (!$transferHash && !empty($p['transfer_pin_hash'])) { $transferHash = $p['transfer_pin_hash']; }
    }

    if (!$transferHash || !Security::verifyPIN($transferPin, $transferHash)) {
        redirectWithMessage('transfer.php', 'Invalid Transfer PIN.', 'danger');
    }
    foreach (['authorization' => $authPin, 'payment' => $paymentPin, 'secure_pass' => $securePin] as $type => $entered) {
        if (empty($pinMap[$type]) || (int)$pinMap[$type]['is_active'] !== 1) {
            redirectWithMessage('transfer.php', ucfirst(str_replace('_', ' ', $type)) . ' PIN stage is disabled by admin support.', 'danger');
        }
        if (!Security::verifyPIN($entered, $pinMap[$type]['pin_hash'])) {
            redirectWithMessage('transfer.php', 'Invalid ' . ucfirst(str_replace('_', ' ', $type)) . ' PIN.', 'danger');
        }
    }

    $fee = $transferType === 'wire' ? 25.00 : ($transferType === 'external' ? (($amount * 0.01) + 2.00) : 0.00);
    $total = $amount + $fee;
    if ($total > (float)$user['account_balance'] || $total > MAX_SINGLE_TRANSFER) {
        redirectWithMessage('transfer.php', 'Insufficient funds or transfer exceeds limits.', 'danger');
    }

    $db->beginTransaction();
    $newBalance = (float)$user['account_balance'] - $total;
    $ref = Security::generateReferenceNumber();

    $upd = $db->prepare('UPDATE users SET account_balance = ? WHERE user_id = ?');
    $upd->execute([$newBalance, $_SESSION['user_id']]);

    $ins = $db->prepare("INSERT INTO transactions (user_id, transaction_type, amount, description, reference_number, status, balance_after, authorization_pin_used, payment_pin_used, secure_pass_used, transaction_date) VALUES (?, 'debit', ?, ?, ?, 'completed', ?, 1, 1, 1, NOW())");
    $ins->execute([$_SESSION['user_id'], $total, $description . ' (' . $transferType . ' transfer to ' . $recipientAccount . ($recipientBank ? (' @ ' . $recipientBank) : '') . ')', $ref, $newBalance]);

    $db->commit();
    $_SESSION['pin_attempts'] = 0;
    Security::logAudit('transfer_completed', 'Transfer completed. Ref ' . $ref, $_SESSION['user_id']);
    redirectWithMessage('transactions.php', 'Transfer completed successfully. Reference: ' . $ref, 'success');
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) { $db->rollBack(); }
    error_log('transfer_process error: ' . $e->getMessage());
    redirectWithMessage('transfer.php', 'An error occurred while processing your transfer.', 'danger');
}
