<?php
// Admin manual transaction interface
// Load configuration first (provides Database and Security)
require_once '../includes/config.php';
// Then render the header
require_once '../includes/header.php';

if (!Security::isAdminLoggedIn()) {
    redirectWithMessage('login.php', 'Please login as admin to perform manual transactions.', 'danger');
}

// Handle form submission
$errors = [];
$successMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = Security::sanitizeInput($_POST['username'] ?? '');
    $amountRaw = str_replace(',', '', $_POST['amount'] ?? '0');
    $amount = floatval($amountRaw);
    $type = $_POST['transaction_type'] ?? '';
    $description = Security::sanitizeInput($_POST['description'] ?? 'Manual transaction');
    
    // Validate inputs
    if (empty($username)) {
        $errors[] = 'Username is required.';
    }
    if ($amount <= 0) {
        $errors[] = 'Amount must be greater than zero.';
    }
    if (!in_array($type, ['credit','debit'])) {
        $errors[] = 'Invalid transaction type.';
    }
    
    if (empty($errors)) {
        try {
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();
            
            // Find user
            $stmt = $db->prepare("SELECT user_id, account_balance FROM users WHERE username = ? FOR UPDATE");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            if (!$user) {
                throw new Exception('User not found.');
            }
            
            $newBalance = $user['account_balance'];
            if ($type === 'credit') {
                $newBalance += $amount;
            } else {
                if ($amount > $newBalance) {
                    throw new Exception('Insufficient funds to debit.');
                }
                $newBalance -= $amount;
            }
            
            // Update balance
            $stmt = $db->prepare("UPDATE users SET account_balance = ? WHERE user_id = ?");
            $stmt->execute([$newBalance, $user['user_id']]);
            
            // Create transaction record
            $reference = Security::generateReferenceNumber();
            $stmt = $db->prepare("INSERT INTO transactions (user_id, transaction_type, amount, description, reference_number, status, balance_after, authorization_pin_used, payment_pin_used, secure_pass_used, transaction_date) VALUES (?, ?, ?, ?, ?, 'completed', ?, 0, 0, 0, NOW())");
            $stmt->execute([
                $user['user_id'],
                $type,
                $amount,
                $description,
                $reference,
                $newBalance
            ]);
            
            // Commit
            $db->commit();
            
            // Log admin action
            Security::logAdminAction($_SESSION['admin_id'], 'manual_' . $type, 'Performed manual ' . $type . ' of ' . $amount . ' for user ' . $username, $user['user_id']);
            
            $successMsg = 'Manual transaction completed successfully. Reference: ' . $reference;
        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            $errors[] = $e->getMessage();
        }
    }
}
?>

<!-- Page Header -->
<section style="background: linear-gradient(135deg, var(--dark) 0%, var(--primary-blue) 100%); color: var(--white); padding: 2rem 0;">
    <div class="container">
        <h1><i class="fas fa-plus-circle text-gold"></i> Manual Transaction</h1>
        <p>Create a one‑off credit or debit on a user's account</p>
    </div>
</section>

<section style="padding: 2rem 0;">
    <div class="container">
        <?php if (!empty($successMsg)): ?>
            <div class="success-box" style="margin-bottom:1rem;">
                <p><?php echo htmlspecialchars($successMsg); ?></p>
            </div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="warning-box" style="margin-bottom:1rem;">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-edit text-gold"></i> New Manual Transaction</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" class="form-control" placeholder="Enter user's username" required>
                        </div>
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
                    <div class="form-row">
                        <div class="form-group" style="flex:1;">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3" placeholder="Enter description (optional)"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Submit Transaction</button>
                    <a href="index.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>