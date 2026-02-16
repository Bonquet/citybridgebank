<?php
// Process approval or rejection of deposit/withdrawal requests
require_once '../includes/config.php';

// Only allow admins
if (!Security::isAdminLoggedIn()) {
    redirectWithMessage('login.php', 'Please login as admin to manage transaction requests.', 'danger');
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('transaction_requests.php', 'Invalid request method.', 'danger');
}

// Validate inputs
$requestId = intval($_POST['request_id'] ?? 0);
$action = $_POST['action'] ?? '';
$rejectReason = $_POST['reject_reason'] ?? null;

if (!$requestId || !in_array($action, ['approve','reject'])) {
    redirectWithMessage('transaction_requests.php', 'Invalid request parameters.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Fetch the request
    $stmt = $db->prepare("SELECT * FROM transaction_requests WHERE request_id = ? FOR UPDATE");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();
    if (!$request) {
        throw new Exception('Transaction request not found');
    }
    if ($request['status'] !== 'pending') {
        throw new Exception('Only pending requests can be processed.');
    }

    // Get user info
    $stmt = $db->prepare("SELECT account_balance FROM users WHERE user_id = ? FOR UPDATE");
    $stmt->execute([$request['user_id']]);
    $currentBalance = (float)$stmt->fetchColumn();
    if ($currentBalance === false) {
        throw new Exception('User not found');
    }

    $requestType = $request['request_type'];
    $amount = (float)$request['amount'];

    if ($action === 'approve') {
        if ($requestType === 'deposit') {
            // Credit the user's account
            $newBalance = $currentBalance + $amount;
            $stmt = $db->prepare("UPDATE users SET account_balance = ? WHERE user_id = ?");
            $stmt->execute([$newBalance, $request['user_id']]);

            // Create a completed transaction record
            $reference = Security::generateReferenceNumber();
            $stmt = $db->prepare("INSERT INTO transactions (user_id, transaction_type, amount, description, reference_number, status, balance_after, authorization_pin_used, payment_pin_used, secure_pass_used, transaction_date) VALUES (?, 'credit', ?, ?, ?, 'completed', ?, 0, 0, 0, NOW())");
            $stmt->execute([
                $request['user_id'],
                $amount,
                $request['description'] ?? 'Approved deposit',
                $reference,
                $newBalance
            ]);

            // Log admin action
            Security::logAdminAction($_SESSION['admin_id'], 'deposit_approved', 'Approved deposit request #' . $requestId . ' for user ' . $request['user_id'] . ', amount ' . $amount, $request['user_id']);

        } elseif ($requestType === 'withdrawal') {
            // Verify sufficient funds again
            if ($amount > $currentBalance) {
                throw new Exception('Insufficient funds to approve withdrawal.');
            }
            // Debit the user's account
            $newBalance = $currentBalance - $amount;
            $stmt = $db->prepare("UPDATE users SET account_balance = ? WHERE user_id = ?");
            $stmt->execute([$newBalance, $request['user_id']]);

            // Create a completed transaction record
            $reference = Security::generateReferenceNumber();
            // For withdrawals we record all three pins as used (since they were provided by user)
            $stmt = $db->prepare("INSERT INTO transactions (user_id, transaction_type, amount, description, reference_number, status, balance_after, authorization_pin_used, payment_pin_used, secure_pass_used, transaction_date) VALUES (?, 'debit', ?, ?, ?, 'completed', ?, 1, 1, 1, NOW())");
            $stmt->execute([
                $request['user_id'],
                $amount,
                $request['description'] ?? 'Approved withdrawal',
                $reference,
                $newBalance
            ]);

            // Log admin action
            Security::logAdminAction($_SESSION['admin_id'], 'withdrawal_approved', 'Approved withdrawal request #' . $requestId . ' for user ' . $request['user_id'] . ', amount ' . $amount, $request['user_id']);
        }

        // Update request status to approved
        $stmt = $db->prepare("UPDATE transaction_requests SET status = 'approved', approved_by = ?, approved_at = NOW(), reject_reason = NULL, block1_status = 'success', block2_status = 'success', block3_status = 'success' WHERE request_id = ?");
        $stmt->execute([$_SESSION['admin_id'], $requestId]);

        // Commit transaction
        $db->commit();
        redirectWithMessage('transaction_requests.php', 'Request approved successfully.', 'success');
    }
    elseif ($action === 'reject') {
        // Set status to rejected and record reason
        $stmt = $db->prepare("UPDATE transaction_requests SET status = 'rejected', approved_by = ?, approved_at = NOW(), reject_reason = ? WHERE request_id = ?");
        $stmt->execute([$_SESSION['admin_id'], $rejectReason, $requestId]);

        // Log admin action
        Security::logAdminAction($_SESSION['admin_id'], 'request_rejected', 'Rejected transaction request #' . $requestId . ' for user ' . $request['user_id'] . ', reason: ' . $rejectReason, $request['user_id']);

        // Commit transaction
        $db->commit();
        redirectWithMessage('transaction_requests.php', 'Request rejected successfully.', 'success');
    }

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log('Error processing transaction request: ' . $e->getMessage());
    redirectWithMessage('transaction_requests.php', 'Error: ' . $e->getMessage(), 'danger');
}

?>