<?php
// CITYBRIDGEBANK Deposit Funds Page
// This page allows a logged in user to deposit funds via bank deposit or crypto.
// Title for the deposit page
$pageTitle = 'Deposit Funds';
// Load configuration (Database, Security, constants) before rendering anything
require_once '../includes/config.php';

// Ensure user is logged in before any output
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login to deposit funds.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    // Retrieve user account status and balance
    $stmt = $db->prepare("SELECT account_balance, account_status FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Only active accounts can deposit
    if ($user['account_status'] !== 'active') {
        redirectWithMessage('index.php', 'Your account is not active. Deposits are not available.', 'danger');
    }
} catch (PDOException $e) {
    error_log('Deposit page error: ' . $e->getMessage());
    redirectWithMessage('index.php', 'An error occurred.', 'danger');
}

// Generate CSRF token used by the deposit form
$csrf_token = Security::generateCSRFToken();

// After all checks, render the header
require_once '../includes/header.php';
?>

<!-- Deposit Page Header -->
<section class="hero">
    <div class="container">
        <h1>Deposit Funds</h1>
        <p>Add money to your account via bank deposit or crypto</p>
    </div>
</section>

<!-- Deposit Content -->
<section style="padding: 2rem 0;">
    <div class="container">
        <!-- Current Balance -->
        <div class="glass-card" style="margin-bottom: 2rem;">
            <div class="text-center" style="padding: 1rem;">
                <h3 style="color: var(--text-muted); margin-bottom: 0.5rem;">Current Balance</h3>
                <p style="font-size: 3rem; color: var(--accent-blue); font-weight: 700;">
                    <?php echo formatCurrency($user['account_balance']); ?>
                </p>
            </div>
        </div>
        <!-- Deposit Form -->
        <div class="glass-card">
            <div style="margin-bottom: 1.5rem;">
                <h2 style="display: flex; align-items: center; gap: 0.5rem; font-size: 1.5rem;">
                    <i class="fas fa-piggy-bank" style="color: var(--accent-gold);"></i> New Deposit
                </h2>
            </div>
            <form action="deposit_process.php" method="POST" id="depositForm" style="margin-top: 1rem;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="alert alert-info" style="margin-bottom:1rem;">All deposits are subject to admin approval and will appear as pending until processed.</div>
                <!-- Deposit Method -->
                <div class="form-group">
                    <label for="deposit_method">Deposit Method *</label>
                    <select id="deposit_method" name="deposit_method" class="form-control" required>
                        <option value="bank">Bank Deposit</option>
                        <option value="crypto">Crypto (Bitcoin)</option>
                    </select>
                    <small class="text-muted">Choose how you want to deposit funds</small>
                </div>
                <!-- Amount -->
                <div class="form-group">
                    <label for="deposit_amount">Amount *</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);">$</span>
                        <input type="number" id="deposit_amount" name="amount" class="form-control" required placeholder="0.00" min="1" step="0.01" style="padding-left: 2rem;">
                    </div>
                    <small class="text-muted">Enter the amount you wish to deposit</small>
                </div>
                <!-- Crypto Instructions -->
                <div class="form-group" id="crypto-instructions" style="display: none;">
                    <label>Bitcoin Address</label>
                    <div style="display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; margin-bottom:0.5rem;">
                        <p style="margin:0;">
                            Send Bitcoin to <strong id="btcAddress">bc1qz4usm25a56plnf544vjeuvqz53a7l0huqdk9fr</strong>
                        </p>
                        <button type="button" id="copyBtcBtn" class="btn btn-secondary btn-sm">Copy Address</button>
                    </div>
                    <p id="copyBtcFeedback" style="margin-bottom:0.5rem; font-size:0.9rem; color: var(--text-muted);"></p>
                    <p style="margin-bottom: 0.5rem; font-size: 0.9rem; color: var(--text-muted);">
                        After sending the BTC, please provide the transaction ID or reference in the description field below to help us credit your deposit quickly.
                    </p>
                </div>
                <!-- Description -->
                <div class="form-group">
                    <label for="description">Description (Optional)</label>
                    <input type="text" id="description" name="description" class="form-control" placeholder="Reference or notes for this deposit">
                </div>
                <div class="form-group" style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Deposit Funds</button>
                </div>
                <div class="text-center">
                    <a href="index.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
// Toggle crypto instructions based on deposit method selection
document.getElementById('deposit_method').addEventListener('change', function() {
    const cryptoDiv = document.getElementById('crypto-instructions');
    if (this.value === 'crypto') {
        cryptoDiv.style.display = '';
    } else {
        cryptoDiv.style.display = 'none';
    }
});

const copyBtn = document.getElementById('copyBtcBtn');
if (copyBtn) {
    copyBtn.addEventListener('click', async function() {
        const addr = document.getElementById('btcAddress').textContent.trim();
        const feedback = document.getElementById('copyBtcFeedback');
        try {
            await navigator.clipboard.writeText(addr);
            feedback.textContent = 'Copied';
            feedback.style.color = 'var(--success)';
        } catch (err) {
            feedback.textContent = 'Copy failed';
            feedback.style.color = 'var(--danger)';
        }
    });
}

</script>

<?php require_once '../includes/footer.php'; ?>