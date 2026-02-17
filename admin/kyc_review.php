<?php
require_once '../includes/config.php';
if (!Security::isAdminLoggedIn()) {
    redirectWithMessage('login.php', 'Please login as admin.', 'danger');
}
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (int)($_POST['user_id'] ?? 0);
if ($userId <= 0) {
    redirectWithMessage('kyc_requests.php', 'Invalid user ID.', 'danger');
}
$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        redirectWithMessage('kyc_review.php?user_id=' . $userId, 'Security verification failed.', 'danger');
    }
    $decisionMap = ['approve' => 'verified', 'reject' => 'rejected', 'request_reupload' => 'rejected'];
    $decision = $_POST['decision'] ?? '';
    $reason = Security::sanitizeInput($_POST['reason'] ?? '');
    if (!isset($decisionMap[$decision]) || $reason === '') {
        redirectWithMessage('kyc_review.php?user_id=' . $userId, 'Decision and reason are required.', 'danger');
    }

    $db->beginTransaction();
    $status = $decisionMap[$decision];
    $up = $db->prepare('UPDATE users SET kyc_status = ?, kyc_reviewed_by = ?, kyc_reviewed_at = NOW(), kyc_review_reason = ? WHERE user_id = ?');
    $up->execute([$status, $_SESSION['admin_id'], $reason, $userId]);

    $ins = $db->prepare('INSERT INTO kyc_logs (user_id, admin_id, action, reason) VALUES (?, ?, ?, ?)');
    $ins->execute([$userId, $_SESSION['admin_id'], $decision, $reason]);

    Security::logAudit('kyc_review', 'KYC decision: ' . $decision . '. Reason: ' . $reason, $userId, $_SESSION['admin_id']);
    Security::logAdminAction($_SESSION['admin_id'], 'kyc_' . $decision, 'KYC decision for user #' . $userId . ': ' . $reason, $userId);

    $db->commit();
    redirectWithMessage('kyc_requests.php', 'KYC review submitted.', 'success');
}

$stmt = $db->prepare('SELECT user_id, full_name, username, email, kyc_status, kyc_document, kyc_review_reason FROM users WHERE user_id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user) {
    redirectWithMessage('kyc_requests.php', 'User not found.', 'danger');
}
$csrf = Security::generateCSRFToken();
require_once '../includes/header.php';
?>
<section style="padding:2rem 0;"><div class="container">
<h1>KYC Review</h1>
<div class="card"><div class="card-body">
<p><strong>User:</strong> <?php echo htmlspecialchars($user['full_name'] . ' (' . $user['username'] . ')'); ?></p>
<p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
<p><strong>Status:</strong> <?php echo htmlspecialchars($user['kyc_status']); ?></p>
<?php if (!empty($user['kyc_document'])): ?>
  <p><a class="btn btn-outline" target="_blank" href="../uploads/kyc_docs/<?php echo rawurlencode($user['kyc_document']); ?>">Preview Document</a></p>
<?php else: ?>
  <p>No document uploaded.</p>
<?php endif; ?>
<form method="POST">
  <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
  <input type="hidden" name="user_id" value="<?php echo (int)$user['user_id']; ?>">
  <div class="form-group"><label>Decision</label><select class="form-control" name="decision" required><option value="approve">Approve</option><option value="reject">Reject</option><option value="request_reupload">Request Re-upload</option></select></div>
  <div class="form-group"><label>Review reason</label><textarea class="form-control" name="reason" required></textarea></div>
  <button class="btn btn-primary" type="submit">Submit Review</button>
</form>
</div></div>
</div></section>
<?php require_once '../includes/footer.php'; ?>
