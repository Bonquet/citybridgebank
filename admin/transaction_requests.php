<?php
// Admin interface for managing deposit and withdrawal requests
// Load configuration to access Database and Security classes
require_once '../includes/config.php';
require_once '../includes/header.php';

// Only allow admins to access this page
if (!Security::isAdminLoggedIn()) {
    redirectWithMessage('login.php', 'Please login to access transaction requests.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();

    // Fetch pending and rejected requests (the admin may review rejected ones too)
    $status_filter = isset($_GET['status']) ? Security::sanitizeInput($_GET['status']) : 'pending';
    $where = '';
    $params = [];
    if (in_array($status_filter, ['pending','approved','rejected'])) {
        $where = 'WHERE tr.status = ?';
        $params[] = $status_filter;
    }

    $sql = "SELECT tr.*, u.username, u.full_name FROM transaction_requests tr
            JOIN users u ON tr.user_id = u.user_id
            {$where}
            ORDER BY tr.created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Transaction request management error: ' . $e->getMessage());
    redirectWithMessage('index.php', 'An error occurred while loading transaction requests.', 'danger');
}
?>

<!-- Page Header -->
<section style="background: linear-gradient(135deg, var(--dark) 0%, var(--primary-blue) 100%); color: var(--white); padding: 2rem 0;">
    <div class="container">
        <h1><i class="fas fa-hourglass-half text-gold"></i> Transaction Requests</h1>
        <p>Review and approve or reject pending deposit and withdrawal requests</p>
    </div>
</section>

<!-- Request Content -->
<section style="padding: 2rem 0;">
    <div class="container">
        <!-- Filter -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <form action="" method="GET" class="form-inline">
                    <label for="status" style="margin-right: 0.5rem;">Filter by Status:</label>
                    <select id="status" name="status" class="form-control" onchange="this.form.submit()">
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Requests List -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list text-gold"></i> <?php echo ucfirst($status_filter); ?> Requests</h2>
                <span class="badge badge-info"><?php echo count($requests); ?> requests</span>
            </div>
            <div class="card-body">
                <?php if (empty($requests)): ?>
                    <div class="info-box text-center">
                        <p>No transaction requests found.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Block Statuses</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $req): ?>
                                    <tr>
                                        <td><?php echo $req['request_id']; ?></td>
                                        <td><?php echo htmlspecialchars($req['username']); ?><br><small><?php echo htmlspecialchars($req['full_name']); ?></small></td>
                                        <td><span class="badge <?php echo $req['request_type'] == 'deposit' ? 'badge-success' : 'badge-warning'; ?>"><?php echo ucfirst($req['request_type']); ?></span></td>
                                        <td><?php echo formatCurrency($req['amount']); ?></td>
                                        <td><?php echo htmlspecialchars($req['description']); ?></td>
                                        <td><span class="badge <?php 
                                            if ($req['status'] == 'approved') echo 'badge-success';
                                            elseif ($req['status'] == 'rejected') echo 'badge-danger';
                                            else echo 'badge-warning';
                                        ?>"><?php echo ucfirst($req['status']); ?></span></td>
                                        <td><?php echo formatDateTime($req['created_at']); ?></td>
                                        <td>
                                            <?php if ($req['request_type'] == 'withdrawal'): ?>
                                                <small>
                                                    B1: <?php echo ucfirst($req['block1_status']); ?>,
                                                    B2: <?php echo ucfirst($req['block2_status']); ?>,
                                                    B3: <?php echo ucfirst($req['block3_status']); ?>
                                                </small>
                                            <?php else: ?>
                                                <small>N/A</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($req['status'] == 'pending'): ?>
                                                <form method="POST" action="process_request.php" style="display:inline-block;">
                                                    <input type="hidden" name="request_id" value="<?php echo $req['request_id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Approve this request?');">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>
                                                <form method="POST" action="process_request.php" style="display:inline-block; margin-left:0.5rem;">
                                                    <input type="hidden" name="request_id" value="<?php echo $req['request_id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <input type="text" name="reject_reason" placeholder="Reason" required style="width:120px; font-size:0.75rem; margin-right:0.25rem;">
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Reject this request?');">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <small>Processed</small>
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