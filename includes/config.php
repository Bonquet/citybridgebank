<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!ob_get_level()) {
    ob_start();
}

// Disable display errors in UI to avoid leaking warnings/notices to users.
error_reporting(E_ALL);
ini_set('display_errors', '0');

date_default_timezone_set('UTC');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'citijawp_citibridge');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Security Configuration
define('SESSION_TIMEOUT', 1800);
define('MAX_LOGIN_ATTEMPTS', 5);
define('MAX_PIN_ATTEMPTS', 3);
define('LOGIN_TIMEOUT', 900);
define('CSRF_TOKEN_EXPIRY', 3600);

// Bank Configuration
define('BANK_NAME', 'CITIBRIDGE');
define('BANK_EMAIL', 'support@citibridge.net');
define('ADMIN_EMAIL', 'admin@citibridge.net');
define('DEFAULT_BALANCE', 0.00);

// Transaction Limits
define('MIN_TRANSFER_AMOUNT', 1.00);
define('MAX_DAILY_TRANSFER', 10000.00);
define('MAX_SINGLE_TRANSFER', 5000.00);

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
        ];
        $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
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

class Security {
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']) || (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_EXPIRY) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCSRFToken($token) {
        if (!isset($_SESSION['csrf_token'], $_SESSION['csrf_token_time'])) {
            return false;
        }
        if ((time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_EXPIRY) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], (string)$token);
    }

    public static function sanitizeInput($data) {
        return htmlspecialchars(stripslashes(trim((string)$data)), ENT_QUOTES, 'UTF-8');
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function hashPIN($pin) {
        return password_hash($pin, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verifyPIN($pin, $hash) {
        return password_verify($pin, $hash);
    }

    public static function generateReferenceNumber() {
        return 'TXN' . date('Ymd') . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 8));
    }

    public static function generateAccountNumber() {
        return 'CBB' . str_pad((string)mt_rand(1, 999999999999), 12, '0', STR_PAD_LEFT);
    }

    public static function checkLoginAttempts() {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['login_lockout'] = 0;
        }
        return !($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS && (time() - (int)$_SESSION['login_lockout']) < LOGIN_TIMEOUT);
    }

    public static function recordFailedLogin() {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
        }
        $_SESSION['login_attempts']++;
        if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
            $_SESSION['login_lockout'] = time();
        }
    }

    public static function resetLoginAttempts() {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_lockout'] = 0;
    }

    public static function isLoggedIn() {
        return !empty($_SESSION['user_id']);
    }

    public static function isAdminLoggedIn() {
        return !empty($_SESSION['admin_id']);
    }

    public static function checkSessionTimeout() {
        if (isset($_SESSION['last_activity']) && (time() - (int)$_SESSION['last_activity']) > SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public static function getUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    public static function logAudit($action_type, $action_details, $user_id = null, $admin_id = null, $transaction_id = null, $pin_type = null, $status = 'success') {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare('INSERT INTO audit_logs (user_id, admin_id, action_type, action_details, transaction_id, pin_type, ip_address, user_agent, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$user_id, $admin_id, $action_type, $action_details, $transaction_id, $pin_type, self::getClientIP(), self::getUserAgent(), $status]);
        } catch (PDOException $e) {
            error_log('Audit log failed: ' . $e->getMessage());
        }
    }

    public static function logAdminAction($admin_id, $action_type, $action_details, $affected_user_id = null) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare('INSERT INTO admin_logs (admin_id, action_type, action_details, affected_user_id, ip_address) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$admin_id, $action_type, $action_details, $affected_user_id, self::getClientIP()]);
        } catch (PDOException $e) {
            error_log('Admin log failed: ' . $e->getMessage());
        }
    }
}

function formatCurrency($amount) {
    if ($amount === null || $amount === '' || !is_numeric($amount)) {
        $amount = (float)$amount;
    }
    return '$' . number_format((float)$amount, 2, '.', ',');
}

function formatDate($date) {
    return date('F j, Y', strtotime((string)$date));
}

function formatDateTime($datetime) {
    return date('F j, Y g:i A', strtotime((string)$datetime));
}

function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header('Location: ' . $url);
    exit();
}

function setNoCacheHeaders() {
    header('Cache-Control: private, no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
}
