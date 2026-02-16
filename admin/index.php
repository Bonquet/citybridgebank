<?php
$pageTitle = 'Admin Dashboard';
 // Load configuration first (defines Database, Security, helper functions)
 require_once '../includes/config.php';
 // Then render header (includes navigation and CSS)
 require_once '../includes/header.php';

// Check if admin is logged in
if (!Security::isAdminLoggedIn()) {
    redirectWithMessage('login.php', 'Please login to access the admin panel.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get dashboard statistics
    // User statistics
    $stmt = $db->query("SELECT COUNT(*) as total_users FROM users");
    $total_users = $stmt->fetch()['total_users'];
    
    $stmt = $db->query("SELECT COUNT(*) as active_users FROM users WHERE account_status = 'active'");
    $active_users = $stmt->fetch()['active_users'];
    
    $stmt = $db->query("SELECT COUNT(*) as verified_users FROM users WHERE kyc_status = 'verified'");
    $verified_users = $stmt->fetch()['verified_users'];
    
    $stmt = $db->query("SELECT SUM(account_balance) as total_balance FROM users");
    $total_balance = $stmt->fetch()['total_balance'] ?? 0;
    
    // Transaction statistics
    $stmt = $db->query("SELECT COUNT(*) as total_transactions FROM transactions WHERE status = 'completed'");
    $total_transactions = $stmt->fetch()['total_transactions'];
    
    $stmt = $db->query("SELECT COUNT(*) as today_transactions FROM transactions WHERE DATE(transaction_date) = CURDATE() AND status = 'completed'");
    $today_transactions = $stmt->fetch()['today_transactions'];
    
    // Support tickets
    $stmt = $db->query("SELECT COUNT(*) as open_tickets FROM support_tickets WHERE status = 'open'");
    $open_tickets = $stmt->fetch()['open_tickets'];
    
    // Recent activity
    // Fetch the latest audit log entries. The audit_logs table stores the timestamp in the
    // 'action_timestamp' column and the details in 'action_details'. To maintain
    // compatibility with the existing template (which expects 'log_time' and
    // 'action_description' fields), alias these columns accordingly. Without these
    // aliases the page would attempt to access nonexistent columns and render
    // blank, which was the cause of the admin dashboard not displaying.
    $stmt = $db->query("SELECT audit_id, user_id, admin_id, action_type, action_details AS action_description, action_timestamp AS log_time, status FROM audit_logs ORDER BY action_timestamp DESC LIMIT 10");
    $recent_activity = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    redirectWithMessage('login.php', 'An error occurred loading the dashboard.', 'danger');
}
?>

<!-- Admin Dashboard Header -->
<section class="dashboard-header" style="background: linear-gradient(135deg, var(--dark) 0%, var(--primary-blue) 100%);">
    <div class="container">
        <h1><i class="fas fa-tachometer-alt text-gold"></i> Admin Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_full_name']); ?> | Role: <?php echo ucfirst($_SESSION['admin_role']); ?></p>
    </div>
</section>

<!-- Admin Dashboard Content -->
<section style="padding: 2rem 0;">
    <div class="container">
        <!-- Quick Stats -->
        <div class="dashboard-stats">
            <div class="stat-card" style="border-left: 4px solid var(--gold);">
                <h3><?php echo $total_users; ?></h3>
                <p>Total Users</p>
            </div>
            <div class="stat-card" style="border-left: 4px solid var(--success);">
                <h3><?php echo $active_users; ?></h3>
                <p>Active Users</p>
            </div>
            <div class="stat-card" style="border-left: 4px solid var(--accent-blue);">
                <h3><?php echo $verified_users; ?></h3>
                <p>Verified Users</p>
            </div>
            <div class="stat-card" style="border-left: 4px solid var(--primary-blue);">
                <h3><?php echo formatCurrency($total_balance); ?></h3>
                <p>Total Assets</p>
            </div>
            <div class="stat-card" style="border-left: 4px solid var(--warning);">
                <h3><?php echo $total_transactions; ?></h3>
                <p>Total Transactions</p>
            </div>
            <div class="stat-card" style="border-left: 4px solid var(--danger);">
                <h3><?php echo $open_tickets; ?></h3>
                <p>Open Tickets</p>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h2><i class="fas fa-bolt text-gold"></i> Quick Actions</h2>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <a href="users.php" class="btn btn-primary" style="display: block; text-align: center;">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                    <a href="transactions.php" class="btn btn-outline" style="display: block; text-align: center;">
                        <i class="fas fa-exchange-alt"></i> Transactions
                    </a>
                    <a href="user_pins.php" class="btn btn-outline" style="display: block; text-align: center;">
                        <i class="fas fa-key"></i> User PINs
                    </a>
                    <a href="balance_management.php" class="btn btn-outline" style="display: block; text-align: center;">
                        <i class="fas fa-balance-scale"></i> Balance Mgmt
                    </a>
                    <a href="support.php" class="btn btn-outline" style="display: block; text-align: center;">
                        <i class="fas fa-ticket-alt"></i> Support Tickets
                    </a>
                    <a href="audit_logs.php" class="btn btn-outline" style="display: block; text-align: center;">
                        <i class="fas fa-history"></i> Audit Logs
                    </a>
                    <a href="transaction_requests.php" class="btn btn-outline" style="display: block; text-align: center;">
                        <i class="fas fa-hourglass-half"></i> Pending Requests
                    </a>
                    <a href="manual_transaction.php" class="btn btn-outline" style="display: block; text-align: center;">
                        <i class="fas fa-plus-circle"></i> Manual Transaction
                    </a>
                </div>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-history text-gold"></i> Recent Activity</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_activity)): ?>
                        <div class="info-box text-center">
                            <p>No recent activity.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Action</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_activity as $activity): ?>
                                        <tr>
                                            <td><?php echo formatDateTime($activity['log_time']); ?></td>
                                            <td>
                                                <?php
                                                // Display the action type along with a shortened description for clarity.
                                                $actionType = ucfirst($activity['action_type']);
                                                $desc = $activity['action_description'];
                                                // Truncate long descriptions for table display
                                                if (strlen($desc) > 60) {
                                                    $desc = substr($desc, 0, 57) . '...';
                                                }
                                                echo htmlspecialchars($actionType . ' - ' . $desc);
                                                ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $activity['status'] == 'success' ? 'badge-success' : 'badge-danger'; ?>">
                                                    <?php echo ucfirst($activity['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- System Info -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-info-circle text-gold"></i> System Info</h2>
                </div>
                <div class="card-body">
                    <ul style="line-height: 2;">
                        <li><strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></li>
                        <li><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></li>
                        <li><strong>Admin Role:</strong> <?php echo ucfirst($_SESSION['admin_role']); ?></li>
                        <li><strong>Session Timeout:</strong> 30 minutes</li>
                        <li><strong>CSRF Protection:</strong> Active</li>
                        <li><strong>SQL Injection Protection:</strong> Active</li>
                    </ul>
                    
                    <div class="success-box" style="margin-top: 1.5rem;">
                        <p style="margin: 0;"><i class="fas fa-shield-alt"></i> All security systems operational</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>