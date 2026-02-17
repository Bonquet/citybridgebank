<?php
// Set page title for Transaction History
$pageTitle = 'Transaction History';
// Load configuration before header so Database and Security classes are defined
require_once '../includes/config.php';

// Check if user is logged in before output
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login to access your transactions.', 'danger');
}

// After authentication, render the header so navigation and assets are loaded
require_once '../includes/header.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get filter parameters
    $filter_type = isset($_GET['type']) ? Security::sanitizeInput($_GET['type']) : 'all';
    $filter_status = isset($_GET['status']) ? Security::sanitizeInput($_GET['status']) : 'all';
    
    // Build query
    $where_conditions = ["user_id = ?"];
    $params = [$_SESSION['user_id']];
    
    if ($filter_type != 'all') {
        $where_conditions[] = "transaction_type = ?";
        $params[] = $filter_type;
    }
    
    if ($filter_status != 'all') {
        $where_conditions[] = "status = ?";
        $params[] = $filter_status;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get completed/processed transactions for this user. These include all
    // transactions that have already been posted to the ledger. Note that
    // pending deposit/withdrawal requests are stored in the transaction_requests
    // table and are merged below.
    $stmt = $db->prepare("SELECT * FROM transactions WHERE {$where_clause} ORDER BY transaction_date DESC LIMIT 100");
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();

    // Fetch pending requests only if transaction_requests table exists
    $pendingRequests = [];
    try {
        $reqStmt = $db->prepare("SELECT request_id, request_type, amount, description, created_at, status FROM transaction_requests WHERE user_id = ? AND status = 'pending'");
        $reqStmt->execute([$_SESSION['user_id']]);
        $pendingRequests = $reqStmt->fetchAll();
    } catch (PDOException $inner) {
        $pendingRequests = [];
    }

    // Merge pending requests into the transactions array when appropriate
    if (!empty($pendingRequests)) {
        // Only show pending requests if the status filter is 'all' or 'pending'
        if ($filter_status === 'all' || $filter_status === 'pending') {
            // Fetch current balance once to use for pending requests' balance_after display
            $currentBal = 0;
            try {
                $balanceStmt = $db->prepare("SELECT account_balance FROM users WHERE user_id = ?");
                $balanceStmt->execute([$_SESSION['user_id']]);
                $currentBal = (float)$balanceStmt->fetchColumn();
            } catch (PDOException $cb) {
                $currentBal = 0;
            }

            foreach ($pendingRequests as $req) {
                $transactions[] = [
                    'transaction_date'   => $req['created_at'],
                    'reference_number'   => 'REQ' . $req['request_id'],
                    'description'        => $req['description'] ?? ($req['request_type'] . ' request'),
                    // Map request types to transaction types for display purposes
                    'transaction_type'   => ($req['request_type'] === 'deposit' ? 'credit' : 'debit'),
                    'amount'             => $req['amount'],
                    'balance_after'      => $currentBal,
                    'status'             => 'pending',
                    // Additional flags: mark all PIN flags as false for pending requests
                    'authorization_pin_used' => 0,
                    'payment_pin_used'       => 0,
                    'secure_pass_used'       => 0
                ];
            }
        }
        // Sort the combined array by transaction_date descending
        usort($transactions, function($a, $b) {
            return strtotime($b['transaction_date']) <=> strtotime($a['transaction_date']);
        });
    }
    
    // Get transaction summary
    $stmt = $db->prepare("SELECT 
        COUNT(*) as total,
        COALESCE(SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE 0 END), 0) as total_credits,
        COALESCE(SUM(CASE WHEN transaction_type = 'debit' THEN amount ELSE 0 END), 0) as total_debits
        FROM transactions WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $summary = $stmt->fetch();
    
} catch (PDOException $e) {
    error_log("Transaction history error: " . $e->getMessage());
    redirectWithMessage('index.php', 'An error occurred loading transactions.', 'danger');
}
?>

<!-- Page Header -->
<section style="background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%); color: var(--white); padding: 2rem 0;">
    <div class="container">
        <h1><i class="fas fa-history text-gold"></i> Transaction History</h1>
        <p>View and manage your account transactions</p>
    </div>
</section>

<!-- Transaction Content -->
<section style="padding: 2rem 0;">
    <div class="container">
        <!-- Transaction Summary -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3><?php echo $summary['total']; ?></h3>
                <p>Total Transactions</p>
            </div>
            <div class="stat-card">
                <h3><?php echo formatCurrency($summary['total_credits']); ?></h3>
                <p>Total Credits</p>
            </div>
            <div class="stat-card">
                <h3><?php echo formatCurrency($summary['total_debits']); ?></h3>
                <p>Total Debits</p>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <form action="" method="GET">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="type">Filter by Type</label>
                            <select id="type" name="type" class="form-control">
                                <option value="all" <?php echo $filter_type == 'all' ? 'selected' : ''; ?>>All Types</option>
                                <option value="credit" <?php echo $filter_type == 'credit' ? 'selected' : ''; ?>>Credits</option>
                                <option value="debit" <?php echo $filter_type == 'debit' ? 'selected' : ''; ?>>Debits</option>
                                <option value="transfer" <?php echo $filter_type == 'transfer' ? 'selected' : ''; ?>>Transfers</option>
                                <option value="payment" <?php echo $filter_type == 'payment' ? 'selected' : ''; ?>>Payments</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status">Filter by Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="all" <?php echo $filter_status == 'all' ? 'selected' : ''; ?>>All Statuses</option>
                                <option value="completed" <?php echo $filter_status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="failed" <?php echo $filter_status == 'failed' ? 'selected' : ''; ?>>Failed</option>
                                <option value="reversed" <?php echo $filter_status == 'reversed' ? 'selected' : ''; ?>>Reversed</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="transactions.php" class="btn btn-outline">Clear Filters</a>
                </form>
            </div>
        </div>
        
        <!-- Transactions Table -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list text-gold"></i> Transactions</h2>
            </div>
            <div class="card-body">
                <?php if (empty($transactions)): ?>
                    <div class="info-box text-center">
                        <p>No transactions yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Reference</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Balance After</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?php echo formatDateTime($transaction['transaction_date']); ?></td>
                                        <td style="font-family: monospace;"><?php echo htmlspecialchars($transaction['reference_number']); ?></td>
                                        <td><?php echo htmlspecialchars(preg_replace('/^Admin\s+/i', '', (string)$transaction['description'])); ?></td>
                                        <td>
                                            <span class="badge <?php echo $transaction['transaction_type'] == 'credit' ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo ucfirst($transaction['transaction_type']); ?>
                                            </span>
                                        </td>
                                        <td style="color: <?php echo $transaction['transaction_type'] == 'credit' ? 'var(--success)' : 'var(--danger)'; ?>; font-weight: 600;">
                                            <?php echo $transaction['transaction_type'] == 'credit' ? '+' : '-'; ?><?php echo formatCurrency($transaction['amount']); ?>
                                        </td>
                                        <td><?php echo formatCurrency($transaction['balance_after']); ?></td>
                                        <td>
                                            <?php 
                                            $statusClass = 'badge-info';
                                            if ($transaction['status'] == 'completed') $statusClass = 'badge-success';
                                            elseif ($transaction['status'] == 'failed') $statusClass = 'badge-danger';
                                            elseif ($transaction['status'] == 'pending') $statusClass = 'badge-warning';
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>">
                                                <?php echo ucfirst($transaction['status']); ?>
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
        
        <!-- Back to Dashboard -->
        <div style="text-align: center; margin-top: 2rem;">
            <a href="index.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>