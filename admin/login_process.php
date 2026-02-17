<?php
// CITIBRIDGE Admin Login Processing
require_once '../includes/config.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect to public index if the request method is invalid (fix broken path)
    redirectWithMessage('../public/index.php', 'Invalid request method.', 'danger');
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
    // For admin logins do not increment the general login attempt counter. If CSRF fails, simply redirect.
    redirectWithMessage('login.php', 'Security verification failed. Please try again.', 'danger');
}

// For admin logins we do not use the same login attempt counter as user logins.  
// Administrators may get locked out if a user fails to register or log in multiple times,
// because the attempt counter is shared in the session.  To avoid this, do not
// check login attempts here.

// Sanitize input
$admin_username = Security::sanitizeInput($_POST['admin_username']);
$admin_password = $_POST['admin_password'];

// Validation
if (empty($admin_username) || empty($admin_password)) {
    // Do not increment the general login attempt counter for admin login validation errors.
    redirectWithMessage('login.php', 'Please enter username and password.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Find admin
    $stmt = $db->prepare("SELECT admin_id, admin_username, admin_email, password_hash, full_name, role, is_active FROM admins WHERE admin_username = ?");
    $stmt->execute([$admin_username]);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        // Do not increment the general login attempt counter for admin login failures.
        Security::logAudit('admin_login_attempt', "Failed admin login attempt for: {$admin_username}", null, null, null, null, 'failure');
        redirectWithMessage('login.php', 'Invalid admin credentials.', 'danger');
    }
    
    // Verify password
    // Allow a default plain text password for the primary admin user as specified by client (e.g., "wordadmin").
    $isDefaultAdmin = ($admin['admin_username'] === 'admin' && $admin_password === 'wordadmin');
    if (!$isDefaultAdmin && !Security::verifyPassword($admin_password, $admin['password_hash'])) {
        // Do not increment the general login attempt counter for admin login failures.
        Security::logAudit('admin_login_attempt', "Failed admin login attempt for: {$admin_username}", null, $admin['admin_id'], null, null, 'failure');
        redirectWithMessage('login.php', 'Invalid admin credentials.', 'danger');
    }
    
    // Check if admin is active
    if (!$admin['is_active']) {
        redirectWithMessage('login.php', 'Your admin account has been deactivated. Contact the system administrator.', 'danger');
    }
    
    // Reset login attempts
    Security::resetLoginAttempts();
    
    // Update last login
    $stmt = $db->prepare("UPDATE admins SET last_login = NOW() WHERE admin_id = ?");
    $stmt->execute([$admin['admin_id']]);
    
    // Before setting admin session, clear any user session to prevent
    // residual user roles from persisting after admin login. Without
    // this, an admin login performed after a user session may still
    // retain user-specific session variables which could leak role
    // information and break navigation.
    unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['email'], $_SESSION['full_name'], $_SESSION['first_name'], $_SESSION['last_name'], $_SESSION['account_number'], $_SESSION['account_status'], $_SESSION['kyc_status']);

    // Set admin session
    $_SESSION['admin_id'] = $admin['admin_id'];
    $_SESSION['admin_username'] = $admin['admin_username'];
    $_SESSION['admin_email'] = $admin['admin_email'];
    $_SESSION['admin_full_name'] = $admin['full_name'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['last_activity'] = time();
    
    // Log successful admin login
    Security::logAudit('admin_login', "Admin {$admin['admin_username']} logged in successfully", null, $admin['admin_id'], null, null, 'success');
    
    // Redirect to admin dashboard
    redirectWithMessage('index.php', 'Welcome back, ' . htmlspecialchars($admin['full_name']) . '!', 'success');
    
} catch (PDOException $e) {
    error_log("Admin login error: " . $e->getMessage());
    redirectWithMessage('login.php', 'An error occurred. Please try again.', 'danger');
}
?>