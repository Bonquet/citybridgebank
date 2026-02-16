<?php
$pageTitle = 'Support Tickets';
// Load configuration before header to access database and security functions
require_once '../includes/config.php';
require_once '../includes/header.php';

// Check if admin is logged in
if (!Security::isAdminLoggedIn()) {
    redirectWithMessage('login.php', 'Please login to access support tickets.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get filter parameters
    $filter_status = isset($_GET['status']) ? Security::sanitizeInput($_GET['status']) : 'all';
    $filter_priority = isset($_GET['priority']) ? Security::sanitizeInput($_GET['priority']) : 'all';
    $search = isset($_GET['search']) ? Security::sanitizeInput($_GET['search']) : '';
    
    // Build query
    $where_conditions = [];
    $params = [];
    
    if ($filter_status != 'all') {
        $where_conditions[] = "st.status = ?";
        $params[] = $filter_status;
    }
    
    if ($filter_priority != 'all') {
        $where_conditions[] = "st.priority = ?";
        $params[] = $filter_priority;
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(u.username LIKE ? OR u.email LIKE ? OR st.subject LIKE ?)";
        $search_param = "%{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get support tickets
    $sql = "SELECT st.*, u.username, u.email, u.full_name 
            FROM support_tickets st 
            LEFT JOIN users u ON st.user_id = u.user_id 
            {$where_clause} 
            ORDER BY st.priority DESC, st.created_at DESC LIMIT 50";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $tickets = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Support tickets error: " . $e->getMessage());
    redirectWithMessage('index.php', 'An error occurred loading support tickets.', 'danger');
}
?>

<!-- Page Header -->
<section style="background: linear-gradient(135deg, var(--dark) 0%, var(--primary-blue) 100%); color: var(--white); padding: 2rem 0;">
    <div class="container">
        <h1><i class="fas fa-ticket-alt text-gold"></i> Support Tickets</h1>
        <p>Manage and respond to all customer support requests</p>
    </div>
</section>

<!-- Ticket Details Modal -->
<div id="ticketModal" class="modal-overlay" style="display:none;">
    <div class="modal glass-card" style="max-width: 600px; width: 90%; position: relative;">
        <h3 style="margin-bottom: 1rem;"><i class="fas fa-ticket-alt" style="margin-right:0.5rem;"></i>Ticket #<span id="modalTicketId"></span></h3>
        <div id="ticketModalContent" style="max-height: 50vh; overflow-y: auto;"></div>
        <div id="ticketActionArea" style="margin-top:1rem;"></div>
        <div style="margin-top:1.5rem; text-align: right;">
            <button type="button" class="btn btn-secondary" onclick="closeTicketModal()">Close</button>
        </div>
    </div>
</div>

<!-- Support Tickets Content -->
<section style="padding: 2rem 0;">
    <div class="container">
        <!-- Quick Stats -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem;">
            <?php
            // Use fetchColumn() instead of fetch()[0] to avoid undefined index
            // when there are zero rows. Coalesce to 0 if null.
            $openCount = (int) ($db->query("SELECT COUNT(*) FROM support_tickets WHERE status = 'open'")->fetchColumn() ?? 0);
            $inProgressCount = (int) ($db->query("SELECT COUNT(*) FROM support_tickets WHERE status = 'in_progress'")->fetchColumn() ?? 0);
            $closedCount = (int) ($db->query("SELECT COUNT(*) FROM support_tickets WHERE status = 'closed'")->fetchColumn() ?? 0);
            $urgentCount = (int) ($db->query("SELECT COUNT(*) FROM support_tickets WHERE priority = 'urgent'")->fetchColumn() ?? 0);
            ?>
            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <h3 style="color: var(--warning);"><?php echo number_format($openCount); ?></h3>
                    <p>Open Tickets</p>
                </div>
            </div>
            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <h3 style="color: var(--accent-blue);"><?php echo number_format($inProgressCount); ?></h3>
                    <p>In Progress</p>
                </div>
            </div>
            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <h3 style="color: var(--success);"><?php echo number_format($closedCount); ?></h3>
                    <p>Closed Tickets</p>
                </div>
            </div>
            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <h3 style="color: var(--danger);"><?php echo number_format($urgentCount); ?></h3>
                    <p>Urgent Tickets</p>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <form action="" method="GET">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" id="search" name="search" class="form-control" placeholder="Username, email, or subject" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="all" <?php echo $filter_status == 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="open" <?php echo $filter_status == 'open' ? 'selected' : ''; ?>>Open</option>
                                <option value="in_progress" <?php echo $filter_status == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="closed" <?php echo $filter_status == 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="priority">Priority</label>
                            <select id="priority" name="priority" class="form-control">
                                <option value="all" <?php echo $filter_priority == 'all' ? 'selected' : ''; ?>>All Priorities</option>
                                <option value="low" <?php echo $filter_priority == 'low' ? 'selected' : ''; ?>>Low</option>
                                <option value="medium" <?php echo $filter_priority == 'medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="high" <?php echo $filter_priority == 'high' ? 'selected' : ''; ?>>High</option>
                                <option value="urgent" <?php echo $filter_priority == 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                            </select>
                        </div>
                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Ticket List -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list text-gold"></i> Support Tickets</h2>
                <span class="badge badge-info"><?php echo count($tickets); ?> tickets</span>
            </div>
            <div class="card-body">
                <?php if (empty($tickets)): ?>
                    <div class="info-box text-center">
                        <p>No support tickets found matching your criteria.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>User</th>
                                    <th>Subject</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $ticket): ?>
                                    <tr>
                                        <td><strong>#<?php echo $ticket['ticket_id']; ?></strong></td>
                                        <td>
                                            <?php echo htmlspecialchars($ticket['username'] ?? 'Unknown'); ?><br>
                                            <small><?php echo htmlspecialchars($ticket['email'] ?? ''); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                        <td>
                                            <span class="badge <?php 
                                                echo $ticket['priority'] == 'urgent' ? 'badge-danger' : 
                                                     ($ticket['priority'] == 'high' ? 'badge-warning' : 
                                                     ($ticket['priority'] == 'medium' ? 'badge-info' : 'badge-secondary')); 
                                            ?>">
                                                <?php echo ucfirst($ticket['priority']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?php 
                                                echo $ticket['status'] == 'open' ? 'badge-warning' : 
                                                     ($ticket['status'] == 'in_progress' ? 'badge-info' : 'badge-success'); 
                                            ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDateTime($ticket['created_at']); ?></td>
                                        <td>
                                            <!-- All ticket actions now open a modal for details and actions -->
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;" onclick="openTicketModal(<?php echo $ticket['ticket_id']; ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <?php if ($ticket['status'] == 'open'): ?>
                                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;" onclick="openTicketModal(<?php echo $ticket['ticket_id']; ?>)">
                                                    <i class="fas fa-play"></i> Start
                                                </button>
                                            <?php elseif ($ticket['status'] == 'in_progress'): ?>
                                                <button class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;" onclick="openTicketModal(<?php echo $ticket['ticket_id']; ?>)">
                                                    <i class="fas fa-check"></i> Close
                                                </button>
                                            <?php endif; ?>
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

<script>
// Modal for viewing and managing support tickets
function openTicketModal(ticketId) {
    const modal = document.getElementById('ticketModal');
    const contentDiv = document.getElementById('ticketModalContent');
    const actionDiv = document.getElementById('ticketActionArea');
    const ticketIdSpan = document.getElementById('modalTicketId');
    // Reset modal contents
    contentDiv.innerHTML = '<p>Loading ticket details...</p>';
    actionDiv.innerHTML = '';
    ticketIdSpan.textContent = ticketId;
    modal.style.display = 'flex';
    // Fetch ticket details via AJAX
    fetch('get_ticket_details.php?ticket_id=' + ticketId)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                contentDiv.innerHTML = '<p>' + (data.message || 'Failed to load ticket details.') + '</p>';
                return;
            }
                const t = data.ticket;
                // Build details HTML
                let html = '';
                html += '<p><strong>User:</strong> ' + (t.full_name || 'Unknown') + ' (' + (t.username || '') + ')</p>';
                html += '<p><strong>Email:</strong> ' + (t.email || '') + '</p>';
                html += '<p><strong>Subject:</strong> ' + t.subject + '</p>';
                html += '<p><strong>Message:</strong><br>' + t.message.replace(/\n/g, '<br>') + '</p>';
                html += '<p><strong>Priority:</strong> ' + t.priority.charAt(0).toUpperCase() + t.priority.slice(1) + '</p>';
                html += '<p><strong>Status:</strong> ' + t.status.replace('_', ' ') + '</p>';
                html += '<p><strong>Created:</strong> ' + t.created_at + '</p>';
                if (t.updated_at) html += '<p><strong>Updated:</strong> ' + t.updated_at + '</p>';
                if (t.closed_at) html += '<p><strong>Closed:</strong> ' + t.closed_at + '</p>';
                if (t.admin_response) html += '<p><strong>Admin Response:</strong><br>' + t.admin_response.replace(/\n/g, '<br>') + '</p>';
                contentDiv.innerHTML = html;
                // Determine actions based on status
                let actions = '';
                if (t.status === 'open') {
                    // Show start and close actions
                    actions += '<button class="btn btn-outline" onclick="updateTicketStatus(' + ticketId + ',\'in_progress\')">\n' +
                               '<i class="fas fa-play"></i> Mark In Progress</button> ';
                    actions += '<button class="btn btn-danger" onclick="showCloseForm(' + ticketId + ')">\n' +
                               '<i class="fas fa-check"></i> Close Ticket</button>';
                } else if (t.status === 'in_progress') {
                    // Show response textarea and close action
                    actions += '<textarea id="adminResponse" class="form-control" placeholder="Enter response to close the ticket"></textarea>';
                    actions += '<button class="btn btn-danger" style="margin-top:0.5rem;" onclick="updateTicketStatus(' + ticketId + ',\'closed\')">\n' +
                               '<i class="fas fa-check"></i> Close Ticket</button>';
                } else {
                    actions += '<p>No further actions available. This ticket is closed.</p>';
                }
                actionDiv.innerHTML = actions;
        })
        .catch(() => {
            contentDiv.innerHTML = '<p>Error loading ticket details. Please try again.</p>';
        });
}

// Hide the ticket modal
function closeTicketModal() {
    const modal = document.getElementById('ticketModal');
    modal.style.display = 'none';
}

// Simple notification function for admin pages (fallback if main.js is not loaded)
function showNotification(message, type = 'success') {
    // Remove existing notification
    const existing = document.querySelector('.notification');
    if (existing) existing.remove();
    const notification = document.createElement('div');
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
    setTimeout(() => { notification.remove(); }, 5000);
}

// Show a response field for closing a ticket when currently open
function showCloseForm(ticketId) {
    const actionDiv = document.getElementById('ticketActionArea');
    actionDiv.innerHTML = '<textarea id="adminResponse" class="form-control" placeholder="Enter response to close the ticket"></textarea>' +
                          '<button class="btn btn-danger" style="margin-top:0.5rem;" onclick="updateTicketStatus(' + ticketId + ',\'closed\')">\n' +
                          '<i class="fas fa-check"></i> Close Ticket</button>';
}

// Update ticket status via AJAX
function updateTicketStatus(ticketId, newStatus) {
    let adminResponse = '';
    if (newStatus === 'closed') {
        const responseField = document.getElementById('adminResponse');
        if (responseField) {
            adminResponse = responseField.value.trim();
        }
        if (adminResponse === '') {
            // Require a response to close
            showNotification('A response is required to close the ticket.', 'danger');
            return;
        }
    }
    const fd = new FormData();
    fd.append('ticket_id', ticketId);
    fd.append('new_status', newStatus);
    fd.append('admin_response', adminResponse);
    fetch('update_ticket.php', {
        method: 'POST',
        body: fd
    })
    .then(resp => resp.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            // Close modal and reload page after short delay
            closeTicketModal();
            setTimeout(() => { location.reload(); }, 1000);
        } else {
            showNotification(data.message || 'Operation failed.', 'danger');
        }
    })
    .catch(() => {
        showNotification('Network error. Please try again.', 'danger');
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>