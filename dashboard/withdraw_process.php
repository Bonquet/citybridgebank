<?php
// CITYBRIDGEBANK Withdrawal Processing
// Three-Layer PIN System implementation for withdrawals
require_once '../includes/config.php';

// Check if user is logged in
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login to withdraw funds.', 'danger');
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('withdraw.php', 'Invalid request method.', 'danger');
}

// Rate limiting: prevent brute force PIN attempts. If more than 3 failed attempts
// have been recorded in the session within the current login session, block
// further withdrawals until the session resets. The counter is incremented
// below when invalid PINs are detected. A successful withdrawal resets
// the counter.
if (!isset($_SESSION['pin_attempts'])) {
    $_SESSION['pin_attempts'] = 0;
}
if ($_SESSION['pin_attempts'] >= 3) {
    redirectWithMessage('withdraw.php', 'Too many incorrect PIN attempts. Please wait and try again later.', 'danger');
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('withdraw.php', 'Security verification failed. Please try again.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get user information
    $stmt = $db->prepare("SELECT user_id, account_balance, account_status FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user || $user['account_status'] !== 'active') {
        redirectWithMessage('index.php', 'Your account is not active.', 'danger');
    }
    
    // Validate withdrawal details
    $recipient_account = Security::sanitizeInput($_POST['recipient_account']);
    $recipient_bank = isset($_POST['recipient_bank']) ? Security::sanitizeInput($_POST['recipient_bank']) : '';
    // Remove commas from amount before converting to float (handles formatted inputs like "1,000")
    $raw_amount = str_replace(',', '', $_POST['amount']);
    $amount = floatval($raw_amount);
    $description = Security::sanitizeInput($_POST['description']);
    
    $errors = [];
    
    if (empty($recipient_account) || strlen($recipient_account) < 10) {
        $errors[] = 'Invalid recipient account number.';
    }
    if (empty($recipient_bank)) {
        $errors[] = 'Please select the recipient bank.';
    }
    if ($amount < MIN_TRANSFER_AMOUNT) {
        $errors[] = 'Minimum withdrawal amount is $' . number_format(MIN_TRANSFER_AMOUNT, 2) . '.';
    }
    if ($amount > MAX_SINGLE_TRANSFER) {
        $errors[] = 'Maximum single withdrawal is $' . number_format(MAX_SINGLE_TRANSFER, 2) . '.';
    }
    if ($amount > $user['account_balance']) {
        $errors[] = 'Insufficient funds for this withdrawal.';
    }
    if (empty($description) || strlen($description) < 5) {
        $errors[] = 'Description must be at least 5 characters.';
    }
    
    // Validate all three PINs
    $auth_pin = $_POST['authorization_pin'] ?? ($_POST['auth_pin'] ?? '');
    $payment_pin = $_POST['payment_pin'] ?? '';
    $secure_pass_pin = $_POST['secure_pass_pin'] ?? '';
    
    if (empty($auth_pin) || empty($payment_pin) || empty($secure_pass_pin)) {
        $errors[] = 'All three PINs (Authorization, Payment, Secure Pass) are required to complete any transaction.';
    }
    
    if (!empty($errors)) {
        Security::logAudit('withdrawal_failed', 'Withdrawal validation failed for user ' . $_SESSION['username'] . ': ' . implode('; ', $errors), $_SESSION['user_id']);
        redirectWithMessage('withdraw.php', implode(' ', $errors), 'danger');
    }
    
    // Validate stored pins
    $stmt = $db->prepare("SELECT pin_type, pin_hash FROM user_pins WHERE user_id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['user_id']]);
    $stored_pins = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    if (count($stored_pins) < 3) {
        Security::logAudit('withdrawal_failed', 'Incomplete PIN setup for user ' . $_SESSION['username'], $_SESSION['user_id']);
        redirectWithMessage('setup_pins.php', 'Please complete your three-layer PIN setup before making withdrawals.', 'warning');
    }
    
    // Validate each pin
    if (!isset($stored_pins['authorization']) || !Security::verifyPIN($auth_pin, $stored_pins['authorization'])) {
        Security::logAudit('pin_validation_failed', 'Invalid Authorization PIN for user ' . $_SESSION['username'], $_SESSION['user_id'], null, null, null, 'failure');
        // Increment failed PIN attempts
        $_SESSION['pin_attempts'] = ($_SESSION['pin_attempts'] ?? 0) + 1;
        redirectWithMessage('withdraw.php', 'Invalid Authorization PIN. Withdrawal aborted for your security.', 'danger');
    }
    if (!isset($stored_pins['payment']) || !Security::verifyPIN($payment_pin, $stored_pins['payment'])) {
        Security::logAudit('pin_validation_failed', 'Invalid Payment PIN for user ' . $_SESSION['username'], $_SESSION['user_id'], null, null, null, 'failure');
        // Increment failed PIN attempts
        $_SESSION['pin_attempts'] = ($_SESSION['pin_attempts'] ?? 0) + 1;
        redirectWithMessage('withdraw.php', 'Invalid Payment PIN. Withdrawal aborted for your security.', 'danger');
    }
    if (!isset($stored_pins['secure_pass']) || !Security::verifyPIN($secure_pass_pin, $stored_pins['secure_pass'])) {
        Security::logAudit('pin_validation_failed', 'Invalid Secure Pass PIN for user ' . $_SESSION['username'], $_SESSION['user_id'], null, null, null, 'failure');
        // Increment failed PIN attempts
        $_SESSION['pin_attempts'] = ($_SESSION['pin_attempts'] ?? 0) + 1;
        redirectWithMessage('withdraw.php', 'Invalid Secure Pass PIN. Withdrawal aborted for your security.', 'danger');
    }
    
    // Calculate fee: same as external transfer (1% + $2)
    $fee = ($amount * 0.01) + 2.00;
    $total_amount = $amount + $fee;
    
    if ($total_amount > $user['account_balance']) {
        Security::logAudit('withdrawal_failed', 'Insufficient funds including fee for user ' . $_SESSION['username'], $_SESSION['user_id']);
        redirectWithMessage('withdraw.php', 'Insufficient funds including transaction fee.', 'danger');
    }
    
    // Instead of processing the withdrawal immediately, create a pending withdrawal request
    $db->beginTransaction();

    // Build description for the request
    $desc = $description . ' (withdrawal to ' . $recipient_account . ' @ ' . $recipient_bank . ')';

    // Insert request into transaction_requests table with pending status
    $stmt = $db->prepare("INSERT INTO transaction_requests (user_id, amount, request_type, description, status, block1_status, block2_status, block3_status) VALUES (?, ?, 'withdrawal', ?, 'pending', 'pending', 'pending', 'pending')");
    $stmt->execute([
        $_SESSION['user_id'],
        $total_amount,
        $desc
    ]);

    // Update PIN last used timestamps (for audit)
    $stmt = $db->prepare("UPDATE user_pins SET last_used = NOW() WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);

    // Log each PIN usage
    Security::logAudit('pin_validated', 'Authorization PIN used for withdrawal request by user ' . $_SESSION['username'], $_SESSION['user_id'], null, null, null, 'success');
    Security::logAudit('pin_validated', 'Payment PIN used for withdrawal request by user ' . $_SESSION['username'], $_SESSION['user_id'], null, null, null, 'success');
    Security::logAudit('pin_validated', 'Secure Pass PIN used for withdrawal request by user ' . $_SESSION['username'], $_SESSION['user_id'], null, null, null, 'success');

    // Log that a withdrawal request has been created
    Security::logAudit('withdrawal_request', 'Withdrawal request of ' . $total_amount . ' created by user ' . $_SESSION['username'] . ' to ' . $recipient_account . ' @ ' . $recipient_bank, $_SESSION['user_id']);

    $db->commit();

    // Reset failed pin attempts on success
    $_SESSION['pin_attempts'] = 0;

    // Inform user that withdrawal is pending approval
    $message = 'Withdrawal request submitted! Your withdrawal will be processed after admin approval.';
    redirectWithMessage('transactions.php', $message, 'info');
    
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log('Withdrawal processing error: ' . $e->getMessage());
    Security::logAudit('withdrawal_error', 'Withdrawal processing error for user ' . $_SESSION['username'] . ': ' . $e->getMessage(), $_SESSION['user_id']);
    redirectWithMessage('withdraw.php', 'An error occurred processing your withdrawal. Please try again.', 'danger');
}
?>