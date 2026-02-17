-- CITIBRIDGE Database Schema
-- Created for secure multi-page online banking system

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS support_tickets;
DROP TABLE IF EXISTS user_pins;
DROP TABLE IF EXISTS admin_logs;
DROP TABLE IF EXISTS accounts;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS kyc_logs;

-- Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100) NULL,
    state VARCHAR(2) NULL,
    zip VARCHAR(10) NULL,
    ssn VARCHAR(4) NULL,
    date_of_birth DATE,
    account_type ENUM('personal','premium','student') DEFAULT 'personal',
    kyc_status ENUM('none','pending', 'verified', 'rejected', 'suspended') DEFAULT 'none',
    kyc_document VARCHAR(255) NULL,
    kyc_reviewed_by INT NULL,
    kyc_reviewed_at DATETIME NULL,
    kyc_review_reason TEXT NULL,
    account_number VARCHAR(20) UNIQUE NOT NULL,
    account_balance DECIMAL(15, 2) DEFAULT 0.00,
    account_status ENUM('active', 'frozen', 'closed') DEFAULT 'active',
    reset_token VARCHAR(255) NULL,
    reset_token_expiry DATETIME NULL,
    force_password_reset BOOLEAN DEFAULT FALSE,
    remember_token VARCHAR(255) NULL,
    token_expiry DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_account_number (account_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User PINs Table (Three-Layer PIN System)
CREATE TABLE user_pins (
    pin_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pin_type ENUM('authorization', 'payment', 'secure_pass') NOT NULL,
    pin_hash VARCHAR(255) NOT NULL,
    -- Legacy column retained for compatibility.
    -- Application policy keeps this NULL and uses reset/regenerate flows only.
    pin_plain VARCHAR(50) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    pin_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    pin_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_used TIMESTAMP NULL,
    transfer_pin_hash VARCHAR(255) NULL,
    transfer_pin_created_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_pin_type (user_id, pin_type),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transactions Table
CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    transaction_type ENUM('credit', 'debit', 'transfer', 'payment') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    description TEXT NOT NULL,
    reference_number VARCHAR(50) UNIQUE NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'reversed', 'deleted') DEFAULT 'completed',
    balance_after DECIMAL(15, 2) NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    authorization_pin_used BOOLEAN DEFAULT FALSE,
    payment_pin_used BOOLEAN DEFAULT FALSE,
    secure_pass_used BOOLEAN DEFAULT FALSE,
    admin_notes TEXT,
    deleted_by INT NULL,
    deleted_at TIMESTAMP NULL,
    delete_reason TEXT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_reference (reference_number),
    INDEX idx_status (status),
    INDEX idx_date (transaction_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Support Tickets Table
CREATE TABLE support_tickets (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'closed') DEFAULT 'open',
    admin_response TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    closed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admins Table
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_username VARCHAR(50) UNIQUE NOT NULL,
    admin_email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin', 'support') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (admin_username),
    INDEX idx_email (admin_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Logs Table
CREATE TABLE admin_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    action_details TEXT NOT NULL,
    affected_user_id INT NULL,
    ip_address VARCHAR(45),
    action_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id) ON DELETE CASCADE,
    INDEX idx_admin_id (admin_id),
    INDEX idx_timestamp (action_timestamp),
    INDEX idx_user_id (affected_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit Logs Table (Comprehensive System Logging)
CREATE TABLE audit_logs (
    audit_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    admin_id INT NULL,
    action_type VARCHAR(50) NOT NULL,
    action_details TEXT NOT NULL,
    transaction_id INT NULL,
    pin_type VARCHAR(50) NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    status ENUM('success', 'failure', 'blocked') DEFAULT 'success',
    action_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_admin_id (admin_id),
    INDEX idx_timestamp (action_timestamp),
    INDEX idx_action_type (action_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- KYC Action Logs
CREATE TABLE kyc_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    admin_id INT NULL,
    action ENUM('upload', 'approve', 'reject', 'request_reupload') NOT NULL,
    reason TEXT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_kyc_user (user_id),
    INDEX idx_kyc_admin (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transaction Requests Table
-- This table stores deposit and withdrawal requests that require admin approval.
-- Each request begins in a 'pending' state and can be approved or rejected by an admin.
-- For withdrawals, three multi‑factor verification blocks (authorization, payment, secure) are tracked
-- with individual statuses. For deposits these blocks can remain at their default values.
CREATE TABLE transaction_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    request_type ENUM('deposit','withdrawal') NOT NULL,
    description TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    reject_reason TEXT NULL,
    block1_status ENUM('disabled','enabled','pending','success','failed') DEFAULT 'pending',
    block2_status ENUM('disabled','enabled','pending','success','failed') DEFAULT 'pending',
    block3_status ENUM('disabled','enabled','pending','success','failed') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES admins(admin_id) ON DELETE SET NULL,
    INDEX idx_user_id_request (user_id),
    INDEX idx_status_request (status),
    INDEX idx_request_type (request_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin account (password: Admin123!)
-- Hash for 'Admin123!' will be generated by PHP during setup
INSERT INTO admins (admin_username, admin_email, password_hash, full_name, role) 
VALUES ('admin', 'admin@citibridge.net', '$2y$10$placeholder_hash_to_be_updated', 'System Administrator', 'super_admin');

-- Insert test user account for demonstration (password: User123!)
-- Hash for 'User123!' will be generated by PHP during setup
INSERT INTO users (username, email, password_hash, full_name, phone, address, date_of_birth, account_number, kyc_status) 
VALUES ('testuser', 'test@citibridge.net', '$2y$10$placeholder_hash_to_be_updated', 'Test User', '+1234567890', '123 Main Street, City', '1990-01-01', 'CBB100000000001', 'verified');