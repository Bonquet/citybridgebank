<?php
// Dashboard Support Page for authenticated users
// This page allows logged-in users to view their support tickets and submit new ones.
require_once '../includes/config.php';

// Ensure user is logged in (not admin).  Admins should use the admin support page.
// Use the isLoggedIn() method since isUserLoggedIn() is undefined
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login to view support.', 'danger');
}

// Include header after config so assets paths resolve correctly
require_once '../includes/header.php';

// Generate CSRF token for ticket submission
$csrf_token = Security::generateCSRFToken();

try {
    $db = Database::getInstance()->getConnection();
    
    // Fetch the logged-in user's support tickets
    $stmt = $db->prepare("SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
    $stmt->execute([$_SESSION['user_id']]);
    $userTickets = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Dashboard support error: ' . $e->getMessage());
    $userTickets = [];
}
?>

<!-- Hero section -->
<section class="section" style="padding-top: 2rem;">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-headset text-gold"></i> Support Center</h2>
            <p class="section-subtitle">View your past tickets or create a new support request.</p>
        </div>
    </div>
</section>

<!-- User Tickets List -->
<section class="section" style="padding-top: 1rem;">
    <div class="container">
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3>Your Tickets</h3>
            </div>
            <div class="card-body">
                <?php if (empty($userTickets)): ?>
                    <p>You have not created any support tickets yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
<table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Subject</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Admin Response</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userTickets as $ticket): ?>
                                    <tr>
                                        <td>#<?php echo $ticket['ticket_id']; ?></td>
                                        <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                        <td><?php echo ucfirst($ticket['priority']); ?></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?></td>
                                        <td><?php echo formatDateTime($ticket['created_at']); ?></td>
                                        <td>
                                            <?php
                                            if (!empty($ticket['admin_response'])) {
                                                // Display a truncated admin response with tooltip for full text
                                                $response = strip_tags($ticket['admin_response']);
                                                $truncated = (strlen($response) > 60) ? substr($response, 0, 57) . '...' : $response;
                                                echo '<span title="' . htmlspecialchars($response) . '">' . htmlspecialchars($truncated) . '</span>';
                                            } else {
                                                echo '<em>No response yet</em>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Submit Ticket Form -->
<section class="section" style="padding-top: 0;">
    <div class="container">
        <div class="card" style="max-width: 800px; margin: 0 auto;">
            <div class="card-header">
                <h3>Create a Ticket</h3>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                <form action="../includes/submit_ticket.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="form-group">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" id="subject" name="subject" class="form-input" placeholder="Brief description of your issue" required>
                    </div>
                    <div class="form-group">
                        <label for="category" class="form-label">Category</label>
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
                    <div class="form-group">
                        <label for="priority" class="form-label">Priority</label>
                        <select id="priority" name="priority" class="form-select" required>
                            <option value="">Select priority level</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="message" class="form-label">Message</label>
                        <textarea id="message" name="message" class="form-textarea" placeholder="Please describe your issue in detail" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane" style="margin-right: 0.5rem;"></i> Submit Ticket</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>