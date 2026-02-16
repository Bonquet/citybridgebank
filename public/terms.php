<?php
// Load config before outputting anything.  This initializes the session
// and makes configuration and security functions available.
require_once '../includes/config.php';
// Then include the header for the page structure and navigation.
require_once '../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero" style="min-height: 40vh;">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">
                Terms of <span class="hero-title-gradient">Service</span>
            </h1>
            <p class="hero-subtitle">
                Please read these terms carefully before using CITYBRIDGEBANK services.
            </p>
        </div>
    </div>
</section>

<!-- Terms Content -->
<section class="section">
    <div class="container">
        <div class="glass-card" style="max-width: 900px; margin: 0 auto;">
            <h2 style="margin-bottom: 2rem; color: var(--accent-purple);">1. Acceptance of Terms</h2>
            <p style="color: var(--text-secondary); margin-bottom: 2rem; line-height: 1.8;">
                By accessing or using CITYBRIDGEBANK services, you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using our services.
            </p>
            
            <h2 style="margin-bottom: 2rem; color: var(--accent-purple);">2. Account Registration</h2>
            <p style="color: var(--text-secondary); margin-bottom: 2rem; line-height: 1.8;">
                To use our services, you must register for an account. You must provide accurate, current, and complete information during registration. You are responsible for maintaining the confidentiality of your account credentials.
            </p>
            
            <h2 style="margin-bottom: 2rem; color: var(--accent-purple);">3. Services</h2>
            <p style="color: var(--text-secondary); margin-bottom: 2rem; line-height: 1.8;">
                CITYBRIDGEBANK provides various banking services including checking accounts, savings accounts, transfers, bill payments, and more. We reserve the right to modify, suspend, or discontinue any service at any time without notice.
            </p>
            
            <h2 style="margin-bottom: 2rem; color: var(--accent-purple);">4. Security and Privacy</h2>
            <p style="color: var(--text-secondary); margin-bottom: 2rem; line-height: 1.8;">
                We employ industry-standard security measures to protect your information. However, no method of transmission over the Internet is completely secure. Please review our Privacy Policy for more information about how we protect your data.
            </p>
            
            <h2 style="margin-bottom: 2rem; color: var(--accent-purple);">5. User Responsibilities</h2>
            <p style="color: var(--text-secondary); margin-bottom: 2rem; line-height: 1.8;">
                You agree to use our services only for lawful purposes and in accordance with these Terms. You are responsible for all activities that occur under your account. You must notify us immediately of any unauthorized use of your account.
            </p>
            
            <h2 style="margin-bottom: 2rem; color: var(--accent-purple);">6. Fees and Charges</h2>
            <p style="color: var(--text-secondary); margin-bottom: 2rem; line-height: 1.8;">
                Certain services may incur fees. All fees are disclosed in our Fees & Limits page. By using a fee-based service, you agree to pay the applicable fees. We reserve the right to modify our fees at any time.
            </p>
            
            <h2 style="margin-bottom: 2rem; color: var(--accent-purple);">7. Limitation of Liability</h2>
            <p style="color: var(--text-secondary); margin-bottom: 2rem; line-height: 1.8;">
                CITYBRIDGEBANK shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising from your use of our services. Our liability is limited to the maximum extent permitted by law.
            </p>
            
            <h2 style="margin-bottom: 2rem; color: var(--accent-purple);">8. Termination</h2>
            <p style="color: var(--text-secondary); margin-bottom: 2rem; line-height: 1.8;">
                We may terminate or suspend your account at any time for violation of these Terms or for any other reason at our sole discretion. Upon termination, your right to use the services will immediately cease.
            </p>
            
            <h2 style="margin-bottom: 2rem; color: var(--accent-purple);">9. Governing Law</h2>
            <p style="color: var(--text-secondary); margin-bottom: 2rem; line-height: 1.8;">
                These Terms shall be governed by and construed in accordance with the laws of the United States. Any disputes arising under these Terms shall be subject to the exclusive jurisdiction of the courts of the United States.
            </p>
            
            <h2 style="margin-bottom: 2rem; color: var(--accent-purple);">10. Changes to Terms</h2>
            <p style="color: var(--text-secondary); margin-bottom: 2rem; line-height: 1.8;">
                We reserve the right to modify these Terms at any time. We will notify you of any changes by posting the new Terms on this page. Your continued use of the services after such modifications constitutes your acceptance of the new Terms.
            </p>
            
            <h2 style="margin-bottom: 2rem; color: var(--accent-purple);">11. Contact Us</h2>
            <p style="color: var(--text-secondary); margin-bottom: 2rem; line-height: 1.8;">
                If you have any questions about these Terms, please contact us at:
            </p>
            <p style="color: var(--text-secondary); line-height: 1.8;">
                <strong>Email:</strong> support@citybridgebank.com<br>
                <strong>Phone:</strong> +12489164654
            </p>
            
            <p style="color: var(--text-muted); margin-top: 3rem; font-size: 0.9rem;">
                Last Updated: February 2024
            </p>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>