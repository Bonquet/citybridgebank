<?php
require_once '../includes/config.php';
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login to manage your PIN settings.', 'danger');
}

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('SELECT pin_type, transfer_pin_hash FROM user_pins WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$rows = $stmt->fetchAll();
$status = ['authorization' => false, 'payment' => false, 'secure_pass' => false];
$transferSet = false;
foreach ($rows as $r) {
    if (isset($status[$r['pin_type']])) {
        $status[$r['pin_type']] = true;
    }
    if (!empty($r['transfer_pin_hash'])) {
        $transferSet = true;
    }
}

$changeMode = isset($_GET['change']) && $_GET['change'] === '1';
$csrf = Security::generateCSRFToken();
require_once '../includes/header.php';
?>
<section class="hero"><div class="container"><h1>Transfer PIN Setup</h1><p>Your Transfer PIN is user-managed and required for transfer/withdraw verification.</p></div></section>
<section style="padding:2rem 0;"><div class="container">
  <div class="card"><div class="card-body">
    <h3>PIN Ownership Policy</h3>
    <p>Transfer PIN: <strong><?php echo $transferSet ? 'Set' : 'Not set'; ?></strong></p>
    <p>Authentication PIN: <strong><?php echo $status['authorization'] ? 'Configured by Admin' : 'Managed by Admin'; ?></strong></p>
    <p>Payment PIN: <strong><?php echo $status['payment'] ? 'Configured by Admin' : 'Managed by Admin'; ?></strong></p>
    <p>Secure PIN: <strong><?php echo $status['secure_pass'] ? 'Configured by Admin' : 'Managed by Admin'; ?></strong></p>
    <p style="color:var(--text-secondary)">Authentication/Payment/Secure PIN assistance is handled through Support Ticket only.</p>
  </div></div>

  <div class="card"><div class="card-body">
    <h3><?php echo $changeMode ? 'Change Transfer PIN' : 'Set Your Transfer PIN'; ?></h3>
    <form method="POST" action="setup_pins_process.php">
      <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
      <input type="hidden" name="change_mode" value="<?php echo $changeMode ? '1' : '0'; ?>">

      <div class="form-group"><label>Transfer PIN (4 digits)</label><input class="form-control" type="password" name="transfer_pin" pattern="\d{4}" required></div>
      <div class="form-group"><label>Confirm Transfer PIN</label><input class="form-control" type="password" name="transfer_pin_confirm" pattern="\d{4}" required></div>
      <button class="btn btn-primary" type="submit"><?php echo $changeMode ? 'Update Transfer PIN' : 'Save Transfer PIN'; ?></button>
      <a href="support.php" class="btn btn-secondary" style="margin-left:.5rem;">Contact Support for Other PINs</a>
    </form>
  </div></div>
</div></section>
<?php require_once '../includes/footer.php'; ?>
