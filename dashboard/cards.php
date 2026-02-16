<?php
// Page title for Cards Management
$pageTitle = 'Cards Management';
// Load configuration to ensure Database and Security are defined
require_once '../includes/config.php';

// Check if user is logged in before output
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login to manage your cards.', 'danger');
}

// After authentication, render the header
require_once '../includes/header.php';

// Mock card data for demonstration
$cards = [
    [
        'card_type' => 'Debit',
        'card_number' => '**** **** **** 4532',
        'expiry' => '12/26',
        'cvv' => '***',
        'status' => 'Active',
        'daily_limit' => 5000,
        'monthly_limit' => 15000
    ],
    [
        'card_type' => 'Credit',
        'card_number' => '**** **** **** 8901',
        'expiry' => '06/25',
        'cvv' => '***',
        'status' => 'Active',
        'credit_limit' => 10000,
        'balance' => 2500
    ]
];
?>

<!-- Page Header -->
<section class="hero">
    <div class="container">
        <h1>Card Management</h1>
        <p>Manage your CITYBRIDGEBANK debit and credit cards</p>
    </div>
</section>

<!-- Cards Content -->
<section style="padding: 2rem 0;">
    <div class="container">
        <!-- Active Cards -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h2><i class="fas fa-credit-card text-gold"></i> Active Cards</h2>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem;">
                    <?php foreach ($cards as $card): ?>
                        <div style="background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%); color: var(--white); padding: 2rem; border-radius: var(--radius-lg); position: relative; overflow: hidden;">
                            <div style="position: absolute; top: -50px; right: -50px; font-size: 200px; opacity: 0.1;">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                                <span style="font-size: 1.5rem; font-weight: 700;">CITYBRIDGEBANK</span>
                                <span style="background: var(--gold); color: var(--primary-blue); padding: 0.25rem 0.75rem; border-radius: var(--radius-sm); font-weight: 600;"><?php echo $card['card_type']; ?></span>
                            </div>
                            
                            <p style="font-size: 1.5rem; letter-spacing: 0.2em; margin-bottom: 1rem; font-family: monospace;">
                                <?php echo $card['card_number']; ?>
                            </p>
                            
                            <div style="display: flex; justify-content: space-between;">
                                <div>
                                    <small style="opacity: 0.8;">Expires</small>
                                    <p style="margin: 0;"><?php echo $card['expiry']; ?></p>
                                </div>
                                <div>
                                    <small style="opacity: 0.8;">CVV</small>
                                    <p style="margin: 0;"><?php echo $card['cvv']; ?></p>
                                </div>
                                <div>
                                    <small style="opacity: 0.8;">Status</small>
                                    <p style="margin: 0; color: var(--gold-light);"><i class="fas fa-check-circle"></i> <?php echo $card['status']; ?></p>
                                </div>
                            </div>
                            
                            <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.2);">
                                <?php if ($card['card_type'] == 'Debit'): ?>
                                    <small>Daily Limit: <?php echo formatCurrency($card['daily_limit']); ?></small><br>
                                    <small>Monthly Limit: <?php echo formatCurrency($card['monthly_limit']); ?></small>
                                <?php else: ?>
                                    <small>Credit Limit: <?php echo formatCurrency($card['credit_limit']); ?></small><br>
                                    <small>Current Balance: <?php echo formatCurrency($card['balance']); ?></small>
                                <?php endif; ?>
                            </div>
                            
                            <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <button class="btn btn-gold" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                    <i class="fas fa-cog"></i> Settings
                                </button>
                                <button class="btn btn-outline-white" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                    <i class="fas fa-lock"></i> Lock Card
                                </button>
                                <button class="btn btn-outline-white" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                    <i class="fas fa-chart-line"></i> Transactions
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <button class="btn btn-outline"><i class="fas fa-plus"></i> Request New Card</button>
                </div>
            </div>
        </div>
        
        <!-- Card Features -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <div class="card">
                <div class="card-body">
                    <h3 style="color: var(--primary-blue); margin-bottom: 1rem;"><i class="fas fa-lock text-gold"></i> Security Features</h3>
                    <ul style="line-height: 1.8;">
                        <li><i class="fas fa-check text-success"></i> EMV Chip Technology</li>
                        <li><i class="fas fa-check text-success"></i> Contactless Payments</li>
                        <li><i class="fas fa-check text-success"></i> Real-time Fraud Monitoring</li>
                        <li><i class="fas fa-check text-success"></i> Instant Freeze/Unfreeze</li>
                        <li><i class="fas fa-check text-success"></i> Transaction Alerts</li>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h3 style="color: var(--primary-blue); margin-bottom: 1rem;"><i class="fas fa-mobile-alt text-gold"></i> Digital Wallet</h3>
                    <ul style="line-height: 1.8;">
                        <li><i class="fas fa-check text-success"></i> Apple Pay</li>
                        <li><i class="fas fa-check text-success"></i> Google Pay</li>
                        <li><i class="fas fa-check text-success"></i> Samsung Pay</li>
                        <li><i class="fas fa-check text-success"></i> PayPal Integration</li>
                        <li><i class="fas fa-check text-success"></i> Instant Add to Wallet</li>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h3 style="color: var(--primary-blue); margin-bottom: 1rem;"><i class="fas fa-percentage text-gold"></i> Rewards</h3>
                    <ul style="line-height: 1.8;">
                        <li><i class="fas fa-check text-success"></i> 2% Cashback on Purchases</li>
                        <li><i class="fas fa-check text-success"></i> No Annual Fee</li>
                        <li><i class="fas fa-check text-success"></i> Foreign Transaction Waiver</li>
                        <li><i class="fas fa-check text-success"></i> Welcome Bonus</li>
                        <li><i class="fas fa-check text-success"></i> 0% Intro APR</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Back to Dashboard -->
        <div style="text-align: center; margin-top: 2rem;">
            <a href="index.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>