<?php
// Load configuration first to start the session and make Security functions
// available before any output.  This avoids "headers already sent" issues
// when redirects are triggered by session or auth logic.
require_once '../includes/config.php';

// Then include the header for navigation and HTML skeleton.  The header
// performs routing guards based on the session, which are now safe because
// the session has already been started.
require_once '../includes/header.php';

// Generate a CSRF token for this session; used to prevent cross-site request
// forgery.  The token should be generated after the session is started.
$csrf_token = Security::generateCSRFToken();
?>

<!-- Login Section -->
<section class="hero" style="min-height: 100vh;">
    <div class="container">
        <div class="form-container">
            <!-- Login Card -->
            <div class="glass-card glass-card-gradient">
                <div class="text-center mb-4">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">
                        <i class="fas fa-lock" style="background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                    </div>
                    <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem;">
                        Welcome Back
                    </h1>
                    <p style="color: var(--text-secondary);">Sign in to access your account</p>
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
                
                <form action="../includes/login_process.php" method="POST" id="loginForm">
                    <!-- CSRF Token for security -->
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
                            placeholder="Enter your username or email"
                            required
                            autocomplete="username"
                        >
                    </div>
                    
                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-key" style="margin-right: 0.5rem;"></i> Password
                        </label>
                        <div style="position: relative;">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                placeholder="Enter your password"
                                required
                                autocomplete="current-password"
                            >
                            <button 
                                type="button" 
                                class="password-toggle"
                                onclick="togglePassword('password')"
                                style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 1.2rem;"
                            >
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Remember Me -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-secondary);">
                            <input type="checkbox" name="remember" style="width: 18px; height: 18px; cursor: pointer;">
                            Remember me
                        </label>
                        <a href="forgot_password.php" style="color: var(--accent-purple); text-decoration: none; font-weight: 500;">
                            Forgot password?
                        </a>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
                        <i class="fas fa-sign-in-alt" style="margin-right: 0.5rem;"></i> Sign In
                    </button>
                </form>
                
                <!-- Divider -->
                <div style="display: flex; align-items: center; gap: 1rem; margin: 2rem 0;">
                    <div style="flex: 1; height: 1px; background: var(--border-glass);"></div>
                    <span style="color: var(--text-muted);">or</span>
                    <div style="flex: 1; height: 1px; background: var(--border-glass);"></div>
                </div>
                
                <!-- Register Link -->
                <div class="text-center">
                    <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                        Don't have an account?
                    </p>
                    <a href="register.php" class="btn btn-gradient" style="width: 100%; padding: 1rem;">
                        <i class="fas fa-user-plus" style="margin-right: 0.5rem;"></i> Create Account
                    </a>
                </div>
            </div>
            
            <!-- Security Notice -->
            <div class="glass-card mt-3" style="background: rgba(79, 172, 254, 0.1);">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <i class="fas fa-shield-alt" style="font-size: 2rem; color: var(--accent-blue);"></i>
                    <div>
                        <h4 style="margin-bottom: 0.25rem;">Secure Login</h4>
                        <p style="font-size: 0.9rem; color: var(--text-secondary);">
                            Your security is our priority. We use advanced encryption to protect your data.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Password Toggle Function
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const toggleBtn = input.parentElement.querySelector('.password-toggle');
    const icon = toggleBtn.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Form Validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const usernameEmail = document.getElementById('username_email').value;
    const password = document.getElementById('password').value;
    
    // Validate fields are filled
    if (!usernameEmail || !password) {
        e.preventDefault();
        showToast('Please fill in all fields', 'error');
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
    submitBtn.disabled = true;
});
</script>

<?php require_once '../includes/footer.php'; ?>