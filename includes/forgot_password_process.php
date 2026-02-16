<?php
// CITYBRIDGEBANK Password Recovery Processing
require_once 'config.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect to public index for invalid request (fix broken path)
    redirectWithMessage('../public/index.php', 'Invalid request method.', 'danger');
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('../public/forgot_password.php', 'Security verification failed. Please try again.', 'danger');
}

// Sanitize input
$username_email = Security::sanitizeInput($_POST['username_email']);
$account_number = isset($_POST['account_number']) ? Security::sanitizeInput($_POST['account_number']) : '';
$ssn_last4 = isset($_POST['ssn_last4']) ? Security::sanitizeInput($_POST['ssn_last4']) : '';

// Validation
if (empty($username_email)) {
    redirectWithMessage('../public/forgot_password.php', 'Please enter your username or email address.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if input is email or username
    $isEmail = filter_var($username_email, FILTER_VALIDATE_EMAIL);
    
    // Find user
    if ($isEmail) {
        $stmt = $db->prepare("SELECT user_id, username, email, full_name, account_number FROM users WHERE email = ?");
    } else {
        $stmt = $db->prepare("SELECT user_id, username, email, full_name, account_number FROM users WHERE username = ?");
    }
    
    $stmt->execute([$username_email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Don't reveal if user exists for security
        redirectWithMessage('../public/login.php', 'If your account exists, you will receive a password reset link at your email address.', 'success');
    }
    
    // Additional verification if account number or SSN provided
    $verified = true;
    if (!empty($account_number) && $user['account_number'] !== $account_number) {
        $verified = false;
    }
    
    // Note: SSN verification would require access to the full SSN, which isn't stored in the basic user query
    // In production, you'd have a separate verification process
    
    if ($verified) {
        // Generate reset token
        $reset_token = bin2hex(random_bytes(32));
        $token_expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
        
        // Store reset token
        $stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE user_id = ?");
        $stmt->execute([$reset_token, $token_expiry, $user['user_id']]);
        
        // Log password reset request
        Security::logAudit('password_reset_request', "Password reset requested for: {$user['username']}", $user['user_id']);

        // Send password reset email with token (basic example)
        $reset_link = "http://yourdomain.com/reset_password.php?token={$reset_token}";
        $subject = "Password Reset Request";
        $message = "Hello {$user['full_name']},\n\n".
                   "We received a request to reset your password for your " . BANK_NAME . " account.\n".
                   "Please click the link below to set a new password. This link will expire in 1 hour.\n\n".
                   "{$reset_link}\n\n".
                   "If you did not request a password reset, please ignore this email or contact support.\n\n".
                   "Regards,\n".
                   BANK_NAME . " Team";
        @mail($user['email'], $subject, $message, "From: ".BANK_EMAIL."\r\n");

        redirectWithMessage('../public/login.php', 'If your account exists, you will receive a password reset link at your email address.', 'success');
    } else {
        redirectWithMessage('../public/forgot_password.php', 'Account information does not match. Please verify your details.', 'danger');
    }
    
} catch (PDOException $e) {
    error_log("Password reset error: " . $e->getMessage());
    redirectWithMessage('../public/forgot_password.php', 'An error occurred. Please try again.', 'danger');
}
?>