<?php
// CITYBRIDGEBANK PIN Setup Processing
// Simplified PIN Setup - User enters one PIN, backend creates all three PINs with same value
require_once '../includes/config.php';

// Check if user is logged in
// If the user is not logged in, redirect to the public login page
if (!Security::isLoggedIn()) {
    // Use the public login page because the login form lives in the public directory
    redirectWithMessage('../public/login.php', 'Please login to setup your PIN.', 'danger');
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('index.php', 'Invalid request method.', 'danger');
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('setup_pins.php', 'Security verification failed. Please try again.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get existing PINs
    $stmt = $db->prepare("SELECT pin_type FROM user_pins WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $existing_pins = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Validate input
    $errors = [];
    
    // Authentication PIN
    if (empty($_POST['auth_pin']) || empty($_POST['auth_pin_confirm'])) {
        $errors[] = 'Authentication PIN is required.';
    } elseif ($_POST['auth_pin'] !== $_POST['auth_pin_confirm']) {
        $errors[] = 'PINs do not match.';
    } elseif (!preg_match('/^\d{4,6}$/', $_POST['auth_pin'])) {
        $errors[] = 'PIN must be 4-6 digits.';
    } else {
        $auth_pin = $_POST['auth_pin'];
    }
    
    // If there are errors, redirect back
    if (!empty($errors)) {
        $_SESSION['pin_setup_errors'] = $errors;
        redirectWithMessage('setup_pins.php', implode(' ', $errors), 'danger');
    }
    
    // Generate random payment and secure pass pins (6 digits) and store plain values
    $random_payment_pin = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $random_secure_pin = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // Begin transaction to insert pins
    $db->beginTransaction();
    
    // Authorization PIN
    $pins_created = 0;
    if (!in_array('authorization', $existing_pins)) {
        $auth_hash = Security::hashPIN($auth_pin);
        $stmt = $db->prepare("INSERT INTO user_pins (user_id, pin_type, pin_hash, pin_plain, is_active, pin_created_at) VALUES (?, 'authorization', ?, ?, 1, NOW())");
        $stmt->execute([$_SESSION['user_id'], $auth_hash, $auth_pin]);
        $pins_created++;
        Security::logAudit('pin_created', "authorization_PIN created for user {$_SESSION['username']}", $_SESSION['user_id'], null, null, null, 'success');
    }
    // Payment PIN
    if (!in_array('payment', $existing_pins)) {
        $pay_hash = Security::hashPIN($random_payment_pin);
        $stmt = $db->prepare("INSERT INTO user_pins (user_id, pin_type, pin_hash, pin_plain, is_active, pin_created_at) VALUES (?, 'payment', ?, ?, 1, NOW())");
        $stmt->execute([$_SESSION['user_id'], $pay_hash, $random_payment_pin]);
        $pins_created++;
        Security::logAudit('pin_created', "payment_PIN created for user {$_SESSION['username']}", $_SESSION['user_id'], null, null, null, 'success');
    }
    // Secure pass PIN
    if (!in_array('secure_pass', $existing_pins)) {
        $sec_hash = Security::hashPIN($random_secure_pin);
        $stmt = $db->prepare("INSERT INTO user_pins (user_id, pin_type, pin_hash, pin_plain, is_active, pin_created_at) VALUES (?, 'secure_pass', ?, ?, 1, NOW())");
        $stmt->execute([$_SESSION['user_id'], $sec_hash, $random_secure_pin]);
        $pins_created++;
        Security::logAudit('pin_created', "secure_pass_PIN created for user {$_SESSION['username']}", $_SESSION['user_id'], null, null, null, 'success');
    }
    
    $db->commit();
    
    // Build message including randomly generated payment and secure pins so the user can note them down
    if ($pins_created > 0) {
        $msg = "Your authentication PIN has been successfully set up.";
        $msg .= " Payment PIN: " . $random_payment_pin . ". Secure PIN: " . $random_secure_pin . ". Please store these pins securely; you will need them for every transaction.";
        redirectWithMessage('index.php', $msg, 'success');
    } else {
        redirectWithMessage('index.php', 'Your PIN is already set up. You can change it in Security Settings.', 'success');
    }
    
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("PIN setup error: " . $e->getMessage());
    redirectWithMessage('setup_pins.php', 'An error occurred setting up your PIN. Please try again.', 'danger');
}
?>