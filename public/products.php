<?php
// Initialize session and environment before output
require_once '../includes/config.php';
// Then load the header
require_once '../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero" style="min-height: 60vh;">
    <div class="container">
        <div class="hero-content">
            <span style="background: var(--gradient-primary); padding: 0.5rem 1.5rem; border-radius: 50px; font-size: 0.9rem; font-weight: 600; display: inline-block; margin-bottom: 1.5rem;">
                <i class="fas fa-box-open" style="margin-right: 0.5rem;"></i> Our Products
            </span>
            
            <h1 class="hero-title">
                Banking <span class="hero-title-gradient">Products</span>
            </h1>
            
            <p class="hero-subtitle">
                Discover our comprehensive range of banking products designed to meet all your financial needs.
            </p>
        </div>
    </div>
</section>

<!-- Personal Banking -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Personal <span class="hero-title-gradient">Banking</span></h2>
            <p class="section-subtitle">Manage your personal finances with ease</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
            <!-- Checking Account -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-wallet"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Checking Account</h3>
                <ul style="color: var(--text-secondary); line-height: 2; margin-bottom: 1.5rem; list-style: none;">
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> No monthly fees</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Free debit card</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Mobile check deposit</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Online bill pay</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> 24/7 customer support</li>
                </ul>
                <a href="register.php" class="btn btn-primary" style="width: 100%;">
                    Open Account <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
                </a>
            </div>
            
            <!-- Savings Account -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-secondary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-piggy-bank"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Savings Account</h3>
                <ul style="color: var(--text-secondary); line-height: 2; margin-bottom: 1.5rem; list-style: none;">
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> 4.5% APY interest rate</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> No minimum balance</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Compound interest daily</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Easy withdrawals</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> FDIC insured</li>
                </ul>
                <a href="register.php" class="btn btn-gradient" style="width: 100%;">
                    Start Saving <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
                </a>
            </div>
            
            <!-- Money Market -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-success); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Money Market Account</h3>
                <ul style="color: var(--text-secondary); line-height: 2; margin-bottom: 1.5rem; list-style: none;">
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> 5.0% APY interest rate</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Check writing privileges</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Higher interest rates</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> ATM access</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> $2,500 minimum balance</li>
                </ul>
                <a href="register.php" class="btn btn-primary" style="width: 100%;">
                    Learn More <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Business Banking -->
<section class="section" style="background: rgba(102, 126, 234, 0.05);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Business <span class="hero-title-gradient">Banking</span></h2>
            <p class="section-subtitle">Solutions tailored for your business growth</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
            <!-- Business Checking -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-warning); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-building"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Business Checking</h3>
                <ul style="color: var(--text-secondary); line-height: 2; margin-bottom: 1.5rem; list-style: none;">
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Unlimited transactions</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Free online banking</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Payroll services</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Invoice management</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Dedicated support</li>
                </ul>
                <a href="register.php" class="btn btn-primary" style="width: 100%;">
                    Get Started <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
                </a>
            </div>
            
            <!-- Business Savings -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-piggy-bank"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Business Savings</h3>
                <ul style="color: var(--text-secondary); line-height: 2; margin-bottom: 1.5rem; list-style: none;">
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Competitive interest rates</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> No monthly maintenance fee</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Easy transfers</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Business planning tools</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> FDIC insured</li>
                </ul>
                <a href="register.php" class="btn btn-gradient" style="width: 100%;">
                    Start Saving <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
                </a>
            </div>
            
            <!-- Business Credit Line -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-secondary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem">Business Credit Line</h3>
                <ul style="color: var(--text-secondary); line-height: 2; margin-bottom: 1.5rem; list-style: none;">
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Up to $500,000 credit</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Competitive rates</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Flexible repayment</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Quick approval</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> No collateral required</li>
                </ul>
                <a href="register.php" class="btn btn-primary" style="width: 100%;">
                    Apply Now <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Cards -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Our <span class="hero-title-gradient">Cards</span></h2>
            <p class="section-subtitle">Premium cards with exclusive benefits</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
            <!-- Debit Card -->
            <div class="glass-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 3rem; text-align: center;">
                <i class="fas fa-credit-card" style="font-size: 4rem; color: white; margin-bottom: 2rem;"></i>
                <h3 style="font-size: 1.8rem; color: white; margin-bottom: 1rem;">Debit Card</h3>
                <p style="color: rgba(255, 255, 255, 0.8); margin-bottom: 2rem;">
                    Access your funds anywhere, anytime with our premium debit card.
                </p>
                <ul style="color: rgba(255, 255, 255, 0.8); line-height: 2; margin-bottom: 2rem; list-style: none;">
                    <li><i class="fas fa-check" style="margin-right: 0.75rem;"></i> Free with checking account</li>
                    <li><i class="fas fa-check" style="margin-right: 0.75rem;"></i> Contactless payments</li>
                    <li><i class="fas fa-check" style="margin-right: 0.75rem;"></i> ATM access worldwide</li>
                    <li><i class="fas fa-check" style="margin-right: 0.75rem;"></i> Mobile wallet compatible</li>
                </ul>
                <a href="register.php" class="btn" style="background: white; color: var(--accent-purple); width: 100%;">
                    Get Card
                </a>
            </div>
            
            <!-- Credit Card -->
            <div class="glass-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 3rem; text-align: center;">
                <i class="fab fa-cc-visa" style="font-size: 4rem; color: white; margin-bottom: 2rem;"></i>
                <h3 style="font-size: 1.8rem; color: white; margin-bottom: 1rem;">Credit Card</h3>
                <p style="color: rgba(255, 255, 255, 0.8); margin-bottom: 2rem;">
                    Enjoy exclusive rewards and benefits with our premium credit card.
                </p>
                <ul style="color: rgba(255, 255, 255, 0.8); line-height: 2; margin-bottom: 2rem; list-style: none;">
                    <li><i class="fas fa-check" style="margin-right: 0.75rem;"></i> 2% cashback on all purchases</li>
                    <li><i class="fas fa-check" style="margin-right: 0.75rem;"></i> No annual fee</li>
                    <li><i class="fas fa-check" style="margin-right: 0.75rem;"></i> 0% APR for 12 months</li>
                    <li><i class="fas fa-check" style="margin-right: 0.75rem;"></i> Travel insurance included</li>
                </ul>
                <a href="register.php" class="btn" style="background: white; color: var(--accent-pink); width: 100%;">
                    Apply Now
                </a>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section" style="background: rgba(102, 126, 234, 0.05);">
    <div class="container">
        <div class="glass-card glass-card-gradient" style="text-align: center; padding: 4rem 2rem;">
            <h2 style="font-size: 2.5rem; margin-bottom: 1.5rem;">
                Ready to <span class="hero-title-gradient">Get Started</span>?
            </h2>
            <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto 2.5rem; font-size: 1.1rem;">
                Open an account today and start experiencing the future of banking. It only takes a few minutes.
            </p>
            <a href="register.php" class="btn btn-primary btn-lg">
                <i class="fas fa-user-plus" style="margin-right: 0.5rem;"></i> Open Account Now
            </a>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>