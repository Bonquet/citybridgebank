<?php
require_once '../includes/config.php';
$pageTitle = 'Balance Management';

if (!Security::isAdminLoggedIn()) {
    redirectWithMessage('login.php', 'Please login to access balance management.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    $search = isset($_GET['search']) ? Security::sanitizeInput($_GET['search']) : '';

    $where_conditions = ["account_status != 'closed'"];
    $params = [];

    if ($search !== '') {
        $where_conditions[] = "(username LIKE ? OR email LIKE ? OR full_name LIKE ? OR account_number LIKE ?)";
        $search_param = "%{$search}%";
        $params = [$search_param, $search_param, $search_param, $search_param];
    }

    $where_clause = implode(' AND ', $where_conditions);
    $stmt = $db->prepare("SELECT user_id, full_name, username, email, account_number, account_balance, account_status FROM users WHERE {$where_clause} ORDER BY account_balance DESC LIMIT 50");
    $stmt->execute($params);
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Balance management error: " . $e->getMessage());
    redirectWithMessage('index.php', 'An error occurred loading balance management.', 'danger');
}

$balance_csrf = Security::generateCSRFToken();
require_once '../includes/header.php';
?>
<section style="background: linear-gradient(135deg, var(--dark) 0%, var(--primary-blue) 100%); color: var(--white); padding: 2rem 0;">
    <div class="container">
        <h1><i class="fas fa-balance-scale text-gold"></i> Balance Management</h1>
        <p>Manage user account balances with full audit logging</p>
    </div>
</section>

<section style="padding: 2rem 0;">
    <div class="container">
        <div class="danger-box" style="margin-bottom: 2rem;">
            <h4 style="color: var(--primary-blue);"><i class="fas fa-exclamation-triangle text-gold"></i> CRITICAL: Balance Modifications Require Reason</h4>
            <p style="margin: 0;">You can include an optional reason for user-visible notes; internal admin audit logging is always recorded.</p>
        </div>

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

        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list text-gold"></i> User Balances</h2>
                <span class="badge badge-info"><?php echo count($users); ?> users</span>
            </div>
            <div class="card-body">
                <?php if (empty($users)): ?>
                    <div class="info-box text-center"><p>No users found matching your criteria.</p></div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr><th>User</th><th>Username</th><th>Account Number</th><th>Current Balance</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong><br><small><?php echo htmlspecialchars($user['email']); ?></small></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['account_number']); ?></td>
                                        <td><strong style="font-size:1.125rem;color:<?php echo $user['account_balance'] >= 0 ? 'var(--success)' : 'var(--danger)'; ?>;"><?php echo formatCurrency($user['account_balance']); ?></strong></td>
                                        <td><span class="badge <?php echo $user['account_status'] === 'active' ? 'badge-success' : 'badge-warning'; ?>"><?php echo ucfirst($user['account_status']); ?></span></td>
                                        <td>
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;" onclick="openBalanceModal(<?php echo (int)$user['user_id']; ?>)"><i class="fas fa-edit"></i> Modify</button>
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

<div id="balanceModal" class="pin-modal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); align-items: center; justify-content: center; z-index: 10000;">
    <div class="pin-modal-content glass-card" style="width: 400px; padding: 1.5rem; position: relative;">
        <h3 style="margin-bottom: 1rem; color: var(--accent-blue);">Modify Balance</h3>
        <form id="balanceForm" method="POST" action="modify_balance.php">
            <input type="hidden" name="csrf_token" value="<?php echo $balance_csrf; ?>">
            <input type="hidden" name="user_id" id="balance_user_id" value="">
            <div class="form-group">
                <label for="balance_action">Action Type *</label>
                <select id="balance_action" name="action" class="form-control" required>
                    <option value="credit">Credit (Add funds)</option>
                    <option value="debit">Debit (Remove funds)</option>
                    <option value="adjustment">Adjustment (Signed amount)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="balance_amount">Amount *</label>
                <input type="number" id="balance_amount" name="amount" class="form-control" step="0.01" placeholder="0.00" required>
                <small class="text-muted">For adjustment, use positive/negative values. Credit/Debit require positive amount.</small>
            </div>
            <div class="form-group">
                <label for="balance_reason">Reason (optional)</label>
                <textarea id="balance_reason" name="reason" class="form-control" placeholder="Optional note shown in transaction description"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1rem;">
                <button type="button" class="btn btn-outline" onclick="closeBalanceModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm</button>
            </div>
        </form>
        <button type="button" class="btn btn-outline pin-cancel-btn" style="position: absolute; top: 0.5rem; right: 0.5rem;" onclick="closeBalanceModal()">&times;</button>
    </div>
</div>

<script>
function openBalanceModal(userId) {
    document.getElementById('balance_user_id').value = userId;
    document.getElementById('balance_action').value = 'credit';
    document.getElementById('balance_amount').value = '';
    document.getElementById('balance_reason').value = '';
    document.getElementById('balanceModal').style.display = 'flex';
}
function closeBalanceModal() {
    document.getElementById('balanceModal').style.display = 'none';
}
</script>

<?php require_once '../includes/footer.php'; ?>
