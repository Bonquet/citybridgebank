<?php
require_once '../includes/config.php';
if (!Security::isAdminLoggedIn()) {
    redirectWithMessage('login.php', 'Please login to access support tickets.', 'danger');
}

$db = Database::getInstance()->getConnection();
$isSupportRole = (($_SESSION['admin_role'] ?? '') === 'support' || ($_SESSION['admin_role'] ?? '') === 'super_admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        redirectWithMessage('support.php', 'Security verification failed.', 'danger');
    }

    if (($_POST['action'] ?? '') === 'reply') {
        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $response = Security::sanitizeInput($_POST['response'] ?? '');
        $status = Security::sanitizeInput($_POST['status'] ?? 'open');

        if ($ticketId > 0 && $response !== '' && in_array($status, ['open', 'in_progress', 'closed'], true)) {
            $stmt = $db->prepare('UPDATE support_tickets SET admin_response = ?, status = ?, updated_at = NOW(), closed_at = CASE WHEN ? = "closed" THEN NOW() ELSE NULL END WHERE ticket_id = ?');
            $stmt->execute([$response, $status, $status, $ticketId]);

            Security::logAudit('ticket_updated', 'Admin replied to ticket #' . $ticketId, null, $_SESSION['admin_id']);
            Security::logAdminAction($_SESSION['admin_id'], 'ticket_updated', 'Replied to support ticket #' . $ticketId . ' status=' . $status, null);

            redirectWithMessage('support.php', 'Ticket updated successfully.', 'success');
        }

        redirectWithMessage('support.php', 'Please provide a valid response and status.', 'danger');
    }
}

$stmt = $db->query('SELECT t.ticket_id, t.user_id, t.subject, t.message, t.priority, t.status, t.admin_response, t.created_at, t.updated_at, u.username, u.full_name FROM support_tickets t JOIN users u ON u.user_id = t.user_id ORDER BY t.created_at DESC LIMIT 200');
$tickets = $stmt->fetchAll();
$csrf = Security::generateCSRFToken();
require_once '../includes/header.php';
?>
<section style="padding:2rem 0;"><div class="container">
<h1>Support Tickets</h1>
<?php foreach ($tickets as $t): ?>
<div class="card" style="margin-bottom:1rem;"><div class="card-body">
  <h3>#<?php echo (int)$t['ticket_id']; ?> - <?php echo htmlspecialchars($t['subject']); ?></h3>
  <p><strong>User:</strong> <?php echo htmlspecialchars($t['full_name'] . ' (' . $t['username'] . ')'); ?></p>
  <p><strong>Status:</strong> <?php echo htmlspecialchars($t['status']); ?> | <strong>Priority:</strong> <?php echo htmlspecialchars($t['priority']); ?></p>
  <p><strong>Message:</strong><br><?php echo nl2br(htmlspecialchars($t['message'])); ?></p>
  <p><strong>Admin Reply:</strong><br><?php echo $t['admin_response'] ? nl2br(htmlspecialchars($t['admin_response'])) : '<em>No response yet</em>'; ?></p>
  <p><small>Created: <?php echo formatDateTime($t['created_at']); ?> | Updated: <?php echo formatDateTime($t['updated_at']); ?></small></p>

  <form method="POST" style="margin-top:.5rem;">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
    <input type="hidden" name="action" value="reply">
    <input type="hidden" name="ticket_id" value="<?php echo (int)$t['ticket_id']; ?>">
    <div class="form-group"><textarea class="form-control" name="response" placeholder="Add admin response" required></textarea></div>
    <div class="form-group"><select name="status" class="form-control"><option value="open">Open</option><option value="in_progress">In Progress</option><option value="closed">Closed</option></select></div>
    <button class="btn btn-primary btn-sm" type="submit">Save Response</button>
  </form>

  <?php if ($isSupportRole): ?>
    <div style="margin-top:.75rem;">
      <a href="manage_pins.php?user_id=<?php echo (int)$t['user_id']; ?>" class="btn btn-secondary btn-sm">Manage Authentication/Payment/Secure PINs</a>
      <p style="margin-top:.35rem;color:var(--text-secondary)">Use admin PIN tools for reset/regenerate actions with a required reason and audit trail.</p>
    </div>
  <?php endif; ?>
</div></div>
<?php endforeach; ?>
</div></section>
<?php require_once '../includes/footer.php'; ?>
