<?php
require_once '../includes/config.php';
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login to access support.', 'danger');
}
$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        redirectWithMessage('support.php', 'Security verification failed.', 'danger');
    }
    $subject = Security::sanitizeInput($_POST['subject'] ?? '');
    $message = Security::sanitizeInput($_POST['message'] ?? '');
    $priority = Security::sanitizeInput($_POST['priority'] ?? 'medium');
    if ($subject === '' || $message === '') {
        redirectWithMessage('support.php', 'Subject and message are required.', 'danger');
    }
    $stmt = $db->prepare('INSERT INTO support_tickets (user_id, subject, message, priority, status) VALUES (?, ?, ?, ?, "open")');
    $stmt->execute([$_SESSION['user_id'], $subject, $message, in_array($priority, ['low','medium','high','urgent'], true) ? $priority : 'medium']);
    Security::logAudit('support_ticket_created', 'User created support ticket', $_SESSION['user_id']);
    redirectWithMessage('support.php', 'Support ticket submitted.', 'success');
}

$stmt = $db->prepare('SELECT ticket_id, subject, message, admin_response, priority, status, created_at, updated_at, closed_at FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$tickets = $stmt->fetchAll();
$csrf = Security::generateCSRFToken();
require_once '../includes/header.php';
?>
<section class="hero"><div class="container"><h1>Support Center</h1><p>Contact support and track replies.</p></div></section>
<section style="padding:2rem 0;"><div class="container">
<div class="card" style="margin-bottom:1rem;"><div class="card-body">
  <h2>Submit Ticket</h2>
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
    <div class="form-group"><label>Subject</label><input class="form-control" name="subject" required></div>
    <div class="form-group"><label>Priority</label><select class="form-control" name="priority"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
    <div class="form-group"><label>Message</label><textarea class="form-control" name="message" required></textarea></div>
    <button class="btn btn-primary" type="submit">Create Ticket</button>
  </form>
</div></div>

<?php foreach ($tickets as $t): ?>
<div class="card" style="margin-bottom:1rem;"><div class="card-body">
  <h3>#<?php echo (int)$t['ticket_id']; ?> - <?php echo htmlspecialchars($t['subject']); ?></h3>
  <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($t['status'])); ?> | <strong>Priority:</strong> <?php echo htmlspecialchars(ucfirst($t['priority'])); ?></p>
  <div style="padding:.75rem;border:1px solid rgba(255,255,255,.1);border-radius:8px;margin-bottom:.5rem;">
    <p style="margin:0 0 .25rem 0;"><strong>You</strong> <small><?php echo formatDateTime($t['created_at']); ?></small></p>
    <p style="margin:0;"><?php echo nl2br(htmlspecialchars($t['message'])); ?></p>
  </div>
  <div style="padding:.75rem;border:1px solid rgba(255,255,255,.1);border-radius:8px;">
    <p style="margin:0 0 .25rem 0;"><strong>Admin</strong> <small><?php echo formatDateTime($t['updated_at']); ?></small></p>
    <p style="margin:0;"><?php echo $t['admin_response'] ? nl2br(htmlspecialchars($t['admin_response'])) : '<em>No response yet</em>'; ?></p>
  </div>
  <?php if (!empty($t['closed_at'])): ?><p><small>Closed at <?php echo formatDateTime($t['closed_at']); ?></small></p><?php endif; ?>
</div></div>
<?php endforeach; ?>
</div></section>
<?php require_once '../includes/footer.php'; ?>
