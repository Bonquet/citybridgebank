<?php
// Load configuration first to start the session and set up environment
require_once '../includes/config.php';
// Then include the header for navigation and page layout
require_once '../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero" style="min-height: 40vh;">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">
                Compliance & <span class="hero-title-gradient">Regulatory</span>
            </h1>
            <p class="hero-subtitle">
                Our commitment to regulatory compliance and financial integrity.
            </p>
        </div>
    </div>
</section>

<!-- Compliance Content -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Regulatory <span class="hero-title-gradient">Compliance</span></h2>
            <p class="section-subtitle">Operating with integrity and transparency</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem; margin-bottom: 4rem;">
            <!-- FDIC -->
            <div class="glass-card" style="text-align: center; padding: 3rem 2rem;">
                <i class="fas fa-shield-alt" style="font-size: 4rem; color: var(--accent-blue); margin-bottom: 1.5rem;"></i>
                <h3 style="font-size: 1.8rem; margin-bottom: 1rem;">FDIC Insured</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    All deposits are insured by the Federal Deposit Insurance Corporation (FDIC) up to $250,000 per depositor, providing you with complete peace of mind.
                </p>
            </div>
            
            <!-- OCC -->
            <div class="glass-card" style="text-align: center; padding: 3rem 2rem;">
                <i class="fas fa-landmark" style="font-size: 4rem; color: var(--accent-purple); margin-bottom: 1.5rem;"></i>
                <h3 style="font-size: 1.8rem; margin-bottom: 1rem;">OCC Regulated</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    Regulated by the Office of the Comptroller of the Currency (OCC), ensuring we meet the highest standards of banking operations and consumer protection.
                </p>
            </div>
            
            <!-- NMLS -->
            <div class="glass-card" style="text-align: center; padding: 3rem 2rem;">
                <i class="fas fa-certificate" style="font-size: 4rem; color: var(--accent-pink); margin-bottom: 1.5rem;"></i>
                <h3 style="font-size: 1.8rem; margin-bottom: 1rem">NMLS Registered</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    Registered with the Nationwide Multistate Licensing System (NMLS) #123456, demonstrating our commitment to regulatory compliance.
                </p>
            </div>
        </div>
        
        <!-- AML & KYC -->
        <div class="glass-card" style="margin-bottom: 3rem;">
            <h2 style="margin-bottom: 2rem; color: var(--accent-purple);">Anti-Money Laundering (AML) & Know Your Customer (KYC)</h2>
            <p style="color: var(--text-secondary); margin-bottom: 1.5rem; line-height: 1.8;">
                CITYBRIDGEBANK maintains a comprehensive Anti-Money Laundering (AML) program in compliance with the Bank Secrecy Act (BSA) and USA PATRIOT Act. Our Know Your Customer (KYC) procedures ensure we verify the identity of all customers and understand their financial activities.
            </p>
            <ul style="color: var(--text-secondary); line-height: 2; list-style: none;">
                <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Customer identification and verification</li>
                <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Ongoing monitoring of transactions</li>
                <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Suspicious activity reporting</li>
                <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Enhanced due diligence for high-risk customers</li>
                <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Regular staff training on AML/KYC requirements</li>
            </ul>
        </div>
        
        <!-- Privacy -->
        <div class="glass-card" style="margin-bottom: 3rem;">
            <h2 style="margin-bottom: 2rem; color: var(--accent-purple);">Privacy & Data Protection</h2>
            <p style="color: var(--text-secondary); margin-bottom: 1.5rem; line-height: 1.8;">
                We are committed to protecting your personal and financial information. Our privacy practices comply with applicable laws and regulations, including the Gramm-Leach-Bliley Act (GLBA) and state privacy laws.
            </p>
            <ul style="color: var(--text-secondary); line-height: 2; list-style: none;">
                <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> 256-bit SSL encryption for all data transmission</li>
                <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Secure data storage with access controls</li>
                <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Regular security audits and assessments</li>
                <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Privacy policy compliance</li>
                <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Data breach notification procedures</li>
            </ul>
        </div>
        
        <!-- Fair Lending -->
        <div class="glass-card" style="margin-bottom: 3rem;">
            <h2 style="margin-bottom: 2rem; color: var(--accent-purple);">Fair Lending & Equal Credit Opportunity</h2>
            <p style="color: var(--text-secondary); margin-bottom: 1.5rem; line-height: 1.8;">
                CITYBRIDGEBANK is committed to providing equal access to credit and does not discriminate on the basis of race, color, religion, national origin, sex, marital status, age, or any other prohibited basis. We comply with the Equal Credit Opportunity Act (ECOA), Fair Housing Act, and other fair lending laws.
            </p>
        </div>
        
        <!-- Consumer Protection -->
        <div class="glass-card" style="margin-bottom: 3rem;">
            <h2 style="margin-bottom: 2rem; color: var(--accent-purple);">Consumer Protection</h2>
            <p style="color: var(--text-secondary); margin-bottom: 1.5rem; line-height: 1.8;">
                We adhere to all consumer protection regulations, including the Truth in Lending Act (TILA), Truth in Savings Act (TISA), Electronic Fund Transfer Act (EFTA), and the Dodd-Frank Wall Street Reform and Consumer Protection Act.
            </p>
            <ul style="color: var(--text-secondary); line-height: 2; list-style: none;">
                <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Clear disclosure of fees and terms</li>
                <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Accurate billing and transaction records</li>
                <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Error resolution procedures</li>
                <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Electronic fund transfer rights</li>
                <li><i class="fas fa-check" style="color: var(--accent-blue); margin-right: 0.75rem;"></i> Fair debt collection practices</li>
            </ul>
        </div>
        
        <!-- Contact -->
        <div class="glass-card glass-card-gradient" style="text-align: center; padding: 3rem 2rem;">
            <h2 style="font-size: 2rem; margin-bottom: 1.5rem;">
                Compliance <span class="hero-title-gradient">Contact</span>
            </h2>
            <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto 2rem; font-size: 1.1rem;">
                If you have any questions about our compliance practices or need to report a concern, please contact our Compliance Officer.
            </p>
            <div style="display: flex; gap: 2rem; justify-content: center; flex-wrap: wrap;">
                <a href="mailto:compliance@citybridgebank.com" class="btn btn-primary">
                    <i class="fas fa-envelope" style="margin-right: 0.5rem;"></i> compliance@citybridgebank.com
                </a>
                <a href="tel:+12489164654" class="btn btn-secondary">
                    <i class="fas fa-phone" style="margin-right: 0.5rem;"></i> +12489164654
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>