<?php
// CITYBRIDGEBANK Transfer Processing
// Three-Layer PIN System Implementation - ALL THREE PINS REQUIRED
require_once '../includes/config.php';

// Check if user is logged in
if (!Security::isLoggedIn()) {
    // Redirect to public login if user is not logged in (fix broken path)
    redirectWithMessage('../public/login.php', 'Please login to make transfers.', 'danger');
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('transfer.php', 'Invalid request method.', 'danger');
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('transfer.php', 'Security verification failed. Please try again.', 'danger');
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
    
    // Validate transfer details
    $transfer_type = Security::sanitizeInput($_POST['transfer_type']);
    $recipient_account = Security::sanitizeInput($_POST['recipient_account']);
    // Optional recipient bank for external/wire transfers
    $recipient_bank = isset($_POST['recipient_bank']) ? Security::sanitizeInput($_POST['recipient_bank']) : '';
    // Remove commas from amount before converting to float (handles formatted inputs like "1,000")
    $raw_amount = str_replace(',', '', $_POST['amount']);
    $amount = floatval($raw_amount);
    $description = Security::sanitizeInput($_POST['description']);
    
    // Validation errors
    $errors = [];
    
    if (empty($transfer_type) || !in_array($transfer_type, ['internal', 'external', 'wire'])) {
        $errors[] = 'Invalid transfer type.';
    }
    
    if (empty($recipient_account) || strlen($recipient_account) < 10) {
        $errors[] = 'Invalid recipient account number.';
    }
    
    if ($amount < MIN_TRANSFER_AMOUNT) {
        $errors[] = "Minimum transfer amount is $" . number_format(MIN_TRANSFER_AMOUNT, 2) . ".";
    }
    
    if ($amount > MAX_SINGLE_TRANSFER) {
        $errors[] = "Maximum single transfer is $" . number_format(MAX_SINGLE_TRANSFER, 2) . ".";
    }
    
    if ($amount > $user['account_balance']) {
        $errors[] = 'Insufficient funds for this transfer.';
    }
    
    if (empty($description) || strlen($description) < 5) {
        $errors[] = 'Description must be at least 5 characters.';
    }
    
    // CRITICAL: Validate all three PINs
    // Authorization PIN may come from the field named authorization_pin (new) or auth_pin (legacy)
    $auth_pin = $_POST['authorization_pin'] ?? ($_POST['auth_pin'] ?? '');
    // Payment and secure pass pins may come from hidden fields or user input
    $payment_pin = $_POST['payment_pin'] ?? '';
    $secure_pass_pin = $_POST['secure_pass_pin'] ?? '';
    
    if (empty($auth_pin) || empty($payment_pin) || empty($secure_pass_pin)) {
        $errors[] = 'All three PINs (Authorization, Payment, Secure Pass) are required to complete any transaction.';
    }
    
    if (!empty($errors)) {
        Security::logAudit('transfer_failed', "Transfer validation failed for user {$_SESSION['username']}: " . implode('; ', $errors), $_SESSION['user_id']);
        redirectWithMessage('transfer.php', implode(' ', $errors), 'danger');
    }
    
    // VALIDATE ALL THREE PINS - ALL MUST BE CORRECT
    $stmt = $db->prepare("SELECT pin_type, pin_hash FROM user_pins WHERE user_id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['user_id']]);
    $stored_pins = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Check if all three PINs are set
    if (count($stored_pins) < 3) {
        Security::logAudit('transfer_failed', "Incomplete PIN setup for user {$_SESSION['username']}", $_SESSION['user_id']);
        redirectWithMessage('setup_pins.php', 'Please complete your three-layer PIN setup before making transfers.', 'warning');
    }
    
    // Validate Authorization PIN
    if (!isset($stored_pins['authorization']) || !Security::verifyPIN($auth_pin, $stored_pins['authorization'])) {
        Security::logAudit('pin_validation_failed', "Invalid Authorization PIN for user {$_SESSION['username']}", $_SESSION['user_id'], null, null, null, 'failure');
        redirectWithMessage('transfer.php', 'Invalid Authorization PIN. Transaction aborted for your security.', 'danger');
    }
    
    // Validate Payment PIN
    if (!isset($stored_pins['payment']) || !Security::verifyPIN($payment_pin, $stored_pins['payment'])) {
        Security::logAudit('pin_validation_failed', "Invalid Payment PIN for user {$_SESSION['username']}", $_SESSION['user_id'], null, null, null, 'failure');
        redirectWithMessage('transfer.php', 'Invalid Payment PIN. Transaction aborted for your security.', 'danger');
    }
    
    // Validate Secure Pass PIN
    if (!isset($stored_pins['secure_pass']) || !Security::verifyPIN($secure_pass_pin, $stored_pins['secure_pass'])) {
        Security::logAudit('pin_validation_failed', "Invalid Secure Pass PIN for user {$_SESSION['username']}", $_SESSION['user_id'], null, null, null, 'failure');
        redirectWithMessage('transfer.php', 'Invalid Secure Pass PIN. Transaction aborted for your security.', 'danger');
    }
    
    // Calculate fee based on transfer type
    $fee = 0.00;
    switch ($transfer_type) {
        case 'internal':
            $fee = 0.00;
            break;
        case 'external':
            $fee = ($amount * 0.01) + 2.00; // 1% + $2
            break;
        case 'wire':
            $fee = 25.00;
            break;
    }
    
    $total_amount = $amount + $fee;
    
    // Check final balance including fee
    if ($total_amount > $user['account_balance']) {
        Security::logAudit('transfer_failed', "Insufficient funds including fee for user {$_SESSION['username']}", $_SESSION['user_id']);
        redirectWithMessage('transfer.php', 'Insufficient funds including transaction fee.', 'danger');
    }
    
    // CRITICAL: ALL THREE PINS VALIDATED - PROCESS TRANSACTION
    $db->beginTransaction();
    
    // Generate reference number
    $reference_number = Security::generateReferenceNumber();
    
    // Update sender balance
    $new_balance = $user['account_balance'] - $total_amount;
    $stmt = $db->prepare("UPDATE users SET account_balance = ? WHERE user_id = ?");
    $stmt->execute([$new_balance, $_SESSION['user_id']]);
    
    // Record transaction with ALL THREE PINS marked as used
    $stmt = $db->prepare("INSERT INTO transactions 
        (user_id, transaction_type, amount, description, reference_number, status, balance_after, 
         authorization_pin_used, payment_pin_used, secure_pass_used, transaction_date) 
        VALUES (?, 'debit', ?, ?, ?, 'completed', ?, 1, 1, 1, NOW())");
    $stmt->execute([
        $_SESSION['user_id'],
        $total_amount,
        // Append bank name to recipient info for external and wire transfers
        "{$description} ({$transfer_type} transfer to " . (!empty($recipient_bank) ? ($recipient_account . ' @ ' . $recipient_bank) : $recipient_account) . ")",
        $reference_number,
        $new_balance
    ]);
    
    // Update PIN last used timestamps
    $stmt = $db->prepare("UPDATE user_pins SET last_used = NOW() WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    // Log all three PIN usages
    Security::logAudit('pin_validated', "Authorization PIN used for transaction {$reference_number} by user {$_SESSION['username']}", $_SESSION['user_id'], null, null, null, 'success');
    Security::logAudit('pin_validated', "Payment PIN used for transaction {$reference_number} by user {$_SESSION['username']}", $_SESSION['user_id'], null, null, null, 'success');
    Security::logAudit('pin_validated', "Secure Pass PIN used for transaction {$reference_number} by user {$_SESSION['username']}", $_SESSION['user_id'], null, null, null, 'success');
    
    // Log successful transfer
    Security::logAudit('transfer_completed', "Transfer of {$total_amount} ({$transfer_type}) completed by user {$_SESSION['username']} to {$recipient_account}", $_SESSION['user_id']);
    
    $db->commit();
    
    // Success message with confirmation
    $message = "Transfer completed successfully! Reference: {$reference_number}. Amount: $" . number_format($total_amount, 2) . " (including $" . number_format($fee, 2) . " fee).";
    redirectWithMessage('transactions.php', $message, 'success');
    
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Transfer processing error: " . $e->getMessage());
    Security::logAudit('transfer_error', "Transfer processing error for user {$_SESSION['username']}: {$e->getMessage()}", $_SESSION['user_id']);
    redirectWithMessage('transfer.php', 'An error occurred processing your transfer. Please try again.', 'danger');
}
?>