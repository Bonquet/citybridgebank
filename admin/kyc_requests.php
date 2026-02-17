<?php
require_once '../includes/config.php';
if (!Security::isAdminLoggedIn()) {
    redirectWithMessage('login.php', 'Please login as admin.', 'danger');
}
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT user_id, full_name, username, email, kyc_status, kyc_review_reason, created_at FROM users WHERE kyc_status IN ('pending','rejected','suspended') ORDER BY created_at DESC");
$users = $stmt->fetchAll();
require_once '../includes/header.php';
?>
<section style="padding:2rem 0;"><div class="container">
<h1>KYC Requests</h1>
<div class="card"><div class="card-body"><div class="table-responsive"><table class="table"><thead><tr><th>User</th><th>Email</th><th>Status</th><th>Reason</th><th>Action</th></tr></thead><tbody>
<?php foreach ($users as $u): ?>
<tr><td><?php echo htmlspecialchars($u['full_name'] . ' (' . $u['username'] . ')'); ?></td><td><?php echo htmlspecialchars($u['email']); ?></td><td><?php echo htmlspecialchars($u['kyc_status']); ?></td><td><?php echo htmlspecialchars($u['kyc_review_reason'] ?? ''); ?></td><td><a class="btn btn-primary btn-sm" href="kyc_review.php?user_id=<?php echo (int)$u['user_id']; ?>">Review</a></td></tr>
<?php endforeach; ?>
</tbody></table></div></div></div>
</div></section>
<?php require_once '../includes/footer.php'; ?>
