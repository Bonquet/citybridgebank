<?php
// Manage a user's three-layer PINs (Authorization, Payment, Secure Pass)
// This script allows an admin to view and update a user's PINs. It processes
// form submissions before any HTML is sent to the browser to avoid headers
// already sent errors. It logs all updates and status changes for audit.

require_once '../includes/config.php';

// Ensure admin is logged in
if (!Security::isAdminLoggedIn()) {
    redirectWithMessage('login.php', 'Please login to manage user PINs.', 'danger');
}

// Validate user_id parameter
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    redirectWithMessage('user_pins.php', 'No user selected for PIN management.', 'danger');
}
$user_id = (int)$_GET['user_id'];

try {
    $db = Database::getInstance()->getConnection();

    // Handle form submission (POST) before output
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
            redirectWithMessage("manage_pins.php?user_id={$user_id}", 'Security verification failed. Please try again.', 'danger');
        }
        // Confirm hidden user_id matches expected ID
        $form_user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : $user_id;
        if ($form_user_id !== $user_id) {
            redirectWithMessage("manage_pins.php?user_id={$user_id}", 'Invalid user ID.', 'danger');
        }
        // Begin transaction for atomic updates
        $db->beginTransaction();
        // Helper to create or update a PIN value
        $updateOrInsertPin = function($type, $newPlain) use ($db, $user_id) {
            $newHash = Security::hashPIN($newPlain);
            // Check if pin exists
            $stmt = $db->prepare("SELECT pin_id FROM user_pins WHERE user_id = ? AND pin_type = ?");
            $stmt->execute([$user_id, $type]);
            $existing = $stmt->fetchColumn();
            if ($existing) {
                $stmt = $db->prepare("UPDATE user_pins SET pin_hash = ?, pin_plain = ?, is_active = 1, pin_created_at = NOW(), last_used = NULL WHERE pin_id = ?");
                $stmt->execute([$newHash, $newPlain, $existing]);
            } else {
                $stmt = $db->prepare("INSERT INTO user_pins (user_id, pin_type, pin_hash, pin_plain, is_active, pin_created_at) VALUES (?, ?, ?, ?, 1, NOW())");
                $stmt->execute([$user_id, $type, $newHash, $newPlain]);
            }
            // Log audit and admin action
            $adminId = $_SESSION['admin_id'] ?? null;
            Security::logAudit('pin_updated', "$type PIN updated for user ID {$user_id}", $user_id, $adminId, null, $type, 'success');
            Security::logAdminAction($adminId, 'pin_updated', ucfirst($type) . ' PIN updated', $user_id);
        };
        // Gather new PINs from the form
        $authPin    = isset($_POST['authorization_pin']) ? trim($_POST['authorization_pin']) : '';
        $paymentPin = isset($_POST['payment_pin']) ? trim($_POST['payment_pin']) : '';
        $securePin  = isset($_POST['secure_pass_pin']) ? trim($_POST['secure_pass_pin']) : '';
        $errors = [];
        if ($authPin !== '') {
            if (!preg_match('/^\d{4,6}$/', $authPin)) {
                $errors[] = 'Authorization PIN must be 4-6 digits.';
            } else {
                $updateOrInsertPin('authorization', $authPin);
            }
        }
        if ($paymentPin !== '') {
            if (!preg_match('/^\d{4,6}$/', $paymentPin)) {
                $errors[] = 'Payment PIN must be 4-6 digits.';
            } else {
                $updateOrInsertPin('payment', $paymentPin);
            }
        }
        if ($securePin !== '') {
            if (!preg_match('/^\d{4,6}$/', $securePin)) {
                $errors[] = 'Secure Pass PIN must be 4-6 digits.';
            } else {
                $updateOrInsertPin('secure_pass', $securePin);
            }
        }
        if (!empty($errors)) {
            $_SESSION['pin_update_errors'] = $errors;
            $db->rollBack();
            redirectWithMessage("manage_pins.php?user_id={$user_id}", implode(' ', $errors), 'danger');
        }
        // Determine active status from checkboxes
        $authActive    = isset($_POST['authorization_active']) ? 1 : 0;
        $paymentActive = isset($_POST['payment_active']) ? 1 : 0;
        $secureActive  = isset($_POST['secure_pass_active']) ? 1 : 0;
        // Helper to update active status and log changes
        $updateStatus = function($type, $active) use ($db, $user_id) {
            $stmt = $db->prepare("SELECT pin_id, is_active FROM user_pins WHERE user_id = ? AND pin_type = ?");
            $stmt->execute([$user_id, $type]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && (int)$row['is_active'] !== (int)$active) {
                $stmt = $db->prepare("UPDATE user_pins SET is_active = ? WHERE pin_id = ?");
                $stmt->execute([$active, $row['pin_id']]);
                $adminId = $_SESSION['admin_id'] ?? null;
                $action  = $active ? 'enabled' : 'disabled';
                Security::logAudit('pin_status_change', "{$type} PIN {$action} for user {$user_id}", $user_id, $adminId, null, $type, 'success');
                Security::logAdminAction($adminId, 'pin_status_change', ucfirst($type) . " PIN {$action}", $user_id);
            }
        };
        // Apply active status updates
        $updateStatus('authorization', $authActive);
        $updateStatus('payment', $paymentActive);
        $updateStatus('secure_pass', $secureActive);
        // Commit transaction and redirect
        $db->commit();
        redirectWithMessage('user_pins.php', 'User PINs updated successfully.', 'success');
    }

    // GET request: fetch user and pin details for display
    $stmt = $db->prepare("SELECT username, full_name, email FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!$user) {
        redirectWithMessage('user_pins.php', 'User not found.', 'danger');
    }
    $stmt = $db->prepare("SELECT pin_type, pin_plain, is_active FROM user_pins WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $pins = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $pins[$row['pin_type']] = $row;
    }
    // Generate CSRF token for the form
    $csrf_token = Security::generateCSRFToken();

} catch (PDOException $e) {
    error_log('Manage PINs error: ' . $e->getMessage());
    redirectWithMessage('user_pins.php', 'An error occurred loading user PIN data.', 'danger');
}

// Include the header after processing; this sends HTML output
require_once '../includes/header.php';
?>

<!-- Page Header -->
<section style="background: linear-gradient(135deg, var(--dark) 0%, var(--primary-blue) 100%); color: var(--white); padding: 2rem 0;">
    <div class="container">
        <h1><i class="fas fa-user-lock text-gold"></i> Manage PINs for <?php echo htmlspecialchars($user['full_name']); ?></h1>
        <p>View and update the user's authentication, payment, and secure pass PINs</p>
    </div>
</section>

<!-- Manage PIN Form -->
<section style="padding: 2rem 0;">
    <div class="container">
        <?php
        // Display errors stored in the session
        if (isset($_SESSION['pin_update_errors'])) {
            echo '<div class="danger-box" style="margin-bottom: 1rem;">' . implode('<br>', $_SESSION['pin_update_errors']) . '</div>';
            unset($_SESSION['pin_update_errors']);
        }
        ?>
        <div class="glass-card">
            <h2 style="margin-bottom: 1.5rem; color: var(--accent-blue);">
                <i class="fas fa-key" style="color: var(--accent-gold); margin-right: 0.5rem;"></i> Update User PINs
            </h2>
            <form action="" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <div class="form-group">
                    <label for="authorization_pin">Authorization PIN</label>
                    <input type="password" id="authorization_pin" name="authorization_pin" class="form-control" placeholder="<?php echo isset($pins['authorization']['pin_plain']) ? $pins['authorization']['pin_plain'] : 'Not Set'; ?>" maxlength="6" autocomplete="off">
                    <div class="form-check" style="margin-top: 0.5rem;">
                        <input type="checkbox" id="authorization_active" name="authorization_active" class="form-check-input" <?php echo (isset($pins['authorization']) && (int)$pins['authorization']['is_active'] === 1) ? 'checked' : ''; ?>>
                        <label for="authorization_active" class="form-check-label">Enabled</label>
                    </div>
                    <small class="text-muted">Leave blank to keep the current PIN. Current: <?php echo isset($pins['authorization']['pin_plain']) ? $pins['authorization']['pin_plain'] : 'Not Set'; ?>; Status: <?php echo (isset($pins['authorization']) && (int)$pins['authorization']['is_active'] === 1) ? 'Enabled' : 'Disabled'; ?></small>
                </div>

                <div class="form-group">
                    <label for="payment_pin">Payment PIN</label>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <input type="password" id="payment_pin" name="payment_pin" class="form-control" placeholder="<?php echo isset($pins['payment']['pin_plain']) ? $pins['payment']['pin_plain'] : 'Not Set'; ?>" maxlength="6" autocomplete="off">
                        <button type="button" class="btn btn-secondary" onclick="generateRandom('payment_pin')">Random</button>
                    </div>
                    <div class="form-check" style="margin-top: 0.5rem;">
                        <input type="checkbox" id="payment_active" name="payment_active" class="form-check-input" <?php echo (isset($pins['payment']) && (int)$pins['payment']['is_active'] === 1) ? 'checked' : ''; ?>>
                        <label for="payment_active" class="form-check-label">Enabled</label>
                    </div>
                    <small class="text-muted">Leave blank to keep the current PIN. Current: <?php echo isset($pins['payment']['pin_plain']) ? $pins['payment']['pin_plain'] : 'Not Set'; ?>; Status: <?php echo (isset($pins['payment']) && (int)$pins['payment']['is_active'] === 1) ? 'Enabled' : 'Disabled'; ?></small>
                </div>

                <div class="form-group">
                    <label for="secure_pass_pin">Secure Pass PIN</label>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <input type="password" id="secure_pass_pin" name="secure_pass_pin" class="form-control" placeholder="<?php echo isset($pins['secure_pass']['pin_plain']) ? $pins['secure_pass']['pin_plain'] : 'Not Set'; ?>" maxlength="6" autocomplete="off">
                        <button type="button" class="btn btn-secondary" onclick="generateRandom('secure_pass_pin')">Random</button>
                    </div>
                    <div class="form-check" style="margin-top: 0.5rem;">
                        <input type="checkbox" id="secure_pass_active" name="secure_pass_active" class="form-check-input" <?php echo (isset($pins['secure_pass']) && (int)$pins['secure_pass']['is_active'] === 1) ? 'checked' : ''; ?>>
                        <label for="secure_pass_active" class="form-check-label">Enabled</label>
                    </div>
                    <small class="text-muted">Leave blank to keep the current PIN. Current: <?php echo isset($pins['secure_pass']['pin_plain']) ? $pins['secure_pass']['pin_plain'] : 'Not Set'; ?>; Status: <?php echo (isset($pins['secure_pass']) && (int)$pins['secure_pass']['is_active'] === 1) ? 'Enabled' : 'Disabled'; ?></small>
                </div>

                <div class="form-group" style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="user_pins.php" class="btn btn-outline" style="margin-left: 1rem;">Back to Users</a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
// Generate a random 6-digit PIN and fill the specified input
function generateRandom(id) {
    const random = Math.floor(100000 + Math.random() * 900000).toString();
    document.getElementById(id).value = random;
}
</script>

<?php require_once '../includes/footer.php'; ?>