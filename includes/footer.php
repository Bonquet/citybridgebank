</main>

<!-- Footer -->
<footer class='footer'>
    <div class='footer-content'>
        <!-- About Section -->
        <div class='footer-section'>
            <h3>About CITYBRIDGEBANK</h3>
            <p style='color: var(--text-secondary); line-height: 1.8;'>
                We are a modern digital bank committed to providing secure, innovative, and user-friendly banking solutions. Join thousands of satisfied customers who trust us with their financial future.
            </p>
            <div style='margin-top: 1.5rem; display: flex; gap: 1rem;'>
                <a href='#' style='color: var(--text-primary); font-size: 1.5rem;'><i class='fab fa-facebook'></i></a>
                <a href='#' style='color: var(--text-primary); font-size: 1.5rem;'><i class='fab fa-twitter'></i></a>
                <a href='#' style='color: var(--text-primary); font-size: 1.5rem;'><i class='fab fa-linkedin'></i></a>
                <a href='#' style='color: var(--text-primary); font-size: 1.5rem;'><i class='fab fa-instagram'></i></a>
            </div>
        </div>
        
        <!-- Quick Links -->
        <div class='footer-section'>
            <h3>Quick Links</h3>
            <ul class='footer-links'>
                <li><a href='index.php'><i class='fas fa-chevron-right' style='font-size: 0.8rem; margin-right: 0.5rem;'></i> Home</a></li>
                <li><a href='about.php'><i class='fas fa-chevron-right' style='font-size: 0.8rem; margin-right: 0.5rem;'></i> About Us</a></li>
                <li><a href='products.php'><i class='fas fa-chevron-right' style='font-size: 0.8rem; margin-right: 0.5rem;'></i> Products</a></li>
                <li><a href='services.php'><i class='fas fa-chevron-right' style='font-size: 0.8rem; margin-right: 0.5rem;'></i> Services</a></li>
                <li><a href='fees.php'><i class='fas fa-chevron-right' style='font-size: 0.8rem; margin-right: 0.5rem;'></i> Fees &amp; Limits</a></li>
            </ul>
        </div>
        
        <!-- Support -->
        <div class='footer-section'>
            <h3>Support</h3>
            <ul class='footer-links'>
                <li><a href='support.php'><i class='fas fa-chevron-right' style='font-size: 0.8rem; margin-right: 0.5rem;'></i> Help Center</a></li>
                <li><a href='security.php'><i class='fas fa-chevron-right' style='font-size: 0.8rem; margin-right: 0.5rem;'></i> Security</a></li>
                <li><a href='terms.php'><i class='fas fa-chevron-right' style='font-size: 0.8rem; margin-right: 0.5rem;'></i> Terms of Service</a></li>
                <li><a href='privacy.php'><i class='fas fa-chevron-right' style='font-size: 0.8rem; margin-right: 0.5rem;'></i> Privacy Policy</a></li>
                <li><a href='compliance.php'><i class='fas fa-chevron-right' style='font-size: 0.8rem; margin-right: 0.5rem;'></i> Compliance</a></li>
            </ul>
        </div>
        
        <!-- Dynamic Footer Path Script -->
        <script>
            // Fix footer links based on current directory
            (function() {
                const currentPath = window.location.pathname;
                let prefix = '';
                
                if (currentPath.includes('/dashboard/') || currentPath.includes('/admin/')) {
                    prefix = '../public/';
                } else if (currentPath.includes('/public/')) {
                    prefix = '';
                } else {
                    prefix = 'public/';
                }
                
                // Update all footer links
                const footerLinks = document.querySelectorAll('.footer-links a');
                footerLinks.forEach(link => {
                    const href = link.getAttribute('href');
                    // Only update internal links; ignore absolute URLs
                    if (href && !href.startsWith('http')) {
                        link.setAttribute('href', prefix + href);
                    }
                });
                
                // Update contact button
                const contactBtn = document.querySelector('.footer-section a.btn');
                if (contactBtn) {
                    contactBtn.setAttribute('href', prefix + 'support.php');
                }
            })();
        </script>
        
        <!-- Contact -->
        <div class='footer-section'>
            <h3>Contact Us</h3>
            <ul class='footer-links'>
                <li><i class='fas fa-phone' style='margin-right: 0.75rem; color: var(--accent-purple);'></i> +12489164654</li>
                <li><i class='fas fa-envelope' style='margin-right: 0.75rem; color: var(--accent-purple);'></i> support@citybridgebank.com</li>
                <li><i class='fas fa-map-marker-alt' style='margin-right: 0.75rem; color: var(--accent-purple);'></i> North Miami, FL</li>
            </ul>
            <div style='margin-top: 1.5rem;'>
                <a href='support.php' class='btn btn-gradient btn-sm'>
                    <i class='fas fa-headset'></i> Contact Support
                </a>
            </div>
        </div>
    </div>
    
    <!-- Footer Bottom -->
    <div class='footer-bottom'>
        <p>&amp;copy; 2024 CITYBRIDGEBANK. All rights reserved.</p>
        <p style='margin-top: 0.5rem; font-size: 0.9rem;'>
            Member FDIC | Equal Housing Lender | NMLS #123456
        </p>
    </div>
</footer>

<!-- Modern JavaScript -->
<script src='<?php 
$request_uri = $_SERVER['REQUEST_URI'] ?? '';
// Similar logic to header: when page is inside dashboard, admin, or public directories
// the JS file resides one level above the current directory. Append "../" accordingly.
if (strpos($request_uri, '/dashboard') !== false ||
    strpos($request_uri, '/admin') !== false ||
    strpos($request_uri, '/public') !== false) {
    echo '../js/modern-main.js';
} else {
    echo 'js/modern-main.js';
}
?>'></script>

    <!-- Fallback: hide the loading screen after a delay -->
    <script>
        // Fallback to hide loading screen after 3 seconds in case the
        // main script fails to hide it (e.g., due to JS errors). This
        // ensures that users are not stuck on the loading overlay.
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                var loading = document.getElementById('loading-screen');
                if (loading && loading.style.visibility !== 'hidden') {
                    loading.style.opacity = '0';
                    loading.style.visibility = 'hidden';
                }
            }, 3000);
        });
    </script>

    <!-- Fallback to hide the loading screen after a delay -->
    <script>
        // In case the main JS file fails to remove the loading screen (e.g. if the script
        // fails to load or execute), automatically hide it after a few seconds.
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var loadingScreen = document.getElementById('loading-screen');
                if (loadingScreen) {
                    loadingScreen.style.opacity = '0';
                    loadingScreen.style.visibility = 'hidden';
                }
            }, 3000);
        });
    </script>

    <!-- Display flash messages -->
    <?php if (isset($_SESSION['flash_message'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var message = <?php echo json_encode($_SESSION['flash_message']); ?>;
            var type = <?php echo json_encode($_SESSION['flash_type'] ?? 'info'); ?>;
            // Remove existing notification elements if any
            var existing = document.querySelector('.notification');
            if (existing) { existing.remove(); }
            // Create notification element
            var notification = document.createElement('div');
            notification.className = 'notification alert alert-' + type;
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '10000';
            notification.style.minWidth = '300px';
            notification.style.padding = '1rem';
            notification.style.borderRadius = '0.5rem';
            notification.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
            notification.textContent = message;
            document.body.appendChild(notification);
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                if (notification) notification.remove();
            }, 5000);
        });
    </script>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); endif; ?>

</body>
</html>