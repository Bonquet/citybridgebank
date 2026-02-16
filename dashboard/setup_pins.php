<?php
// Title for PIN setup page
$pageTitle = 'PIN Setup';
// Load configuration first for DB and security classes
require_once '../includes/config.php';

// Check if user is logged in before any output
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login to setup your PIN.', 'danger');
}

// Generate CSRF token used in the form below
$csrf_token = Security::generateCSRFToken();

try {
    $db = Database::getInstance()->getConnection();
    
    // Get user's PIN status
    $stmt = $db->prepare("SELECT * FROM user_pins WHERE user_id = ?");
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
    
    // Check if all PINs are already set
    $is_setup_complete = $pin_status['authorization'] && $pin_status['payment'] && $pin_status['secure_pass'];
    
    if ($is_setup_complete) {
        redirectWithMessage('index.php', 'Your authentication PIN is already set up. You can change it in Security Settings.', 'success');
    }
    
} catch (PDOException $e) {
    error_log("PIN setup error: " . $e->getMessage());
    redirectWithMessage('index.php', 'An error occurred.', 'danger');
}

// All checks passed; render the header after ensuring no redirects have been triggered
require_once '../includes/header.php';
?>

<!-- Page Header -->
<section class="hero">
    <div class="container">
        <h1>Authentication PIN Setup</h1>
        <p>Complete your PIN setup to enable secure transactions</p>
    </div>
</section>

<!-- PIN Setup Content -->
<section style="padding: 2rem 0;">
    <div class="container">
        <!-- Setup Progress -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <h3 style="color: #2c5f8d; margin-bottom: 1rem;"><i class="fas fa-tasks text-gold"></i> Setup Progress</h3>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: #e8f4f8; color: #6c757d; display: flex; align-items: center; justify-content: center; margin: 0 auto; font-weight: 700; font-size: 2rem; border: 4px solid <?php echo $is_setup_complete ? '#28a745' : '#e8f4f8'; ?>;">
                        <?php echo $is_setup_complete ? '<i class="fas fa-check"></i>' : '1'; ?>
                    </div>
                    <p style="margin-top: 1rem; font-weight: 600; font-size: 1.25rem;">Authentication PIN</p>
                    <p style="font-size: 0.875rem; color: <?php echo $is_setup_complete ? '#28a745' : '#6c757d'; ?>">
                        <?php echo $is_setup_complete ? 'Complete' : 'Pending'; ?>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- PIN Setup Form -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-lock text-gold"></i> Setup Your Authentication PIN</h2>
            </div>
            <div class="card-body">
                <div class="warning-box" style="margin-bottom: 2rem;">
                    <h4 style="color: #2c5f8d;"><i class="fas fa-exclamation-triangle text-gold"></i> Important Security Notice</h4>
                    <p style="margin: 0;">For your security, your PIN must be different from your password. Write down your PIN in a secure location and never share it with anyone.</p>
                </div>
                
                <form action="setup_pins_process.php" method="POST" id="pinSetupForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="card" style="border: 2px solid #e8f4f8; margin-bottom: 0;">
                        <div class="card-body" style="max-width: 500px; margin: 0 auto;">
                            <h3 style="color: #2c5f8d; text-align: center;"><i class="fas fa-key text-gold"></i> Authentication PIN</h3>
                    <p style="margin-top: 0.5rem; text-align: center;">Your Authorization PIN for confirming all transactions. Additional Payment and Secure Pass PINs will be generated for you automatically and shown after setup.</p>
                            
                            <div style="margin-top: 1.5rem;">
                                <div class="form-group">
                                    <label for="auth_pin">Authentication PIN *</label>
                                    <input type="password" id="auth_pin" name="auth_pin" class="form-control" required pattern="\d{4,6}" placeholder="Enter 4-6 digit PIN" data-type="pin">
                                </div>
                                
                                <div class="form-group">
                                    <label for="auth_pin_confirm">Confirm Authentication PIN *</label>
                                    <input type="password" id="auth_pin_confirm" name="auth_pin_confirm" class="form-control" required pattern="\d{4,6}" placeholder="Re-enter PIN" data-type="pin">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-save"></i> Set Up PIN</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Security Tips -->
        <div class="card" style="margin-top: 2rem;">
            <div class="card-body">
                <h3 style="color: #2c5f8d; margin-bottom: 1rem;"><i class="fas fa-lightbulb text-gold"></i> PIN Security Tips</h3>
                <ul style="line-height: 1.8;">
                    <li><i class="fas fa-check text-success"></i> Choose a PIN that is easy for you to remember but difficult for others to guess</li>
                    <li><i class="fas fa-check text-success"></i> Avoid using obvious numbers like birth dates, phone numbers, or sequential numbers</li>
                    <li><i class="fas fa-check text-success"></i> Never write your PIN in a place where others can find them</li>
                    <li><i class="fas fa-check text-success"></i> Use a PIN that is different from your password</li>
                    <li><i class="fas fa-check text-success"></i> If you suspect your PIN has been compromised, change it immediately</li>
                </ul>
            </div>
        </div>
        
        <!-- Back to Dashboard -->
        <div style="text-align: center; margin-top: 2rem;">
            <a href="index.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
</section>

<script>
// PIN validation
document.getElementById('pinSetupForm').addEventListener('submit', function(e) {
    const authPin = document.getElementById('auth_pin');
    const authPinConfirm = document.getElementById('auth_pin_confirm');
    
    // Validate PIN length
    if (authPin.value.length < 4 || authPin.value.length > 6) {
        e.preventDefault();
        alert('PIN must be 4-6 digits.');
        return;
    }
    
    // Validate PINs match
    if (authPin.value !== authPinConfirm.value) {
        e.preventDefault();
        alert('PINs do not match. Please re-enter your authentication PIN.');
        return;
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>