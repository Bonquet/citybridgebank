<?php
// Page title for admin login
$pageTitle = 'Admin Login';
// Load configuration first to ensure Security functions are available
require_once '../includes/config.php';
// Then include the header (navigation, CSS)
require_once '../includes/header.php';
// Generate CSRF token used by the admin login form
$csrf_token = Security::generateCSRFToken();

// Redirect if already logged in as admin
if (Security::isAdminLoggedIn()) {
    header('Location: index.php');
    exit();
}

// Do not enforce the general login attempt lockout for the admin login page.
// The login attempt counter is shared with user registrations and may cause
// an unintended lockout. Any lockout logic should be implemented
// separately for administrators if needed.
?>

<!-- Hero Section -->
<section class="hero" style="background: linear-gradient(135deg, var(--dark) 0%, var(--primary-blue) 100%);">
    <div class="container">
        <h1><i class="fas fa-user-shield text-gold"></i> Admin Access</h1>
        <p>Secure administrative access for CITIBRIDGE management</p>
    </div>
</section>

<!-- Admin Login Form -->
<section style="padding: 4rem 0;">
    <div class="container">
        <div style="max-width: 500px; margin: 0 auto;">
            <div class="card" style="border: 3px solid var(--gold);">
                <div class="card-header">
                    <h2><i class="fas fa-lock text-gold"></i> Administrator Login</h2>
                </div>
                <div class="card-body">
                    <!-- Always show the admin login form. Lockout messages are not used for admin pages -->
                    <form action="login_process.php" method="POST" id="adminLoginForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="form-group">
                                <label for="admin_username">Admin Username</label>
                                <input type="text" id="admin_username" name="admin_username" class="form-control" required placeholder="Enter admin username" autofocus>
                            </div>
                            
                            <div class="form-group">
                                <label for="admin_password">Admin Password</label>
                                <div style="position: relative;">
                                    <input type="password" id="admin_password" name="admin_password" class="form-control" required placeholder="Enter admin password">
                                    <button type="button" id="toggleAdminPassword" class="btn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 0; color: var(--gray);">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="danger-box">
                                <p style="margin: 0;"><i class="fas fa-exclamation-triangle text-gold"></i> <strong>Warning:</strong> Unauthorized access to this system is a criminal offense. All actions are logged and monitored.</p>
                            </div>
                            
                            <div class="form-group" style="margin-top: 1.5rem;">
                                <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-sign-in-alt"></i> Admin Login</button>
                            </div>
                            
                            <div class="text-center" style="margin-top: 1rem;">
                                <!-- Fixed incorrect return link to website -->
                                <p><a href="../public/index.php" style="color: var(--gold);"><i class="fas fa-arrow-left"></i> Return to Website</a></p>
                            </div>
                        </form>
                </div>
            </div>
            
            <!-- Admin Access Requirements -->
            <div class="card" style="margin-top: 2rem;">
                <div class="card-body">
                    <h4 style="color: var(--primary-blue); margin-bottom: 1rem;"><i class="fas fa-info-circle"></i> Admin Access Requirements</h4>
                    <ul style="line-height: 1.8; margin: 0;">
                        <li><i class="fas fa-check text-success"></i> Valid administrator credentials</li>
                        <li><i class="fas fa-check text-success"></i> Authorized IP address (if configured)</li>
                        <li><i class="fas fa-check text-success"></i> Role-based access permissions</li>
                        <li><i class="fas fa-check text-success"></i> All actions logged for audit</li>
                        <li><i class="fas fa-check text-success"></i> Session timeout after inactivity</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('toggleAdminPassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('admin_password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>