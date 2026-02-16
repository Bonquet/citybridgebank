<?php
// Load configuration (database, utility functions)
require_once '../includes/config.php';

// Ensure the user is logged in; redirect to login if not
if (!Security::isLoggedIn()) {
    header('Location: ../public/login.php');
    exit();
}

// Render the header (includes navigation and session start if needed)
require_once '../includes/header.php';
?>

<!-- Dashboard Overview -->
<section class="section" style="padding-top: 8rem;">
    <div class="container">
        <!-- Welcome Section -->
        <div class="glass-card glass-card-gradient" style="margin-bottom: 3rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
                <div>
                    <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">
                        Welcome back, <span style="background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo htmlspecialchars($_SESSION['first_name'] ?? 'User'); ?></span>!
                    </h1>
                    <p style="color: var(--text-secondary);">
                        Here's what's happening with your accounts today.
                    </p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <a href="transfer.php" class="btn btn-primary">
                        <i class="fas fa-paper-plane" style="margin-right: 0.5rem;"></i> Transfer Money
                    </a>
                    <a href="cards.php" class="btn btn-secondary">
                        <i class="fas fa-credit-card" style="margin-right: 0.5rem;"></i> My Cards
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Balance Cards -->
        <?php
        // Fetch current account balance for dynamic display
        try {
            $dbBal = Database::getInstance()->getConnection();
            $stmtBal = $dbBal->prepare("SELECT account_balance FROM users WHERE user_id = ?");
            $stmtBal->execute([$_SESSION['user_id']]);
            $currentBalance = (float)$stmtBal->fetchColumn();
        } catch (PDOException $e) {
            error_log("Balance fetch error: " . $e->getMessage());
            $currentBalance = 0.00;
        }
        ?>
        <!-- Total Balance Card only -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
            <div class="glass-card glass-card-gradient" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(245, 87, 108, 0.2) 100%);">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem;">
                    <div>
                        <p style="color: var(--text-secondary); margin-bottom: 0.5rem;">Current Balance</p>
                        <h2 style="font-size: 2.5rem; margin-bottom: 0;">
                            <?php echo formatCurrency($currentBalance); ?>
                        </h2>
                    </div>
                    <div style="background: var(--gradient-primary); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-wallet" style="font-size: 1.5rem; color: white;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Dashboard Content Grid -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Recent Transactions -->
        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h3 style="font-size: 1.5rem;">
                    <i class="fas fa-exchange-alt" style="margin-right: 0.5rem; color: var(--accent-purple);"></i>
                    Recent Transactions
                </h3>
                <a href="transactions.php" class="btn btn-secondary btn-sm">
                    View All <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
                </a>
            </div>
            <?php
            // Fetch the latest transactions (including pending requests) for this user.
            try {
                $dbRecent = Database::getInstance()->getConnection();
                // Fetch completed/posted transactions
                $stmtRecent = $dbRecent->prepare("SELECT transaction_type, amount, description, transaction_date, status FROM transactions WHERE user_id = ? ORDER BY transaction_date DESC LIMIT 5");
                $stmtRecent->execute([$_SESSION['user_id']]);
                $recentTxns = $stmtRecent->fetchAll();

                // Attempt to fetch pending requests from transaction_requests table
                $pending = [];
                try {
                    $stmtReq = $dbRecent->prepare("SELECT request_id, request_type, amount, description, created_at, status FROM transaction_requests WHERE user_id = ? AND status = 'pending'");
                    $stmtReq->execute([$_SESSION['user_id']]);
                    $pending = $stmtReq->fetchAll();
                } catch (PDOException $inner) {
                    // transaction_requests table might not exist; ignore pending requests in this case
                    $pending = [];
                }

                if (!empty($pending)) {
                    // Merge pending requests into recent transactions
                    foreach ($pending as $req) {
                        $recentTxns[] = [
                            'transaction_type' => ($req['request_type'] === 'deposit' ? 'credit' : 'debit'),
                            'amount'          => $req['amount'],
                            'description'     => $req['description'] ?? ($req['request_type'] . ' request'),
                            'transaction_date'=> $req['created_at'],
                            'status'          => 'pending'
                        ];
                    }
                    // Sort by transaction_date descending
                    usort($recentTxns, function($a, $b) {
                        return strtotime($b['transaction_date']) <=> strtotime($a['transaction_date']);
                    });
                    // Limit to 5 items
                    $recentTxns = array_slice($recentTxns, 0, 5);
                }
            } catch (PDOException $e) {
                $recentTxns = [];
                error_log('Recent transactions fetch error: ' . $e->getMessage());
            }
            ?>
            <?php if (empty($recentTxns)): ?>
                <div class="info-box text-center">
                    <p>No transactions yet.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($recentTxns as $tx): ?>
                        <?php
                            // Determine icon and colors based on type
                            $icon = 'fa-exchange-alt';
                            $iconBg = 'var(--gradient-primary)';
                            $amountColor = 'var(--text-primary)';
                            // Determine icon and color based on transaction type
                            if ($tx['transaction_type'] === 'credit') {
                                $icon = 'fa-arrow-down';
                                $iconBg = 'var(--gradient-success)';
                                $amountColor = '#4facfe';
                            } elseif ($tx['transaction_type'] === 'debit') {
                                $icon = 'fa-arrow-up';
                                $iconBg = 'var(--gradient-warning)';
                                $amountColor = 'var(--accent-pink)';
                            } elseif ($tx['transaction_type'] === 'transfer') {
                                $icon = 'fa-paper-plane';
                                $iconBg = 'var(--gradient-primary)';
                                $amountColor = 'var(--accent-pink)';
                            } elseif ($tx['transaction_type'] === 'payment') {
                                $icon = 'fa-credit-card';
                                $iconBg = 'var(--gradient-warning)';
                                $amountColor = 'var(--accent-pink)';
                            }
                            // Determine sign and style for pending requests
                            $sign = ($tx['transaction_type'] === 'credit') ? '+' : '-';
                            if (isset($tx['status']) && $tx['status'] === 'pending') {
                                // For pending transactions, use a neutral color and no +/- sign
                                $amountColor = 'var(--warning)';
                                $sign = '';
                                // Append pending flag to description
                                $tx['description'] = $tx['description'] . ' (Pending)';
                            }
                            $amountFormatted = formatCurrency($tx['amount']);
                            // Format date/time
                            $dateStr = formatDateTime($tx['transaction_date']);
                        ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; background: var(--bg-glass); border-radius: 12px;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 50px; height: 50px; background: <?php echo $iconBg; ?>; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas <?php echo $icon; ?>" style="color: white;"></i>
                                </div>
                                <div>
                                    <h4 style="margin-bottom: 0.25rem;"><?php echo htmlspecialchars($tx['description']); ?></h4>
                                    <p style="font-size: 0.9rem; color: var(--text-secondary);"><?php echo $dateStr; ?></p>
                                </div>
                            </div>
                            <span style="color: <?php echo $amountColor; ?>; font-weight: 600; font-size: 1.2rem;">
                                <?php echo $sign . $amountFormatted; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
            
            <!-- Quick Actions & Cards -->
            <div style="display: flex; flex-direction: column; gap: 2rem;">
                <!-- Quick Actions -->
                <div class="glass-card">
                    <h3 style="font-size: 1.5rem; margin-bottom: 1.5rem;">
                        <i class="fas fa-bolt" style="margin-right: 0.5rem; color: var(--accent-purple);"></i>
                        Quick Actions
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 1rem;">
                        <a href="transfer.php" class="btn btn-secondary" style="padding: 1.5rem; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-paper-plane" style="font-size: 1.5rem;"></i>
                            <span style="font-size: 0.9rem;">Transfer</span>
                        </a>
                        <a href="withdraw.php" class="btn btn-secondary" style="padding: 1.5rem; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-money-bill-wave" style="font-size: 1.5rem;"></i>
                            <span style="font-size: 0.9rem;">Withdraw</span>
                        </a>
                        <a href="deposit.php" class="btn btn-secondary" style="padding: 1.5rem; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-piggy-bank" style="font-size: 1.5rem;"></i>
                            <span style="font-size: 0.9rem;">Deposit</span>
                        </a>
                        <a href="cards.php" class="btn btn-secondary" style="padding: 1.5rem; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-credit-card" style="font-size: 1.5rem;"></i>
                            <span style="font-size: 0.9rem;">Cards</span>
                        </a>
                        <a href="profile.php" class="btn btn-secondary" style="padding: 1.5rem; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-user" style="font-size: 1.5rem;"></i>
                            <span style="font-size: 0.9rem;">Profile</span>
                        </a>
                        <a href="security.php" class="btn btn-secondary" style="padding: 1.5rem; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-shield-alt" style="font-size: 1.5rem;"></i>
                            <span style="font-size: 0.9rem;">Security</span>
                        </a>
                    </div>
                </div>
                
                <!-- My Card -->
                <div class="glass-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 2rem;">
                        <i class="fas fa-wifi" style="font-size: 1.5rem; color: white; transform: rotate(90deg);"></i>
                        <i class="fab fa-cc-visa" style="font-size: 2.5rem; color: white;"></i>
                    </div>
                    <div style="color: white; font-size: 1.4rem; letter-spacing: 2px; margin-bottom: 1.5rem; font-family: 'Courier New', monospace;">
                        •••• •••• •••• 4589
                    </div>
                    <div style="display: flex; justify-content: space-between; color: white;">
                        <div>
                            <p style="font-size: 0.8rem; opacity: 0.8; margin-bottom: 0.25rem;">CARD HOLDER</p>
                            <p style="font-size: 1rem; font-weight: 600;"><?php echo htmlspecialchars($_SESSION['first_name'] ?? 'USER'); ?> <?php echo htmlspecialchars($_SESSION['last_name'] ?? 'NAME'); ?></p>
                        </div>
                        <div>
                            <p style="font-size: 0.8rem; opacity: 0.8; margin-bottom: 0.25rem;">EXPIRES</p>
                            <p style="font-size: 1rem; font-weight: 600;">12/26</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>