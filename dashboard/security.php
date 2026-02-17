<?php
require_once '../includes/config.php';
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login to access security settings.', 'danger');
}
$db = Database::getInstance()->getConnection();

$pinStmt = $db->prepare('SELECT transfer_pin_hash FROM user_pins WHERE user_id = ? AND transfer_pin_hash IS NOT NULL LIMIT 1');
$pinStmt->execute([$_SESSION['user_id']]);
$transferSet = (bool)$pinStmt->fetchColumn();

$userStmt = $db->prepare('SELECT kyc_status, kyc_review_reason, kyc_document FROM users WHERE user_id = ?');
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch() ?: ['kyc_status' => 'none', 'kyc_review_reason' => null, 'kyc_document' => null];

$logStmt = $db->prepare("SELECT action_timestamp, action_details, status, ip_address FROM audit_logs WHERE user_id = ? ORDER BY action_timestamp DESC LIMIT 15");
$logStmt->execute([$_SESSION['user_id']]);
$securityLogs = $logStmt->fetchAll();

$csrf = Security::generateCSRFToken();
require_once '../includes/header.php';
?>
<section class="hero"><div class="container"><h1>Security Settings</h1><p>Manage your Transfer PIN and KYC verification.</p></div></section>
<section style="padding:2rem 0;"><div class="container">

<div class="card" style="margin-bottom:1rem;"><div class="card-body">
  <h2>Transfer PIN</h2>
  <p>Status: <strong><?php echo $transferSet ? 'Set' : 'Not Set'; ?></strong></p>
  <a class="btn btn-outline btn-sm" href="setup_pins.php?change=1">Set / Change Transfer PIN</a>
</div></div>

<div class="card" style="margin-bottom:1rem;"><div class="card-body">
  <h2>KYC Verification</h2>
  <p>Current status: <strong><?php echo htmlspecialchars(ucfirst($user['kyc_status'])); ?></strong></p>
  <?php if (!empty($user['kyc_review_reason'])): ?>
    <p><strong>Review note:</strong> <?php echo htmlspecialchars($user['kyc_review_reason']); ?></p>
  <?php endif; ?>

  <?php if (in_array($user['kyc_status'], ['none', 'rejected'], true)): ?>
    <form action="kyc_upload.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
      <div class="form-group"><label>Upload KYC document (jpg/png/pdf, max 5MB)</label><input type="file" class="form-control" name="kyc_document" accept=".jpg,.jpeg,.png,.pdf" required></div>
      <button class="btn btn-primary" type="submit">Upload KYC Document</button>
    </form>
  <?php elseif ($user['kyc_status'] === 'pending'): ?>
    <div class="alert alert-info">Your document is under review.</div>
  <?php elseif ($user['kyc_status'] === 'verified'): ?>
    <div class="alert alert-success">KYC verified.</div>
  <?php endif; ?>
</div></div>

<div class="card"><div class="card-body">
  <h2>Recent Security Activity</h2>
  <div class="table-responsive"><table class="table"><thead><tr><th>Date</th><th>Activity</th><th>Status</th><th>IP</th></tr></thead><tbody>
    <?php foreach ($securityLogs as $log): ?>
      <tr><td><?php echo formatDateTime($log['action_timestamp']); ?></td><td><?php echo htmlspecialchars($log['action_details']); ?></td><td><?php echo htmlspecialchars(ucfirst($log['status'])); ?></td><td><?php echo htmlspecialchars($log['ip_address']); ?></td></tr>
    <?php endforeach; ?>
  </tbody></table></div>
</div></div>

</div></section>
<?php require_once '../includes/footer.php'; ?>
