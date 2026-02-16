<?php
$pageTitle = 'User Management';
// Load configuration first for database and security functions
require_once '../includes/config.php';
// Then include the header
require_once '../includes/header.php';

// Check if admin is logged in
if (!Security::isAdminLoggedIn()) {
    redirectWithMessage('login.php', 'Please login to access user management.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get filter parameters
    $filter_status = isset($_GET['status']) ? Security::sanitizeInput($_GET['status']) : 'all';
    $filter_kyc = isset($_GET['kyc']) ? Security::sanitizeInput($_GET['kyc']) : 'all';
    $search = isset($_GET['search']) ? Security::sanitizeInput($_GET['search']) : '';
    
    // Build query
    $where_conditions = [];
    $params = [];
    
    if ($filter_status != 'all') {
        $where_conditions[] = "account_status = ?";
        $params[] = $filter_status;
    }
    
    if ($filter_kyc != 'all') {
        $where_conditions[] = "kyc_status = ?";
        $params[] = $filter_kyc;
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(username LIKE ? OR email LIKE ? OR full_name LIKE ? OR account_number LIKE ?)";
        $search_param = "%{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get users
    $stmt = $db->prepare("SELECT * FROM users {$where_clause} ORDER BY created_at DESC LIMIT 100");
    $stmt->execute($params);
    $users = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("User management error: " . $e->getMessage());
    redirectWithMessage('index.php', 'An error occurred loading users.', 'danger');
}
?>

<!-- Page Header -->
<section style="background: linear-gradient(135deg, var(--dark) 0%, var(--primary-blue) 100%); color: var(--white); padding: 2rem 0;">
    <div class="container">
        <h1><i class="fas fa-users text-gold"></i> User Management</h1>
        <p>View and manage all user accounts</p>
    </div>
</section>

<!-- User Management Content -->
<section style="padding: 2rem 0;">
    <div class="container">
        <!-- Filters -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <form action="" method="GET">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="search">Search Users</label>
                            <input type="text" id="search" name="search" class="form-control" placeholder="Username, email, name, or account number" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="form-group">
                            <label for="status">Account Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="all" <?php echo $filter_status == 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="active" <?php echo $filter_status == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="frozen" <?php echo $filter_status == 'frozen' ? 'selected' : ''; ?>>Frozen</option>
                                <option value="closed" <?php echo $filter_status == 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="kyc">KYC Status</label>
                            <select id="kyc" name="kyc" class="form-control">
                                <option value="all" <?php echo $filter_kyc == 'all' ? 'selected' : ''; ?>>All KYC Status</option>
                                <option value="verified" <?php echo $filter_kyc == 'verified' ? 'selected' : ''; ?>>Verified</option>
                                <option value="pending" <?php echo $filter_kyc == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="rejected" <?php echo $filter_kyc == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- User List -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list text-gold"></i> User Accounts</h2>
                <span class="badge badge-info"><?php echo count($users); ?> users found</span>
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
                                    <th>User ID</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Account Number</th>
                                    <th>Balance</th>
                                    <th>Account Status</th>
                                    <th>KYC Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>#<?php echo $user['user_id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['account_number']); ?></td>
                                        <td><?php echo formatCurrency($user['account_balance']); ?></td>
                                        <td>
                                            <span class="badge <?php 
                                                echo $user['account_status'] == 'active' ? 'badge-success' : 
                                                     ($user['account_status'] == 'frozen' ? 'badge-warning' : 'badge-danger'); 
                                            ?>">
                                                <?php echo ucfirst($user['account_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?php 
                                                echo $user['kyc_status'] == 'verified' ? 'badge-success' : 
                                                     ($user['kyc_status'] == 'pending' ? 'badge-warning' : 'badge-danger'); 
                                            ?>">
                                                <?php echo ucfirst($user['kyc_status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($user['created_at']); ?></td>
                                        <td>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;" onclick="openUserModal(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars(addslashes($user['username'])); ?>')">View</button>
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

<!-- User Details Modal -->
<div id="userModal" class="modal-overlay" style="display:none;">
    <div class="modal glass-card" style="max-width: 500px; width: 90%; position: relative;">
        <h3 style="margin-bottom: 1rem;"><i class="fas fa-user" style="margin-right:0.5rem;"></i>User Details</h3>
        <div id="userModalContent">
            <!-- Dynamic content filled via JavaScript -->
            <p><strong>Name:</strong> <span id="modalUserFullName"></span></p>
            <p><strong>Username:</strong> <span id="modalUserUsername"></span></p>
            <p><strong>Email:</strong> <span id="modalUserEmail"></span></p>
            <p><strong>Phone:</strong> <span id="modalUserPhone"></span></p>
            <p><strong>Address:</strong> <span id="modalUserAddress"></span></p>
            <p><strong>Account Number:</strong> <span id="modalUserAccountNumber"></span></p>
            <p><strong>Balance:</strong> <span id="modalUserBalance"></span></p>
            <p><strong>Status:</strong> <span id="modalUserStatus"></span></p>
            <p><strong>KYC:</strong> <span id="modalUserKyc"></span></p>
            <p><strong>Joined:</strong> <span id="modalUserJoined"></span></p>
        </div>
        <div style="margin-top:1.5rem; display:flex; justify-content:flex-end; gap:0.5rem;">
            <a id="managePinsBtn" href="#" class="btn btn-secondary">Manage PINs</a>
            <button type="button" class="btn btn-primary" onclick="closeUserModal()">Close</button>
        </div>
    </div>
</div>

<script>
// Open the user modal and fetch details via AJAX
function openUserModal(userId, username) {
    // Reset modal content
    document.getElementById('modalUserFullName').textContent = 'Loading...';
    document.getElementById('modalUserUsername').textContent = '';
    document.getElementById('modalUserEmail').textContent = '';
    document.getElementById('modalUserPhone').textContent = '';
    document.getElementById('modalUserAddress').textContent = '';
    document.getElementById('modalUserAccountNumber').textContent = '';
    document.getElementById('modalUserBalance').textContent = '';
    document.getElementById('modalUserStatus').textContent = '';
    document.getElementById('modalUserKyc').textContent = '';
    document.getElementById('modalUserJoined').textContent = '';
    // Show modal
    document.getElementById('userModal').style.display = 'flex';
    // Fetch details
    fetch('get_user_details.php?user_id=' + userId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                document.getElementById('modalUserFullName').textContent = user.full_name;
                document.getElementById('modalUserUsername').textContent = user.username;
                document.getElementById('modalUserEmail').textContent = user.email;
                document.getElementById('modalUserPhone').textContent = user.phone || 'N/A';
                document.getElementById('modalUserAddress').textContent = user.address || 'N/A';
                document.getElementById('modalUserAccountNumber').textContent = user.account_number;
                document.getElementById('modalUserBalance').textContent = user.account_balance;
                document.getElementById('modalUserStatus').textContent = user.account_status;
                document.getElementById('modalUserKyc').textContent = user.kyc_status;
                document.getElementById('modalUserJoined').textContent = user.created_at;
                // Set manage pins link
                const manageBtn = document.getElementById('managePinsBtn');
                manageBtn.href = 'manage_pins.php?user_id=' + userId;
                manageBtn.innerHTML = '<i class="fas fa-key"></i> Manage PINs';
            } else {
                document.getElementById('modalUserFullName').textContent = data.message || 'Error loading user';
            }
        })
        .catch(() => {
            document.getElementById('modalUserFullName').textContent = 'Error loading user details.';
        });
}

// Close the user modal
function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}
</script>

<?php require_once '../includes/footer.php'; ?>