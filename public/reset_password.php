<?php
// CITYBRIDGEBANK Password Reset
require_once '../includes/config.php';

// Display password reset form when token provided
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $token = $_GET['token'] ?? '';
    if (empty($token)) {
        echo "Invalid or missing token.";
        exit;
    }
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT user_id, reset_token_expiry FROM users WHERE reset_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Password reset lookup error: " . $e->getMessage());
        echo "An error occurred. Please try again later.";
        exit;
    }
    if (!$user || (isset($user['reset_token_expiry']) && strtotime($user['reset_token_expiry']) < time())) {
        echo "This password reset link is invalid or has expired.";
        exit;
    }
    // Show simple password reset form
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo BANK_NAME; ?></title>
    <link rel="stylesheet" href="../css/modern-style.css">
</head>
<body style="display:flex;justify-content:center;align-items:center;min-height:100vh;background:#f5f7fa;">
    <div class="card" style="max-width: 400px; width:100%; padding:2rem;">
        <h2 style="margin-bottom:1.5rem;">Reset Your Password</h2>
        <form method="POST" action="">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" class="form-control" required minlength="8">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="8">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:1rem;">Reset Password</button>
        </form>
    </div>
</body>
</html>
    <?php
    exit;
}

// Handle password reset submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if (empty($token) || empty($new_password)) {
        echo "Invalid request.";
        exit;
    }
    if ($new_password !== $confirm_password) {
        echo "Passwords do not match.";
        exit;
    }
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT user_id, reset_token_expiry FROM users WHERE reset_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Password reset validation error: " . $e->getMessage());
        echo "An error occurred. Please try again later.";
        exit;
    }
    if (!$user || (isset($user['reset_token_expiry']) && strtotime($user['reset_token_expiry']) < time())) {
        echo "This password reset link is invalid or has expired.";
        exit;
    }
    // Update password and clear reset token
    $hash = Security::hashPassword($new_password);
    try {
        $stmt = $db->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expiry = NULL WHERE user_id = ?");
        $stmt->execute([$hash, $user['user_id']]);
    } catch (PDOException $e) {
        error_log("Password reset update error: " . $e->getMessage());
        echo "An error occurred updating your password. Please try again later.";
        exit;
    }
    echo "Your password has been reset successfully. You may now <a href=\"login.php\">log in</a>.";
    exit;
}

// Default fallback
echo "Invalid request.";
?>