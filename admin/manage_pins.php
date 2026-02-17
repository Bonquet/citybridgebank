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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        redirectWithMessage('manage_pins.php?user_id=' . $userId, 'Security verification failed.', 'danger');
    }

    $action = $_POST['action'] ?? '';
    $pinType = $_POST['pin_type'] ?? '';
    $reason = Security::sanitizeInput($_POST['reason'] ?? '');

    if ($action === 'toggle' && in_array($pinType, ['authorization', 'payment', 'secure_pass'], true)) {
        $active = isset($_POST['is_active']) ? 1 : 0;
        $stmt = $db->prepare('UPDATE user_pins SET is_active = ? WHERE user_id = ? AND pin_type = ?');
        $stmt->execute([$active, $userId, $pinType]);
        Security::logAudit('pin_stage_toggled', 'Admin changed pin stage availability: ' . $pinType . ' => ' . $active, $userId, $_SESSION['admin_id'], null, $pinType);
        Security::logAdminAction($_SESSION['admin_id'], 'pin_stage_toggled', 'Changed ' . $pinType . ' stage to ' . $active, $userId);
        redirectWithMessage('manage_pins.php?user_id=' . $userId, 'PIN stage updated.', 'success');
    }

    if ($action === 'reset' && in_array($pinType, ['authorization', 'payment', 'secure_pass', 'transfer'], true)) {
        if ($reason === '') {
            redirectWithMessage('manage_pins.php?user_id=' . $userId, 'Reason is required for PIN reset.', 'danger');
        }

        $newPin = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $hash = Security::hashPIN($newPin);

        if ($pinType === 'transfer') {
            $upd = $db->prepare('UPDATE user_pins SET transfer_pin_hash = ?, transfer_pin_created_at = NOW() WHERE user_id = ?');
            $upd->execute([$hash, $userId]);
            if ($upd->rowCount() === 0) {
                $ins = $db->prepare("INSERT INTO user_pins (user_id, pin_type, pin_hash, pin_plain, is_active, pin_created_at, transfer_pin_hash, transfer_pin_created_at) VALUES (?, 'authorization', ?, NULL, 1, NOW(), ?, NOW())");
                $ins->execute([$userId, Security::hashPIN('0000'), $hash]);
            }
        } else {
            $stmt = $db->prepare('INSERT INTO user_pins (user_id, pin_type, pin_hash, pin_plain, is_active, pin_created_at) VALUES (?, ?, ?, NULL, 1, NOW()) ON DUPLICATE KEY UPDATE pin_hash = VALUES(pin_hash), pin_plain = NULL, is_active = 1, pin_updated_at = NOW()');
            $stmt->execute([$userId, $pinType, $hash]);
        }

        // Keep plain PIN out of UI; record only hashed operation metadata.
        Security::logAudit('pin_reset', 'Admin reset ' . $pinType . ' PIN. Reason: ' . $reason, $userId, $_SESSION['admin_id'], null, $pinType);
        Security::logAdminAction($_SESSION['admin_id'], 'pin_reset', 'Reset ' . $pinType . ' PIN with reason: ' . $reason, $userId);

        $k = $db->prepare("INSERT INTO kyc_logs (user_id, admin_id, action, reason) VALUES (?, ?, 'request_reupload', ?)");
        $k->execute([$userId, $_SESSION['admin_id'], 'PIN reset (' . $pinType . '), reason: ' . $reason]);

        // Force user to set new transfer pin on next use by tracking banner flag.
        if ($pinType === 'transfer') {
            $db->prepare('UPDATE users SET kyc_review_reason = CONCAT(IFNULL(kyc_review_reason, ""), "\nTransfer PIN was reset by support. Please set a new Transfer PIN.") WHERE user_id = ?')->execute([$userId]);
        }

        redirectWithMessage('manage_pins.php?user_id=' . $userId, 'PIN reset completed. Value remains hidden by policy.', 'success');
    }
}

$userStmt = $db->prepare('SELECT user_id, full_name, username, email FROM users WHERE user_id = ?');
$userStmt->execute([$userId]);
$user = $userStmt->fetch();
if (!$user) {
    redirectWithMessage('users.php', 'User not found.', 'danger');
}

$pinStmt = $db->prepare('SELECT pin_type, is_active, transfer_pin_hash FROM user_pins WHERE user_id = ?');
$pinStmt->execute([$userId]);
$rows = $pinStmt->fetchAll();
$pins = ['authorization' => ['set'=>false,'active'=>false], 'payment' => ['set'=>false,'active'=>false], 'secure_pass' => ['set'=>false,'active'=>false], 'transfer' => ['set'=>false,'active'=>true]];
foreach ($rows as $r) {
    if (isset($pins[$r['pin_type']])) {
        $pins[$r['pin_type']]['set'] = true;
        $pins[$r['pin_type']]['active'] = (int)$r['is_active'] === 1;
    }
    if (!empty($r['transfer_pin_hash'])) {
        $pins['transfer']['set'] = true;
    }
}
$csrf = Security::generateCSRFToken();
require_once '../includes/header.php';
?>
<section style="padding:2rem 0;"><div class="container">
<h1>Manage PINs: <?php echo htmlspecialchars($user['full_name']); ?></h1>
<p><?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['email']); ?>)</p>

<div class="card"><div class="card-body">
<?php foreach (['authorization','payment','secure_pass','transfer'] as $t): ?>
  <div style="border-bottom:1px solid rgba(255,255,255,.1);padding:1rem 0;">
    <h3 style="margin:0 0 .5rem 0;"><?php echo ucfirst(str_replace('_',' ', $t)); ?> PIN</h3>
    <p>Status: <?php echo $pins[$t]['set'] ? 'Set (••••)' : 'Not set'; ?></p>

    <?php if ($t !== 'transfer'): ?>
      <form method="POST" style="display:flex;gap:.5rem;align-items:end;flex-wrap:wrap;margin-bottom:.5rem;">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>"><input type="hidden" name="user_id" value="<?php echo (int)$userId; ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="pin_type" value="<?php echo $t; ?>">
        <label><input type="checkbox" name="is_active" <?php echo $pins[$t]['active'] ? 'checked' : ''; ?>> Stage Enabled</label>
        <button class="btn btn-secondary btn-sm" type="submit">Save Stage Status</button>
      </form>
    <?php endif; ?>

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
