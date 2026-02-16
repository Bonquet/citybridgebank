<?php
// Fetch user details via AJAX for the admin user management modal
require_once '../includes/config.php';

header('Content-Type: application/json');

// Only allow admins
if (!Security::isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate user ID
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

$user_id = (int)$_GET['user_id'];

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT user_id, username, full_name, email, phone, address, account_number, account_balance, account_status, kyc_status, created_at FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    // Format monetary fields
    $user['account_balance'] = formatCurrency($user['account_balance']);
    $user['created_at'] = formatDateTime($user['created_at']);
    echo json_encode(['success' => true, 'user' => $user]);
} catch (PDOException $e) {
    error_log('get_user_details error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}