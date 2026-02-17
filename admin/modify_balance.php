<?php
require_once '../includes/config.php';

if (!Security::isAdminLoggedIn()) {
    redirectWithMessage('login.php', 'Unauthorized access.', 'danger');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('balance_management.php', 'Invalid request method.', 'danger');
}
if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirectWithMessage('balance_management.php', 'Invalid or expired session token.', 'danger');
}

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$action = Security::sanitizeInput($_POST['action'] ?? '');
$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0.0;
$reason = trim(Security::sanitizeInput($_POST['reason'] ?? ''));

if ($user_id <= 0 || !in_array($action, ['credit', 'debit', 'adjustment'], true) || $reason === '') {
    redirectWithMessage('balance_management.php', 'Invalid input parameters.', 'danger');
}
if (($action === 'credit' || $action === 'debit') && $amount <= 0) {
    redirectWithMessage('balance_management.php', 'Amount must be greater than zero.', 'danger');
}
if ($action === 'adjustment' && $amount == 0.0) {
    redirectWithMessage('balance_management.php', 'Adjustment cannot be zero.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    $stmt = $db->prepare("SELECT account_balance, account_status FROM users WHERE user_id = ? FOR UPDATE");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!$user) {
        throw new RuntimeException('User not found.');
    }
    if ($user['account_status'] !== 'active') {
        throw new RuntimeException('Cannot modify balance for non-active account.');
    }

    $balance = (float)$user['account_balance'];
    $delta = 0.0;
    $transactionType = 'credit';

    if ($action === 'credit') {
        $delta = abs($amount);
        $transactionType = 'credit';
    } elseif ($action === 'debit') {
        $delta = -abs($amount);
        $transactionType = 'debit';
    } else {
        $delta = $amount;
        $transactionType = $delta >= 0 ? 'credit' : 'debit';
    }

    $newBalance = $balance + $delta;
    if ($newBalance < 0) {
        throw new RuntimeException('Insufficient funds to perform this action.');
    }

    $stmt = $db->prepare("UPDATE users SET account_balance = ? WHERE user_id = ?");
    $stmt->execute([$newBalance, $user_id]);

    $reference = Security::generateReferenceNumber();
    $txnDesc = 'Admin ' . $action . ' balance update. Reason: ' . $reason;
    $stmt = $db->prepare("INSERT INTO transactions (user_id, transaction_type, amount, description, reference_number, status, balance_after, authorization_pin_used, payment_pin_used, secure_pass_used, transaction_date) VALUES (?, ?, ?, ?, ?, 'completed', ?, 0, 0, 0, NOW())");
    $stmt->execute([$user_id, $transactionType, abs($delta), $txnDesc, $reference, $newBalance]);
    $transactionId = (int)$db->lastInsertId();

    $db->commit();

    $adminId = $_SESSION['admin_id'] ?? null;
    Security::logAudit('balance_change', "Admin performed {$action} ({$delta}) for user {$user_id}. Reason: {$reason}", $user_id, $adminId, $transactionId, null, 'success');
    Security::logAdminAction($adminId, 'balance_change', ucfirst($action) . " amount {$delta} for user {$user_id}. Reason: {$reason}", $user_id);

    redirectWithMessage('balance_management.php', 'Balance updated successfully.', 'success');
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log('Balance modification error: ' . $e->getMessage());
    redirectWithMessage('balance_management.php', 'An error occurred updating balance: ' . $e->getMessage(), 'danger');
}
