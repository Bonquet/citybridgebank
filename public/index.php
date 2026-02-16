<?php
// Load configuration to initialize the session and provide Security functions.
// By including config before header, we avoid output before redirects and
// ensure session_start() is called prior to using $_SESSION in the header.
require_once '../includes/config.php';
// Now include the header which relies on the session.
require_once '../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div style="margin-bottom: 2rem;">
                <span style="background: var(--gradient-primary); padding: 0.5rem 1.5rem; border-radius: 50px; font-size: 0.9rem; font-weight: 600; display: inline-block; margin-bottom: 1.5rem;">
                    <i class="fas fa-rocket" style="margin-right: 0.5rem;"></i> Next-Generation Banking
                </span>
            </div>
            
            <h1 class="hero-title">
                The Future of<br>
                <span class="hero-title-gradient">Digital Banking</span>
            </h1>
            
            <p class="hero-subtitle">
                Experience seamless, secure, and innovative banking solutions designed for the modern world. 
                Join thousands of customers who trust CITYBRIDGEBANK with their financial future.
            </p>
            
            <div class="hero-buttons">
                <a href="register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus" style="margin-right: 0.5rem;"></i> Get Started Free
                </a>
                <a href="products.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-play-circle" style="margin-right: 0.5rem;"></i> Learn More
                </a>
            </div>
            
            <!-- Stats -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-top: 4rem;">
                <div class="glass-card" style="padding: 1.5rem; text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: 800; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">50K+</div>
                    <p style="color: var(--text-secondary); margin-top: 0.5rem;">Active Users</p>
                </div>
                <div class="glass-card" style="padding: 1.5rem; text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: 800; background: var(--gradient-secondary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">99.9%</div>
                    <p style="color: var(--text-secondary); margin-top: 0.5rem;">Uptime</p>
                </div>
                <div class="glass-card" style="padding: 1.5rem; text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: 800; background: var(--gradient-success); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">24/7</div>
                    <p style="color: var(--text-secondary); margin-top: 0.5rem;">Support</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Why Choose <span class="hero-title-gradient">CITYBRIDGEBANK</span>?</h2>
            <p class="section-subtitle">Discover the features that set us apart from traditional banks</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <!-- Feature 1 -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Bank-Level Security</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    Your money and data are protected with 256-bit encryption and advanced security protocols. 
                    We use state-of-the-art security measures to keep your information safe.
                </p>
            </div>
            
            <!-- Feature 2 -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-secondary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Instant Transfers</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    Send and receive money instantly with our lightning-fast transfer system. 
                    No more waiting days for your money to arrive.
                </p>
            </div>
            
            <!-- Feature 3 -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-success); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Mobile Banking</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    Access your account anytime, anywhere with our mobile-friendly platform. 
                    Manage your finances on the go with ease.
                </p>
            </div>
            
            <!-- Feature 4 -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-warning); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-percentage"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Competitive Rates</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    Enjoy competitive interest rates on savings and low fees on transactions. 
                    Your money works harder with us.
                </p>
            </div>
            
            <!-- Feature 5 -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-headset"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">24/7 Support</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    Our dedicated support team is available around the clock to help you with any questions or issues. 
                    Never feel alone in your banking journey.
                </p>
            </div>
            
            <!-- Feature 6 -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-secondary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Smart Analytics</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    Track your spending, set budgets, and get insights into your financial health. 
                    Make smarter decisions with our analytics tools.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Services Preview Section -->
<section class="section" style="background: rgba(102, 126, 234, 0.05);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Our <span class="hero-title-gradient">Services</span></h2>
            <p class="section-subtitle">Comprehensive banking solutions for all your needs</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
            <!-- Service 1 -->
            <div class="glass-card" style="padding: 2rem; text-align: center; transition: all 0.3s ease;">
                <div style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2rem;">
                    <i class="fas fa-university"></i>
                </div>
                <h3 style="margin-bottom: 1rem;">Personal Banking</h3>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    Manage your personal finances with ease. Savings, checking, and investment accounts.
                </p>
                <a href="products.php" class="btn btn-secondary btn-sm">
                    Learn More <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
                </a>
            </div>
            
            <!-- Service 2 -->
            <div class="glass-card" style="padding: 2rem; text-align: center; transition: all 0.3s ease;">
                <div style="width: 80px; height: 80px; background: var(--gradient-secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2rem;">
                    <i class="fas fa-building"></i>
                </div>
                <h3 style="margin-bottom: 1rem;">Business Banking</h3>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    Solutions tailored for businesses. Payroll, invoicing, and business accounts.
                </p>
                <a href="products.php" class="btn btn-secondary btn-sm">
                    Learn More <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
                </a>
            </div>
            
            <!-- Service 3 -->
            <div class="glass-card" style="padding: 2rem; text-align: center; transition: all 0.3s ease;">
                <div style="width: 80px; height: 80px; background: var(--gradient-success); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2rem;">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h3 style="margin-bottom: 1rem">Wire Transfers</h3>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    Send money anywhere in the world quickly and securely with competitive exchange rates.
                </p>
                <a href="services.php" class="btn btn-secondary btn-sm">
                    Learn More <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
                </a>
            </div>
            
            <!-- Service 4 -->
            <div class="glass-card" style="padding: 2rem; text-align: center; transition: all 0.3s ease;">
                <div style="width: 80px; height: 80px; background: var(--gradient-warning); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2rem;">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h3 style="margin-bottom: 1rem;">Cards</h3>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    Premium debit and credit cards with rewards, cashback, and exclusive benefits.
                </p>
                <a href="products.php" class="btn btn-secondary btn-sm">
                    Learn More <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section">
    <div class="container">
        <div class="glass-card glass-card-gradient" style="text-align: center; padding: 4rem 2rem;">
            <h2 style="font-size: 2.5rem; margin-bottom: 1.5rem;">
                Ready to Start Your <span class="hero-title-gradient">Banking Journey</span>?
            </h2>
            <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto 2.5rem; font-size: 1.2rem;">
                Join thousands of satisfied customers who trust CITYBRIDGEBANK with their financial future. 
                Opening an account takes just a few minutes.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus" style="margin-right: 0.5rem;"></i> Open Free Account
                </a>
                <a href="support.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-phone" style="margin-right: 0.5rem;"></i> Contact Us
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>