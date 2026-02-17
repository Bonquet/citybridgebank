<?php
require_once '../includes/config.php';
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login to manage your PIN settings.', 'danger');
}

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('SELECT transfer_pin_hash FROM user_pins WHERE user_id = ? AND transfer_pin_hash IS NOT NULL LIMIT 1');
$stmt->execute([$_SESSION['user_id']]);
$transferSet = (bool)$stmt->fetchColumn();

$changeMode = isset($_GET['change']) && $_GET['change'] === '1';
$csrf = Security::generateCSRFToken();
require_once '../includes/header.php';
?>
<section class="hero"><div class="container"><h1>Transfer PIN Setup</h1><p>Your Transfer PIN is required for transfer and withdrawal verification.</p></div></section>
<section style="padding:2rem 0;"><div class="container">
  <div class="card"><div class="card-body">
    <h3>Transfer PIN Status</h3>
    <p>Transfer PIN: <strong><?php echo $transferSet ? 'Set' : 'Not set'; ?></strong></p>
  </div></div>

  <div class="card"><div class="card-body">
    <h3><?php echo $changeMode ? 'Change Transfer PIN' : 'Set Your Transfer PIN'; ?></h3>
    <form method="POST" action="setup_pins_process.php">
      <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
      <input type="hidden" name="change_mode" value="<?php echo $changeMode ? '1' : '0'; ?>">

      <div class="form-group"><label>Transfer PIN (4 digits)</label><input class="form-control" type="password" name="transfer_pin" pattern="\d{4}" required></div>
      <div class="form-group"><label>Confirm Transfer PIN</label><input class="form-control" type="password" name="transfer_pin_confirm" pattern="\d{4}" required></div>
      <button class="btn btn-primary" type="submit"><?php echo $changeMode ? 'Update Transfer PIN' : 'Save Transfer PIN'; ?></button>
    </form>
  </div></div>
</div></section>
<?php require_once '../includes/footer.php'; ?>
