<?php
// Update support ticket status and admin response via AJAX
require_once '../includes/config.php';

header('Content-Type: application/json');

// Only admins may update tickets
if (!Security::isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate and sanitize input
$ticket_id = isset($_POST['ticket_id']) && is_numeric($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
$new_status = isset($_POST['new_status']) ? Security::sanitizeInput($_POST['new_status']) : '';
$admin_response = isset($_POST['admin_response']) ? trim($_POST['admin_response']) : '';

// Allowed status transitions
$allowed = ['in_progress', 'closed'];
if ($ticket_id <= 0 || !in_array($new_status, $allowed, true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    // Fetch existing ticket
    $stmt = $db->prepare("SELECT * FROM support_tickets WHERE ticket_id = ?");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$ticket) {
        echo json_encode(['success' => false, 'message' => 'Ticket not found']);
        exit;
    }
    // Prepare update
    $adminId = $_SESSION['admin_id'];
    if ($new_status === 'in_progress') {
        // Cannot mark closed tickets back to in_progress
        if ($ticket['status'] === 'closed') {
            echo json_encode(['success' => false, 'message' => 'Cannot re-open closed ticket']);
            exit;
        }
        $stmt = $db->prepare("UPDATE support_tickets SET status = 'in_progress', updated_at = NOW() WHERE ticket_id = ?");
        $stmt->execute([$ticket_id]);
        Security::logAudit('ticket_status_update', "Ticket #{$ticket_id} marked In Progress", $ticket['user_id'], $adminId, null, null, 'success');
        Security::logAdminAction($adminId, 'ticket_status_update', "Ticket #{$ticket_id} marked In Progress", $ticket['user_id']);
        echo json_encode(['success' => true, 'message' => 'Ticket marked as In Progress']);
    } elseif ($new_status === 'closed') {
        // Require response to close
        if ($admin_response === '') {
            echo json_encode(['success' => false, 'message' => 'A response is required to close the ticket']);
            exit;
        }
        $stmt = $db->prepare("UPDATE support_tickets SET status = 'closed', admin_response = ?, closed_at = NOW(), updated_at = NOW() WHERE ticket_id = ?");
        $stmt->execute([$admin_response, $ticket_id]);
        Security::logAudit('ticket_closed', "Ticket #{$ticket_id} closed with response", $ticket['user_id'], $adminId, null, null, 'success');
        Security::logAdminAction($adminId, 'ticket_closed', "Ticket #{$ticket_id} closed", $ticket['user_id']);
        echo json_encode(['success' => true, 'message' => 'Ticket closed successfully']);
    }
} catch (PDOException $e) {
    error_log('update_ticket error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}