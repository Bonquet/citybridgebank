<?php
// Title for Transfers & Payments page
$pageTitle = 'Transfers & Payments';
// Load configuration first to access Database and Security
require_once '../includes/config.php';

// Ensure the user is logged in before outputting any HTML.  If the user is not
// authenticated, redirect them to the public login page. Performing this check
// before including the header prevents "headers already sent" warnings when
// redirectWithMessage() sends HTTP headers.
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login to make transfers.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get user balance and account status
    $stmt = $db->prepare("SELECT account_balance, account_status FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    // Redirect if the account is not active
    if ($user['account_status'] !== 'active') {
        redirectWithMessage('index.php', 'Your account is not active. Transfers are not available.', 'danger');
    }
    
    // Ensure all three pins are set and active before allowing transfers
    $stmt = $db->prepare("SELECT COUNT(*) AS pin_count FROM user_pins WHERE user_id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['user_id']]);
    $pin_count = $stmt->fetch()['pin_count'];
    
    if ($pin_count < 3) {
        redirectWithMessage('setup_pins.php', 'Please complete your PIN setup before making transfers.', 'warning');
    }
} catch (PDOException $e) {
    error_log('Transfer error: ' . $e->getMessage());
    redirectWithMessage('index.php', 'An error occurred.', 'danger');
}

// Determine if the user has insufficient funds for transfers
$availableBalance = isset($user['account_balance']) ? $user['account_balance'] : 0;
$insufficientFunds = ($availableBalance < MIN_TRANSFER_AMOUNT);

// Generate CSRF token used by the transfer form
$csrf_token = Security::generateCSRFToken();

// Now that login and account checks are done, render the header.  Placing this
// after the login checks prevents premature output of HTML before a redirect.
require_once '../includes/header.php';
?>

<!-- Page Header -->
<section class="hero">
    <div class="container">
        <h1>Make a Transfer</h1>
        <p>Transfer funds securely with our three-layer PIN protection</p>
    </div>
</section>

<!-- Transfer Content -->
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
            Your available balance is below the minimum transfer amount (<?php echo formatCurrency(MIN_TRANSFER_AMOUNT); ?>). Please make a deposit before attempting a transfer.
        </div>
        <?php endif; ?>

        <!-- Transfer Form -->
        <div class="glass-card">
            <!-- Form Header -->
            <div style="margin-bottom: 1.5rem;">
                <h2 style="display: flex; align-items: center; gap: 0.5rem; font-size: 1.5rem;">
                    <i class="fas fa-exchange-alt" style="color: var(--accent-gold);"></i> New Transfer
                </h2>
            </div>
            <div>
                <div class="warning-box" style="margin-bottom: 2rem; padding: 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                    <h4 style="color: var(--accent-blue); margin-bottom: 0.5rem;">
                        <i class="fas fa-shield-alt" style="color: var(--accent-gold); margin-right: 0.5rem;"></i> Secure Transfer
                    </h4>
                    <p style="margin: 0; color: var(--text-muted);">
                        This transfer is protected by our advanced multi-layer authentication system. You will be prompted to enter your security credentials to complete the transaction.
                    </p>
                </div>
                
                <form action="transfer_process.php" method="POST" id="transferForm" style="margin-top: 1rem;">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <!-- Transfer Details -->
                    <h3 style="color: var(--accent-blue); margin-bottom: 1rem;">Transfer Details</h3>
                    
                    <div class="form-group">
                        <label for="transfer_type">Transfer Type *</label>
                        <select id="transfer_type" name="transfer_type" class="form-control" required>
                            <option value="internal">Internal Transfer (to another CITYBRIDGEBANK account)</option>
                            <option value="external">External Transfer (to another bank)</option>
                            <option value="wire">Wire Transfer</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="recipient_account">Recipient Account Number *</label>
                        <input type="text" id="recipient_account" name="recipient_account" class="form-control" required placeholder="Enter account number" maxlength="20">
                    </div>

                    <!-- Bank Selection for External and Wire Transfers -->
                    <div class="form-group" id="bank-group" style="display: none;">
                        <label for="recipient_bank">Recipient Bank *</label>
                        <select id="recipient_bank" name="recipient_bank" class="form-control">
                            <?php
                            // List of major US banks
                            $banks = [
                                'Bank of America',
                                'JPMorgan Chase',
                                'Wells Fargo',
                                'Citibank',
                                'U.S. Bank',
                                'PNC Bank',
                                'Capital One',
                                'Truist (SunTrust/BB&T)',
                                'TD Bank',
                                'Fifth Third Bank',
                                'KeyBank',
                                'Regions Bank',
                                'Ally Bank',
                                'BBVA USA',
                                'Santander Bank',
                                'M&T Bank',
                                'Huntington Bank',
                                'BMO Harris',
                                'Charles Schwab Bank',
                                'HSBC'
                            ];
                            foreach ($banks as $bank): ?>
                                <option value="<?php echo htmlspecialchars($bank); ?>"><?php echo htmlspecialchars($bank); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Select the recipient's bank for external or wire transfers</small>
                    </div>
                    
                    <?php
                    // Dynamically set the minimum and maximum for the amount field. If the user's
                    // available balance is less than the minimum transfer amount, set both min
                    // and max to the available balance to avoid an invalid HTML range (e.g., min>max).
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
                        <input type="text" id="description" name="description" class="form-control" placeholder="Payment for invoice #12345">
                    </div>
                    
                    <!-- Security Verification -->
                    <h3 style="color: var(--accent-blue); margin-bottom: 1rem; margin-top: 2rem;">Security Verification</h3>
                    
                    <p style="margin-bottom: 1rem; color: var(--text-muted);">For your security, please enter your authentication PIN to complete this transfer.</p>
                    
                    <!-- Hidden PIN inputs to store values after modal verification -->
                    <input type="hidden" name="authorization_pin" id="authorization_pin">
                    <input type="hidden" name="payment_pin" id="payment_pin">
                    <input type="hidden" name="secure_pass_pin" id="secure_pass_pin">
                    
                    <!-- Transaction Summary -->
                    <div class="info-box" style="margin-top: 2rem; padding: 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                        <h4 style="color: var(--accent-blue); margin-bottom: 0.5rem;">Transaction Summary</h4>
                        <p><strong>Type:</strong> <span id="summary_type">-</span></p>
                        <p><strong>Recipient:</strong> <span id="summary_recipient">-</span></p>
                        <p><strong>Amount:</strong> <span id="summary_amount">-</span></p>
                        <p><strong>Fees:</strong> <span id="summary_fees">$0.00</span></p>
                        <p><strong>Total:</strong> <span id="summary_total">-</span></p>
                    </div>
                    
                    <div class="form-group" style="margin-top: 2rem;">
                        <!-- Trigger PIN verification modal instead of immediate form submission -->
                        <button type="button" id="startTransferVerificationBtn" class="btn btn-primary" style="width: 100%;" <?php echo $insufficientFunds ? 'disabled' : ''; ?>>Complete Transfer</button>
                    </div>
                    
                    <div class="text-center">
                        <a href="index.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Transfer Limits -->
        <div class="glass-card" style="margin-top: 2rem;">
            <div>
                <h4 style="color: var(--accent-blue); margin-bottom: 1rem;">
                    <i class="fas fa-info-circle" style="color: var(--accent-gold); margin-right: 0.5rem;"></i> Transfer Limits
                </h4>
                <ul style="line-height: 2; color: var(--text-muted);">
                    <li><strong>Minimum Transfer:</strong> <?php echo formatCurrency(MIN_TRANSFER_AMOUNT); ?></li>
                    <li><strong>Maximum Single Transfer:</strong> <?php echo formatCurrency(MAX_SINGLE_TRANSFER); ?></li>
                    <li><strong>Maximum Daily Transfers:</strong> <?php echo formatCurrency(MAX_DAILY_TRANSFER); ?></li>
                    <li><strong>Internal Transfers:</strong> Free and instant</li>
                    <li><strong>External Transfers:</strong> 1-3 business days, $2.50 express fee</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<script>
// Update transaction summary
document.getElementById('amount').addEventListener('input', function() {
    // Remove commas before parsing to float to prevent values like "1,000" from becoming 1
    const rawValue = this.value.replace(/,/g, '');
    const amount = parseFloat(rawValue) || 0;
    const type = document.getElementById('transfer_type').value;
    const recipient = document.getElementById('recipient_account').value || '-';

    let fees = 0;
    if (type === 'external') {
        fees = 2.50;
    } else if (type === 'wire') {
        fees = 15.00;
    }

    const total = amount + fees;

    document.getElementById('summary_type').textContent = type.charAt(0).toUpperCase() + type.slice(1) + ' Transfer';
    document.getElementById('summary_recipient').textContent = recipient;
    document.getElementById('summary_amount').textContent = formatCurrency(amount);
    document.getElementById('summary_fees').textContent = formatCurrency(fees);
    document.getElementById('summary_total').textContent = formatCurrency(total);
});

document.getElementById('recipient_account').addEventListener('input', function() {
    document.getElementById('summary_recipient').textContent = this.value || '-';
});

document.getElementById('transfer_type').addEventListener('change', function() {
    document.getElementById('summary_type').textContent = this.value.charAt(0).toUpperCase() + this.value.slice(1) + ' Transfer';
    
    // Trigger amount change to update fees
    document.getElementById('amount').dispatchEvent(new Event('input'));

    // Show/hide bank selection based on transfer type
    const bankGroup = document.getElementById('bank-group');
    if (this.value === 'external' || this.value === 'wire') {
        bankGroup.style.display = 'block';
    } else {
        bankGroup.style.display = 'none';
    }
});


// Helper function to format numbers as currency (e.g., 1234.56 -> $1,234.56)
function formatCurrency(num) {
    return '$' + parseFloat(num).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// The transfer form will be submitted via the PIN verification modal. No direct submit handler needed here.
</script>

<!-- PIN Verification Modal for Transfers -->
<div id="pinModal" class="pin-modal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); align-items: center; justify-content: center; z-index: 10000;">
    <div class="pin-modal-content glass-card" style="width: 350px; padding: 1.5rem; position: relative;">
        <h3 style="margin-bottom: 1rem; color: var(--accent-blue);">Verify Transfer</h3>
        <!-- Step Containers: only one visible at a time; no step numbers shown -->
        <div class="pin-step-container" id="pin-step-1" style="display:none;">
            <label for="pin_step1_input">Authentication PIN</label>
            <input type="password" id="pin_step1_input" class="form-control" autocomplete="off" placeholder="Enter Authentication PIN">
            <small class="pin-error text-danger"></small>
            <button type="button" class="btn btn-primary pin-next-btn" style="margin-top: 1rem; width:100%;">Next</button>
        </div>
        <div class="pin-step-container" id="pin-step-2" style="display:none;">
            <label for="pin_step2_input">Payment PIN</label>
            <input type="password" id="pin_step2_input" class="form-control" autocomplete="off" placeholder="Enter Payment PIN">
            <small class="pin-error text-danger"></small>
            <button type="button" class="btn btn-primary pin-next-btn" style="margin-top: 1rem; width:100%;">Next</button>
        </div>
        <div class="pin-step-container" id="pin-step-3" style="display:none;">
            <label for="pin_step3_input">Secure PIN</label>
            <input type="password" id="pin_step3_input" class="form-control" autocomplete="off" placeholder="Enter Secure PIN">
            <small class="pin-error text-danger"></small>
            <button type="button" class="btn btn-primary pin-next-btn" style="margin-top: 1rem; width:100%;">Submit</button>
        </div>
        <!-- Close button -->
        <button type="button" class="btn btn-outline pin-cancel-btn" style="position: absolute; top: 0.5rem; right: 0.5rem;" onclick="hidePinModal()">&times;</button>
    </div>
</div>

<script>
// Multi-step PIN verification modal logic for transfers
document.addEventListener('DOMContentLoaded', function() {
    const startBtn = document.getElementById('startTransferVerificationBtn');
    const modal = document.getElementById('pinModal');
    const stepContainers = modal.querySelectorAll('.pin-step-container');
    let currentStep = 1;
    const totalSteps = 3;
    // Map step number to pin type
    const stepMap = {1: 'authorization', 2: 'payment', 3: 'secure_pass'};

    function updateStepDisplay() {
        stepContainers.forEach((el, idx) => {
            el.style.display = (idx + 1 === currentStep) ? 'block' : 'none';
        });
    }

    function showModal() {
        currentStep = 1;
        updateStepDisplay();
        modal.style.display = 'flex';
        // Clear all input fields and error messages
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
        const input = modal.querySelector(`#pin-step-${currentStep} input`);
        const errorEl = modal.querySelector(`#pin-step-${currentStep} .pin-error`);
        const pinValue = input.value.trim();
        // Validate input length
        if (!/^\d{4,6}$/.test(pinValue)) {
            input.classList.add('is-invalid');
            errorEl.textContent = 'PIN must be 4-6 digits.';
            return false;
        }
        // Send verification request to server
        try {
            const formData = new FormData();
            formData.append('step', stepMap[currentStep]);
            formData.append('pin', pinValue);
            const response = await fetch('verify_pin.php', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            if (!data.success) {
                input.classList.add('is-invalid');
                errorEl.textContent = data.message || 'Invalid PIN.';
                return false;
            }
            // Set hidden value on main form
            document.getElementById(stepMap[currentStep] + '_pin').value = pinValue;
            if (currentStep < totalSteps) {
                currentStep++;
                updateStepDisplay();
            } else {
                // All steps verified; submit form
                hideModal();
                document.getElementById('transferForm').submit();
            }
            return true;
        } catch (err) {
            console.error(err);
            input.classList.add('is-invalid');
            errorEl.textContent = 'Error verifying PIN. Please try again.';
            return false;
        }
    }

    // Attach click handler to start button
    if (startBtn) {
        startBtn.addEventListener('click', function() {
            // Validate transfer details before showing modal
            const rawAmount = document.getElementById('amount').value.replace(/,/g, '');
            const amount = parseFloat(rawAmount);
            const availableBalance = <?php echo $user['account_balance']; ?>;
            // Validate amount vs min and max
            if (isNaN(amount) || amount < <?php echo MIN_TRANSFER_AMOUNT; ?>) {
                showNotification('Minimum transfer amount is <?php echo formatCurrency(MIN_TRANSFER_AMOUNT); ?>', 'danger');
                return;
            }
            if (amount > availableBalance) {
                showNotification('Insufficient funds. Your available balance is ' + formatCurrency(availableBalance), 'danger');
                return;
            }
            // Validate recipient account
            const recipient = document.getElementById('recipient_account').value.trim();
            if (!recipient) {
                showNotification('Please enter the recipient account number.', 'danger');
                return;
            }
            // Validate bank selection for external/wire
            const type = document.getElementById('transfer_type').value;
            if ((type === 'external' || type === 'wire') && !document.getElementById('recipient_bank').value.trim()) {
                showNotification('Please select the recipient bank.', 'danger');
                return;
            }
            // Validate description (optional, but we can check for length if present)
            // Show modal
            showModal();
        });
    }

    // Attach next button handlers
    modal.querySelectorAll('.pin-next-btn').forEach(btn => {
        btn.addEventListener('click', verifyCurrentStep);
    });
    // Expose hide function globally for close button
    window.hidePinModal = hideModal;
});
</script>

<?php require_once '../includes/footer.php'; ?>