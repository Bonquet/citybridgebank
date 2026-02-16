<?php
// Start the session via config before any output
require_once '../includes/config.php';
// Then include the header file
require_once '../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero" style="min-height: 60vh;">
    <div class="container">
        <div class="hero-content">
            <span style="background: var(--gradient-primary); padding: 0.5rem 1.5rem; border-radius: 50px; font-size: 0.9rem; font-weight: 600; display: inline-block; margin-bottom: 1.5rem;">
                <i class="fas fa-concierge-bell" style="margin-right: 0.5rem;"></i> Our Services
            </span>
            
            <h1 class="hero-title">
                Banking <span class="hero-title-gradient">Services</span>
            </h1>
            
            <p class="hero-subtitle">
                Comprehensive financial services designed to make your life easier and your money work harder.
            </p>
        </div>
    </div>
</section>

<!-- Transfer Services -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Transfer <span class="hero-title-gradient">Services</span></h2>
            <p class="section-subtitle">Move money quickly and securely</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem;">
            <!-- Internal Transfers -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Internal Transfers</h3>
                <p style="color: var(--text-secondary); line-height: 1.8; margin-bottom: 1.5rem;">
                    Transfer money between your CITYBRIDGEBANK accounts instantly. No fees, no waiting.
                </p>
                <ul style="color: var(--text-secondary); line-height: 2; list-style: none;">
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Instant transfers</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> No transaction fees</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Available 24/7</li>
                </ul>
            </div>
            
            <!-- External Transfers -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-secondary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-external-link-alt"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">External Transfers</h3>
                <p style="color: var(--text-secondary); line-height: 1.8; margin-bottom: 1.5rem;">
                    Send money to other banks quickly and securely using ACH or wire transfers.
                </p>
                <ul style="color: var(--text-secondary); line-height: 2; list-style: none;">
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> ACH transfers (1-2 business days)</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Wire transfers (same day)</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Low transaction fees</li>
                </ul>
            </div>
            
            <!-- Person-to-Person -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-success); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-users"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Person-to-Person</h3>
                <p style="color: var(--text-secondary); line-height: 1.8; margin-bottom: 1.5rem;">
                    Send money to friends and family instantly using their email or phone number.
                </p>
                <ul style="color: var(--text-secondary); line-height: 2; list-style: none;">
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Instant delivery</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> No fees</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Easy to use</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Payment Services -->
<section class="section" style="background: rgba(102, 126, 234, 0.05);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Payment <span class="hero-title-gradient">Services</span></h2>
            <p class="section-subtitle">Pay bills and manage your payments effortlessly</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem;">
            <!-- Bill Pay -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-warning); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Online Bill Pay</h3>
                <p style="color: var(--text-secondary); line-height: 1.8; margin-bottom: 1.5rem;">
                    Pay your bills online and schedule recurring payments. Never miss a due date again.
                </p>
                <ul style="color: var(--text-secondary); line-height: 2; list-style: none;">
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Pay any biller</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Schedule payments</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Payment reminders</li>
                </ul>
            </div>
            
            <!-- Mobile Payments -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Mobile Payments</h3>
                <p style="color: var(--text-secondary); line-height: 1.8; margin-bottom: 1.5rem;">
                    Use your CITYBRIDGEBANK card with Apple Pay, Google Pay, or Samsung Pay.
                </p>
                <ul style="color: var(--text-secondary); line-height: 2; list-style: none;">
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Contactless payments</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Secure transactions</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Works everywhere</li>
                </ul>
            </div>
            
            <!-- Direct Deposit -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-secondary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-download"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem">Direct Deposit</h3>
                <p style="color: var(--text-secondary); line-height: 1.8; margin-bottom: 1.5rem;">
                    Have your paycheck, government benefits, or other payments deposited directly.
                </p>
                <ul style="color: var(--text-secondary); line-height: 2; list-style: none;">
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Faster access to funds</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> No check-cashing fees</li>
                    <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Easy setup</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Wire Transfers -->
<section class="section">
    <div class="container">
        <div class="glass-card" style="text-align: center; padding: 4rem 2rem;">
            <div style="font-size: 5rem; margin-bottom: 2rem; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                <i class="fas fa-globe"></i>
            </div>
            <h2 style="font-size: 2.5rem; margin-bottom: 1.5rem;">
                International <span class="hero-title-gradient">Wire Transfers</span>
            </h2>
            <p style="color: var(--text-secondary); max-width: 700px; margin: 0 auto 3rem; font-size: 1.1rem;">
                Send money to anyone, anywhere in the world. Our international wire transfer service is fast, secure, and affordable.
            </p>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
                <div class="glass-card" style="padding: 2rem;">
                    <h4 style="margin-bottom: 0.5rem; color: var(--accent-purple);">Same-Day Delivery</h4>
                    <p style="color: var(--text-muted); font-size: 0.95rem;">Most international transfers arrive the same business day</p>
                </div>
                <div class="glass-card" style="padding: 2rem;">
                    <h4 style="margin-bottom: 0.5rem; color: var(--accent-purple);">Competitive Rates</h4>
                    <p style="color: var(--text-muted); font-size: 0.95rem;">Low fees and great exchange rates</p>
                </div>
                <div class="glass-card" style="padding: 2rem;">
                    <h4 style="margin-bottom: 0.5rem; color: var(--accent-purple);">Full Tracking</h4>
                    <p style="color: var(--text-muted); font-size: 0.95rem;">Track your transfer every step of the way</p>
                </div>
            </div>
            <a href="register.php" class="btn btn-primary btn-lg">
                <i class="fas fa-paper-plane" style="margin-right: 0.5rem;"></i> Send Wire Transfer
            </a>
        </div>
    </div>
</section>

<!-- Security -->
<section class="section" style="background: rgba(102, 126, 234, 0.05);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Secure <span class="hero-title-gradient">Transactions</span></h2>
            <p class="section-subtitle">Your security is our priority</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <div class="glass-card">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <i class="fas fa-lock" style="font-size: 3rem; color: var(--accent-blue);"></i>
                </div>
                <h4 style="text-align: center; margin-bottom: 1rem;">End-to-End Encryption</h4>
                <p style="color: var(--text-secondary); text-align: center; font-size: 0.95rem;">
                    All transactions are encrypted with 256-bit SSL technology.
                </p>
            </div>
            
            <div class="glass-card">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <i class="fas fa-shield-alt" style="font-size: 3rem; color: var(--accent-blue);"></i>
                </div>
                <h4 style="text-align: center; margin-bottom: 1rem;">Fraud Protection</h4>
                <p style="color: var(--text-secondary); text-align: center; font-size: 0.95rem;">
                    Advanced fraud detection systems monitor all transactions 24/7.
                </p>
            </div>
            
            <div class="glass-card">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <i class="fas fa-fingerprint" style="font-size: 3rem; color: var(--accent-blue);"></i>
                </div>
                <h4 style="text-align: center; margin-bottom: 1rem">Multi-Factor Authentication</h4>
                <p style="color: var(--text-secondary); text-align: center; font-size: 0.95rem;">
                    Extra layer of security for all your transactions.
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>