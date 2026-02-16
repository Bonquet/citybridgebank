<?php
// Title for withdrawal page
$pageTitle = 'Withdraw Funds';
// Load configuration first (Database and Security classes) before rendering the page
require_once '../includes/config.php';

// Ensure the user is logged in before any output
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login to withdraw funds.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get user balance and account status
    $stmt = $db->prepare("SELECT account_balance, account_status FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    // Check if user can withdraw
    if ($user['account_status'] !== 'active') {
        redirectWithMessage('index.php', 'Your account is not active. Withdrawals are not available.', 'danger');
    }
    
    // Ensure all three pins are set and active before allowing withdrawals
    $stmt = $db->prepare("SELECT COUNT(*) AS pin_count FROM user_pins WHERE user_id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['user_id']]);
    $pin_count = $stmt->fetch()['pin_count'];
    
    if ($pin_count < 3) {
        redirectWithMessage('setup_pins.php', 'Please complete your PIN setup before making withdrawals.', 'warning');
    }
} catch (PDOException $e) {
    error_log('Withdrawal error: ' . $e->getMessage());
    redirectWithMessage('index.php', 'An error occurred.', 'danger');
}

// Determine if the user has insufficient funds for withdrawals
$availableBalance = isset($user['account_balance']) ? $user['account_balance'] : 0;
$insufficientFunds = ($availableBalance < MIN_TRANSFER_AMOUNT);

// Generate CSRF token used by the withdrawal form
$csrf_token = Security::generateCSRFToken();

// Now that checks are complete, render the header
require_once '../includes/header.php';
?>

<!-- Page Header -->
<section class="hero">
    <div class="container">
        <h1>Withdraw Funds</h1>
        <p>Withdraw money from your account securely</p>
    </div>
</section>

<!-- Withdrawal Content -->
<section style="padding: 2rem 0;">
    <div class="container">
        <!-- Current Balance -->
        <div class="glass-card" style="margin-bottom: 2rem;">
            <div class="text-center" style="padding: 1rem;">
                <h3 style="color: var(--text-muted); margin-bottom: 0.5rem;">Available Balance</h3>
                <p style="font-size: 3rem; color: var(--accent-blue); font-weight: 700;">
                    <?php echo formatCurrency($user['account_balance']); ?>
                </p>
            </div>
        </div>

        <!-- Show insufficient funds warning -->
        <?php if ($insufficientFunds): ?>
        <div class="alert alert-warning" style="margin-bottom: 1.5rem;">
            <i class="fas fa-info-circle"></i>
            Your available balance is below the minimum withdrawal amount (<?php echo formatCurrency(MIN_TRANSFER_AMOUNT); ?>). Please make a deposit before attempting a withdrawal.
        </div>
        <?php endif; ?>
        
        <!-- Withdrawal Form -->
        <div class="glass-card">
            <!-- Form Header -->
            <div style="margin-bottom: 1.5rem;">
                <h2 style="display: flex; align-items: center; gap: 0.5rem; font-size: 1.5rem;">
                    <i class="fas fa-money-bill-wave" style="color: var(--accent-gold);"></i> New Withdrawal
                </h2>
            </div>
            <div>
                <div class="warning-box" style="margin-bottom: 2rem; padding: 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                    <h4 style="color: var(--accent-blue); margin-bottom: 0.5rem;">
                        <i class="fas fa-shield-alt" style="color: var(--accent-gold); margin-right: 0.5rem;"></i> Secure Withdrawal
                    </h4>
                    <p style="margin: 0; color: var(--text-muted);">This withdrawal is protected by our advanced multi-layer authentication system. You will be prompted to enter your security credentials to complete the transaction.</p>
                </div>
                
                <form action="withdraw_process.php" method="POST" id="withdrawForm" style="margin-top: 1rem;">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="alert alert-info" style="margin-bottom:1rem;">All withdrawals are subject to admin approval. Your withdrawal request will remain pending until approved.</div>
                    
                    <!-- Withdrawal Details -->
                    <h3 style="color: var(--accent-blue); margin-bottom: 1rem;">Withdrawal Details</h3>
                    
                    <div class="form-group">
                        <label for="recipient_account">Recipient Account Number *</label>
                        <input type="text" id="recipient_account" name="recipient_account" class="form-control" required placeholder="Enter account number" maxlength="20">
                    </div>
                    
                    <!-- Bank Selection -->
                    <div class="form-group" id="bank-group">
                        <label for="recipient_bank">Recipient Bank *</label>
                        <select id="recipient_bank" name="recipient_bank" class="form-control" required>
                            <?php
                            $banks = [
                                'Bank of America', 'JPMorgan Chase', 'Wells Fargo', 'Citibank',
                                'U.S. Bank', 'PNC Bank', 'Capital One', 'Truist (SunTrust/BB&T)',
                                'TD Bank', 'Fifth Third Bank', 'KeyBank', 'Regions Bank', 'Ally Bank',
                                'BBVA USA', 'Santander Bank', 'M&T Bank', 'Huntington Bank', 'BMO Harris',
                                'Charles Schwab Bank', 'HSBC'
                            ];
                            foreach ($banks as $bank): ?>
                                <option value="<?php echo htmlspecialchars($bank); ?>"><?php echo htmlspecialchars($bank); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Select the bank where funds will be sent</small>
                    </div>
                    
                    <?php
                    // Dynamically set the minimum and maximum withdrawal values based on the
                    // available balance. When the balance is below the configured minimum,
                    // set both the min and max to the available balance to prevent the
                    // HTML input from throwing a range error (min > max).
                    $availableBalance = $user['account_balance'];
                    $minAmount = ($availableBalance >= MIN_TRANSFER_AMOUNT) ? MIN_TRANSFER_AMOUNT : 0;
                    $maxAmount = $availableBalance;
                    ?>
                    <div class="form-group">
                        <label for="amount">Amount *</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);">$</span>
                            <input type="number" id="amount" name="amount" class="form-control" required placeholder="0.00" min="<?php echo $minAmount; ?>" max="<?php echo $maxAmount; ?>" step="0.01" style="padding-left: 2rem;">
                        </div>
                        <small class="text-muted">
                            Minimum: <?php echo formatCurrency($minAmount); ?> |
                            Maximum: <?php echo $availableBalance > 0 ? formatCurrency($availableBalance) : formatCurrency(0); ?>
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description (Optional)</label>
                        <input type="text" id="description" name="description" class="form-control" placeholder="ATM withdrawal">
                    </div>
                    
                    <!-- Security Verification -->
                    <h3 style="color: var(--accent-blue); margin-bottom: 1rem; margin-top: 2rem;">Security Verification</h3>
                    <p style="margin-bottom: 1rem; color: var(--text-muted);">For your security, please enter all three of your authentication PINs to complete this withdrawal.</p>
                    
                    <!-- Hidden fields to hold verified PINs. These will be populated by the PIN modal. -->
                    <input type="hidden" id="authorization_pin" name="authorization_pin" value="">
                    <input type="hidden" id="payment_pin" name="payment_pin" value="">
                    <input type="hidden" id="secure_pass_pin" name="secure_pass_pin" value="">

                    <!-- Transaction Summary -->
                    <div class="info-box" style="margin-top: 2rem; padding: 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                        <h4 style="color: var(--accent-blue); margin-bottom: 0.5rem;">Withdrawal Summary</h4>
                        <p><strong>Recipient:</strong> <span id="summary_recipient">-</span></p>
                        <p><strong>Amount:</strong> <span id="summary_amount">-</span></p>
                        <p><strong>Fees:</strong> <span id="summary_fees">$0.00</span></p>
                        <p><strong>Total:</strong> <span id="summary_total">-</span></p>
                    </div>

                    <!-- Initiate multi-step PIN verification -->
                    <div class="form-group" style="margin-top: 2rem;">
                        <button type="button" id="startVerificationBtn" class="btn btn-primary" style="width: 100%;" <?php echo $insufficientFunds ? 'disabled' : ''; ?>>
                            Start Withdrawal Verification
                        </button>
                    </div>

                    <!-- Cancel button remains available -->
                    <div class="text-center">
                        <a href="index.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Withdrawal Limits -->
        <div class="glass-card" style="margin-top: 2rem;">
            <div>
                <h4 style="color: var(--accent-blue); margin-bottom: 1rem;">
                    <i class="fas fa-info-circle" style="color: var(--accent-gold); margin-right: 0.5rem;"></i> Withdrawal Limits
                </h4>
                <ul style="line-height: 2; color: var(--text-muted);">
                    <li><strong>Minimum Withdrawal:</strong> <?php echo formatCurrency(MIN_TRANSFER_AMOUNT); ?></li>
                    <li><strong>Maximum Single Withdrawal:</strong> <?php echo formatCurrency(MAX_SINGLE_TRANSFER); ?></li>
                    <li><strong>Maximum Daily Withdrawals:</strong> <?php echo formatCurrency(MAX_DAILY_TRANSFER); ?></li>
                    <li><strong>Processing Time:</strong> 1-3 business days depending on your bank</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- PIN Verification Modal -->
<div id="pinModal" class="pin-modal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); align-items: center; justify-content: center; z-index: 10000;">
    <div class="pin-modal-content glass-card" style="width: 350px; padding: 1.5rem; position: relative;">
        <h3 style="margin-bottom: 1rem; color: var(--accent-blue);">Verify Withdrawal</h3>
        <!-- Step indicator -->
        <ul class="pin-step-indicator" style="display:flex; gap:0.5rem; margin-bottom: 1rem; list-style: none; padding:0;">
            <li style="flex:1; text-align:center;" class="active">Step 1</li>
            <li style="flex:1; text-align:center;">Step 2</li>
            <li style="flex:1; text-align:center;">Step 3</li>
        </ul>
        <!-- Step 1: Authorization PIN -->
        <div class="pin-step-container" id="pin-step-1" style="display:none;">
            <label for="pin_step1_input">Authorization PIN</label>
            <input type="password" id="pin_step1_input" class="form-control" autocomplete="off" placeholder="Enter Authorization PIN">
            <small class="pin-error text-danger"></small>
            <button type="button" class="btn btn-primary pin-next-btn" style="margin-top: 1rem; width:100%;">Next</button>
        </div>
        <!-- Step 2: Payment PIN -->
        <div class="pin-step-container" id="pin-step-2" style="display:none;">
            <label for="pin_step2_input">Payment PIN</label>
            <input type="password" id="pin_step2_input" class="form-control" autocomplete="off" placeholder="Enter Payment PIN">
            <small class="pin-error text-danger"></small>
            <button type="button" class="btn btn-primary pin-next-btn" style="margin-top: 1rem; width:100%;">Next</button>
        </div>
        <!-- Step 3: Secure PIN -->
        <div class="pin-step-container" id="pin-step-3" style="display:none;">
            <label for="pin_step3_input">Secure PIN</label>
            <input type="password" id="pin_step3_input" class="form-control" autocomplete="off" placeholder="Enter Secure PIN">
            <small class="pin-error text-danger"></small>
            <button type="button" class="btn btn-primary pin-next-btn" style="margin-top: 1rem; width:100%;">Submit</button>
        </div>
        <!-- Close button -->
        <button type="button" class="btn btn-outline pin-cancel-btn" style="position: absolute; top: 0.5rem; right: 0.5rem;">&times;</button>
    </div>
</div>

<script>
// Update summary on input change
function updateSummary() {
    // Remove commas before parsing to float
    const rawValue = document.getElementById('amount').value.replace(/,/g, '');
    const amount = parseFloat(rawValue) || 0;
    const recipient = document.getElementById('recipient_account').value || '-';
    let fees = 0;
    // For withdrawals, charge the same fee as external transfer (1% + $2)
    fees = amount > 0 ? ((amount * 0.01) + 2.00) : 0;
    const total = amount + fees;
    
    document.getElementById('summary_recipient').textContent = recipient;
    document.getElementById('summary_amount').textContent = formatCurrency(amount);
    document.getElementById('summary_fees').textContent = formatCurrency(fees);
    document.getElementById('summary_total').textContent = formatCurrency(total);
}
document.getElementById('amount').addEventListener('input', updateSummary);
document.getElementById('recipient_account').addEventListener('input', updateSummary);

// Format currency helper
function formatCurrency(num) {
    return '$' + (num).toFixed(2);
}

// Form validation for amount when submitting via modal verification.
document.getElementById('withdrawForm').addEventListener('submit', function(e) {
    // Remove commas before parsing amount to float
    const amountValue = document.getElementById('amount').value.replace(/,/g, '');
    const amount = parseFloat(amountValue);
    const availableBalance = <?php echo $user['account_balance']; ?>;
    if (amount > availableBalance) {
        e.preventDefault();
        alert('Insufficient funds. Your available balance is ' + formatCurrency(availableBalance));
        return false;
    }
    if (amount < <?php echo MIN_TRANSFER_AMOUNT; ?>) {
        e.preventDefault();
        alert('Minimum withdrawal amount is <?php echo formatCurrency(MIN_TRANSFER_AMOUNT); ?>');
        return false;
    }
});

// Multi-step PIN verification modal logic
document.addEventListener('DOMContentLoaded', function() {
    const startBtn = document.getElementById('startVerificationBtn');
    const modal = document.getElementById('pinModal');
    const stepContainers = modal.querySelectorAll('.pin-step-container');
    const stepIndicator = modal.querySelectorAll('.pin-step-indicator li');
    let currentStep = 1;
    const totalSteps = 3;

    function updateStepDisplay() {
        // Hide all step containers
        stepContainers.forEach((el, idx) => {
            el.style.display = (idx + 1 === currentStep) ? 'block' : 'none';
        });
        // Update indicator
        stepIndicator.forEach((el, idx) => {
            el.classList.remove('active', 'completed');
            if (idx + 1 < currentStep) {
                el.classList.add('completed');
            } else if (idx + 1 === currentStep) {
                el.classList.add('active');
            }
        });
    }

    function showModal() {
        currentStep = 1;
        updateStepDisplay();
        modal.style.display = 'flex';
        // Clear previous values and errors
        modal.querySelectorAll('input[type="password"]').forEach(input => {
            input.value = '';
            input.classList.remove('is-invalid');
        });
        modal.querySelectorAll('.pin-error').forEach(err => err.textContent = '');
    }

    function hideModal() {
        modal.style.display = 'none';
    }

    async function verifyCurrentStep() {
        const stepMap = {1: 'authorization', 2: 'payment', 3: 'secure_pass'};
        const input = modal.querySelector(`#pin-step-${currentStep} input`);
        const errorEl = modal.querySelector(`#pin-step-${currentStep} .pin-error`);
        const pinValue = input.value.trim();
        if (!/^\d{4,6}$/.test(pinValue)) {
            input.classList.add('is-invalid');
            errorEl.textContent = 'PIN must be 4-6 digits.';
            return false;
        }
        // Send to server for verification
        try {
            const formData = new FormData();
            formData.append('step', stepMap[currentStep]);
            formData.append('pin', pinValue);
            const response = await fetch('verify_pin.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            if (!data.success) {
                input.classList.add('is-invalid');
                errorEl.textContent = data.message || 'Invalid PIN.';
                return false;
            }
            // Success: set value in hidden input on main form
            document.getElementById(stepMap[currentStep] + '_pin').value = pinValue;
            // Proceed to next step or submit form
            if (currentStep < totalSteps) {
                currentStep++;
                updateStepDisplay();
            } else {
                // All steps complete; hide modal and submit form
                hideModal();
                // Reset pin attempts on success
                // We'll rely on server to reset after successful submission
                document.getElementById('withdrawForm').submit();
            }
            return true;
        } catch (err) {
            console.error(err);
            input.classList.add('is-invalid');
            errorEl.textContent = 'Error verifying PIN. Please try again.';
            return false;
        }
    }

    // Attach event listeners
    if (startBtn) {
        startBtn.addEventListener('click', function(e) {
            // Validate amount and other required fields before showing modal
            const amountValue = document.getElementById('amount').value.replace(/,/g, '');
            const amount = parseFloat(amountValue);
            const availableBalance = <?php echo $user['account_balance']; ?>;
            if (amount > availableBalance) {
                alert('Insufficient funds. Your available balance is ' + formatCurrency(availableBalance));
                return;
            }
            if (amount < <?php echo MIN_TRANSFER_AMOUNT; ?>) {
                alert('Minimum withdrawal amount is <?php echo formatCurrency(MIN_TRANSFER_AMOUNT); ?>');
                return;
            }
            if (!document.getElementById('recipient_account').value.trim()) {
                alert('Please enter the recipient account number.');
                return;
            }
            if (!document.getElementById('recipient_bank').value.trim()) {
                alert('Please select the recipient bank.');
                return;
            }
            if (!document.getElementById('description').value.trim() || document.getElementById('description').value.trim().length < 5) {
                alert('Description must be at least 5 characters.');
                return;
            }
            // Show modal
            showModal();
        });
    }
    // Next buttons inside modal
    modal.querySelectorAll('.pin-next-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            verifyCurrentStep();
        });
    });
    // Cancel/close modal buttons
    modal.querySelectorAll('.pin-cancel-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            hideModal();
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>