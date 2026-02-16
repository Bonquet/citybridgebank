<?php
// Include configuration first to start the session and set up environment
require_once '../includes/config.php';
// Then include the header to output the navigation and handle routing
require_once '../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero" style="min-height: 60vh;">
    <div class="container">
        <div class="hero-content">
            <span style="background: var(--gradient-primary); padding: 0.5rem 1.5rem; border-radius: 50px; font-size: 0.9rem; font-weight: 600; display: inline-block; margin-bottom: 1.5rem;">
                <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i> About Us
            </span>
            
            <h1 class="hero-title">
                About <span class="hero-title-gradient">CITYBRIDGEBANK</span>
            </h1>
            
            <p class="hero-subtitle">
                Building bridges to financial freedom through innovation, trust, and exceptional service since 2024.
            </p>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Our <span class="hero-title-gradient">Mission & Vision</span></h2>
            <p class="section-subtitle">Discover what drives us to excellence every day</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem;">
            <!-- Mission Card -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-bullseye"></i>
                </div>
                <h3 style="font-size: 1.8rem; margin-bottom: 1rem;">Our Mission</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    To provide accessible, secure, and innovative banking solutions that empower individuals and businesses to achieve their financial goals. We are committed to delivering exceptional service while maintaining the highest standards of integrity and trust.
                </p>
            </div>
            
            <!-- Vision Card -->
            <div class="glass-card glass-card-gradient">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; background: var(--gradient-secondary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="fas fa-eye"></i>
                </div>
                <h3 style="font-size: 1.8rem; margin-bottom: 1rem;">Our Vision</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    To be the leading digital bank that bridges the gap between traditional banking and modern technology, creating a seamless financial experience for everyone. We envision a future where banking is simple, transparent, and accessible to all.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Our Story -->
<section class="section" style="background: rgba(102, 126, 234, 0.05);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Our <span class="hero-title-gradient">Story</span></h2>
            <p class="section-subtitle">The journey from idea to innovation</p>
        </div>
        
        <div class="glass-card">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center;">
                <div>
                    <h3 style="font-size: 2rem; margin-bottom: 1.5rem; color: var(--accent-purple);">
                        Founded in 2024
                    </h3>
                    <p style="color: var(--text-secondary); line-height: 1.8; margin-bottom: 1.5rem;">
                        CITYBRIDGEBANK was born from a simple idea: banking should be easy, secure, and accessible to everyone. Our founders recognized the challenges people faced with traditional banking and set out to create a better way.
                    </p>
                    <p style="color: var(--text-secondary); line-height: 1.8; margin-bottom: 1.5rem;">
                        Starting as a small team of passionate innovators, we've grown into a trusted financial institution serving thousands of customers across the nation. Our commitment to excellence has remained unchanged since day one.
                    </p>
                    <p style="color: var(--text-secondary); line-height: 1.8;">
                        Today, we continue to push the boundaries of what's possible in digital banking, always keeping our customers' needs at the heart of everything we do.
                    </p>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 8rem; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent; opacity: 0.9;">
                        <i class="fas fa-building-columns"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Values -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Our <span class="hero-title-gradient">Core Values</span></h2>
            <p class="section-subtitle">The principles that guide our every action</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            <div class="glass-card" style="text-align: center; padding: 2rem;">
                <div style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-shield-alt" style="font-size: 2rem; color: white;"></i>
                </div>
                <h3 style="font-size: 1.3rem; margin-bottom: 1rem;">Security First</h3>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">
                    Your security is our top priority. We employ state-of-the-art encryption and security measures.
                </p>
            </div>
            
            <div class="glass-card" style="text-align: center; padding: 2rem;">
                <div style="width: 80px; height: 80px; background: var(--gradient-secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-heart" style="font-size: 2rem; color: white;"></i>
                </div>
                <h3 style="font-size: 1.3rem; margin-bottom: 1rem;">Customer Focus</h3>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">
                    Our customers are at the heart of everything we do. Your success is our success.
                </p>
            </div>
            
            <div class="glass-card" style="text-align: center; padding: 2rem;">
                <div style="width: 80px; height: 80px; background: var(--gradient-success); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-lightbulb" style="font-size: 2rem; color: white;"></i>
                </div>
                <h3 style="font-size: 1.3rem; margin-bottom: 1rem;">Innovation</h3>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">
                    We constantly innovate to bring you the best banking experience possible.
                </p>
            </div>
            
            <div class="glass-card" style="text-align: center; padding: 2rem;">
                <div style="width: 80px; height: 80px; background: var(--gradient-warning); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-handshake" style="font-size: 2rem; color: white;"></i>
                </div>
                <h3 style="font-size: 1.3rem; margin-bottom: 1rem;">Integrity</h3>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">
                    We operate with complete transparency and honesty in all our dealings.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Leadership Team -->
<section class="section" style="background: rgba(102, 126, 234, 0.05);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Leadership <span class="hero-title-gradient">Team</span></h2>
            <p class="section-subtitle">Meet the people driving our vision forward</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
            <div class="glass-card" style="text-align: center;">
                <div style="width: 120px; height: 120px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-user-tie" style="font-size: 3rem; color: white;"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">John Mitchell</h3>
                <p style="color: var(--accent-purple); font-weight: 600; margin-bottom: 1rem;">Chief Executive Officer</p>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">
                    20+ years of experience in banking and financial services leadership.
                </p>
            </div>
            
            <div class="glass-card" style="text-align: center;">
                <div style="width: 120px; height: 120px; background: var(--gradient-secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-user-tie" style="font-size: 3rem; color: white;"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Sarah Chen</h3>
                <p style="color: var(--accent-pink); font-weight: 600; margin-bottom: 1rem;">Chief Technology Officer</p>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">
                    Expert in fintech innovation and digital transformation strategies.
                </p>
            </div>
            
            <div class="glass-card" style="text-align: center;">
                <div style="width: 120px; height: 120px; background: var(--gradient-success); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-user-tie" style="font-size: 3rem; color: white;"></i>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Michael Roberts</h3>
                <p style="color: var(--accent-blue); font-weight: 600; margin-bottom: 1rem;">Chief Financial Officer</p>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">
                    Former investment banker with extensive experience in financial management.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Trust & Compliance -->
<section class="section">
    <div class="container">
        <div class="glass-card" style="text-align: center; padding: 4rem 2rem;">
            <h2 style="font-size: 2.5rem; margin-bottom: 1.5rem;">
                Trust & <span class="hero-title-gradient">Compliance</span>
            </h2>
            <p style="color: var(--text-secondary); max-width: 700px; margin: 0 auto 3rem; font-size: 1.1rem;">
                We are committed to maintaining the highest standards of regulatory compliance and financial integrity. Your trust is our most valuable asset.
            </p>
            <div style="display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap;">
                <div class="glass-card" style="padding: 1.5rem; text-align: center;">
                    <i class="fas fa-check-circle" style="font-size: 2rem; color: var(--accent-blue); margin-bottom: 1rem;"></i>
                    <h4 style="margin-bottom: 0.5rem;">FDIC Insured</h4>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Your deposits protected</p>
                </div>
                <div class="glass-card" style="padding: 1.5rem; text-align: center;">
                    <i class="fas fa-check-circle" style="font-size: 2rem; color: var(--accent-blue); margin-bottom: 1rem;"></i>
                    <h4 style="margin-bottom: 0.5rem;">OCC Regulated</h4>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Federal oversight</p>
                </div>
                <div class="glass-card" style="padding: 1.5rem; text-align: center;">
                    <i class="fas fa-check-circle" style="font-size: 2rem; color: var(--accent-blue); margin-bottom: 1rem;"></i>
                    <h4 style="margin-bottom: 0.5rem;">Secure & Encrypted</h4>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">256-bit encryption</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>