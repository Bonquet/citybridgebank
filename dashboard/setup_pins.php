<?php
require_once '../includes/config.php';
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login to manage your PIN settings.', 'danger');
}

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('SELECT pin_type, is_active, transfer_pin_hash FROM user_pins WHERE user_id = ?');
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
<section class="hero"><div class="container"><h1>PIN Settings</h1><p>Set transaction PINs and Transfer PIN securely.</p></div></section>
<section style="padding:2rem 0;"><div class="container">
  <div class="card"><div class="card-body">
    <h3>PIN Status</h3>
    <p>Authorization PIN: <strong><?php echo $status['authorization'] ? 'Set' : 'Not set'; ?></strong></p>
    <p>Payment PIN: <strong><?php echo $status['payment'] ? 'Set' : 'Not set'; ?></strong></p>
    <p>Secure PIN: <strong><?php echo $status['secure_pass'] ? 'Set' : 'Not set'; ?></strong></p>
    <p>Transfer PIN: <strong><?php echo $transferSet ? 'Set' : 'Not set'; ?></strong></p>
    <p style="color:var(--text-secondary)">For Authentication/Payment/Secure PIN recovery, contact support.</p>
  </div></div>

  <div class="card"><div class="card-body">
    <h3><?php echo $changeMode ? 'Change Transfer PIN' : 'Set PINs'; ?></h3>
    <form method="POST" action="setup_pins_process.php">
      <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
      <input type="hidden" name="change_mode" value="<?php echo $changeMode ? '1' : '0'; ?>">

      <?php if (!$changeMode): ?>
      <div class="form-group"><label>Authorization PIN (4-6 digits)</label><input class="form-control" type="password" name="authorization_pin" pattern="\d{4,6}" required></div>
      <div class="form-group"><label>Payment PIN (4-6 digits)</label><input class="form-control" type="password" name="payment_pin" pattern="\d{4,6}" required></div>
      <div class="form-group"><label>Secure PIN (4-6 digits)</label><input class="form-control" type="password" name="secure_pass_pin" pattern="\d{4,6}" required></div>
      <?php endif; ?>

      <div class="form-group"><label>Transfer PIN (4 digits)</label><input class="form-control" type="password" name="transfer_pin" pattern="\d{4}" required></div>
      <div class="form-group"><label>Confirm Transfer PIN</label><input class="form-control" type="password" name="transfer_pin_confirm" pattern="\d{4}" required></div>
      <button class="btn btn-primary" type="submit"><?php echo $changeMode ? 'Update Transfer PIN' : 'Save PINs'; ?></button>
    </form>
  </div></div>
</div></section>
<?php require_once '../includes/footer.php'; ?>
