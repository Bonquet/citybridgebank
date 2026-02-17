<?php
require_once '../includes/config.php';
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login first.', 'danger');
}
$type = Security::sanitizeInput($_GET['type'] ?? $_POST['type'] ?? '');
$map = ['payment' => 'payment', 'secure' => 'secure_pass'];
if (!isset($map[$type])) {
    redirectWithMessage('security.php', 'Invalid PIN type requested.', 'danger');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        redirectWithMessage('change_pin.php?type=' . $type, 'Security verification failed.', 'danger');
    }
    $newPin = trim($_POST['new_pin'] ?? '');
    $confirmPin = trim($_POST['confirm_pin'] ?? '');
    if (!preg_match('/^\d{4,6}$/', $newPin) || $newPin !== $confirmPin) {
        redirectWithMessage('change_pin.php?type=' . $type, 'PIN must be 4-6 digits and match confirmation.', 'danger');
    }

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('INSERT INTO user_pins (user_id, pin_type, pin_hash, pin_plain, is_active, pin_created_at) VALUES (?, ?, ?, NULL, 1, NOW()) ON DUPLICATE KEY UPDATE pin_hash = VALUES(pin_hash), pin_plain = NULL, is_active = 1, pin_updated_at = NOW()');
    $stmt->execute([$_SESSION['user_id'], $map[$type], Security::hashPIN($newPin)]);
    Security::logAudit('pin_changed', ucfirst($type) . ' PIN changed by user', $_SESSION['user_id'], null, null, $map[$type]);
    redirectWithMessage('security.php', ucfirst($type) . ' PIN updated successfully.', 'success');
}
$csrf = Security::generateCSRFToken();
require_once '../includes/header.php';
?>
<section class="hero"><div class="container"><h1>Change <?php echo ucfirst($type); ?> PIN</h1></div></section>
<section style="padding:2rem 0;"><div class="container"><div class="card"><div class="card-body">
<form method="POST">
<input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
<input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
<div class="form-group"><label>New PIN</label><input type="password" class="form-control" name="new_pin" required pattern="\d{4,6}"></div>
<div class="form-group"><label>Confirm PIN</label><input type="password" class="form-control" name="confirm_pin" required pattern="\d{4,6}"></div>
<button class="btn btn-primary" type="submit">Save PIN</button>
</form>
</div></div></div></section>
<?php require_once '../includes/footer.php'; ?>
