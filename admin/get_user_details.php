<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!Security::isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('SELECT user_id, full_name, username, email, phone, address, account_number, account_balance, account_status, kyc_status, created_at FROM users WHERE user_id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'user' => [
            'user_id' => $user['user_id'],
            'full_name' => $user['full_name'],
            'username' => $user['username'],
            'email' => $user['email'],
            'phone' => $user['phone'] ?: 'N/A',
            'address' => $user['address'] ?: 'N/A',
            'account_number' => $user['account_number'],
            'account_balance' => formatCurrency($user['account_balance']),
            'account_status' => ucfirst($user['account_status']),
            'kyc_status' => ucfirst($user['kyc_status']),
            'created_at' => formatDateTime($user['created_at'])
        ]
    ]);
} catch (PDOException $e) {
    error_log('get_user_details error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error loading user details.']);
}
