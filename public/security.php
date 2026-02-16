<?php
// Initialize the session and environment before output
require_once '../includes/config.php';
// Then include the header to render the page and navigation
require_once '../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero" style="min-height: 60vh;">
    <div class="container">
        <div class="hero-content">
            <span style="background: var(--gradient-primary); padding: 0.5rem 1.5rem; border-radius: 50px; font-size: 0.9rem; font-weight: 600; display: inline-block; margin-bottom: 1.5rem;">
                <i class="fas fa-shield-alt" style="margin-right: 0.5rem;"></i> Security
            </span>
            
            <h1 class="hero-title">
                Your Security is <span class="hero-title-gradient">Our Priority</span>
            </h1>
            
            <p class="hero-subtitle">
                Bank-level security protecting your money and personal information 24/7.
            </p>
        </div>
    </div>
</section>

<!-- Security Features -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Advanced <span class="hero-title-gradient">Security Features</span></h2>
            <p class="section-subtitle">State-of-the-art protection for your accounts</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
            <!-- Encryption -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-lock"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">256-Bit Encryption</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    All data is protected with military-grade 256-bit SSL encryption, the same level of security used by major banks and government agencies.
                </p>
            </div>
            
            <!-- Multi-Factor Authentication -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-secondary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-fingerprint"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Multi-Factor Authentication</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    Add an extra layer of security with two-factor authentication. Use SMS, authenticator apps, or biometrics to verify your identity.
                </p>
            </div>
            
            <!-- Fraud Detection -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-success); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem">Fraud Detection</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    Our advanced AI-powered fraud detection system monitors all transactions 24/7, automatically blocking suspicious activity.
                </p>
            </div>
            
            <!-- Real-Time Alerts -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-warning); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-bell"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Real-Time Alerts</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    Get instant notifications for all account activity including logins, transactions, and password changes via SMS, email, or push notifications.
                </p>
            </div>
            
            <!-- Secure Login -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Secure Login</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    Protected by advanced authentication PIN system with automatic session timeouts and secure password management.
                </p>
            </div>
            
            <!-- Account Monitoring -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-secondary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-eye"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Account Monitoring</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    Continuous monitoring of your account with automatic detection of unusual patterns and immediate alerts to protect your funds.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Security Certifications -->
<section class="section" style="background: rgba(102, 126, 234, 0.05);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Security <span class="hero-title-gradient">Certifications</span></h2>
            <p class="section-subtitle">Industry-recognized security standards</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
            <div class="glass-card" style="text-align: center; padding: 2.5rem;">
                <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--accent-blue); margin-bottom: 1.5rem;"></i>
                <h3 style="font-size: 1.3rem; margin-bottom: 0.5rem;">FDIC Insured</h3>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Your deposits are insured up to $250,000</p>
            </div>
            
            <div class="glass-card" style="text-align: center; padding: 2.5rem;">
                <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--accent-blue); margin-bottom: 1.5rem;"></i>
                <h3 style="font-size: 1.3rem; margin-bottom: 0.5rem;">PCI DSS Compliant</h3>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Payment Card Industry Data Security Standard</p>
            </div>
            
            <div class="glass-card" style="text-align: center; padding: 2.5rem;">
                <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--accent-blue); margin-bottom: 1.5rem;"></i>
                <h3 style="font-size: 1.3rem; margin-bottom: 0.5rem;">SOC 2 Certified</h3>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Service Organization Control 2 Type II</p>
            </div>
            
            <div class="glass-card" style="text-align: center; padding: 2.5rem;">
                <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--accent-blue); margin-bottom: 1.5rem;"></i>
                <h3 style="font-size: 1.3rem; margin-bottom: 0.5rem;">OCC Regulated</h3>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Office of the Comptroller of the Currency</p>
            </div>
        </div>
    </div>
</section>

<!-- Tips for Staying Safe -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Tips for <span class="hero-title-gradient">Staying Safe</span></h2>
            <p class="section-subtitle">Best practices to protect your account</p>
        </div>
        
        <div class="glass-card" style="padding: 3rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <div style="display: flex; gap: 1rem; align-items: start;">
                    <div style="width: 50px; height: 50px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <span style="color: white; font-weight: 700;">1</span>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 0.5rem;">Use Strong Passwords</h4>
                        <p style="color: var(--text-secondary); font-size: 0.95rem;">Create unique, complex passwords and never share them with anyone.</p>
                    </div>
                </div>
                
                <div style="display: flex; gap: 1rem; align-items: start;">
                    <div style="width: 50px; height: 50px; background: var(--gradient-secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <span style="color: white; font-weight: 700;">2</span>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 0.5rem;">Enable Two-Factor Authentication</h4>
                        <p style="color: var(--text-secondary); font-size: 0.95rem;">Add an extra layer of security to protect your account from unauthorized access.</p>
                    </div>
                </div>
                
                <div style="display: flex; gap: 1rem; align-items: start;">
                    <div style="width: 50px; height: 50px; background: var(--gradient-success); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <span style="color: white; font-weight: 700;">3</span>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 0.5rem">Monitor Your Account</h4>
                        <p style="color: var(--text-secondary); font-size: 0.95rem;">Regularly check your account activity and report any suspicious transactions immediately.</p>
                    </div>
                </div>
                
                <div style="display: flex; gap: 1rem; align-items: start;">
                    <div style="width: 50px; height: 50px; background: var(--gradient-warning); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <span style="color: white; font-weight: 700;">4</span>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 0.5rem;">Keep Software Updated</h4>
                        <p style="color: var(--text-secondary); font-size: 0.95rem;">Keep your browser, operating system, and antivirus software up to date.</p>
                    </div>
                </div>
                
                <div style="display: flex; gap: 1rem; align-items: start;">
                    <div style="width: 50px; height: 50px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <span style="color: white; font-weight: 700;">5</span>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 0.5rem;">Beware of Phishing</h4>
                        <p style="color: var(--text-secondary); font-size: 0.95rem;">Never click on suspicious links or provide personal information in response to unsolicited requests.</p>
                    </div>
                </div>
                
                <div style="display: flex; gap: 1rem; align-items: start;">
                    <div style="width: 50px; height: 50px; background: var(--gradient-secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <span style="color: white; font-weight: 700;">6</span>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 0.5rem;">Use Secure Networks</h4>
                        <p style="color: var(--text-secondary); font-size: 0.95rem;">Avoid accessing your account on public Wi-Fi. Use a secure, private network whenever possible.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Security -->
<section class="section" style="background: rgba(102, 126, 234, 0.05);">
    <div class="container">
        <div class="glass-card glass-card-gradient" style="text-align: center; padding: 4rem 2rem;">
            <h2 style="font-size: 2.5rem; margin-bottom: 1.5rem;">
                Notice <span class="hero-title-gradient">Suspicious Activity</span>?
            </h2>
            <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto 2.5rem; font-size: 1.1rem;">
                If you notice any suspicious activity on your account or believe your account has been compromised, contact us immediately.
            </p>
            <div style="display: flex; gap: 2rem; justify-content: center; flex-wrap: wrap;">
                <a href="support.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-headset" style="margin-right: 0.5rem;"></i> Contact Support
                </a>
                <!-- Updated support phone number -->
                <a href="tel:+12489164654" class="btn btn-secondary btn-lg">
                    <i class="fas fa-phone" style="margin-right: 0.5rem;"></i> +12489164654
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>