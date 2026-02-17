<?php
// Load configuration to initialize session and environment
require_once '../includes/config.php';
// Then include the header
require_once '../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero" style="min-height: 60vh;">
    <div class="container">
        <div class="hero-content">
            <span style="background: var(--gradient-primary); padding: 0.5rem 1.5rem; border-radius: 50px; font-size: 0.9rem; font-weight: 600; display: inline-block; margin-bottom: 1.5rem;">
                <i class="fas fa-dollar-sign" style="margin-right: 0.5rem;"></i> Fees & Limits
            </span>
            
            <h1 class="hero-title">
                Transparent <span class="hero-title-gradient">Pricing</span>
            </h1>
            
            <p class="hero-subtitle">
                Clear, upfront pricing with no hidden fees. Know exactly what you'll pay.
            </p>
        </div>
    </div>
</section>

<!-- Account Fees -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Account <span class="hero-title-gradient">Fees</span></h2>
            <p class="section-subtitle">Simple pricing for all our accounts</p>
        </div>
        
        <div class="glass-card" style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--gradient-primary); color: white;">
                        <th style="padding: 1.5rem; text-align: left;">Service</th>
                        <th style="padding: 1.5rem; text-align: left;">Fee</th>
                        <th style="padding: 1.5rem; text-align: left;">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: var(--border-glass);">
                        <td style="padding: 1.5rem;">Checking Account</td>
                        <td style="padding: 1.5rem; color: var(--accent-blue); font-weight: 600;">$0.00</td>
                        <td style="padding: 1.5rem; color: var(--text-secondary);">No monthly maintenance fee</td>
                    </tr>
                    <tr style="border-bottom: var(--border-glass);">
                        <td style="padding: 1.5rem;">Savings Account</td>
                        <td style="padding: 1.5rem; color: var(--accent-blue); font-weight: 600;">$0.00</td>
                        <td style="padding: 1.5rem; color: var(--text-secondary);">No monthly maintenance fee</td>
                    </tr>
                    <tr style="border-bottom: var(--border-glass);">
                        <td style="padding: 1.5rem;">Money Market Account</td>
                        <td style="padding: 1.5rem; color: var(--accent-blue); font-weight: 600;">$10.00</td>
                        <td style="padding: 1.5rem; color: var(--text-secondary);">Waived with $2,500 minimum balance</td>
                    </tr>
                    <tr style="border-bottom: var(--border-glass);">
                        <td style="padding: 1.5rem;">Business Checking</td>
                        <td style="padding: 1.5rem; color: var(--accent-blue); font-weight: 600;">$15.00</td>
                        <td style="padding: 1.5rem; color: var(--text-secondary);">Waived with 50 transactions/month</td>
                    </tr>
                    <tr>
                        <td style="padding: 1.5rem;">Debit Card</td>
                        <td style="padding: 1.5rem; color: var(--accent-blue); font-weight: 600;">$0.00</td>
                        <td style="padding: 1.5rem; color: var(--text-secondary);">Free with checking account</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Transaction Fees -->
<section class="section" style="background: rgba(102, 126, 234, 0.05);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Transaction <span class="hero-title-gradient">Fees</span></h2>
            <p class="section-subtitle">Fees for various transactions</p>
        </div>
        
        <div class="glass-card" style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--gradient-secondary); color: white;">
                        <th style="padding: 1.5rem; text-align: left;">Transaction Type</th>
                        <th style="padding: 1.5rem; text-align: left;">Fee</th>
                        <th style="padding: 1.5rem; text-align: left;">Processing Time</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: var(--border-glass);">
                        <td style="padding: 1.5rem;">Internal Transfer</td>
                        <td style="padding: 1.5rem; color: var(--accent-blue); font-weight: 600;">$0.00</td>
                        <td style="padding: 1.5rem; color: var(--text-secondary);">Instant</td>
                    </tr>
                    <tr style="border-bottom: var(--border-glass);">
                        <td style="padding: 1.5rem;">External ACH Transfer</td>
                        <td style="padding: 1.5rem; color: var(--accent-blue); font-weight: 600;">$3.00</td>
                        <td style="padding: 1.5rem; color: var(--text-secondary);">1-2 business days</td>
                    </tr>
                    <tr style="border-bottom: var(--border-glass);">
                        <td style="padding: 1.5rem;">Domestic Wire Transfer</td>
                        <td style="padding: 1.5rem; color: var(--accent-blue); font-weight: 600;">$25.00</td>
                        <td style="padding: 1.5rem; color: var(--text-secondary);">Same day</td>
                    </tr>
                    <tr style="border-bottom: var(--border-glass);">
                        <td style="padding: 1.5rem;">International Wire Transfer</td>
                        <td style="padding: 1.5rem; color: var(--accent-blue); font-weight: 600;">$45.00</td>
                        <td style="padding: 1.5rem; color: var(--text-secondary);">1-2 business days</td>
                    </tr>
                    <tr>
                        <td style="padding: 1.5rem;">Person-to-Person Transfer</td>
                        <td style="padding: 1.5rem; color: var(--accent-blue); font-weight: 600;">$0.00</td>
                        <td style="padding: 1.5rem; color: var(--text-secondary);">Instant</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- ATM Fees -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">ATM <span class="hero-title-gradient">Fees</span></h2>
            <p class="section-subtitle">Access your cash conveniently</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <div class="glass-card" style="text-align: center; padding: 2.5rem;">
                <i class="fas fa-money-bill-wave" style="font-size: 3rem; color: var(--accent-blue); margin-bottom: 1.5rem;"></i>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">CITIBRIDGE ATMs</h3>
                <div style="font-size: 2.5rem; font-weight: 800; color: var(--accent-blue); margin-bottom: 1rem;">FREE</div>
                <p style="color: var(--text-secondary);">Unlimited free withdrawals at all CITIBRIDGE ATMs</p>
            </div>
            
            <div class="glass-card" style="text-align: center; padding: 2.5rem;">
                <i class="fas fa-globe" style="font-size: 3rem; color: var(--accent-purple); margin-bottom: 1.5rem;"></i>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Other Bank ATMs</h3>
                <div style="font-size: 2.5rem; font-weight: 800; color: var(--accent-purple); margin-bottom: 1rem;">$2.50</div>
                <p style="color: var(--text-secondary);">Fee for using non-CITIBRIDGE ATMs</p>
            </div>
            
            <div class="glass-card" style="text-align: center; padding: 2.5rem;">
                <i class="fas fa-plane" style="font-size: 3rem; color: var(--accent-pink); margin-bottom: 1.5rem;"></i>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem">International ATMs</h3>
                <div style="font-size: 2.5rem; font-weight: 800; color: var(--accent-pink); margin-bottom: 1rem;">$5.00</div>
                <p style="color: var(--text-secondary);">Fee for international ATM withdrawals</p>
            </div>
        </div>
    </div>
</section>

<!-- Transaction Limits -->
<section class="section" style="background: rgba(102, 126, 234, 0.05);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Transaction <span class="hero-title-gradient">Limits</span></h2>
            <p class="section-subtitle">Daily and monthly limits for your security</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1.5rem;">Debit Card Limits</h3>
                <ul style="color: var(--text-secondary); line-height: 2; list-style: none;">
                    <li><strong>Daily Withdrawal:</strong> $2,500</li>
                    <li><strong>Daily Purchase:</strong> $5,000</li>
                    <li><strong>Monthly Transfer:</strong> $15,000</li>
                </ul>
            </div>
            
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-secondary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1.5rem;">Transfer Limits</h3>
                <ul style="color: var(--text-secondary); line-height: 2; list-style: none;">
                    <li><strong>Daily Internal:</strong> Unlimited</li>
                    <li><strong>Daily External:</strong> $10,000</li>
                    <li><strong>Monthly External:</strong> $50,000</li>
                </ul>
            </div>
            
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-success); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1.5rem">Wire Transfer Limits</h3>
                <ul style="color: var(--text-secondary); line-height: 2; list-style: none;">
                    <li><strong>Daily Domestic:</strong> $100,000</li>
                    <li><strong>Daily International:</strong> $50,000</li>
                    <li><strong>Monthly International:</strong> $200,000</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Additional Fees -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Additional <span class="hero-title-gradient">Fees</span></h2>
            <p class="section-subtitle">Other service fees</p>
        </div>
        
        <div class="glass-card" style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--gradient-warning); color: white;">
                        <th style="padding: 1.5rem; text-align: left;">Service</th>
                        <th style="padding: 1.5rem; text-align: left;">Fee</th>
                        <th style="padding: 1.5rem; text-align: left;">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: var(--border-glass);">
                        <td style="padding: 1.5rem;">Stop Payment</td>
                        <td style="padding: 1.5rem; color: var(--accent-blue); font-weight: 600;">$30.00</td>
                        <td style="padding: 1.5rem; color: var(--text-secondary);">Per check or transaction</td>
                    </tr>
                    <tr style="border-bottom: var(--border-glass);">
                        <td style="padding: 1.5rem;">Cashier's Check</td>
                        <td style="padding: 1.5rem; color: var(--accent-blue); font-weight: 600;">$10.00</td>
                        <td style="padding: 1.5rem; color: var(--text-secondary);">Per check</td>
                    </tr>
                    <tr style="border-bottom: var(--border-glass);">
                        <td style="padding: 1.5rem;">Money Order</td>
                        <td style="padding: 1.5rem; color: var(--accent-blue); font-weight: 600;">$5.00</td>
                        <td style="padding: 1.5rem; color: var(--text-secondary);">Per money order</td>
                    </tr>
                    <tr style="border-bottom: var(--border-glass);">
                        <td style="padding: 1.5rem;"> overdraft Fee</td>
                        <td style="padding: 1.5rem; color: var(--accent-blue); font-weight: 600;">$35.00</td>
                        <td style="padding: 1.5rem; color: var(--text-secondary);">Per occurrence (max 5/day)</td>
                    </tr>
                    <tr>
                        <td style="padding: 1.5rem;">Returned Check</td>
                        <td style="padding: 1.5rem; color: var(--accent-blue); font-weight: 600;">$15.00</td>
                        <td style="padding: 1.5rem; color: var(--text-secondary);">Per returned check</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- No Hidden Fees Promise -->
<section class="section" style="background: rgba(102, 126, 234, 0.05);">
    <div class="container">
        <div class="glass-card glass-card-gradient" style="text-align: center; padding: 4rem 2rem;">
            <div style="font-size: 5rem; margin-bottom: 2rem; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                <i class="fas fa-handshake"></i>
            </div>
            <h2 style="font-size: 2.5rem; margin-bottom: 1.5rem;">
                No Hidden <span class="hero-title-gradient">Fees</span>
            </h2>
            <p style="color: var(--text-secondary); max-width: 700px; margin: 0 auto 2.5rem; font-size: 1.1rem;">
                We believe in complete transparency. All fees are clearly disclosed upfront. We will never charge you hidden fees or surprise charges. What you see is what you pay.
            </p>
            <div style="display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap;">
                <div class="glass-card" style="padding: 1.5rem;">
                    <i class="fas fa-check-circle" style="color: var(--accent-blue); font-size: 2rem; margin-bottom: 0.5rem;"></i>
                    <h4>Transparent Pricing</h4>
                </div>
                <div class="glass-card" style="padding: 1.5rem;">
                    <i class="fas fa-check-circle" style="color: var(--accent-blue); font-size: 2rem; margin-bottom: 0.5rem;"></i>
                    <h4>No Hidden Charges</h4>
                </div>
                <div class="glass-card" style="padding: 1.5rem;">
                    <i class="fas fa-check-circle" style="color: var(--accent-blue); font-size: 2rem; margin-bottom: 0.5rem;"></i>
                    <h4>Clear Disclosures</h4>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>