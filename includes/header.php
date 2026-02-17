<?php
require_once __DIR__ . '/config.php';

$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$currentPath = $_SERVER['PHP_SELF'] ?? '';
$inPublic = strpos($currentPath, '/public/') !== false;
$inDashboard = strpos($currentPath, '/dashboard/') !== false;
$inAdmin = strpos($currentPath, '/admin/') !== false;

$isAdmin = Security::isAdminLoggedIn();
$isUser = Security::isLoggedIn();

if ($isAdmin || $isUser) {
    setNoCacheHeaders();
    Security::checkSessionTimeout();
}

// Strict route separation
if ($isUser && $inAdmin) {
    header('Location: ../dashboard/index.php');
    exit;
}
if ($isAdmin && $inDashboard) {
    header('Location: ../admin/index.php');
    exit;
}
if ($inPublic) {
    $publicAuth = ['login.php', 'register.php', 'forgot_password.php', 'reset_password.php'];
    $basename = basename($currentPath);
    if ($isUser && !in_array($basename, $publicAuth, true)) {
        header('Location: ../dashboard/index.php');
        exit;
    }
    if ($isAdmin && !in_array($basename, $publicAuth, true)) {
        header('Location: ../admin/index.php');
        exit;
    }
}

$assetPath = ($inPublic || $inDashboard || $inAdmin) ? '../' : '';
$logoutPath = ($inPublic || $inDashboard || $inAdmin) ? '../includes/logout.php' : 'includes/logout.php';

if ($inPublic) {
    $userDashboardPath = '../dashboard/index.php';
    $adminDashboardPath = '../admin/index.php';
    $publicBase = '';
} elseif ($inDashboard || $inAdmin) {
    $userDashboardPath = '../dashboard/index.php';
    $adminDashboardPath = '../admin/index.php';
    $publicBase = '../public/';
} else {
    $userDashboardPath = 'dashboard/index.php';
    $adminDashboardPath = 'admin/index.php';
    $publicBase = 'public/';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CITIBRIDGE - Your Trusted Banking Partner">
    <title>CITIBRIDGE - Modern Banking</title>
    <link rel="stylesheet" href="<?php echo $assetPath; ?>css/modern-style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header class="header" id="header">
    <div class="header-content">
        <a href="<?php echo $isAdmin ? $adminDashboardPath : ($isUser ? $userDashboardPath : $publicBase . 'index.php'); ?>" class="logo">
            <div class="logo-icon"><i class="fas fa-building-columns"></i></div>
            CITIBRIDGE
        </a>

        <nav>
            <ul class="nav" id="nav">
                <?php if ($isAdmin): ?>
                    <li><a href="<?php echo $adminDashboardPath; ?>" class="nav-link">Dashboard</a></li>
                    <li><a href="<?php echo ($inAdmin ? '' : '../admin/'); ?>users.php" class="nav-link">Users</a></li>
                    <li><a href="<?php echo ($inAdmin ? '' : '../admin/'); ?>transaction_requests.php" class="nav-link">Requests</a></li>
                    <li><a href="<?php echo ($inAdmin ? '' : '../admin/'); ?>support.php" class="nav-link">Support</a></li>
                    <li><a href="<?php echo ($inAdmin ? '' : '../admin/'); ?>kyc_requests.php" class="nav-link">KYC Review</a></li>
                    <li><a href="<?php echo ($inAdmin ? '' : '../admin/'); ?>audit_logs.php" class="nav-link">Audit Logs</a></li>
                <?php elseif ($isUser): ?>
                    <li><a href="<?php echo ($inDashboard ? '' : '../dashboard/'); ?>index.php" class="nav-link">Dashboard</a></li>
                    <li><a href="<?php echo ($inDashboard ? '' : '../dashboard/'); ?>transactions.php" class="nav-link">Transactions</a></li>
                    <li><a href="<?php echo ($inDashboard ? '' : '../dashboard/'); ?>transfer.php" class="nav-link">Transfer</a></li>
                    <li><a href="<?php echo ($inDashboard ? '' : '../dashboard/'); ?>withdraw.php" class="nav-link">Withdraw</a></li>
                    <li><a href="<?php echo ($inDashboard ? '' : '../dashboard/'); ?>security.php" class="nav-link">Security</a></li>
                    <li><a href="<?php echo ($inDashboard ? '' : '../dashboard/'); ?>support.php" class="nav-link">Support</a></li>
                <?php else: ?>
                    <li><a href="<?php echo $publicBase; ?>index.php" class="nav-link">Home</a></li>
                    <li><a href="<?php echo $publicBase; ?>about.php" class="nav-link">About</a></li>
                    <li><a href="<?php echo $publicBase; ?>products.php" class="nav-link">Products</a></li>
                    <li><a href="<?php echo $publicBase; ?>services.php" class="nav-link">Services</a></li>
                    <li><a href="<?php echo $publicBase; ?>security.php" class="nav-link">Security</a></li>
                    <li><a href="<?php echo $publicBase; ?>support.php" class="nav-link">Support</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="header-actions">
            <?php if ($isAdmin || $isUser): ?>
                <a href="<?php echo $logoutPath; ?>" class="btn btn-secondary btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="<?php echo $publicBase; ?>login.php" class="btn btn-primary btn-sm"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="<?php echo $publicBase; ?>register.php" class="btn btn-gradient btn-sm">Get Started</a>
            <?php endif; ?>
            <button class="menu-toggle" id="menuToggle"><span></span><span></span><span></span></button>
        </div>
    </div>
</header>
