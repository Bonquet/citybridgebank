<?php
// CITYBRIDGEBANK Deposit Processing
require_once '../includes/config.php';

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('deposit.php', 'Invalid request method.', 'danger');
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('deposit.php', 'Security verification failed. Please try again.', 'danger');
}

// Check if user is logged in
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login to deposit funds.', 'danger');
}

// Sanitize and validate inputs
$deposit_method = Security::sanitizeInput($_POST['deposit_method'] ?? '');
$amountRaw = str_replace(',', '', $_POST['amount'] ?? '');
$amount = floatval($amountRaw);
$description = Security::sanitizeInput($_POST['description'] ?? '');

// Validate deposit method
if (!in_array($deposit_method, ['bank', 'crypto'])) {
    redirectWithMessage('deposit.php', 'Invalid deposit method selected.', 'danger');
}

// Validate amount > 0
if ($amount <= 0) {
    redirectWithMessage('deposit.php', 'Please enter a valid deposit amount greater than zero.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Generate a description for the request
    $desc = ($deposit_method === 'bank') ? 'Bank deposit' : 'Crypto deposit (BTC)';
    if (!empty($description)) {
        $desc .= ' - ' . $description;
    }

    // Create a pending deposit request instead of immediately crediting the account
    $stmt = $db->prepare("INSERT INTO transaction_requests (user_id, amount, request_type, description, status, block1_status, block2_status, block3_status) VALUES (?, ?, 'deposit', ?, 'pending', 'success', 'success', 'success')");
    $stmt->execute([
        $_SESSION['user_id'],
        $amount,
        $desc
    ]);

    // Commit changes
    $db->commit();

    // Log audit that a deposit request was created
    Security::logAudit('deposit_request', "User submitted a deposit request via {$deposit_method}: {$amount}", $_SESSION['user_id']);

    // Redirect to transactions or dashboard with informational message
    redirectWithMessage('transactions.php', 'Deposit request submitted. Awaiting admin approval.', 'info');

} catch (PDOException $e) {
    // Rollback on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Deposit processing error: " . $e->getMessage());
    redirectWithMessage('deposit.php', 'An error occurred while processing your deposit. Please try again.', 'danger');
}

?>