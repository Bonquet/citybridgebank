<?php
require_once '../includes/config.php';
if (!Security::isLoggedIn()) { redirectWithMessage('../public/login.php', 'Please login to withdraw funds.', 'danger'); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirectWithMessage('withdraw.php', 'Invalid request method.', 'danger'); }
if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) { redirectWithMessage('withdraw.php', 'Security verification failed.', 'danger'); }

$recipientAccount = Security::sanitizeInput($_POST['recipient_account'] ?? '');
$recipientBank = Security::sanitizeInput($_POST['recipient_bank'] ?? '');
$amount = (float)str_replace(',', '', $_POST['amount'] ?? '0');
$description = Security::sanitizeInput($_POST['description'] ?? '');
$transferPin = trim($_POST['transfer_pin'] ?? '');
$authPin = trim($_POST['authorization_pin'] ?? '');
$paymentPin = trim($_POST['payment_pin'] ?? '');
$securePin = trim($_POST['secure_pass_pin'] ?? '');

if (strlen($recipientAccount) < 6 || $recipientBank === '' || $amount < MIN_TRANSFER_AMOUNT || strlen($description) < 5) {
    redirectWithMessage('withdraw.php', 'Please review withdrawal details.', 'danger');
}
if (!preg_match('/^\d{4}$/', $transferPin) || !preg_match('/^\d{4,6}$/', $authPin) || !preg_match('/^\d{4,6}$/', $paymentPin) || !preg_match('/^\d{4,6}$/', $securePin)) {
    redirectWithMessage('withdraw.php', 'PIN verification failed. Please try again.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('SELECT account_balance, account_status, kyc_status FROM users WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user || $user['account_status'] !== 'active') { redirectWithMessage('index.php', 'Your account is not active.', 'danger'); }
    if (($user['kyc_status'] ?? 'none') !== 'verified') { redirectWithMessage('security.php', 'KYC verification is required before this transaction type.', 'info'); }

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
        redirectWithMessage('withdraw.php', 'Invalid Transfer PIN.', 'danger');
    }

    foreach (['authorization' => $authPin, 'payment' => $paymentPin, 'secure_pass' => $securePin] as $type => $entered) {
        if (empty($pinMap[$type]) || (int)$pinMap[$type]['is_active'] !== 1) {
            redirectWithMessage('withdraw.php', ucfirst(str_replace('_', ' ', $type)) . ' PIN stage is disabled by admin support.', 'danger');
        }
        if (!Security::verifyPIN($entered, $pinMap[$type]['pin_hash'])) {
            redirectWithMessage('withdraw.php', 'Invalid ' . ucfirst(str_replace('_', ' ', $type)) . ' PIN.', 'danger');
        }
    }

    $fee = ($amount * 0.01) + 2.00;
    $total = $amount + $fee;
    if ($total > (float)$user['account_balance'] || $total > MAX_SINGLE_TRANSFER) {
        redirectWithMessage('withdraw.php', 'Insufficient funds or withdrawal exceeds limits.', 'danger');
    }

    $db->beginTransaction();
    $desc = $description . ' (withdrawal to ' . $recipientAccount . ' @ ' . $recipientBank . ')';
    $ins = $db->prepare("INSERT INTO transaction_requests (user_id, amount, request_type, description, status, block1_status, block2_status, block3_status) VALUES (?, ?, 'withdrawal', ?, 'pending', 'pending', 'pending', 'pending')");
    $ins->execute([$_SESSION['user_id'], $total, $desc]);
    $db->commit();

    $_SESSION['pin_attempts'] = 0;
    Security::logAudit('withdrawal_request', 'Withdrawal request submitted', $_SESSION['user_id']);
    redirectWithMessage('transactions.php', 'Withdrawal request submitted. Awaiting admin approval.', 'info');
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) { $db->rollBack(); }
    error_log('withdraw_process error: ' . $e->getMessage());
    redirectWithMessage('withdraw.php', 'An error occurred while processing your withdrawal.', 'danger');
}
