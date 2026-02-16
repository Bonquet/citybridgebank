<?php
// AJAX endpoint to verify individual PIN steps during withdrawal
// This script validates a single PIN (authorization, payment, or secure_pass)
// and returns a JSON response indicating success or failure. It increments
// the session-based PIN attempt counter on failures and logs all
// validation attempts to the audit trail.

require_once '../includes/config.php';

// Return JSON responses
header('Content-Type: application/json');

// Ensure user is logged in
if (!Security::isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to verify PINs.'
    ]);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

// Sanitize and validate inputs
$step = $_POST['step'] ?? '';
$pin  = $_POST['pin'] ?? '';

// Map step to pin_type
$validSteps = [
    'authorization' => 'authorization',
    'payment'       => 'payment',
    'secure_pass'   => 'secure_pass'
];

if (!array_key_exists($step, $validSteps)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid PIN type requested.'
    ]);
    exit;
}

// Enforce basic PIN format (4 to 6 digits)
if (!preg_match('/^\d{4,6}$/', $pin)) {
    echo json_encode([
        'success' => false,
        'message' => ucfirst($step) . ' PIN must be 4-6 digits.'
    ]);
    exit;
}

// Rate limiting: check PIN attempts stored in session
if (!isset($_SESSION['pin_attempts'])) {
    $_SESSION['pin_attempts'] = 0;
}
if ($_SESSION['pin_attempts'] >= MAX_PIN_ATTEMPTS) {
    echo json_encode([
        'success' => false,
        'message' => 'Too many incorrect PIN attempts. Please wait before trying again.'
    ]);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    // Fetch the stored hash for the requested PIN type
    $stmt = $db->prepare("SELECT pin_hash, is_active FROM user_pins WHERE user_id = ? AND pin_type = ?");
    $stmt->execute([$_SESSION['user_id'], $validSteps[$step]]);
    $row = $stmt->fetch();

    // If no PIN found or it is inactive, block verification
    if (!$row || !$row['is_active']) {
        echo json_encode([
            'success' => false,
            'message' => ucfirst($step) . ' PIN is not active. Please contact support.'
        ]);
        exit;
    }

    // Verify the provided PIN against the stored hash
    if (!Security::verifyPIN($pin, $row['pin_hash'])) {
        // Increment failed attempts
        $_SESSION['pin_attempts']++;
        // Log audit
        Security::logAudit('pin_validation_failed', 'Invalid ' . $validSteps[$step] . ' PIN via AJAX', $_SESSION['user_id'], null, null, $validSteps[$step], 'failure');
        echo json_encode([
            'success' => false,
            'message' => 'Invalid ' . ucfirst($step) . ' PIN.'
        ]);
        exit;
    }

    // PIN is correct. Reset attempts on success?
    // Do not reset here so user still must complete all steps; only reset after final submission
    // Update last_used timestamp
    $upd = $db->prepare("UPDATE user_pins SET last_used = NOW() WHERE user_id = ? AND pin_type = ?");
    $upd->execute([$_SESSION['user_id'], $validSteps[$step]]);

    // Log successful validation
    Security::logAudit('pin_validated', ucfirst($validSteps[$step]) . ' PIN validated via AJAX', $_SESSION['user_id'], null, null, $validSteps[$step], 'success');
    echo json_encode([
        'success' => true
    ]);
} catch (PDOException $e) {
    error_log('PIN verification error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during PIN verification.'
    ]);
}
?>