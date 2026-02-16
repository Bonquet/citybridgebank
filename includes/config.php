<?php
// CITYBRIDGEBANK Database Configuration
// This file contains sensitive configuration - protect from web access

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'citybridgebank');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Security Configuration
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('MAX_LOGIN_ATTEMPTS', 5);
define('MAX_PIN_ATTEMPTS', 3);
define('LOGIN_TIMEOUT', 900); // 15 minutes
define('CSRF_TOKEN_EXPIRY', 3600); // 1 hour

// Bank Configuration
define('BANK_NAME', 'CITYBRIDGEBANK');
define('BANK_EMAIL', 'support@citybridgebank.com');
define('ADMIN_EMAIL', 'admin@citybridgebank.com');
define('DEFAULT_BALANCE', 0.00);

// Transaction Limits
define('MIN_TRANSFER_AMOUNT', 1.00);
define('MAX_DAILY_TRANSFER', 10000.00);
define('MAX_SINGLE_TRANSFER', 5000.00);

// Error Reporting (Disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database Connection Class
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

// Security Functions
class Security {
    // Generate CSRF Token
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token']) || 
            !isset($_SESSION['csrf_token_time']) || 
            time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_EXPIRY) {
            
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }
    
    // Verify CSRF Token
    public static function verifyCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || 
            !isset($_SESSION['csrf_token_time']) ||
            time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_EXPIRY) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    // Sanitize Input
    public static function sanitizeInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
    
    // Hash Password
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }
    
    // Verify Password
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    // Hash PIN
    public static function hashPIN($pin) {
        return password_hash($pin, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    // Verify PIN
    public static function verifyPIN($pin, $hash) {
        return password_verify($pin, $hash);
    }
    
    // Generate Reference Number
    public static function generateReferenceNumber() {
        return 'TXN' . date('Ymd') . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    }
    
    // Generate Account Number
    public static function generateAccountNumber() {
        return 'CBB' . str_pad(mt_rand(1, 999999999999), 12, '0', STR_PAD_LEFT);
    }
    
    // Check Login Attempts
    public static function checkLoginAttempts() {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['login_lockout'] = 0;
        }
        
        if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS && 
            time() - $_SESSION['login_lockout'] < LOGIN_TIMEOUT) {
            return false;
        }
        
        return true;
    }
    
    // Record Failed Login
    public static function recordFailedLogin() {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
        }
        
        $_SESSION['login_attempts']++;
        
        if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
            $_SESSION['login_lockout'] = time();
        }
    }
    
    // Reset Login Attempts
    public static function resetLoginAttempts() {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_lockout'] = 0;
    }
    
    // Check if user is logged in
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    // Check if admin is logged in
    public static function isAdminLoggedIn() {
        return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
    }
    
    // Check session timeout
    public static function checkSessionTimeout() {
        if (isset($_SESSION['last_activity']) && 
            time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    // Get Client IP
    public static function getClientIP() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    // Get User Agent
    public static function getUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }
    
    // Log audit trail
    public static function logAudit($action_type, $action_details, $user_id = null, $admin_id = null, 
                                    $transaction_id = null, $pin_type = null, $status = 'success') {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO audit_logs 
                (user_id, admin_id, action_type, action_details, transaction_id, pin_type, ip_address, user_agent, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $user_id,
                $admin_id,
                $action_type,
                $action_details,
                $transaction_id,
                $pin_type,
                self::getClientIP(),
                self::getUserAgent(),
                $status
            ]);
        } catch (PDOException $e) {
            error_log("Audit log failed: " . $e->getMessage());
        }
    }

    /**
     * Log an admin action to the admin_logs table. This is separate from the general audit log and
     * should be used whenever an administrator performs an action such as approving/rejecting requests,
     * adjusting balances, or modifying PINs. The IP address is automatically captured.
     *
     * @param int $admin_id The ID of the admin performing the action.
     * @param string $action_type A short identifier for the action (e.g., 'deposit_approved').
     * @param string $action_details A descriptive message explaining what was done.
     * @param int|null $affected_user_id The user affected by the action, if any.
     */
    public static function logAdminAction($admin_id, $action_type, $action_details, $affected_user_id = null) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO admin_logs (admin_id, action_type, action_details, affected_user_id, ip_address) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $admin_id,
                $action_type,
                $action_details,
                $affected_user_id,
                self::getClientIP()
            ]);
        } catch (PDOException $e) {
            error_log('Admin log failed: ' . $e->getMessage());
        }
    }
}

// Format currency
/**
 * Format a currency value consistently.
 *
 * Some calls to formatCurrency() may pass null or non-numeric values. Passing
 * null directly into number_format() raises a deprecation warning in newer
 * PHP versions. To avoid this, cast the value to float and provide a
 * sensible default (0.00) when the input is empty or not numeric.
 *
 * @param mixed $amount The amount to format. If null or non-numeric,
 *                      defaults to 0.
 * @return string The formatted currency string (e.g., "$1,234.56").
 */
function formatCurrency($amount) {
    // Coalesce null/empty to zero and cast to float for safe formatting
    if ($amount === null || $amount === '') {
        $amount = 0;
    }
    // Ensure numeric input
    if (!is_numeric($amount)) {
        $amount = floatval($amount) ?: 0;
    }
    return '$' . number_format((float)$amount, 2, '.', ',');
}

// Format date
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Format datetime
function formatDateTime($datetime) {
    return date('F j, Y g:i A', strtotime($datetime));
}

// Redirect with message
function redirectWithMessage($url, $message, $type = 'success') {
    // Store flash message in the session.  Note: session_start() must be
    // active when this function is called. If the session has been
    // destroyed (for example, during logout), the redirect will still
    // proceed, but the flash messages will not persist until the next
    // request. A new session will begin automatically on the next page load
    // when config.php is included.
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit();
}

// Intentionally omit the closing PHP tag at the end of this file.  Leaving
// out the ?> prevents accidental whitespace or BOM characters from being
// sent to the browser, which would interfere with header() calls and
// session management.