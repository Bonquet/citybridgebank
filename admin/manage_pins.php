<?php
require_once '../includes/config.php';
if (!Security::isAdminLoggedIn()) {
    redirectWithMessage('login.php', 'Please login to manage PINs.', 'danger');
}

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (int)($_POST['user_id'] ?? 0);
if ($userId <= 0) {
    redirectWithMessage('users.php', 'Invalid user selected.', 'danger');
}

$db = Database::getInstance()->getConnection();
$managedPinTypes = ['authorization', 'payment', 'secure_pass'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        redirectWithMessage('manage_pins.php?user_id=' . $userId, 'Security verification failed.', 'danger');
    }

    $action = $_POST['action'] ?? '';
    $pinType = $_POST['pin_type'] ?? '';
    $reason = Security::sanitizeInput($_POST['reason'] ?? '');

    if (!in_array($pinType, $managedPinTypes, true)) {
        redirectWithMessage('manage_pins.php?user_id=' . $userId, 'Invalid PIN type.', 'danger');
    }

    if ($action === 'toggle') {
        $active = isset($_POST['is_active']) ? 1 : 0;
        $stmt = $db->prepare('UPDATE user_pins SET is_active = ? WHERE user_id = ? AND pin_type = ?');
        $stmt->execute([$active, $userId, $pinType]);

        Security::logAudit('pin_stage_toggled', 'Admin changed pin stage availability: ' . $pinType . ' => ' . $active, $userId, $_SESSION['admin_id'], null, $pinType);
        Security::logAdminAction($_SESSION['admin_id'], 'pin_stage_toggled', 'Changed ' . $pinType . ' stage to ' . $active, $userId);
        redirectWithMessage('manage_pins.php?user_id=' . $userId, 'PIN stage updated.', 'success');
    }

    if ($reason === '') {
        redirectWithMessage('manage_pins.php?user_id=' . $userId, 'Reason is required for PIN update.', 'danger');
    }

    if ($action === 'reset') {
        $newPin = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $hash = Security::hashPIN($newPin);

        $stmt = $db->prepare('INSERT INTO user_pins (user_id, pin_type, pin_hash, pin_plain, is_active, pin_created_at) VALUES (?, ?, ?, ?, 1, NOW()) ON DUPLICATE KEY UPDATE pin_hash = VALUES(pin_hash), pin_plain = VALUES(pin_plain), is_active = 1, pin_updated_at = NOW()');
        $stmt->execute([$userId, $pinType, $hash, $newPin]);

        Security::logAudit('pin_reset', 'Admin reset ' . $pinType . ' PIN. Reason: ' . $reason, $userId, $_SESSION['admin_id'], null, $pinType);
        Security::logAdminAction($_SESSION['admin_id'], 'pin_reset', 'Reset ' . $pinType . ' PIN with reason: ' . $reason, $userId);
        $db->prepare("INSERT INTO kyc_logs (user_id, admin_id, action, reason) VALUES (?, ?, 'request_reupload', ?)")->execute([$userId, $_SESSION['admin_id'], 'PIN reset (' . $pinType . '), reason: ' . $reason]);

        redirectWithMessage('manage_pins.php?user_id=' . $userId, ucfirst(str_replace('_', ' ', $pinType)) . ' PIN reset. New PIN is now visible to admin.', 'success');
    }

    if ($action === 'set_custom') {
        $newPin = trim($_POST['new_pin'] ?? '');
        if (!preg_match('/^\d{4,6}$/', $newPin)) {
            redirectWithMessage('manage_pins.php?user_id=' . $userId, 'Custom PIN must be 4-6 digits.', 'danger');
        }

        $stmt = $db->prepare('INSERT INTO user_pins (user_id, pin_type, pin_hash, pin_plain, is_active, pin_created_at) VALUES (?, ?, ?, ?, 1, NOW()) ON DUPLICATE KEY UPDATE pin_hash = VALUES(pin_hash), pin_plain = VALUES(pin_plain), is_active = 1, pin_updated_at = NOW()');
        $stmt->execute([$userId, $pinType, Security::hashPIN($newPin), $newPin]);

        Security::logAudit('pin_changed_admin', 'Admin set custom ' . $pinType . ' PIN. Reason: ' . $reason, $userId, $_SESSION['admin_id'], null, $pinType);
        Security::logAdminAction($_SESSION['admin_id'], 'pin_changed_admin', 'Set custom ' . $pinType . ' PIN with reason: ' . $reason, $userId);
        $db->prepare("INSERT INTO kyc_logs (user_id, admin_id, action, reason) VALUES (?, ?, 'request_reupload', ?)")->execute([$userId, $_SESSION['admin_id'], 'PIN custom set (' . $pinType . '), reason: ' . $reason]);

        redirectWithMessage('manage_pins.php?user_id=' . $userId, ucfirst(str_replace('_', ' ', $pinType)) . ' PIN changed successfully.', 'success');
    }
}

$userStmt = $db->prepare('SELECT user_id, full_name, username, email FROM users WHERE user_id = ?');
$userStmt->execute([$userId]);
$user = $userStmt->fetch();
if (!$user) {
    redirectWithMessage('users.php', 'User not found.', 'danger');
}

$pinStmt = $db->prepare('SELECT pin_type, is_active, pin_plain FROM user_pins WHERE user_id = ?');
$pinStmt->execute([$userId]);
$rows = $pinStmt->fetchAll();
$pins = [
    'authorization' => ['set' => false, 'active' => false, 'plain' => null],
    'payment' => ['set' => false, 'active' => false, 'plain' => null],
    'secure_pass' => ['set' => false, 'active' => false, 'plain' => null]
];
foreach ($rows as $r) {
    if (isset($pins[$r['pin_type']])) {
        $pins[$r['pin_type']]['set'] = true;
        $pins[$r['pin_type']]['active'] = (int)$r['is_active'] === 1;
        $pins[$r['pin_type']]['plain'] = $r['pin_plain'];
    }
}

$csrf = Security::generateCSRFToken();
require_once '../includes/header.php';
?>
<section style="padding:2rem 0;"><div class="container">
<h1>Manage Security PINs: <?php echo htmlspecialchars($user['full_name']); ?></h1>
<p><?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['email']); ?>)</p>
<p style="color:var(--text-secondary)">Admin can view and change Authentication, Payment, and Secure PIN values.</p>

<div class="card"><div class="card-body">
<?php foreach (['authorization','payment','secure_pass'] as $t): ?>
  <div style="border-bottom:1px solid rgba(255,255,255,.1);padding:1rem 0;">
    <h3 style="margin:0 0 .5rem 0;"><?php echo ucfirst(str_replace('_',' ', $t)); ?> PIN</h3>
    <p>Status: <?php echo $pins[$t]['set'] ? 'Set' : 'Not set'; ?></p>
    <p>Current PIN (Admin View): <strong><?php echo $pins[$t]['plain'] !== null ? htmlspecialchars($pins[$t]['plain']) : 'Unavailable (reset or set custom to reveal)'; ?></strong></p>

    <form method="POST" style="display:flex;gap:.5rem;align-items:end;flex-wrap:wrap;margin-bottom:.5rem;">
      <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>"><input type="hidden" name="user_id" value="<?php echo (int)$userId; ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="pin_type" value="<?php echo $t; ?>">
      <label><input type="checkbox" name="is_active" <?php echo $pins[$t]['active'] ? 'checked' : ''; ?>> Stage Enabled</label>
      <button class="btn btn-secondary btn-sm" type="submit">Save Stage Status</button>
    </form>

    <form method="POST" style="display:flex;gap:.5rem;align-items:end;flex-wrap:wrap;margin-bottom:.5rem;">
      <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>"><input type="hidden" name="user_id" value="<?php echo (int)$userId; ?>"><input type="hidden" name="action" value="set_custom"><input type="hidden" name="pin_type" value="<?php echo $t; ?>">
      <input class="form-control" style="max-width:200px" type="text" name="new_pin" pattern="\d{4,6}" placeholder="New PIN (4-6 digits)" required>
      <input class="form-control" style="max-width:280px" type="text" name="reason" placeholder="Change reason (required)" required>
      <button class="btn btn-primary btn-sm" type="submit">Change PIN</button>
    </form>

    <form method="POST" style="display:flex;gap:.5rem;align-items:end;flex-wrap:wrap;">
      <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>"><input type="hidden" name="user_id" value="<?php echo (int)$userId; ?>"><input type="hidden" name="action" value="reset"><input type="hidden" name="pin_type" value="<?php echo $t; ?>">
      <input class="form-control" style="max-width:340px" type="text" name="reason" placeholder="Reset reason (required)" required>
      <button class="btn btn-primary btn-sm" type="submit">Reset / Regenerate</button>
    </form>
  </div>
<?php endforeach; ?>
</div></div>
</div></section>
<?php require_once '../includes/footer.php'; ?>
