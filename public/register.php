<?php
// Load configuration first to start the session and provide Security
// functions before any HTML output.  Loading config before the header
// prevents duplicate session_start() calls and avoids header output before
// redirects or session operations.
require_once '../includes/config.php';

// Include the header for navigation and page structure after the session
// has been initialized.  The header will perform any necessary routing
// based on the session state.
require_once '../includes/header.php';

// Generate a CSRF token for this session; used to prevent CSRF attacks.
$csrf_token = Security::generateCSRFToken();
?>

<!-- Register Section -->
<section class="hero" style="min-height: 100vh;">
    <div class="container">
        <div class="form-container" style="max-width: 600px;">
            <!-- Register Card -->
            <div class="glass-card glass-card-gradient">
                <div class="text-center mb-4">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">
                        <i class="fas fa-user-plus" style="background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                    </div>
                    <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem;">
                        Create Account
                    </h1>
                    <p style="color: var(--text-secondary);">Join CITYBRIDGEBANK today</p>
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

                <!-- Display detailed registration errors if available -->
                <?php if (isset($_SESSION['registration_errors']) && is_array($_SESSION['registration_errors'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <ul style="margin: 0.5rem 0 0 1rem; padding: 0; list-style-type: disc;">
                            <?php foreach ($_SESSION['registration_errors'] as $reg_error): ?>
                                <li><?php echo htmlspecialchars($reg_error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php unset($_SESSION['registration_errors']); ?>
                <?php endif; ?>
                
                <form action="../includes/register_process.php" method="POST" id="registerForm">
                    <!-- CSRF Token to protect against cross-site request forgery -->
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <!-- Personal Information -->
                    <div style="margin-bottom: 2rem;">
                        <h3 style="margin-bottom: 1rem; color: var(--accent-purple); font-size: 1.2rem;">
                            <i class="fas fa-user" style="margin-right: 0.5rem;"></i> Personal Information
                        </h3>
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                            <!-- First Name -->
                            <div class="form-group">
                                <label for="first_name" class="form-label">First Name</label>
                                <input 
                                    type="text" 
                                    id="first_name" 
                                    name="first_name" 
                                    class="form-input" 
                                    placeholder="John"
                                    required
                                    autocomplete="given-name"
                                >
                            </div>
                            
                            <!-- Last Name -->
                            <div class="form-group">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input 
                                    type="text" 
                                    id="last_name" 
                                    name="last_name" 
                                    class="form-input" 
                                    placeholder="Doe"
                                    required
                                    autocomplete="family-name"
                                >
                            </div>
                        </div>
                        
                        <!-- Email -->
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope" style="margin-right: 0.5rem;"></i> Email Address
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-input" 
                                placeholder="john.doe@example.com"
                                required
                                autocomplete="email"
                            >
                        </div>
                        
                        <!-- Phone -->
                        <div class="form-group">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone" style="margin-right: 0.5rem;"></i> Phone Number
                            </label>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                class="form-input" 
                                placeholder="(123) 456-7890"
                                required
                                autocomplete="tel"
                            >
                        </div>
                        
                        <!-- Date of Birth -->
                        <div class="form-group">
                            <label for="dob" class="form-label">
                                <i class="fas fa-calendar" style="margin-right: 0.5rem;"></i> Date of Birth
                            </label>
                            <input 
                                type="date" 
                                id="dob" 
                                name="dob" 
                                class="form-input" 
                                required
                                autocomplete="bday"
                            >
                            <p class="form-helper">You must be at least 18 years old to register</p>
                        </div>
                    </div>

                    <!-- Contact & Account Details -->
                    <div style="margin-bottom: 2rem;">
                        <h3 style="margin-bottom: 1rem; color: var(--accent-purple); font-size: 1.2rem;">
                            <i class="fas fa-address-card" style="margin-right: 0.5rem;"></i> Contact & Account Details
                        </h3>
                        <!-- Street Address -->
                        <div class="form-group">
                            <label for="address" class="form-label">
                                <i class="fas fa-home" style="margin-right: 0.5rem;"></i> Street Address
                            </label>
                            <input
                                type="text"
                                id="address"
                                name="address"
                                class="form-input"
                                placeholder="123 Main St"
                                required
                                autocomplete="street-address"
                            >
                        </div>
                        <!-- City -->
                        <div class="form-group">
                            <label for="city" class="form-label">
                                <i class="fas fa-city" style="margin-right: 0.5rem;"></i> City
                            </label>
                            <input
                                type="text"
                                id="city"
                                name="city"
                                class="form-input"
                                placeholder="City"
                                required
                                autocomplete="address-level2"
                            >
                        </div>
                        <!-- State -->
                        <div class="form-group">
                            <label for="state" class="form-label">
                                <i class="fas fa-flag-usa" style="margin-right: 0.5rem;"></i> State
                            </label>
                            <input
                                type="text"
                                id="state"
                                name="state"
                                class="form-input"
                                placeholder="CA"
                                maxlength="2"
                                required
                                autocomplete="address-level1"
                            >
                        </div>
                        <!-- ZIP Code -->
                        <div class="form-group">
                            <label for="zip" class="form-label">
                                <i class="fas fa-mail-bulk" style="margin-right: 0.5rem;"></i> ZIP Code
                            </label>
                            <input
                                type="text"
                                id="zip"
                                name="zip"
                                class="form-input"
                                placeholder="12345"
                                required
                                autocomplete="postal-code"
                            >
                        </div>
                        <!-- Last 4 of SSN -->
                        <div class="form-group">
                            <label for="ssn" class="form-label">
                                <i class="fas fa-id-card" style="margin-right: 0.5rem;"></i> SSN (Last 4 digits)
                            </label>
                            <input
                                type="text"
                                id="ssn"
                                name="ssn"
                                class="form-input"
                                placeholder="1234"
                                required
                                maxlength="4"
                            >
                        </div>
                        <!-- Username -->
                        <div class="form-group">
                            <label for="username" class="form-label">
                                <i class="fas fa-user-circle" style="margin-right: 0.5rem;"></i> Username
                            </label>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-input"
                                placeholder="Choose a username"
                                required
                                autocomplete="username"
                            >
                        </div>
                        <!-- Account Type -->
                        <div class="form-group">
                            <label for="account_type" class="form-label">
                                <i class="fas fa-briefcase" style="margin-right: 0.5rem;"></i> Account Type
                            </label>
                            <select id="account_type" name="account_type" class="form-select" required>
                                <option value="">Select account type</option>
                                <option value="personal">Personal</option>
                                <option value="premium">Premium</option>
                                <option value="student">Student</option>
                            </select>
                        </div>
                        <!-- Newsletter Subscription (optional) -->
                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-secondary);">
                                <input type="checkbox" name="newsletter" style="width: 18px; height: 18px; cursor: pointer;">
                                Subscribe to our newsletter for updates and offers
                            </label>
                        </div>
                    </div>

                    <!-- Account Security -->
                    <div style="margin-bottom: 2rem;">
                        <h3 style="margin-bottom: 1rem; color: var(--accent-purple); font-size: 1.2rem;">
                            <i class="fas fa-shield-alt" style="margin-right: 0.5rem;"></i> Account Security
                        </h3>
                        
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
                                    placeholder="Create a strong password"
                                    required
                                    autocomplete="new-password"
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
                            
                            <!-- Password Strength Indicator -->
                            <div id="password-strength" style="margin-top: 0.5rem;">
                                <div style="height: 4px; background: rgba(102, 126, 234, 0.2); border-radius: 2px; overflow: hidden;">
                                    <div id="password-strength-bar" style="height: 100%; width: 0%; transition: all 0.3s ease;"></div>
                                </div>
                                <p id="password-strength-text" style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">
                                    Password strength
                                </p>
                            </div>
                        </div>
                        
                        <!-- Confirm Password -->
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-check-double" style="margin-right: 0.5rem;"></i> Confirm Password
                            </label>
                            <div style="position: relative;">
                                <input 
                                    type="password" 
                                    id="confirm_password" 
                                    name="confirm_password" 
                                    class="form-input" 
                                    placeholder="Confirm your password"
                                    required
                                    autocomplete="new-password"
                                >
                                <button 
                                    type="button" 
                                    class="password-toggle"
                                    onclick="togglePassword('confirm_password')"
                                    style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 1.2rem;"
                                >
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Terms & Conditions -->
                    <div class="form-group">
                        <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; color: var(--text-secondary); line-height: 1.6;">
                            <input type="checkbox" name="terms" required style="width: 18px; height: 18px; margin-top: 0.25rem; cursor: pointer;">
                            <span>I agree to the <a href="terms.php" style="color: var(--accent-purple);">Terms of Service</a> and <a href="privacy.php" style="color: var(--accent-purple);">Privacy Policy</a></span>
                        </label>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
                        <i class="fas fa-user-plus" style="margin-right: 0.5rem;"></i> Create Account
                    </button>
                </form>
                
                <!-- Divider -->
                <div style="display: flex; align-items: center; gap: 1rem; margin: 2rem 0;">
                    <div style="flex: 1; height: 1px; background: var(--border-glass);"></div>
                    <span style="color: var(--text-muted);">or</span>
                    <div style="flex: 1; height: 1px; background: var(--border-glass);"></div>
                </div>
                
                <!-- Login Link -->
                <div class="text-center">
                    <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                        Already have an account?
                    </p>
                    <a href="login.php" class="btn btn-gradient" style="width: 100%; padding: 1rem;">
                        <i class="fas fa-sign-in-alt" style="margin-right: 0.5rem;"></i> Sign In
                    </a>
                </div>
            </div>
            
            <!-- Security Notice -->
            <div class="glass-card mt-3" style="background: rgba(79, 172, 254, 0.1);">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <i class="fas fa-shield-alt" style="font-size: 2rem; color: var(--accent-blue);"></i>
                    <div>
                        <h4 style="margin-bottom: 0.25rem;">Your Security Matters</h4>
                        <p style="font-size: 0.9rem; color: var(--text-secondary);">
                            We use bank-level encryption to protect your personal information and transactions.
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

// Password Strength Checker
const passwordInput = document.getElementById('password');
const passwordStrengthBar = document.getElementById('password-strength-bar');
const passwordStrengthText = document.getElementById('password-strength-text');

passwordInput.addEventListener('input', function() {
    const password = this.value;
    const strength = checkPasswordStrength(password);
    
    let color = '';
    let width = '0%';
    let text = 'Password strength';
    
    switch(strength) {
        case 0:
            color = 'rgba(102, 126, 234, 0.2)';
            width = '0%';
            text = 'Password strength';
            break;
        case 1:
            color = '#f5576c';
            width = '20%';
            text = 'Weak';
            break;
        case 2:
            color = '#fa709a';
            width = '40%';
            text = 'Fair';
            break;
        case 3:
            color = '#fee140';
            width = '60%';
            text = 'Good';
            break;
        case 4:
            color = '#4facfe';
            width = '80%';
            text = 'Strong';
            break;
        case 5:
            color = '#00f2fe';
            width = '100%';
            text = 'Very Strong';
            break;
    }
    
    passwordStrengthBar.style.width = width;
    passwordStrengthBar.style.background = color;
    passwordStrengthText.textContent = text;
});

function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z\d]/.test(password)) strength++;
    
    return strength;
}

// Phone Number Auto-formatting
const phoneInput = document.getElementById('phone');
phoneInput.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    
    if (value.length >= 6) {
        value = `(${value.slice(0, 3)}) ${value.slice(3, 6)}-${value.slice(6, 10)}`;
    } else if (value.length >= 3) {
        value = `(${value.slice(0, 3)}) ${value.slice(3)}`;
    }
    
    e.target.value = value;
});

// Age Validation
// Reference the date of birth field by its updated ID
const dobInput = document.getElementById('dob');
dobInput.addEventListener('change', function() {
    const dob = new Date(this.value);
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const monthDiff = today.getMonth() - dob.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    
    if (age < 18) {
        showToast('You must be at least 18 years old to register', 'error');
        this.value = '';
    }
});

// Form Validation
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const terms = document.querySelector('input[name="terms"]').checked;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        showToast('Passwords do not match', 'error');
        return false;
    }
    
    if (password.length < 8) {
        e.preventDefault();
        showToast('Password must be at least 8 characters long', 'error');
        return false;
    }
    
    if (!terms) {
        e.preventDefault();
        showToast('You must agree to the terms and conditions', 'error');
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';
    submitBtn.disabled = true;
});
</script>

<?php require_once '../includes/footer.php'; ?>