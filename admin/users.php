<?php
require_once '../includes/config.php';
if (!Security::isAdminLoggedIn()) {
    redirectWithMessage('login.php', 'Please login to access user management.', 'danger');
}
require_once '../includes/header.php';

$db = Database::getInstance()->getConnection();
$search = Security::sanitizeInput($_GET['search'] ?? '');
$status = Security::sanitizeInput($_GET['status'] ?? 'all');
$kyc = Security::sanitizeInput($_GET['kyc'] ?? 'all');

$where = [];
$params = [];
if ($status !== 'all') { $where[] = 'account_status = ?'; $params[] = $status; }
if ($kyc !== 'all') { $where[] = 'kyc_status = ?'; $params[] = $kyc; }
if ($search !== '') {
    $where[] = '(username LIKE ? OR email LIKE ? OR full_name LIKE ? OR account_number LIKE ?)';
    $s = "%{$search}%";
    $params = array_merge($params, [$s, $s, $s, $s]);
}
$sql = 'SELECT user_id, full_name, username, email, account_number, account_balance, account_status, kyc_status, created_at FROM users';
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY created_at DESC LIMIT 200';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<section style="padding:2rem 0;">
<div class="container">
    <h1 style="margin-bottom:1rem;"><i class="fas fa-users"></i> User Management</h1>
    <form method="GET" class="card" style="margin-bottom:1rem;">
        <div class="card-body" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:1rem;">
            <input class="form-control" type="text" name="search" placeholder="Search" value="<?php echo htmlspecialchars($search); ?>">
            <select name="status" class="form-control"><option value="all">All Status</option><option value="active" <?php echo $status==='active'?'selected':''; ?>>Active</option><option value="frozen" <?php echo $status==='frozen'?'selected':''; ?>>Frozen</option><option value="closed" <?php echo $status==='closed'?'selected':''; ?>>Closed</option></select>
            <select name="kyc" class="form-control"><option value="all">All KYC</option><option value="none" <?php echo $kyc==='none'?'selected':''; ?>>None</option><option value="pending" <?php echo $kyc==='pending'?'selected':''; ?>>Pending</option><option value="verified" <?php echo $kyc==='verified'?'selected':''; ?>>Verified</option><option value="rejected" <?php echo $kyc==='rejected'?'selected':''; ?>>Rejected</option></select>
            <button class="btn btn-primary" type="submit">Apply</button>
        </div>
    </form>

    <div class="card"><div class="card-body">
    <div class="table-responsive"><table class="table">
        <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Account #</th><th>Balance</th><th>Status</th><th>KYC</th><th>Joined</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                <td><?php echo htmlspecialchars($u['username']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><?php echo htmlspecialchars($u['account_number']); ?></td>
                <td><?php echo formatCurrency($u['account_balance']); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($u['account_status'])); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($u['kyc_status'])); ?></td>
                <td><?php echo formatDate($u['created_at']); ?></td>
                <td><button type="button" class="btn btn-secondary btn-sm" onclick="openUserModal(<?php echo (int)$u['user_id']; ?>)">View</button></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div></div></div>
</div>
</section>

<div id="userModal" class="modal-overlay" style="display:none;">
  <div class="modal glass-card" style="max-width:560px;width:90%;">
    <h3>User Details</h3>
    <div id="userModalMessage" class="alert alert-error" style="display:none;"></div>
    <div id="userModalBody">
      <p><strong>Name:</strong> <span id="f_name"></span></p>
      <p><strong>Username:</strong> <span id="f_username"></span></p>
      <p><strong>Email:</strong> <span id="f_email"></span></p>
      <p><strong>Phone:</strong> <span id="f_phone"></span></p>
      <p><strong>Address:</strong> <span id="f_address"></span></p>
      <p><strong>Account Number:</strong> <span id="f_acct"></span></p>
      <p><strong>Balance:</strong> <span id="f_bal"></span></p>
      <p><strong>Status:</strong> <span id="f_status"></span></p>
      <p><strong>KYC:</strong> <span id="f_kyc"></span></p>
      <p><strong>Joined:</strong> <span id="f_joined"></span></p>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:.5rem;">
      <a id="managePinsBtn" class="btn btn-secondary" href="#">Manage PINs</a>
      <button type="button" class="btn btn-primary" onclick="closeUserModal()">Close</button>
    </div>
  </div>
</div>

<script>
function closeUserModal(){ document.getElementById('userModal').style.display='none'; }
function setUserField(id, value){ document.getElementById(id).textContent = value || 'N/A'; }
async function openUserModal(userId){
  document.getElementById('userModal').style.display='flex';
  document.getElementById('userModalMessage').style.display='none';
  setUserField('f_name','Loading...');
  try {
    const r = await fetch('get_user_details.php?user_id='+encodeURIComponent(userId));
    const data = await r.json();
    if(!data.success){
      document.getElementById('userModalMessage').textContent = data.message || 'User not found.';
      document.getElementById('userModalMessage').style.display='block';
      ['f_username','f_email','f_phone','f_address','f_acct','f_bal','f_status','f_kyc','f_joined'].forEach(id=>setUserField(id,''));
      setUserField('f_name','User not found.');
      return;
    }
    const u = data.user;
    setUserField('f_name',u.full_name); setUserField('f_username',u.username); setUserField('f_email',u.email);
    setUserField('f_phone',u.phone); setUserField('f_address',u.address); setUserField('f_acct',u.account_number);
    setUserField('f_bal',u.account_balance); setUserField('f_status',u.account_status); setUserField('f_kyc',u.kyc_status);
    setUserField('f_joined',u.created_at);
    document.getElementById('managePinsBtn').href = 'manage_pins.php?user_id='+encodeURIComponent(u.user_id);
  } catch(e){
    document.getElementById('userModalMessage').textContent = 'Error loading user details.';
    document.getElementById('userModalMessage').style.display='block';
    setUserField('f_name','User not found.');
  }
}
</script>
<?php require_once '../includes/footer.php'; ?>
