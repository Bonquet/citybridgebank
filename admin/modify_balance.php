<?php
// Handle balance modification requests from admin balance_management page
require_once '../includes/config.php';

// Ensure the caller is an authenticated admin
if (!Security::isAdminLoggedIn()) {
    redirectWithMessage('login.php', 'Unauthorized access.', 'danger');
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('balance_management.php', 'Invalid request method.', 'danger');
}

// Validate CSRF token
$token = $_POST['csrf_token'] ?? '';
if (!Security::verifyCSRFToken($token)) {
    redirectWithMessage('balance_management.php', 'Invalid or expired session token.', 'danger');
}

// Collect and validate inputs
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$action = $_POST['action'] ?? '';
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$reason = trim($_POST['reason'] ?? '');

// Basic validation
if ($user_id <= 0 || !in_array($action, ['credit','debit','adjustment'], true) || $amount <= 0 || $reason === '') {
    redirectWithMessage('balance_management.php', 'Invalid input parameters.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    // Fetch current user balance
    $stmt = $db->prepare("SELECT account_balance, account_status FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!$user) {
        redirectWithMessage('balance_management.php', 'User not found.', 'danger');
    }
    $balance = floatval($user['account_balance']);
    // Calculate new balance based on action
    if ($action === 'credit') {
        $newBalance = $balance + $amount;
    } elseif ($action === 'debit') {
        // Disallow debiting beyond zero
        if ($balance - $amount < 0) {
            redirectWithMessage('balance_management.php', 'Insufficient funds to perform debit.', 'danger');
        }
        $newBalance = $balance - $amount;
    } else {
        // Adjustment: treat as credit (positive addition). Admin may enter negative amount for subtraction.
        $newBalance = $balance + $amount;
    }
    // Begin transaction
    $db->beginTransaction();
    // Update user balance
    $stmt = $db->prepare("UPDATE users SET account_balance = ? WHERE user_id = ?");
    $stmt->execute([$newBalance, $user_id]);
    // Record transaction
    $reference = Security::generateReferenceNumber();
    // Determine transaction type: credit for positive amounts, debit for negative amounts
    $txnType = ($action === 'debit') ? 'debit' : 'credit';
    $txnDesc = ucfirst($action) . ' by admin: ' . $reason;
    $amountForTxn = ($action === 'debit') ? -$amount : $amount;
    $stmt = $db->prepare("INSERT INTO transactions (user_id, reference_number, transaction_type, description, amount, fees, total, balance_after, status, authorization_pin_used, payment_pin_used, secure_pass_used, created_at) VALUES (?, ?, ?, ?, ?, 0, ?, ?, 'completed', 0, 0, 0, NOW())");
    $stmt->execute([
        $user_id,
        $reference,
        $txnType,
        $txnDesc,
        $amount,
        $amountForTxn,
        $newBalance
    ]);
    $transactionId = $db->lastInsertId();
    // Commit the transaction
    $db->commit();
    // Audit & admin logs
    $adminId = $_SESSION['admin_id'] ?? null;
    Security::logAudit('balance_change', "Admin {$adminId} performed {$action} of {$amount} on user {$user_id}. Reason: {$reason}", $user_id, $adminId, $transactionId, null, 'success');
    Security::logAdminAction($adminId, 'balance_change', ucfirst($action) . " of {$amount} for user {$user_id}. Reason: {$reason}", $user_id);
    redirectWithMessage('balance_management.php', 'Balance updated successfully.', 'success');
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log('Balance modification error: ' . $e->getMessage());
    redirectWithMessage('balance_management.php', 'An error occurred updating balance.', 'danger');
}
?>