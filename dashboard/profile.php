<?php
// Set page title for profile
$pageTitle = 'Profile & KYC';
// Load configuration to access Database and Security
require_once '../includes/config.php';

// Check if user is logged in before any output
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login to access your profile.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get user profile
    $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    // Get support tickets
    $stmt = $db->prepare("SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    $tickets = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log('Profile error: ' . $e->getMessage());
    redirectWithMessage('index.php', 'An error occurred loading your profile.', 'danger');
}

// After authentication and data fetch, render the header
require_once '../includes/header.php';
?>

<!-- Page Header -->
<section class="hero">
    <div class="container">
        <h1>My Profile</h1>
        <p>Manage your account information and KYC verification</p>
    </div>
</section>

<!-- Profile Content -->
<section style="padding: 2rem 0;">
    <div class="container">
        <!-- Profile Information -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h2><i class="fas fa-user text-gold"></i> Personal Information</h2>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                    <div>
                        <label style="color: var(--gray); font-size: 0.875rem;">Full Name</label>
                        <p style="font-size: 1.125rem; font-weight: 600;"><?php echo htmlspecialchars($user['full_name']); ?></p>
                    </div>
                    <div>
                        <label style="color: var(--gray); font-size: 0.875rem;">Username</label>
                        <p style="font-size: 1.125rem; font-weight: 600;"><?php echo htmlspecialchars($user['username']); ?></p>
                    </div>
                    <div>
                        <label style="color: var(--gray); font-size: 0.875rem;">Email</label>
                        <p style="font-size: 1.125rem;"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div>
                        <label style="color: var(--gray); font-size: 0.875rem;">Phone</label>
                        <p style="font-size: 1.125rem;"><?php echo htmlspecialchars($user['phone']); ?></p>
                    </div>
                    <div>
                        <label style="color: var(--gray); font-size: 0.875rem;">Date of Birth</label>
                        <p style="font-size: 1.125rem;"><?php echo formatDate($user['date_of_birth']); ?></p>
                    </div>
                    <div>
                        <label style="color: var(--gray); font-size: 0.875rem;">Account Number</label>
                        <p style="font-size: 1.125rem; font-family: monospace;"><?php echo htmlspecialchars($user['account_number']); ?></p>
                    </div>
                    <div>
                        <label style="color: var(--gray); font-size: 0.875rem;">Address</label>
                        <p style="font-size: 1.125rem;"><?php echo htmlspecialchars($user['address']); ?></p>
                    </div>
                    <div>
                        <label style="color: var(--gray); font-size: 0.875rem;">Account Status</label>
                        <p>
                            <span class="badge badge-success"><?php echo ucfirst($user['account_status']); ?></span>
                        </p>
                    </div>
                    <div>
                        <label style="color: var(--gray); font-size: 0.875rem;">Member Since</label>
                        <p style="font-size: 1.125rem;"><?php echo formatDate($user['created_at']); ?></p>
                    </div>
                    <div>
                        <label style="color: var(--gray); font-size: 0.875rem;">Last Login</label>
                        <p style="font-size: 1.125rem;"><?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'First login'; ?></p>
                    </div>
                </div>
                
                <div style="margin-top: 2rem;">
                    <button class="btn btn-primary"><i class="fas fa-edit"></i> Update Profile</button>
                    <button class="btn btn-outline"><i class="fas fa-key"></i> Change Password</button>
                </div>
            </div>
        </div>
        
        <!-- KYC Verification -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h2><i class="fas fa-id-card text-gold"></i> KYC Verification</h2>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div>
                        <h3 style="color: var(--primary-blue); margin-bottom: 1rem;">Current Status</h3>
                        <p><strong>Verification Status:</strong> 
                            <span class="badge <?php echo $user['kyc_status'] == 'verified' ? 'badge-success' : 'badge-warning'; ?>">
                                <?php echo ucfirst($user['kyc_status']); ?>
                            </span>
                        </p>
                        
                        <?php if ($user['kyc_status'] == 'verified'): ?>
                            <div class="success-box" style="margin-top: 1rem;">
                                <p style="margin: 0;"><i class="fas fa-check-circle"></i> Your account is fully verified. You have access to all banking features.</p>
                            </div>
                        <?php elseif ($user['kyc_status'] == 'pending'): ?>
                            <div class="warning-box" style="margin-top: 1rem;">
                                <p style="margin: 0;"><i class="fas fa-clock"></i> Your verification is pending review. This typically takes 1-2 business days.</p>
                            </div>
                        <?php elseif ($user['kyc_status'] == 'rejected'): ?>
                            <div class="danger-box" style="margin-top: 1rem;">
                                <p style="margin: 0;"><i class="fas fa-exclamation-circle"></i> Your verification was rejected. Please resubmit with correct information.</p>
                            </div>
                        <?php else: ?>
                            <div class="info-box" style="margin-top: 1rem;">
                                <p style="margin: 0;"><i class="fas fa-info-circle"></i> Complete KYC verification to unlock all account features and increase transaction limits.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <h3 style="color: var(--primary-blue); margin-bottom: 1rem;">Required Documents</h3>
                        <ul style="line-height: 2;">
                            <li><i class="fas fa-file-alt"></i> Government-issued ID (Passport, Driver's License, or State ID)</li>
                            <li><i class="fas fa-file-alt"></i> Proof of Address (Utility Bill, Bank Statement, or Lease Agreement)</li>
                            <li><i class="fas fa-file-alt"></i> Social Security Number (last 4 digits)</li>
                            <li><i class="fas fa-file-alt"></i> Selfie with ID (for identity verification)</li>
                        </ul>
                        
                        <?php if ($user['kyc_status'] != 'verified'): ?>
                            <button class="btn btn-primary" style="margin-top: 1rem;"><i class="fas fa-upload"></i> Submit Documents</button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="info-box" style="margin-top: 2rem;">
                    <h4 style="color: var(--primary-blue); margin-bottom: 0.5rem;">Benefits of KYC Verification</h4>
                    <ul style="line-height: 1.8; margin: 0;">
                        <li><i class="fas fa-check text-success"></i> Higher transaction limits</li>
                        <li><i class="fas fa-check text-success"></i> Access to premium features</li>
                        <li><i class="fas fa-check text-success"></i> Faster approval for loans and credit</li>
                        <li><i class="fas fa-check text-success"></i> Enhanced security protection</li>
                        <li><i class="fas fa-check text-success"></i> Priority customer support</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Support Tickets -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-ticket-alt text-gold"></i> Recent Support Tickets</h2>
            </div>
            <div class="card-body">
                <?php if (empty($tickets)): ?>
                    <div class="info-box text-center">
                        <p>No support tickets yet. Need help? <a href="../public/support.php">Create a ticket</a>.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $ticket): ?>
                                    <tr>
                                        <td>#<?php echo $ticket['ticket_id']; ?></td>
                                        <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                        <td>
                                            <span class="badge <?php 
                                                echo $ticket['status'] == 'open' ? 'badge-warning' : 
                                                     ($ticket['status'] == 'in_progress' ? 'badge-info' : 'badge-success'); 
                                            ?>">
                                                <?php echo ucfirst($ticket['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($ticket['created_at']); ?></td>
                                        <td>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">View</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="../public/support.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Support Ticket</a>
                </div>
            </div>
        </div>
        
        <!-- Back to Dashboard -->
        <div style="text-align: center; margin-top: 2rem;">
            <a href="index.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>