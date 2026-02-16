<?php
// Title for the security settings page
$pageTitle = 'Security Settings';
// Load configuration first (Database and Security)
require_once '../includes/config.php';

// Check if user is logged in before any output
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login to access security settings.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get user's PIN status
    $stmt = $db->prepare("SELECT pin_type FROM user_pins WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $pins = $stmt->fetchAll();
    
    $pin_status = [
        'authorization' => false,
        'payment' => false,
        'secure_pass' => false
    ];
    
    foreach ($pins as $pin) {
        $pin_status[$pin['pin_type']] = true;
    }
    
    // Get recent security logs.  The audit_logs table stores the timestamp in
    // action_timestamp and the details in action_details.  Alias these to
    // log_time and action_description to match the template fields used
    // below.  Only fetch relevant actions for the current user.
    $stmt = $db->prepare("SELECT action_timestamp AS log_time, action_details AS action_description, status, ip_address FROM audit_logs WHERE user_id = ? AND action_type IN ('login_attempt', 'pin_validation', 'password_change', 'pin_change') ORDER BY action_timestamp DESC LIMIT 10");
    $stmt->execute([$_SESSION['user_id']]);
    $security_logs = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Security settings error: ' . $e->getMessage());
    redirectWithMessage('index.php', 'An error occurred loading security settings.', 'danger');
}

// Generate CSRF token used by forms on this page
$csrf_token = Security::generateCSRFToken();

// After authentication and data retrieval, render the header
require_once '../includes/header.php';
?>

<!-- Page Header -->
<section class="hero">
    <div class="container">
        <h1>Security Settings</h1>
        <p>Manage your account security and three-layer PIN protection</p>
    </div>
</section>

<!-- Security Content -->
<section style="padding: 2rem 0;">
    <div class="container">
        <!-- PIN Status -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h2><i class="fas fa-shield-alt text-gold"></i> Authentication PIN Status</h2>
            </div>
            <div class="card-body">
                <p style="margin-bottom: 1.5rem;">Your authentication PIN provides maximum security for all transactions. Keep your PIN secure and never share it with anyone.</p>
                
                <div class="card <?php echo ($pin_status['authorization'] && $pin_status['payment'] && $pin_status['secure_pass']) ? 'border-success' : 'border-warning'; ?>" style="border-width: 3px; max-width: 600px; margin: 0 auto;">
                    <div class="card-body text-center">
                        <h3 style="color: var(--primary-blue);"><i class="fas fa-key text-gold"></i> Authentication PIN</h3>
                        <p style="margin-top: 1rem;">Your security PIN for authorizing all transactions</p>
                        <p style="margin-top: 0.5rem;">
                            Status: 
                            <span class="badge <?php echo ($pin_status['authorization'] && $pin_status['payment'] && $pin_status['secure_pass']) ? 'badge-success' : 'badge-warning'; ?>">
                                <?php echo ($pin_status['authorization'] && $pin_status['payment'] && $pin_status['secure_pass']) ? 'Active' : 'Not Set'; ?>
                            </span>
                        </p>
                        <?php if (!($pin_status['authorization'] && $pin_status['payment'] && $pin_status['secure_pass'])): ?>
                            <a href="setup_pins.php" class="btn btn-primary" style="margin-top: 1rem; display: inline-block;"><i class="fas fa-plus"></i> Set Up PIN</a>
                        <?php else: ?>
                            <a href="setup_pins.php" class="btn btn-outline" style="margin-top: 1rem; display: inline-block;"><i class="fas fa-edit"></i> Change PIN</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!($pin_status['authorization'] && $pin_status['payment'] && $pin_status['secure_pass'])): ?>
                    <div class="warning-box" style="margin-top: 1.5rem;">
                        <h4 style="color: var(--primary-blue);"><i class="fas fa-exclamation-triangle"></i> PIN Setup Required</h4>
                        <p style="margin: 0;">Please complete your authentication PIN setup to enable full transaction capabilities. <a href="setup_pins.php" style="color: var(--gold);">Complete Setup</a></p>
                    </div>
                <?php else: ?>
                    <div class="success-box" style="margin-top: 1.5rem;">
                        <h4 style="color: var(--primary-blue);"><i class="fas fa-check-circle"></i> Security PIN Active</h4>
                        <p style="margin: 0;">Your authentication PIN is fully configured. All transactions will require your PIN for maximum security.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Password Settings -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h2><i class="fas fa-key text-gold"></i> Password Settings</h2>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div>
                        <h3 style="color: var(--primary-blue); margin-bottom: 1rem;">Password Requirements</h3>
                        <ul style="line-height: 1.8;">
                            <li><i class="fas fa-check text-success"></i> Minimum 8 characters</li>
                            <li><i class="fas fa-check text-success"></i> At least one uppercase letter</li>
                            <li><i class="fas fa-check text-success"></i> At least one lowercase letter</li>
                            <li><i class="fas fa-check text-success"></i> At least one number</li>
                            <li><i class="fas fa-check text-success"></i> At least one special character</li>
                        </ul>
                    </div>
                    <div>
                        <h3 style="color: var(--primary-blue); margin-bottom: 1rem;">Change Password</h3>
                        <form action="change_password_process.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="form-group">
                                <label for="current_password">Current Password *</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">New Password *</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" required minlength="8">
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password *</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Security Logs -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-history text-gold"></i> Recent Security Activity</h2>
            </div>
            <div class="card-body">
                <?php if (empty($security_logs)): ?>
                    <div class="info-box text-center">
                        <p>No recent security activity.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Activity</th>
                                    <th>Status</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($security_logs as $log): ?>
                                    <tr>
                                        <td><?php echo formatDateTime($log['log_time']); ?></td>
                                        <td><?php echo htmlspecialchars($log['action_description']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $log['status'] == 'success' ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo ucfirst($log['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Back to Dashboard -->
        <div style="text-align: center; margin-top: 2rem;">
            <a href="index.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>