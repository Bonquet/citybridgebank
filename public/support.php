<?php
// Load configuration first to start the session.  The support page uses
// Security functions to generate CSRF tokens and may need to check login
// status.  Loading config before the header prevents header output before
// any potential redirect.
require_once '../includes/config.php';

// Include the header after config.  The header will handle navigation and
// route guards based on the session state.
require_once '../includes/header.php';

// Generate a CSRF token for this session; used to prevent CSRF attacks.
$csrf_token = Security::generateCSRFToken();
?>

<!-- Hero Section -->
<section class="hero" style="min-height: 60vh;">
    <div class="container">
        <div class="hero-content">
            <span style="background: var(--gradient-primary); padding: 0.5rem 1.5rem; border-radius: 50px; font-size: 0.9rem; font-weight: 600; display: inline-block; margin-bottom: 1.5rem;">
                <i class="fas fa-headset" style="margin-right: 0.5rem;"></i> Support
            </span>
            
            <h1 class="hero-title">
                How Can We <span class="hero-title-gradient">Help</span>?
            </h1>
            
            <p class="hero-subtitle">
                Our dedicated support team is available 24/7 to assist you with any questions or concerns.
            </p>
        </div>
    </div>
</section>

<!-- Support Options -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Get in <span class="hero-title-gradient">Touch</span></h2>
            <p class="section-subtitle">Choose the support option that works best for you</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <!-- Live Chat -->
            <div class="glass-card" style="text-align: center; padding: 2.5rem;">
                <div style="width: 100px; height: 100px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem;">
                    <i class="fas fa-comments" style="font-size: 3rem; color: white;"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Live Chat</h3>
                <p style="color: var(--text-secondary); margin-bottom: 2rem;">
                    Chat with our support team in real-time. Available 24/7.
                </p>
                <button class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-comment-dots" style="margin-right: 0.5rem;"></i> Start Chat
                </button>
            </div>
            
            <!-- Phone -->
            <div class="glass-card" style="text-align: center; padding: 2.5rem;">
                <div style="width: 100px; height: 100px; background: var(--gradient-secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem;">
                    <i class="fas fa-phone" style="font-size: 3rem; color: white;"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Phone Support</h3>
                <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                    Call us directly for immediate assistance.
                </p>
                <!-- Updated phone number per support requirements -->
                <p style="font-size: 1.5rem; font-weight: 700; color: var(--accent-blue); margin-bottom: 2rem;">
                    +12489164654
                </p>
                <a href="tel:+12489164654" class="btn btn-gradient" style="width: 100%;">
                    <i class="fas fa-phone-alt" style="margin-right: 0.5rem;"></i> Call Now
                </a>
            </div>
            
            <!-- Email -->
            <div class="glass-card" style="text-align: center; padding: 2.5rem;">
                <div style="width: 100px; height: 100px; background: var(--gradient-success); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem;">
                    <i class="fas fa-envelope" style="font-size: 3rem; color: white;"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Email Support</h3>
                <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                    Send us an email and we'll respond within 24 hours.
                </p>
                <p style="font-size: 1.1rem; font-weight: 600; color: var(--accent-blue); margin-bottom: 2rem;">
                    support@citibridge.net
                </p>
                <a href="mailto:support@citibridge.net" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-paper-plane" style="margin-right: 0.5rem;"></i> Send Email
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Submit Ticket -->
<section class="section" style="background: rgba(102, 126, 234, 0.05);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Submit a <span class="hero-title-gradient">Support Ticket</span></h2>
            <p class="section-subtitle">We'll get back to you as soon as possible</p>
        </div>
        
        <div class="glass-card" style="max-width: 700px; margin: 0 auto;">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php 
                    echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php 
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <form action="../includes/submit_ticket.php" method="POST">
                <!-- CSRF Token for support ticket submission -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <!-- Subject -->
                <div class="form-group">
                    <label for="subject" class="form-label">
                        <i class="fas fa-heading" style="margin-right: 0.5rem;"></i> Subject
                    </label>
                    <input 
                        type="text" 
                        id="subject" 
                        name="subject" 
                        class="form-input" 
                        placeholder="Brief description of your issue"
                        required
                    >
                </div>
                
                <!-- Category -->
                <div class="form-group">
                    <label for="category" class="form-label">
                        <i class="fas fa-folder" style="margin-right: 0.5rem;"></i> Category
                    </label>
                    <select id="category" name="category" class="form-select" required>
                        <option value="">Select a category</option>
                        <option value="account">Account Issues</option>
                        <option value="transaction">Transaction Problems</option>
                        <option value="technical">Technical Support</option>
                        <option value="security">Security Concerns</option>
                        <option value="billing">Billing & Fees</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <!-- Priority -->
                <div class="form-group">
                    <label for="priority" class="form-label">
                        <i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i> Priority
                    </label>
                    <select id="priority" name="priority" class="form-select" required>
                        <option value="">Select priority level</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                
                <!-- Message -->
                <div class="form-group">
                    <label for="message" class="form-label">
                        <i class="fas fa-comment-alt" style="margin-right: 0.5rem;"></i> Message
                    </label>
                    <textarea 
                        id="message" 
                        name="message" 
                        class="form-textarea" 
                        placeholder="Please describe your issue in detail"
                        required
                    ></textarea>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
                    <i class="fas fa-paper-plane" style="margin-right: 0.5rem;"></i> Submit Ticket
                </button>
            </form>
        </div>
    </div>
</section>

<!-- FAQs -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Frequently Asked <span class="hero-title-gradient">Questions</span></h2>
            <p class="section-subtitle">Quick answers to common questions</p>
        </div>
        
        <div style="max-width: 800px; margin: 0 auto;">
            <div class="glass-card" style="margin-bottom: 1rem;">
                <h4 style="margin-bottom: 0.5rem; color: var(--accent-purple);">How long does it take to process a withdrawal?</h4>
                <p style="color: var(--text-secondary);">Withdrawals to CITIBRIDGE accounts are instant. External bank transfers take 1-2 business days.</p>
            </div>
            
            <div class="glass-card" style="margin-bottom: 1rem;">
                <h4 style="margin-bottom: 0.5rem; color: var(--accent-purple);">What should I do if I lost my debit card?</h4>
                <p style="color: var(--text-secondary);">Contact us immediately at +12489164654 or through your dashboard to freeze your card and order a replacement.</p>
            </div>
            
            <div class="glass-card" style="margin-bottom: 1rem;">
                <h4 style="margin-bottom: 0.5rem; color: var(--accent-purple);">Is my money FDIC insured?</h4>
                <p style="color: var(--text-secondary);">Yes, all deposits are FDIC insured up to $250,000 per depositor, providing you with complete peace of mind.</p>
            </div>
            
            <div class="glass-card" style="margin-bottom: 1rem;">
                <h4 style="margin-bottom: 0.5rem; color: var(--accent-purple);">How do I reset my password?</h4>
                <p style="color: var(--text-secondary);">Click on "Forgot Password" on the login page and follow the instructions. You'll receive an email with reset instructions.</p>
            </div>
            
            <div class="glass-card" style="margin-bottom: 1rem;">
                <h4 style="margin-bottom: 0.5rem; color: var(--accent-purple);">Can I have multiple accounts?</h4>
                <p style="color: var(--text-secondary);">Yes, you can have multiple checking, savings, and money market accounts under a single login.</p>
            </div>
        </div>
    </div>
</section>

<!-- Response Time Guarantee -->
<section class="section" style="background: rgba(102, 126, 234, 0.05);">
    <div class="container">
        <div class="glass-card glass-card-gradient" style="text-align: center; padding: 4rem 2rem;">
            <h2 style="font-size: 2.5rem; margin-bottom: 2rem;">
                Response Time <span class="hero-title-gradient">Guarantee</span>
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                <div>
                    <div style="font-size: 3rem; font-weight: 800; color: var(--accent-blue);">15 min</div>
                    <p style="color: var(--text-secondary); margin-top: 0.5rem;">Live Chat</p>
                </div>
                <div>
                    <div style="font-size: 3rem; font-weight: 800; color: var(--accent-purple);">1 hour</div>
                    <p style="color: var(--text-secondary); margin-top: 0.5rem;">Phone Support</p>
                </div>
                <div>
                    <div style="font-size: 3rem; font-weight: 800; color: var(--accent-pink);">24 hours</div>
                    <p style="color: var(--text-secondary); margin-top: 0.5rem;">Email Response</p>
                </div>
                <div>
                    <div style="font-size: 3rem; font-weight: 800; color: var(--accent-gold);">48 hours</div>
                    <p style="color: var(--text-secondary); margin-top: 0.5rem;">Ticket Resolution</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>