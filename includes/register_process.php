<?php
// CITIBRIDGE Registration Processing
require_once 'config.php';

// Runtime error display is controlled centrally in includes/config.php.

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('../public/register.php', 'Invalid request method.', 'danger');
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
    error_log("CSRF token verification failed");
    Security::recordFailedLogin();
    redirectWithMessage('../public/register.php', 'Security verification failed. Please try again.', 'danger');
}

// Do not throttle user sign-up using login attempt tracking.
// Registration errors should not count against login attempts because
// they are unrelated to authentication. We intentionally do not call
// Security::checkLoginAttempts() here.  This prevents a scenario where
// repeated registration validation errors exhaust the login attempt
// counter and lock out the user from logging in.

// Sanitize and validate input
$first_name = Security::sanitizeInput($_POST['first_name'] ?? '');
$last_name = Security::sanitizeInput($_POST['last_name'] ?? '');
$email = Security::sanitizeInput($_POST['email'] ?? '');
$phone = Security::sanitizeInput($_POST['phone'] ?? '');
$dob = Security::sanitizeInput($_POST['dob'] ?? '');
$address = Security::sanitizeInput($_POST['address'] ?? '');
$city = Security::sanitizeInput($_POST['city'] ?? '');
$state = Security::sanitizeInput($_POST['state'] ?? '');
$zip = Security::sanitizeInput($_POST['zip'] ?? '');
$ssn = Security::sanitizeInput($_POST['ssn'] ?? '');
$username = Security::sanitizeInput($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$account_type = Security::sanitizeInput($_POST['account_type'] ?? '');
$terms = isset($_POST['terms']) ? true : false;
$newsletter = isset($_POST['newsletter']) ? true : false;

// Validation
$errors = [];

if (empty($first_name) || strlen($first_name) < 2) {
    $errors[] = 'First name is required and must be at least 2 characters.';
}

if (empty($last_name) || strlen($last_name) < 2) {
    $errors[] = 'Last name is required and must be at least 2 characters.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email address is required.';
}

// Phone validation: accept numbers, spaces, parentheses, dashes and plus sign
if (empty($phone) || !preg_match('/^[0-9\-\+\(\)\s]{7,20}$/', $phone)) {
    $errors[] = 'Valid phone number is required.';
}

if (empty($dob)) {
    $errors[] = 'Date of birth is required.';
} else {
    try {
        $dob_date = new DateTime($dob);
        $today = new DateTime();
        $age = $today->diff($dob_date)->y;
        if ($age < 18) {
            $errors[] = 'You must be at least 18 years old to open an account.';
        }
    } catch (Exception $e) {
        $errors[] = 'Invalid date of birth format.';
    }
}

if (empty($address) || strlen($address) < 5) {
    $errors[] = 'Valid street address is required.';
}

if (empty($city) || strlen($city) < 2) {
    $errors[] = 'City is required.';
}

if (empty($state) || strlen($state) != 2) {
    $errors[] = 'Valid state is required.';
}

if (empty($zip) || !preg_match('/^\d{5}(-\d{4})?$/', $zip)) {
    $errors[] = 'Valid ZIP code is required.';
}

if (empty($ssn) || !preg_match('/^\d{4}$/', $ssn)) {
    $errors[] = 'Last 4 digits of SSN are required.';
}

if (empty($username) || !preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
    $errors[] = 'Username must be 4-20 characters and contain only letters, numbers, and underscores.';
}

if (empty($password) || strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
} else {
    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || 
        !preg_match('/\d/', $password) || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = 'Password must include uppercase, lowercase, number, and special character.';
    }
}

if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match.';
}

if (empty($account_type) || !in_array($account_type, ['personal', 'premium', 'student'])) {
    $errors[] = 'Valid account type is required.';
}

if (!$terms) {
    $errors[] = 'You must agree to the Terms & Conditions.';
}

// If there are errors, redirect back with error message
if (!empty($errors)) {
    error_log("Registration validation errors: " . implode(', ', $errors));
    // Do not record a failed login for registration errors.  Otherwise
    // registration mistakes would consume login attempts and cause
    // unintentional lockouts.  Instead, just pass the errors back to
    // the registration page.
    $_SESSION['registration_errors'] = $errors;
    redirectWithMessage('../public/register.php', 'Please correct the errors in your registration form.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if username already exists
    $stmt = $db->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        error_log("Registration failed: Username already exists - {$username}");
        // Do not increment login attempt counter on registration errors.
        redirectWithMessage('../public/register.php', 'Username already exists. Please choose a different username.', 'danger');
    }
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        error_log("Registration failed: Email already registered - {$email}");
        // Do not increment login attempt counter on registration errors.
        redirectWithMessage('../public/register.php', 'Email address already registered. Please login or use a different email.', 'danger');
    }
    
    // Generate account number
    $account_number = Security::generateAccountNumber();
    $password_hash = Security::hashPassword($password);
    $full_name = $first_name . ' ' . $last_name;
    
    // Insert new user with full address details and additional fields
    $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, full_name, phone, address, city, state, zip, ssn, date_of_birth, account_number, account_type, kyc_status, account_status, account_balance) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'none', 'active', 0.00)");
    
    $stmt->execute([
        $username,
        $email,
        $password_hash,
        $full_name,
        $phone,
        $address,
        $city,
        $state,
        $zip,
        $ssn,
        $dob,
        $account_number,
        $account_type
    ]);
    
    $user_id = $db->lastInsertId();
    
    // Log registration
    Security::logAudit('user_registration', "New user registered: {$username}", $user_id);
    
    // Reset login attempts
    Security::resetLoginAttempts();
    
    // Set session for immediate login
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['full_name'] = $full_name;
    $_SESSION['first_name'] = $first_name;
    $_SESSION['last_name'] = $last_name;
    $_SESSION['account_number'] = $account_number;
    $_SESSION['last_activity'] = time();
    
    // Send account creation email (basic example)
    $subject = "Welcome to " . BANK_NAME;
    $message = "Hello {$full_name},\n\n".
               "Thank you for opening a new account with " . BANK_NAME . ".\n".
               "Your account has been created successfully.\n\n".
               "Account Number: {$account_number}\n".
               "Account Type: " . ucfirst($account_type) . "\n".
               "Current Balance: $" . number_format(DEFAULT_BALANCE, 2) . "\n\n".
               "You can now log in to your dashboard to manage your account and set your Transfer PIN.\n\n".
               "If you did not request this account, please contact us immediately at " . BANK_EMAIL . ".\n\n".
               "Regards,\n".
               BANK_NAME . " Team";
    // Use PHP's mail function (configured on server) - suppress errors if mail() fails
    @mail($email, $subject, $message, "From: ".BANK_EMAIL."\r\n");

    // Redirect to PIN setup page
    redirectWithMessage('../dashboard/setup_pins.php', 'Registration successful! Please set your 4-digit Transfer PIN to complete setup.', 'success');
    
} catch (PDOException $e) {
    error_log("Registration database error: " . $e->getMessage());
    // Do not record failed login for registration database errors.
    redirectWithMessage('../public/register.php', 'An error occurred during registration. Please try again.', 'danger');
}
?>
