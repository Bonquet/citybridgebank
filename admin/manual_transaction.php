<?php
$pageTitle = 'Manual Transaction';
require_once '../includes/config.php';

if (!Security::isAdminLoggedIn()) {
    redirectWithMessage('login.php', 'Please login to access manual transactions.', 'danger');
}

$db = Database::getInstance()->getConnection();
$csrf = Security::generateCSRFToken();

$userStmt = $db->query("SELECT user_id, username, email, account_number, account_balance, account_status FROM users WHERE account_status != 'closed' ORDER BY username ASC LIMIT 500");
$users = $userStmt->fetchAll();

$errors = [];
$successMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security verification failed.';
    }

    $userId = (int)($_POST['user_id'] ?? 0);
    $amount = (float)str_replace(',', '', $_POST['amount'] ?? '0');
    $type = Security::sanitizeInput($_POST['transaction_type'] ?? '');
    $description = Security::sanitizeInput($_POST['description'] ?? 'Manual transaction');

    if ($userId <= 0) {
        $errors[] = 'Please select a valid user.';
    }
    if ($amount <= 0) {
        $errors[] = 'Amount must be greater than zero.';
    }
    if (!in_array($type, ['credit', 'debit'], true)) {
        $errors[] = 'Invalid transaction type.';
    }

    if (empty($errors)) {
        try {
            $db->beginTransaction();

            $stmt = $db->prepare("SELECT user_id, username, account_balance, account_status FROM users WHERE user_id = ? FOR UPDATE");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            if (!$user) {
                throw new RuntimeException('Selected user not found.');
            }
            if ($user['account_status'] !== 'active') {
                throw new RuntimeException('Selected user account is not active.');
            }

            $newBalance = (float)$user['account_balance'];
            if ($type === 'credit') {
                $newBalance += $amount;
            } else {
                if ($amount > $newBalance) {
                    throw new RuntimeException('Insufficient funds to debit.');
                }
                $newBalance -= $amount;
            }

            $stmt = $db->prepare("UPDATE users SET account_balance = ? WHERE user_id = ?");
            $stmt->execute([$newBalance, $user['user_id']]);

            $reference = Security::generateReferenceNumber();
            $stmt = $db->prepare("INSERT INTO transactions (user_id, transaction_type, amount, description, reference_number, status, balance_after, authorization_pin_used, payment_pin_used, secure_pass_used, transaction_date) VALUES (?, ?, ?, ?, ?, 'completed', ?, 0, 0, 0, NOW())");
            $stmt->execute([$user['user_id'], $type, $amount, $description, $reference, $newBalance]);
            $txId = (int)$db->lastInsertId();

            $db->commit();

            Security::logAdminAction($_SESSION['admin_id'], 'manual_' . $type, 'Performed manual ' . $type . ' of ' . $amount . ' for user #' . $userId, $user['user_id']);
            Security::logAudit('manual_transaction', 'Admin created manual ' . $type . ' transaction. Ref: ' . $reference, $user['user_id'], $_SESSION['admin_id'], $txId);
            $successMsg = 'Manual transaction completed successfully. Reference: ' . $reference;
        } catch (Throwable $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            $errors[] = $e->getMessage();
        }
    }
}

require_once '../includes/header.php';
?>
<section style="background: linear-gradient(135deg, var(--dark) 0%, var(--primary-blue) 100%); color: var(--white); padding: 2rem 0;">
    <div class="container">
        <h1><i class="fas fa-plus-circle text-gold"></i> Manual Transaction</h1>
        <p>Create a one-off credit or debit on a user's account</p>
    </div>
</section>

<section style="padding: 2rem 0;">
    <div class="container">
        <?php if (!empty($successMsg)): ?>
            <div class="success-box" style="margin-bottom:1rem;"><p><?php echo htmlspecialchars($successMsg); ?></p></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="warning-box" style="margin-bottom:1rem;"><?php foreach ($errors as $error): ?><p><?php echo htmlspecialchars($error); ?></p><?php endforeach; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header"><h2><i class="fas fa-edit text-gold"></i> New Manual Transaction</h2></div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">

                    <div class="form-group">
                        <label for="userSearch">Search User</label>
                        <input type="text" id="userSearch" class="form-control" placeholder="Search by username, email, or account number">
                    </div>
                    <div class="form-group">
                        <label for="user_id">Select User</label>
                        <select id="user_id" name="user_id" class="form-control" required>
                            <option value="">Select a user</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?php echo (int)$u['user_id']; ?>" data-search="<?php echo htmlspecialchars(strtolower($u['username'] . ' ' . $u['email'] . ' ' . $u['account_number'])); ?>">
                                    <?php echo htmlspecialchars($u['username'] . ' — ' . $u['email'] . ' — ' . $u['account_number']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="transaction_type">Type</label>
                            <select id="transaction_type" name="transaction_type" class="form-control" required>
                                <option value="credit">Credit</option>
                                <option value="debit">Debit</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="number" id="amount" name="amount" class="form-control" min="0.01" step="0.01" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3" placeholder="Enter description (optional)"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Submit Transaction</button>
                    <a href="index.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
const searchInput = document.getElementById('userSearch');
const userSelect = document.getElementById('user_id');
searchInput.addEventListener('input', function() {
    const q = this.value.trim().toLowerCase();
    [...userSelect.options].forEach((opt, idx) => {
        if (idx === 0) return;
        const hay = opt.getAttribute('data-search') || '';
        opt.hidden = q !== '' && !hay.includes(q);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
