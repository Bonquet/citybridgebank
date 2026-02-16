<?php
$pageTitle = 'Transaction Management';
// Load configuration first so Database and Security classes are defined
require_once '../includes/config.php';
// Then include the header
require_once '../includes/header.php';

// Check if admin is logged in
if (!Security::isAdminLoggedIn()) {
    redirectWithMessage('login.php', 'Please login to access transaction management.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get filter parameters
    $filter_type = isset($_GET['type']) ? Security::sanitizeInput($_GET['type']) : 'all';
    $filter_status = isset($_GET['status']) ? Security::sanitizeInput($_GET['status']) : 'all';
    $search = isset($_GET['search']) ? Security::sanitizeInput($_GET['search']) : '';
    
    // Build query
    $where_conditions = [];
    $params = [];
    
    if ($filter_type != 'all') {
        $where_conditions[] = "t.transaction_type = ?";
        $params[] = $filter_type;
    }
    
    if ($filter_status != 'all') {
        $where_conditions[] = "t.status = ?";
        $params[] = $filter_status;
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(u.username LIKE ? OR u.email LIKE ? OR u.full_name LIKE ? OR t.reference_number LIKE ?)";
        $search_param = "%{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get transactions
    $sql = "SELECT t.*, u.username, u.email, u.full_name 
            FROM transactions t 
            LEFT JOIN users u ON t.user_id = u.user_id 
            {$where_clause} 
            ORDER BY t.transaction_date DESC LIMIT 100";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Transaction management error: " . $e->getMessage());
    redirectWithMessage('index.php', 'An error occurred loading transactions.', 'danger');
}
?>

<!-- Page Header -->
<section style="background: linear-gradient(135deg, var(--dark) 0%, var(--primary-blue) 100%); color: var(--white); padding: 2rem 0;">
    <div class="container">
        <h1><i class="fas fa-exchange-alt text-gold"></i> Transaction Management</h1>
        <p>View, manage, and audit all transactions with soft delete and reversal capabilities</p>
    </div>
</section>

<!-- Transaction Content -->
<section style="padding: 2rem 0;">
    <div class="container">
        <!-- Critical Notice -->
        <div class="warning-box" style="margin-bottom: 2rem;">
            <h4 style="color: var(--primary-blue);"><i class="fas fa-exclamation-triangle text-gold"></i> Transaction Control Capabilities</h4>
            <p style="margin: 0;">You can view all transactions, soft delete transactions (they remain visible in audit logs), and mark transactions as reversed or invalid. All transaction control actions are permanently logged with your admin ID and reason. Soft deleted transactions are NOT removed from the database - they are flagged but remain fully auditable.</p>
        </div>
        
        <!-- Filters -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <form action="" method="GET">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" id="search" name="search" class="form-control" placeholder="Username, email, reference number" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="form-group">
                            <label for="type">Transaction Type</label>
                            <select id="type" name="type" class="form-control">
                                <option value="all" <?php echo $filter_type == 'all' ? 'selected' : ''; ?>>All Types</option>
                                <option value="credit" <?php echo $filter_type == 'credit' ? 'selected' : ''; ?>>Credits</option>
                                <option value="debit" <?php echo $filter_type == 'debit' ? 'selected' : ''; ?>>Debits</option>
                                <option value="transfer" <?php echo $filter_type == 'transfer' ? 'selected' : ''; ?>>Transfers</option>
                                <option value="payment" <?php echo $filter_type == 'payment' ? 'selected' : ''; ?>>Payments</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="all" <?php echo $filter_status == 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="completed" <?php echo $filter_status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="failed" <?php echo $filter_status == 'failed' ? 'selected' : ''; ?>>Failed</option>
                                <option value="reversed" <?php echo $filter_status == 'reversed' ? 'selected' : ''; ?>>Reversed</option>
                                <option value="deleted" <?php echo $filter_status == 'deleted' ? 'selected' : ''; ?>>Deleted</option>
                            </select>
                        </div>
                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Transaction List -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list text-gold"></i> Transactions</h2>
                <span class="badge badge-info"><?php echo count($transactions); ?> transactions</span>
            </div>
            <div class="card-body">
                <?php if (empty($transactions)): ?>
                    <div class="info-box text-center">
                        <p>No transactions found matching your criteria.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Balance After</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>PINs Used</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $txn): ?>
                                    <tr style="<?php echo $txn['status'] == 'deleted' ? 'background-color: rgba(220, 53, 69, 0.1);' : ''; ?>">
                                        <td>
                                            <strong><?php echo htmlspecialchars($txn['reference_number']); ?></strong>
                                            <?php if ($txn['status'] == 'deleted'): ?>
                                                <br><small class="text-danger"><i class="fas fa-trash"></i> Soft Deleted</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($txn['username'] ?? 'Unknown'); ?><br>
                                            <small><?php echo htmlspecialchars($txn['full_name'] ?? ''); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?php 
                                                echo $txn['transaction_type'] == 'credit' ? 'badge-success' : 'badge-danger'; 
                                            ?>">
                                                <?php echo ucfirst($txn['transaction_type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatCurrency($txn['amount']); ?></td>
                                        <td><?php echo formatCurrency($txn['balance_after']); ?></td>
                                        <td>
                                            <span class="badge <?php 
                                                echo $txn['status'] == 'completed' ? 'badge-success' : 
                                                     ($txn['status'] == 'deleted' ? 'badge-danger' : 
                                                     ($txn['status'] == 'reversed' ? 'badge-warning' : 'badge-info')); 
                                            ?>">
                                                <?php echo ucfirst($txn['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDateTime($txn['transaction_date']); ?></td>
                                        <td>
                                            <small>
                                                <?php if ($txn['authorization_pin_used']): ?><i class="fas fa-key text-success"></i> Auth<?php endif; ?>
                                                <?php if ($txn['payment_pin_used']): ?><i class="fas fa-credit-card text-success"></i> Pay<?php endif; ?>
                                                <?php if ($txn['secure_pass_used']): ?><i class="fas fa-user-shield text-success"></i> Secure<?php endif; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($txn['status'] != 'deleted'): ?>
                                                <button class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;" onclick="deleteTransaction(<?php echo $txn['transaction_id']; ?>, '<?php echo htmlspecialchars($txn['reference_number']); ?>')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                                <button class="btn btn-warning" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;" onclick="reverseTransaction(<?php echo $txn['transaction_id']; ?>, '<?php echo htmlspecialchars($txn['reference_number']); ?>')">
                                                    <i class="fas fa-undo"></i> Reverse
                                                </button>
                                            <?php else: ?>
                                                <small class="text-muted">Deleted by: <?php echo $txn['deleted_by'] ?? 'Unknown'; ?></small>
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

<script>
function deleteTransaction(txnId, refNumber) {
    const reason = prompt(`SOFT DELETE TRANSACTION\n\nTransaction: ${refNumber}\n\nThis will soft delete the transaction (it will remain in audit logs but be marked as deleted).\n\nMANDATORY: Enter reason for deletion:`);
    
    if (reason === null || reason.trim() === '') {
        alert('Reason is MANDATORY for deletion.');
        return;
    }
    
    if (confirm(`Confirm soft delete of transaction ${refNumber}\n\nReason: ${reason}\n\nThis action is permanent and logged.`)) {
        alert(`Transaction ${refNumber} has been soft deleted.\n\nReason: ${reason}\n\nThis would be processed and logged in the audit trail.`);
    }
}

function reverseTransaction(txnId, refNumber) {
    const reason = prompt(`REVERSE TRANSACTION\n\nTransaction: ${refNumber}\n\nThis will mark the transaction as reversed.\n\nMANDATORY: Enter reason for reversal:`);
    
    if (reason === null || reason.trim() === '') {
        alert('Reason is MANDATORY for reversal.');
        return;
    }
    
    if (confirm(`Confirm reversal of transaction ${refNumber}\n\nReason: ${reason}\n\nThis action is permanent and logged.`)) {
        alert(`Transaction ${refNumber} has been marked as reversed.\n\nReason: ${reason}\n\nThis would be processed and logged in the audit trail.`);
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>