<?php
// CITYBRIDGEBANK Login Processing
require_once 'config.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect to public index if request method is invalid (fix broken relative path)
    redirectWithMessage('../public/index.php', 'Invalid request method.', 'danger');
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
    Security::recordFailedLogin();
    redirectWithMessage('../public/login.php', 'Security verification failed. Please try again.', 'danger');
}

// Do not lock out users entirely for failed login attempts. The login attempt counter is
// tracked only to inform the system or administrators, but should not prevent a user
// from accessing their account.  Removing the check prevents a situation where a
// registration or other action increments the counter and causes a login lockout.

// Sanitize input
$username_email = Security::sanitizeInput($_POST['username_email']);
$password = $_POST['password'];
$remember = isset($_POST['remember']) ? true : false;

// Validation
if (empty($username_email) || empty($password)) {
    Security::recordFailedLogin();
    redirectWithMessage('../public/login.php', 'Please enter username and password.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if input is email or username
    $isEmail = filter_var($username_email, FILTER_VALIDATE_EMAIL);
    
    // Find user
    if ($isEmail) {
        $stmt = $db->prepare("SELECT user_id, username, email, password_hash, full_name, account_number, account_status, kyc_status, force_password_reset, reset_token, reset_token_expiry FROM users WHERE email = ?");
    } else {
        $stmt = $db->prepare("SELECT user_id, username, email, password_hash, full_name, account_number, account_status, kyc_status, force_password_reset, reset_token, reset_token_expiry FROM users WHERE username = ?");
    }
    
    $stmt->execute([$username_email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        Security::recordFailedLogin();
        Security::logAudit('login_attempt', "Failed login attempt for: {$username_email}", null, null, null, null, 'failure');
        redirectWithMessage('../public/login.php', 'Invalid username or password.', 'danger');
    }
    
    // Verify password
    if (!Security::verifyPassword($password, $user['password_hash'])) {
        Security::recordFailedLogin();
        Security::logAudit('login_attempt', "Failed login attempt for: {$username_email}", $user['user_id'], null, null, null, 'failure');
        redirectWithMessage('../public/login.php', 'Invalid username or password.', 'danger');
    }
    
    // Check account status
    if ($user['account_status'] === 'closed') {
        redirectWithMessage('../public/login.php', 'Your account has been closed. Please contact customer support.', 'danger');
    }
    
    if ($user['account_status'] === 'frozen') {
        redirectWithMessage('../public/login.php', 'Your account has been frozen. Please contact customer support.', 'danger');
    }
    

    if (!empty($user['force_password_reset'])) {
        $resetToken = $user['reset_token'] ?? '';
        $resetExpiry = isset($user['reset_token_expiry']) ? strtotime((string)$user['reset_token_expiry']) : 0;
        if ($resetToken !== '' && $resetExpiry > time()) {
            Security::logAudit('forced_password_reset_redirect', 'User redirected to forced password reset', $user['user_id']);
            redirectWithMessage('../public/reset_password.php?token=' . urlencode($resetToken), 'Password reset is required before you can continue.', 'info');
        }
    }

    // Check Transfer PIN setup status (user-owned PIN).
    $stmt = $db->prepare("SELECT 1 FROM user_pins WHERE user_id = ? AND transfer_pin_hash IS NOT NULL LIMIT 1");
    $stmt->execute([$user['user_id']]);
    $hasTransferPin = (bool)$stmt->fetchColumn();
    
    // Reset login attempts
    Security::resetLoginAttempts();
    
    // Update last login
    $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
    $stmt->execute([$user['user_id']]);
    
    // Set session
    // When logging in a user, ensure any prior admin session is cleared to
    // prevent navigation from persisting across roles. Without this, a user
    // logging in after an admin session may still see admin navigation.  By
    // unsetting these values first we guarantee a clean session state.
    unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_email'], $_SESSION['admin_full_name'], $_SESSION['admin_role']);

    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['full_name'] = $user['full_name'];
    // Extract first and last name for personalized greetings
    $nameParts = explode(' ', $user['full_name'], 2);
    $_SESSION['first_name'] = $nameParts[0];
    $_SESSION['last_name'] = $nameParts[1] ?? '';
    $_SESSION['account_number'] = $user['account_number'];
    $_SESSION['account_status'] = $user['account_status'];
    $_SESSION['kyc_status'] = $user['kyc_status'];
    $_SESSION['last_activity'] = time();
    
    // Set remember me cookie
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (30 * 24 * 60 * 60); // 30 days
        
        $stmt = $db->prepare("UPDATE users SET remember_token = ?, token_expiry = ? WHERE user_id = ?");
        $stmt->execute([$token, date('Y-m-d H:i:s', $expiry), $user['user_id']]);
        
        setcookie('remember_token', $token, $expiry, '/', '', true, true);
    }
    
    // Log successful login
    Security::logAudit('user_login', "User logged in: {$user['username']}", $user['user_id']);
    
    // Redirect based on Transfer PIN setup status.
    if (!$hasTransferPin) {
        redirectWithMessage('../dashboard/setup_pins.php', 'Welcome back! Please set your 4-digit Transfer PIN to continue.', 'info');
    } else {
        // Use explicit index.php instead of directory to avoid issues on some servers
        redirectWithMessage('../dashboard/index.php', 'Welcome back to CITYBRIDGEBANK!', 'success');
    }
    
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    Security::recordFailedLogin();
    redirectWithMessage('../public/login.php', 'An error occurred during login. Please try again.', 'danger');
}
?>
