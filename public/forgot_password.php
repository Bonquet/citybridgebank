<?php
// Load configuration first to start the session and provide access to
// Security functions.  Doing this before output ensures that any
// redirects executed by the header or later code will not trigger
// "headers already sent" warnings.
require_once '../includes/config.php';

// Include the header after config.  The header contains the navigation and
// routing guards which depend on the session being initialized.
require_once '../includes/header.php';

// Generate a CSRF token for this session; used to prevent CSRF attacks.
$csrf_token = Security::generateCSRFToken();
?>

<!-- Forgot Password Section -->
<section class="hero" style="min-height: 100vh;">
    <div class="container">
        <div class="form-container">
            <!-- Forgot Password Card -->
            <div class="glass-card glass-card-gradient">
                <div class="text-center" style="margin-bottom: 2rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">
                        <i class="fas fa-key" style="background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                    </div>
                    <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem;">
                        Forgot Password?
                    </h1>
                    <p style="color: var(--text-secondary);">No worries, we'll send you reset instructions.</p>
                </div>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php 
                        echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php 
                        echo htmlspecialchars($_SESSION['success']);
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <form action="../includes/forgot_password_process.php" method="POST" id="forgotPasswordForm">
                    <!-- CSRF Token to protect against cross-site request forgery -->
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <!-- Username or Email -->
                    <div class="form-group">
                        <label for="username_email" class="form-label">
                            <i class="fas fa-user" style="margin-right: 0.5rem;"></i> Username or Email
                        </label>
                        <input 
                            type="text" 
                            id="username_email" 
                            name="username_email" 
                            class="form-input" 
                            placeholder="Enter your username or registered email"
                            required
                            autocomplete="username"
                        >
                        <p class="form-helper">
                            We'll send password reset instructions to your email address.
                        </p>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
                        <i class="fas fa-paper-plane" style="margin-right: 0.5rem;"></i> Send Reset Link
                    </button>
                </form>
                
                <!-- Divider -->
                <div style="display: flex; align-items: center; gap: 1rem; margin: 2rem 0;">
                    <div style="flex: 1; height: 1px; background: var(--border-glass);"></div>
                    <span style="color: var(--text-muted);">or</span>
                    <div style="flex: 1; height: 1px; background: var(--border-glass);"></div>
                </div>
                
                <!-- Back to Login -->
                <div class="text-center">
                    <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                        Remember your password?
                    </p>
                    <a href="login.php" class="btn btn-secondary" style="width: 100%; padding: 1rem;">
                        <i class="fas fa-sign-in-alt" style="margin-right: 0.5rem;"></i> Back to Login
                    </a>
                </div>
            </div>
            
            <!-- Security Notice -->
            <div class="glass-card mt-3" style="background: rgba(79, 172, 254, 0.1);">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <i class="fas fa-shield-alt" style="font-size: 2rem; color: var(--accent-blue);"></i>
                    <div>
                        <h4 style="margin-bottom: 0.25rem;">Secure Reset</h4>
                        <p style="font-size: 0.9rem; color: var(--text-secondary);">
                            The reset link will expire in 1 hour for your security.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Form Validation for forgot password
document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
    const input = document.getElementById('username_email').value;
    
    // Require a username or email to be provided
    if (!input) {
        e.preventDefault();
        showToast('Please enter your username or email address', 'error');
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    submitBtn.disabled = true;
});
</script>

<?php require_once '../includes/footer.php'; ?>