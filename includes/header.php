<?php
// Session handling is centralized in `includes/config.php`. Do not start the session here to avoid
// duplicate session_start() calls which can trigger warnings such as
// "session_start(): Ignoring session_start() because a session is already active".  The
// configuration file includes a session_start() if the session is not already
// started, so removing it from the header prevents redundant calls and keeps
// output clean.

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple path detection
$request_uri = $_SERVER['REQUEST_URI'] ?? '';
$current_path = $_SERVER['PHP_SELF'] ?? '';

// -----------------------------------------------------------------------------
// Authentication Routing Guards
//
// Prevent authenticated users (both regular and admin) from browsing public
// marketing pages. If a logged-in user lands on a page under /public (e.g.
// index.php, about.php) we immediately redirect them to their respective
// dashboard. This ensures that once authenticated, users remain within the
// application workflow and do not see the public-facing site. We exclude
// essential auth routes like login, register, forgot_password, and reset_password
// from this redirect to avoid loops and allow access when necessary.
if (strpos($request_uri, '/public') !== false) {
    // Determine the basename of the requested file
    $basename = basename($current_path);
    // Public auth pages we allow even if logged in
    $auth_pages = ['login.php', 'register.php', 'forgot_password.php', 'reset_password.php'];
    // If a user is logged in and requesting a public page (not in allowed list)
    if (isset($_SESSION['user_id']) && !in_array($basename, $auth_pages, true)) {
        // Redirect regular users to their dashboard
        header('Location: ../dashboard/index.php');
        exit;
    }
    // If an admin is logged in and requesting any public page, send to admin dashboard
    if (isset($_SESSION['admin_id']) && !in_array($basename, $auth_pages, true)) {
        header('Location: ../admin/index.php');
        exit;
    }
}

// Determine base path for assets
// For pages within dashboard, admin, or public directories, the asset path needs to
// traverse up one level to reference the shared `css` and `js` folders. Without this,
// public pages incorrectly try to load `css/modern-style.css` relative to the
// current directory, which does not exist. The CSS lives directly under the
// project root. So when the URL contains `/dashboard`, `/admin`, or `/public`,
// set `$asset_path` to `../` to go up one level; otherwise (if ever
// referencing from the project root), leave it empty.
if (strpos($request_uri, '/dashboard') !== false ||
    strpos($request_uri, '/admin') !== false ||
    strpos($request_uri, '/public') !== false) {
    $asset_path = '../';
} else {
    $asset_path = '';
}

// Determine correct logout path for all sections
//
// The logout script lives in the project-level includes folder. When a
// page is loaded from /dashboard, /admin or /public, we need to go up one
// directory to reach includes/logout.php. From the project root (e.g. index
// pages outside any subdirectory), we can reference includes/logout.php
// directly. Without this logic, clicking the logout button on a public
// page resulted in a 404 because the relative path was incorrect.
if (strpos($request_uri, '/dashboard') !== false ||
    strpos($request_uri, '/admin') !== false ||
    strpos($request_uri, '/public') !== false) {
    $logout_path = '../includes/logout.php';
} else {
    $logout_path = 'includes/logout.php';
}

// Determine correct dashboard path for logged-in users
//
// When a user is on a public page (containing '/public'), the dashboard lives
// one directory above in /dashboard. When on a dashboard page itself or
// admin page, simply point to the local index. For root-level pages (rare),
// the dashboard folder is directly under the current directory.
if (strpos($request_uri, '/public') !== false) {
    $dashboard_path = '../dashboard/index.php';
} elseif (strpos($request_uri, '/dashboard') !== false || strpos($request_uri, '/admin') !== false) {
    $dashboard_path = 'index.php';
} else {
    $dashboard_path = 'dashboard/index.php';
}

// For navigation links
if (strpos($request_uri, '/dashboard') !== false || strpos($request_uri, '/admin') !== false) {
    $nav_base = '../public/';
} elseif (strpos($request_uri, '/public') !== false) {
    $nav_base = '';
} else {
    $nav_base = 'public/';
}

// Determine support link based on login status and location
// Users should be directed to the dashboard support page when logged in.
if (isset($_SESSION['admin_id'])) {
    // Admins do not use the user support page; leave support link null
    $support_link = '';
} else {
    if (isset($_SESSION['user_id'])) {
        // Logged-in user
        if (strpos($request_uri, '/dashboard') !== false || strpos($request_uri, '/admin') !== false) {
            // Already inside dashboard or admin directory: go up one and into dashboard support
            $support_link = '../dashboard/support.php';
        } elseif (strpos($request_uri, '/public') !== false) {
            // In /public directory: dashboard lives in sibling directory
            $support_link = '../dashboard/support.php';
        } else {
            // From root-level pages: support page lives in dashboard directory
            $support_link = 'dashboard/support.php';
        }
    } else {
        // Not logged in: default to public support page
        $support_link = $nav_base . 'support.php';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CITYBRIDGEBANK - Your Trusted Banking Partner">
    <title>CITYBRIDGEBANK - Modern Banking</title>
    
    <!-- Modern CSS - Absolute path -->
    <link rel="stylesheet" href="<?php echo $asset_path; ?>css/modern-style.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- Advanced Loading Screen -->
<!--
    The animated loading screen previously displayed a circular progress bar and
    animated logo while the page's JavaScript finished loading. However,
    several users reported that the loader remained stuck at 0% and blocked
    access to the login and registration forms. To ensure the application
    remains usable even if there are JavaScript errors or slow connections,
    the loading overlay is now hidden by default. You can re‑enable it by
    removing the inline `style="display:none;"` if desired.
-->
<div id="loading-screen" style="display: none;">
    <div class="loading-content">
        <!-- Animated Bank Icon -->
        <div class="loading-icon">
            <i class="fas fa-building-columns"></i>
        </div>
        
        <!-- Letter-by-Letter Logo Animation -->
        <div class="loading-logo">
            <span>C</span><span>I</span><span>T</span><span>Y</span><span>B</span><span>R</span><span>I</span><span>D</span><span>G</span><span>E</span><span>B</span><span>A</span><span>N</span><span>K</span>
        </div>
        
        <!-- Circular Progress -->
        <div class="loading-progress">
            <svg width="200" height="200" viewBox="0 0 200 200">
                <defs>
                    <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
                    </linearGradient>
                </defs>
                <circle class="loading-progress-bg" cx="100" cy="100" r="90"></circle>
                <circle class="loading-progress-bar" cx="100" cy="100" r="90"></circle>
            </svg>
            <div class="loading-percentage">0%</div>
        </div>
        
        <!-- Loading Text with Dots -->
        <div class="loading-text">
            Loading
            <div class="loading-dots">
                <span></span><span></span><span></span>
            </div>
        </div>
        
        <!-- Glowing Line -->
        <div class="loading-line"></div>
    </div>
</div>

<!-- Header -->
<header class="header" id="header">
    <div class="header-content">
        <!-- Logo -->
        <a href="<?php echo $nav_base; ?>index.php" class="logo">
            <div class="logo-icon">
                <i class="fas fa-building-columns"></i>
            </div>
            CITYBRIDGEBANK
        </a>
        
        <!-- Navigation -->
        <nav>
            <ul class="nav" id="nav">
                <?php
                // Render a dedicated admin navigation when an admin is logged in. The admin nav
                // includes links to all modules in the admin area and hides the public nav
                // links. The base path for admin links depends on the current URI: if we
                // are already in the /admin directory, links can be relative; otherwise we
                // prefix with '../admin/' to navigate correctly.
                if (isset($_SESSION['admin_id'])) {
                    $admin_nav_base = (strpos($request_uri, '/admin') !== false) ? '' : '../admin/';
                    ?>
                    <li><a href="<?php echo $admin_nav_base; ?>index.php" class="nav-link">Dashboard</a></li>
                    <li><a href="<?php echo $admin_nav_base; ?>users.php" class="nav-link">Manage Users</a></li>
                    <li><a href="<?php echo $admin_nav_base; ?>transactions.php" class="nav-link">Transactions</a></li>
                    <li><a href="<?php echo $admin_nav_base; ?>transaction_requests.php" class="nav-link">Pending Requests</a></li>
                    <li><a href="<?php echo $admin_nav_base; ?>user_pins.php" class="nav-link">User PINs</a></li>
                    <li><a href="<?php echo $admin_nav_base; ?>balance_management.php" class="nav-link">Balance Mgmt</a></li>
                    <li><a href="<?php echo $admin_nav_base; ?>support.php" class="nav-link">Support Tickets</a></li>
                    <li><a href="<?php echo $admin_nav_base; ?>audit_logs.php" class="nav-link">Audit Logs</a></li>
                    <li><a href="<?php echo $admin_nav_base; ?>manual_transaction.php" class="nav-link">Manual Txn</a></li>
                    <?php
                } elseif (strpos($request_uri, '/dashboard') !== false || strpos($request_uri, '/admin') !== false) {
                    // Logged-in user navigation (within dashboard or admin directory) shows
                    // public site links for convenience. nav_base is computed above to
                    // point to the correct directory.
                    ?>
                    <li><a href="<?php echo $nav_base; ?>index.php" class="nav-link">Home</a></li>
                    <li><a href="<?php echo $nav_base; ?>about.php" class="nav-link">About</a></li>
                    <li><a href="<?php echo $nav_base; ?>products.php" class="nav-link">Products</a></li>
                    <li><a href="<?php echo $nav_base; ?>services.php" class="nav-link">Services</a></li>
                    <li><a href="<?php echo $nav_base; ?>fees.php" class="nav-link">Fees</a></li>
                    <li><a href="<?php echo $nav_base; ?>security.php" class="nav-link">Security</a></li>
                    <!-- When a user is logged in, direct the support link to the dashboard support page -->
                    <li><a href="<?php echo isset($_SESSION['user_id']) ? $support_link : $nav_base . 'support.php'; ?>" class="nav-link">Support</a></li>
                    <?php
                } else {
                    // Public navigation for unauthenticated users or pages outside dashboard/admin.
                    ?>
                    <li><a href="index.php" class="nav-link">Home</a></li>
                    <li><a href="about.php" class="nav-link">About</a></li>
                    <li><a href="products.php" class="nav-link">Products</a></li>
                    <li><a href="services.php" class="nav-link">Services</a></li>
                    <li><a href="fees.php" class="nav-link">Fees</a></li>
                    <li><a href="security.php" class="nav-link">Security</a></li>
                    <!-- For logged-in users on public pages, route support to dashboard support; otherwise use public support page -->
                    <li><a href="<?php echo isset($_SESSION['user_id']) ? $support_link : 'support.php'; ?>" class="nav-link">Support</a></li>
                    <?php
                }
                ?>
            </ul>
        </nav>
        
        <!-- Header Actions -->
        <div class="header-actions">
            <?php if (isset($_SESSION['admin_id'])): ?>
                <!-- Admin is logged in: show admin-specific actions -->
                <a href="<?php echo $logout_path; ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            <?php elseif (isset($_SESSION['user_id'])): ?>
                <?php if (strpos($request_uri, '/dashboard') !== false || strpos($request_uri, '/admin') !== false): ?>
                    <!-- Already within dashboard or admin; link to local index for dashboard -->
                    <a href="<?php echo $dashboard_path; ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-circle"></i> Dashboard
                    </a>
                    <a href="<?php echo $logout_path; ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <!-- On public pages; use computed dashboard_path to return to user dashboard -->
                    <a href="<?php echo $dashboard_path; ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-circle"></i> Dashboard
                    </a>
                    <a href="<?php echo $logout_path; ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <?php if (strpos($request_uri, '/dashboard') !== false): ?>
                    <!-- On dashboard pages, non-authenticated users should be sent to the public login/register pages -->
                    <a href="<?php echo $nav_base; ?>login.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="<?php echo $nav_base; ?>register.php" class="btn btn-gradient btn-sm">
                        Get Started
                    </a>
                <?php elseif (strpos($request_uri, '/admin') !== false): ?>
                    <!-- On admin pages, use the local admin login page -->
                    <a href="login.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-sign-in-alt"></i> Admin Login
                    </a>
                    <!-- Registration is for regular users; link to public registration -->
                    <a href="<?php echo $nav_base; ?>register.php" class="btn btn-gradient btn-sm">
                        Get Started
                    </a>
                <?php else: ?>
                    <!-- On public pages, link directly to login/register in the same directory -->
                    <a href="login.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="register.php" class="btn btn-gradient btn-sm">
                        Get Started
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Mobile Menu Toggle -->
            <button class="menu-toggle" id="menuToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>

<!-- Main Content -->
<main>