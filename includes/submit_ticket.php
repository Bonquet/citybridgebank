<?php
// CITYBRIDGEBANK Support Ticket Submission
require_once 'config.php';

// Check if user is logged in
if (!Security::isLoggedIn()) {
    // Redirect to public login when not logged in (fix broken path)
    redirectWithMessage('../public/login.php', 'Please login to submit a support ticket.', 'danger');
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $redirectTarget = '../public/support.php';
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], '/dashboard/') !== false) {
        $redirectTarget = '../dashboard/support.php';
    }
    redirectWithMessage($redirectTarget, 'Invalid request method.', 'danger');
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
    $redirectTarget = '../public/support.php';
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], '/dashboard/') !== false) {
        $redirectTarget = '../dashboard/support.php';
    }
    redirectWithMessage($redirectTarget, 'Security verification failed. Please try again.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Sanitize and validate input
    $subject = Security::sanitizeInput($_POST['subject']);
    $message = Security::sanitizeInput($_POST['message']);
    $priority = isset($_POST['priority']) ? Security::sanitizeInput($_POST['priority']) : 'medium';
    $category = isset($_POST['category']) ? Security::sanitizeInput($_POST['category']) : 'general';
    
    // Validation
    $errors = [];
    
    if (empty($subject) || strlen($subject) < 5) {
        $errors[] = 'Subject must be at least 5 characters.';
    }
    
    if (empty($message) || strlen($message) < 10) {
        $errors[] = 'Message must be at least 10 characters.';
    }
    
    if (!in_array($priority, ['low', 'medium', 'high', 'urgent'])) {
        $errors[] = 'Invalid priority level.';
    }
    
    if (!empty($errors)) {
        $redirectTarget = '../public/support.php';
        if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], '/dashboard/') !== false) {
            $redirectTarget = '../dashboard/support.php';
        }
        redirectWithMessage($redirectTarget, implode(' ', $errors), 'danger');
    }
    
    // Create support ticket
    $stmt = $db->prepare("INSERT INTO support_tickets 
        (user_id, subject, message, priority, status, created_at) 
        VALUES (?, ?, ?, ?, 'open', NOW())");
    $stmt->execute([$_SESSION['user_id'], $subject, $message, $priority]);
    
    $ticket_id = $db->lastInsertId();
    
    // Log ticket creation
    Security::logAudit('support_ticket_created', "Support ticket #{$ticket_id} created by user {$_SESSION['username']}", $_SESSION['user_id']);
    
    // Success message
    // Redirect the user back to the appropriate support page depending on context.
    // If the referer is from the dashboard or the user is logged in and currently
    // on a dashboard page, send them to the dashboard support page. Otherwise
    // direct them to the public support page.
    $redirectTarget = '../public/support.php';
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], '/dashboard/') !== false) {
        $redirectTarget = '../dashboard/support.php';
    }
    redirectWithMessage($redirectTarget, "Support ticket #{$ticket_id} created successfully. Our team will respond within 24 hours.", 'success');
    
} catch (PDOException $e) {
    error_log("Support ticket error: " . $e->getMessage());
    // Redirect to the public support page on error (fix broken path)
    $redirectTarget = '../public/support.php';
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], '/dashboard/') !== false) {
        $redirectTarget = '../dashboard/support.php';
    }
    redirectWithMessage($redirectTarget, 'An error occurred submitting your ticket. Please try again.', 'danger');
}
?>