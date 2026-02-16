<?php
// Load configuration to access Database and Security classes
require_once '../includes/config.php';
// Set page title and render the header
$pageTitle = 'Audit Logs';
require_once '../includes/header.php';

// Check if admin is logged in
if (!Security::isAdminLoggedIn()) {
    redirectWithMessage('login.php', 'Please login to access audit logs.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get filter parameters
    $filter_action = isset($_GET['action']) ? Security::sanitizeInput($_GET['action']) : 'all';
    $filter_status = isset($_GET['status']) ? Security::sanitizeInput($_GET['status']) : 'all';
    $search = isset($_GET['search']) ? Security::sanitizeInput($_GET['search']) : '';
    
    // Build query
    $where_conditions = [];
    $params = [];
    
    if ($filter_action != 'all') {
        $where_conditions[] = "action_type = ?";
        $params[] = $filter_action;
    }
    
    if ($filter_status != 'all') {
        $where_conditions[] = "status = ?";
        $params[] = $filter_status;
    }
    
    if (!empty($search)) {
        // Search on the descriptive text and associated usernames. Use the
        // underlying 'action_details' column rather than the alias
        // 'action_description' to prevent SQL errors.
        $where_conditions[] = "(al.action_details LIKE ? OR u.username LIKE ? OR a.admin_username LIKE ?)";
        $search_param = "%{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get audit logs
    // Alias the action timestamp and details so the template can reference
    // 'log_time' and 'action_description'. Without aliases the page will
    // attempt to access non-existent columns and render blank.
    $sql = "SELECT al.audit_id,
                   al.user_id,
                   al.admin_id,
                   al.action_type,
                   al.action_details AS action_description,
                   al.action_timestamp AS log_time,
                   al.transaction_id,
                   al.pin_type,
                   al.ip_address,
                   al.user_agent,
                   al.status,
                   u.username AS user_username,
                   a.admin_username AS admin_username
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.user_id
            LEFT JOIN admins a ON al.admin_id = a.admin_id
            {$where_clause}
            ORDER BY al.action_timestamp DESC LIMIT 200";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Audit logs error: " . $e->getMessage());
    redirectWithMessage('index.php', 'An error occurred loading audit logs.', 'danger');
}
?>

<!-- Page Header -->
<section style="background: linear-gradient(135deg, var(--dark) 0%, var(--primary-blue) 100%); color: var(--white); padding: 2rem 0;">
    <div class="container">
        <h1><i class="fas fa-history text-gold"></i> Audit Logs</h1>
        <p>Comprehensive immutable audit trail of all system actions</p>
    </div>
</section>

<!-- Audit Logs Content -->
<section style="padding: 2rem 0;">
    <div class="container">
        <!-- Immutable Notice -->
        <div class="info-box" style="margin-bottom: 2rem;">
            <h4 style="color: var(--primary-blue);"><i class="fas fa-database text-gold"></i> Immutable Audit Trail</h4>
            <p style="margin: 0;">All audit logs are immutable - they cannot be modified or deleted. This includes all transactions, all PIN usage, all admin actions, all balance changes, and all transaction deletions. The audit trail provides complete accountability and regulatory compliance.</p>
        </div>
        
        <!-- Filters -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <form action="" method="GET">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" id="search" name="search" class="form-control" placeholder="Action description, username, or admin" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="form-group">
                            <label for="action">Action Type</label>
                            <select id="action" name="action" class="form-control">
                                <option value="all" <?php echo $filter_action == 'all' ? 'selected' : ''; ?>>All Actions</option>
                                <option value="login_attempt" <?php echo $filter_action == 'login_attempt' ? 'selected' : ''; ?>>Login Attempts</option>
                                <option value="admin_login" <?php echo $filter_action == 'admin_login' ? 'selected' : ''; ?>>Admin Logins</option>
                                <option value="transfer_completed" <?php echo $filter_action == 'transfer_completed' ? 'selected' : ''; ?>>Transfers</option>
                                <option value="pin_validated" <?php echo $filter_action == 'pin_validated' ? 'selected' : ''; ?>>PIN Usage</option>
                                <option value="pin_validation_failed" <?php echo $filter_action == 'pin_validation_failed' ? 'selected' : ''; ?>>PIN Failures</option>
                                <option value="balance_change" <?php echo $filter_action == 'balance_change' ? 'selected' : ''; ?>>Balance Changes</option>
                                <option value="support_ticket_created" <?php echo $filter_action == 'support_ticket_created' ? 'selected' : ''; ?>>Support Tickets</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="all" <?php echo $filter_status == 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="success" <?php echo $filter_status == 'success' ? 'selected' : ''; ?>>Success</option>
                                <option value="failure" <?php echo $filter_status == 'failure' ? 'selected' : ''; ?>>Failure</option>
                            </select>
                        </div>
                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Audit Log Entries -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list text-gold"></i> Audit Trail</h2>
                <span class="badge badge-info"><?php echo count($logs); ?> entries</span>
            </div>
            <div class="card-body">
                <?php if (empty($logs)): ?>
                    <div class="info-box text-center">
                        <p>No audit logs found matching your criteria.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Action Type</th>
                                    <th>Description</th>
                                    <th>User</th>
                                    <th>Admin</th>
                                    <th>IP Address</th>
                                    <th>Status</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr style="<?php echo $log['status'] == 'failure' ? 'background-color: rgba(220, 53, 69, 0.1);' : ''; ?>">
                                        <td><?php echo formatDateTime($log['log_time']); ?></td>
                                        <td>
                                            <span class="badge badge-info"><?php echo htmlspecialchars($log['action_type']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['action_description']); ?></td>
                                        <td><?php echo $log['user_username'] ? htmlspecialchars($log['user_username']) : 'N/A'; ?></td>
                                        <td><?php echo $log['admin_username'] ? htmlspecialchars($log['admin_username']) : 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $log['status'] == 'success' ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo ucfirst($log['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($log['transaction_id'])): ?>
                                                <small>Txn: <?php echo htmlspecialchars($log['transaction_id']); ?></small>
                                            <?php endif; ?>
                                            <?php if (!empty($log['pin_type'])): ?>
                                                <?php if (!empty($log['transaction_id'])): ?> <br> <?php endif; ?>
                                                <small>PIN: <?php echo htmlspecialchars($log['pin_type']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>