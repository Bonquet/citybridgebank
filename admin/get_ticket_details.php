<?php
// Fetch detailed information for a support ticket for admin view via AJAX
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!Security::isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['ticket_id']) || !is_numeric($_GET['ticket_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid ticket ID']);
    exit;
}

$ticket_id = (int)$_GET['ticket_id'];

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT st.*, u.username, u.email, u.full_name FROM support_tickets st LEFT JOIN users u ON st.user_id = u.user_id WHERE st.ticket_id = ?");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$ticket) {
        echo json_encode(['success' => false, 'message' => 'Ticket not found']);
        exit;
    }
    // Format timestamps for display
    $ticket['created_at'] = formatDateTime($ticket['created_at']);
    if ($ticket['updated_at']) {
        $ticket['updated_at'] = formatDateTime($ticket['updated_at']);
    }
    if ($ticket['closed_at']) {
        $ticket['closed_at'] = formatDateTime($ticket['closed_at']);
    }
    echo json_encode(['success' => true, 'ticket' => $ticket]);
} catch (PDOException $e) {
    error_log('get_ticket_details error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}