<?php
$pageTitle = 'User PIN Management';
// Load configuration first for DB and Security
require_once '../includes/config.php';
// Then render the header (includes navigation and CSS)
require_once '../includes/header.php';

// Check if admin is logged in
if (!Security::isAdminLoggedIn()) {
    redirectWithMessage('login.php', 'Please login to access PIN management.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get filter parameters
    $filter_status = isset($_GET['status']) ? Security::sanitizeInput($_GET['status']) : 'all';
    $search = isset($_GET['search']) ? Security::sanitizeInput($_GET['search']) : '';
    
    // Build query for users with PINs
    $where_conditions = ["1=1"];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(u.username LIKE ? OR u.email LIKE ? OR u.full_name LIKE ? OR u.account_number LIKE ?)";
        $search_param = "%{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get users with their PINs
    $sql = "SELECT u.*, 
            (SELECT COUNT(*) FROM user_pins WHERE user_id = u.user_id) as total_pins,
            (SELECT GROUP_CONCAT(pin_type) FROM user_pins WHERE user_id = u.user_id) as pin_types
            FROM users u 
            WHERE {$where_clause}
            ORDER BY u.created_at DESC LIMIT 50";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("User PIN management error: " . $e->getMessage());
    redirectWithMessage('index.php', 'An error occurred loading user PINs.', 'danger');
}
?>

<!-- Page Header -->
<section style="background: linear-gradient(135deg, var(--dark) 0%, var(--primary-blue) 100%); color: var(--white); padding: 2rem 0;">
    <div class="container">
        <h1><i class="fas fa-key text-gold"></i> User PIN Management</h1>
        <p>View and manage all user three-layer PINs (Authorization, Payment, Secure Pass)</p>
    </div>
</section>

<!-- User PIN Content -->
<section style="padding: 2rem 0;">
    <div class="container">
        <!-- Warning Notice -->
        <div class="warning-box" style="margin-bottom: 2rem;">
            <h4 style="color: var(--primary-blue);"><i class="fas fa-exclamation-triangle text-gold"></i> Important Security Notice</h4>
            <p style="margin: 0;">You have full control over all user PINs. You can view PIN status, reset any of the three PINs, or change PINs. ALL PIN changes are logged in the audit trail. Use this power responsibly and only when necessary.</p>
        </div>
        
        <!-- Search -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <form action="" method="GET">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="search">Search Users</label>
                            <input type="text" id="search" name="search" class="form-control" placeholder="Username, email, name, or account number" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- User PIN List -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list text-gold"></i> User PINs</h2>
                <span class="badge badge-info"><?php echo count($users); ?> users</span>
            </div>
            <div class="card-body">
                <?php if (empty($users)): ?>
                    <div class="info-box text-center">
                        <p>No users found matching your criteria.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Username</th>
                                    <th>Account Number</th>
                                    <th>Authorization PIN</th>
                                    <th>Payment PIN</th>
                                    <th>Secure Pass PIN</th>
                                    <th>Setup Status</th>
                                    <th>Last Used</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <?php 
                                        $pin_types = explode(',', $user['pin_types']);
                                        $has_auth = in_array('authorization', $pin_types);
                                        $has_payment = in_array('payment', $pin_types);
                                        $has_secure = in_array('secure_pass', $pin_types);
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($user['full_name']); ?></strong><br>
                                            <small><?php echo htmlspecialchars($user['email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['account_number']); ?></td>
                                        <td>
                                            <?php if ($has_auth): ?>
                                                <span class="badge badge-success"><i class="fas fa-check"></i> Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning"><i class="fas fa-times"></i> Not Set</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($has_payment): ?>
                                                <span class="badge badge-success"><i class="fas fa-check"></i> Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning"><i class="fas fa-times"></i> Not Set</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($has_secure): ?>
                                                <span class="badge badge-success"><i class="fas fa-check"></i> Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning"><i class="fas fa-times"></i> Not Set</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($user['total_pins'] == 3): ?>
                                                <span class="badge badge-success">Complete (3/3)</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Incomplete (<?php echo $user['total_pins']; ?>/3)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                                // Get last used timestamp
                                                $stmt = $db->prepare("SELECT MAX(last_used) as last_used FROM user_pins WHERE user_id = ?");
                                                $stmt->execute([$user['user_id']]);
                                                $last_used = $stmt->fetch()['last_used'];
                                                echo $last_used ? formatDateTime($last_used) : 'Never';
                                            ?>
                                        </td>
                                        <td>
                                            <!-- Link to manage a specific user's PINs -->
                                            <a href="manage_pins.php?user_id=<?php echo $user['user_id']; ?>" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">
                                                <i class="fas fa-cog"></i> Manage
                                            </a>
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

<script>
function managePINs(userId) {
    alert('PIN management modal for user ID: ' + userId + '\n\nThis would show options to:\n- View current PIN status\n- Reset Authorization PIN\n- Reset Payment PIN\n- Reset Secure Pass PIN\n- Change any PIN\n\nAll changes are logged.');
}
</script>

<?php require_once '../includes/footer.php'; ?>