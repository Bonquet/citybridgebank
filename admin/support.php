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
        if ($ticketId > 0 && $response !== '' && in_array($status, ['open','in_progress','closed'], true)) {
            $stmt = $db->prepare('UPDATE support_tickets SET admin_response = ?, status = ?, updated_at = NOW(), closed_at = CASE WHEN ? = "closed" THEN NOW() ELSE NULL END WHERE ticket_id = ?');
            $stmt->execute([$response, $status, $status, $ticketId]);
            Security::logAudit('ticket_updated', 'Admin replied to ticket #' . $ticketId, null, $_SESSION['admin_id']);
            redirectWithMessage('support.php', 'Ticket updated successfully.', 'success');
        }
    }

    if (($_POST['action'] ?? '') === 'reset_transfer_pin') {
        if (!$isSupportRole) {
            redirectWithMessage('support.php', 'You do not have permission to reset Transfer PINs.', 'danger');
        }
        $userId = (int)($_POST['user_id'] ?? 0);
        $reason = Security::sanitizeInput($_POST['reason'] ?? '');
        if ($userId <= 0 || $reason === '') {
            redirectWithMessage('support.php', 'Reset reason is required.', 'danger');
        }

        $newPin = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $hash = Security::hashPIN($newPin);
        $upd = $db->prepare('UPDATE user_pins SET transfer_pin_hash = ?, transfer_pin_created_at = NOW() WHERE user_id = ?');
        $upd->execute([$hash, $userId]);
        if ($upd->rowCount() === 0) {
            $ins = $db->prepare("INSERT INTO user_pins (user_id, pin_type, pin_hash, pin_plain, is_active, pin_created_at, transfer_pin_hash, transfer_pin_created_at) VALUES (?, 'authorization', ?, NULL, 1, NOW(), ?, NOW())");
            $ins->execute([$userId, Security::hashPIN('0000'), $hash]);
        }

        $db->prepare('UPDATE users SET kyc_review_reason = CONCAT(IFNULL(kyc_review_reason, ""), "\nSupport reset Transfer PIN. Please set a new Transfer PIN from Security settings.") WHERE user_id = ?')->execute([$userId]);
        Security::logAudit('transfer_pin_support_reset', 'Support reset transfer PIN. Reason: ' . $reason, $userId, $_SESSION['admin_id'], null, 'transfer', 'success');
        Security::logAdminAction($_SESSION['admin_id'], 'transfer_pin_support_reset', 'Transfer PIN reset for user #' . $userId . '. Reason: ' . $reason, $userId);
        $db->prepare("INSERT INTO kyc_logs (user_id, admin_id, action, reason) VALUES (?, ?, 'request_reupload', ?)")->execute([$userId, $_SESSION['admin_id'], 'Transfer PIN reset via support. Reason: ' . $reason]);

        redirectWithMessage('support.php', 'Transfer PIN reset complete. User must set a new PIN.', 'success');
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
  <form method="POST" style="margin-top:.75rem;display:flex;gap:.5rem;align-items:end;flex-wrap:wrap;">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
    <input type="hidden" name="action" value="reset_transfer_pin">
    <input type="hidden" name="user_id" value="<?php echo (int)$t['user_id']; ?>">
    <input class="form-control" style="max-width:360px" type="text" name="reason" placeholder="Reason for transfer PIN reset" required>
    <button class="btn btn-secondary btn-sm" type="submit">Reset Transfer PIN (Support)</button>
  </form>
  <?php endif; ?>
</div></div>
<?php endforeach; ?>
</div></section>
<?php require_once '../includes/footer.php'; ?>
