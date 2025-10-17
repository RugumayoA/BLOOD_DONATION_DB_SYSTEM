<?php
session_start();
// Include database connection
require_once 'config.php';
require_once 'email_helper.php';

// Redirect to login if not authenticated as staff
if (!isset($_SESSION['staff_id'])) {
    header('Location: login.php');
    exit;
}

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    try {
        // CREATE: Send new notification
        if ($action == 'send_notification') {
            $sql = "INSERT INTO notification (recipient_type, recipient_id, notification_type, title, message, sent_date, sent_time, status, delivery_method) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siissssss",
                $_POST['recipient_type'],
                $_POST['recipient_id'],
                $_POST['notification_type'],
                $_POST['title'],
                $_POST['message'],
                date('Y-m-d'),
                date('H:i:s'),
                'Queued',
                $_POST['notification_type']
            );
            
            if ($stmt->execute()) {
                $success_message = "Notification sent successfully!";
            } else {
                $error_message = "Error: " . $conn->error;
            }
            $stmt->close();
        }
        
        // UPDATE: Resend notification
        if ($action == 'resend' && isset($_POST['notification_id'])) {
            // Get notification details
            $notif_sql = "SELECT * FROM notification WHERE notification_id = ?";
            $notif_stmt = $conn->prepare($notif_sql);
            $notif_stmt->bind_param("i", $_POST['notification_id']);
            $notif_stmt->execute();
            $result = $notif_stmt->get_result();
            $notification = $result->fetch_assoc();
            $notif_stmt->close();
            
            if ($notification) {
                // Get recipient email address based on type
                $email = null;
                $recipient_name = '';
                
                if ($notification['recipient_type'] == 'donor') {
                    $email_sql = "SELECT email, first_name, last_name FROM donor WHERE donor_id = ?";
                } elseif ($notification['recipient_type'] == 'recipient') {
                    $email_sql = "SELECT email, first_name, last_name FROM recipient WHERE recipient_id = ?";
                } else {
                    $email_sql = "SELECT email, first_name, last_name FROM staff WHERE staff_id = ?";
                }
                
                $email_stmt = $conn->prepare($email_sql);
                $email_stmt->bind_param("i", $notification['recipient_id']);
                $email_stmt->execute();
                $result = $email_stmt->get_result();
                $recipient_data = $result->fetch_assoc();
                $email_stmt->close();
                
                if ($recipient_data) {
                    $email = $recipient_data['email'];
                    $recipient_name = $recipient_data['first_name'] . ' ' . $recipient_data['last_name'];
                }
                
                // Initial status
                $status = 'Queued';
                
                // If notification type is Email, send the email
                if ($notification['notification_type'] == 'Email' && $email) {
                    // Send email using PHPMailer
                    $result = send_notification_email($email, $recipient_name, $notification['title'], $notification['message']);
                    
                    if ($result['success']) {
                        $status = 'Sent';
                    } else {
                        $status = 'Failed';
                    }
                }
                
                // Update notification
                $sql = "UPDATE notification 
                        SET status = ?, sent_date = ?, sent_time = ? 
                        WHERE notification_id = ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi",
                    $status,
                    date('Y-m-d'),
                    date('H:i:s'),
                    $_POST['notification_id']
                );
                $stmt->execute();
                $stmt->close();
                
                if ($status == 'Sent') {
                    $success_message = "Notification resent successfully to " . htmlspecialchars($email) . "!";
                } else {
                    $error_message = "Failed to resend notification. Please check email configuration.";
                }
            }
        }
        
        // DELETE: Remove notification
        if ($action == 'delete' && isset($_POST['notification_id'])) {
            $sql = "DELETE FROM notification WHERE notification_id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $_POST['notification_id']);
            if ($stmt->execute()) {
                $success_message = "Notification deleted successfully!";
            } else {
                $error_message = "Error: " . $conn->error;
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// READ: Get all notifications
$sql = "SELECT * FROM notification ORDER BY sent_date DESC, sent_time DESC";
$result = $conn->query($sql);
$notifications = $result->fetch_all(MYSQLI_ASSOC);

// READ: Get statistics
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Queued' THEN 1 ELSE 0 END) as queued,
    SUM(CASE WHEN status = 'Sent' THEN 1 ELSE 0 END) as sent,
    SUM(CASE WHEN status = 'Failed' THEN 1 ELSE 0 END) as failed
    FROM notification";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Blood Donation DMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Dashboard-style layout */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #E21C3D 0%, #8B0000 100%);
            color: white;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header img {
            width: 50px;
            height: 50px;
            margin-bottom: 10px;
        }
        
        .sidebar-header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }
        
        .sidebar-content {
            flex: 1;
            overflow-y: auto;
        }
        
        .sidebar-nav {
            flex: 1;
            padding: 20px 0;
            overflow-y: auto;
        }
        
        .nav-section {
            margin-bottom: 20px;
        }
        
        .nav-section-title {
            padding: 0 20px 10px 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            color: rgba(255,255,255,0.7);
            letter-spacing: 1px;
        }
        
        .nav-item {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .nav-item:hover {
            background: rgba(255,255,255,0.1);
            border-left-color: #fff;
        }
        
        .nav-item.active {
            background: rgba(255,255,255,0.2);
            border-left-color: #fff;
            font-weight: 600;
        }
        
        .nav-item i {
            margin-right: 10px;
            font-size: 16px;
        }
        
        .user-info {
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.1);
        }
        
        .user-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .user-role {
            font-size: 12px;
            color: rgba(255,255,255,0.7);
            margin-bottom: 15px;
        }
        
        .logout-btn {
            display: inline-block;
            padding: 8px 15px;
            background: rgba(255,255,255,0.1);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 12px;
            transition: background 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            background: #f5f5f5;
            min-height: 100vh;
        }
        
        .content-area {
            padding: 30px;
        }
        
        /* Additional styles for notifications page */
        .notifications-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .notifications-title {
            color: #E21C3D;
            font-size: 2.5em;
            margin: 0;
        }
        
        .notification-stats {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .stat-card {
            background: #fff;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #E21C3D;
        }
        
        .stat-number {
            font-size: 1.8em;
            font-weight: bold;
            color: #E21C3D;
            margin: 0;
        }
        
        .stat-label {
            font-size: 0.9em;
            color: #666;
            margin: 5px 0 0 0;
        }
        
        .notifications-table-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-title {
            margin: 0;
            color: #333;
            font-size: 1.3em;
        }
        
        .table-actions {
            display: flex;
            gap: 10px;
        }
        
        .notifications-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .notifications-table th {
            background: #f2f2f2;
            padding: 15px 12px;
            text-align: left;
            font-weight: bold;
            color: #333;
            border-bottom: 2px solid #E21C3D;
        }
        
        .notifications-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        .notifications-table tr:hover {
            background: #f8f9fa;
        }
        
        .notification-type {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .type-sms { background: #e3f2fd; color: #1976d2; }
        .type-email { background: #f3e5f5; color: #7b1fa2; }
        .type-push { background: #e8f5e8; color: #388e3c; }
        .type-call { background: #fff3e0; color: #f57c00; }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .status-queued { background: #fff3cd; color: #856404; }
        .status-sent { background: #d4edda; color: #155724; }
        .status-failed { background: #f8d7da; color: #721c24; }
        
        .recipient-type {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: capitalize;
        }
        
        .recipient-donor { background: #e3f2fd; color: #1976d2; }
        .recipient-recipient { background: #f3e5f5; color: #7b1fa2; }
        .recipient-staff { background: #e8f5e8; color: #388e3c; }
        
        .message-preview {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .btn-small {
            padding: 4px 8px;
            font-size: 0.8em;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .btn-view {
            background: #E21C3D;
            color: white;
            border: none;
        }
        
        .btn-resend {
            background: #28a745;
            color: white;
            border: none;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .no-notifications {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .notifications-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .notification-stats {
                justify-content: center;
            }
            
            .notifications-table {
                font-size: 0.9em;
            }
            
            .notifications-table th,
            .notifications-table td {
                padding: 8px 6px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="images/blood-drop-heart-logo.png" alt="Blood Donation DMS">
                <h2>Blood Donation DMS</h2>
            </div>
            
            <div class="sidebar-content">
                <nav class="sidebar-nav">
                    <div class="nav-section">
                        <div class="nav-section-title">Main</div>
                        <a href="index.php" class="nav-item">
                            Dashboard
                        </a>
                        <a href="donors.php" class="nav-item">
                            Donors
                        </a>
                        <a href="recipients.php" class="nav-item">
                            Recipients
                        </a>
                        <a href="donations.php" class="nav-item">
                            <i>🩸</i> Donations
                        </a>
                        <a href="requests.php" class="nav-item">
                            Blood Requests
                        </a>
                        <a href="inventory.php" class="nav-item">
                            Inventory
                        </a>
                        <a href="staff.php" class="nav-item">
                            Staff
                        </a>
                    </div>
                    
                    <div class="nav-section">
                        <div class="nav-section-title">Management</div>
                        <a href="events.php" class="nav-item">
                            Events
                        </a>
                        <a href="sessions.php" class="nav-item">
                            Sessions
                        </a>
                        <a href="testing.php" class="nav-item">
                            Testing
                        </a>
                        <a href="transfusions.php" class="nav-item">
                            Transfusions
                        </a>
                        <a href="insights.php" class="nav-item">
                            <i>📊</i> Insights
                        </a>
                        <a href="reports.php" class="nav-item">
                            Reports
                        </a>
                        <a href="notifications.php" class="nav-item active">
                            Notifications
                        </a>
                    </div>
            </nav>
        </div>
            
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($_SESSION['staff_name']); ?></div>
                <div class="user-role">Staff Member</div>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-area">
                <div class="top-bar">
                    <div>
                        <span style="color: #333; font-size: 18px; font-weight: 500;">Notifications</span>
        </div>
    </div>

    <div class="notifications-container">
        <!-- Success/Error Messages -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="alert alert-success">
                ✅ <?php echo isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Notification sent successfully!'; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success']) && $_GET['success'] == 0): ?>
            <div class="alert alert-error">
                ❌ <?php echo isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Notification could not be sent!'; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Statistics Section -->
        <div class="notifications-header">
            <h2 class="notifications-title">Notifications Dashboard</h2>
            <div class="notification-stats">
                <div class="stat-card">
                    <p class="stat-number"><?php echo $stats['total']; ?></p>
                    <p class="stat-label">Total Notifications</p>
                </div>
                <div class="stat-card">
                    <p class="stat-number"><?php echo $stats['queued']; ?></p>
                    <p class="stat-label">Queued</p>
                </div>
                <div class="stat-card">
                    <p class="stat-number"><?php echo $stats['sent']; ?></p>
                    <p class="stat-label">Sent</p>
                </div>
                <div class="stat-card">
                    <p class="stat-number"><?php echo $stats['failed']; ?></p>
                    <p class="stat-label">Failed</p>
                </div>
            </div>
        </div>


        <!-- Notifications Table -->
        <div class="notifications-table-container">
            <div class="table-header">
                <h3 class="table-title">All Notifications (<?php echo count($notifications); ?>)</h3>
                <div class="table-actions">
                    <a href="send_notification.php" class="btn btn-primary">Send New Notification</a>
                </div>
            </div>
            
            <table class="notifications-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Recipient</th>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Sent Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($notifications)): ?>
                        <tr>
                            <td colspan="8" class="no-notifications">
                                <p>📭 No notifications found</p>
                                <p>Send your first notification to get started!</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                        <tr>
                            <td>#<?php echo str_pad($notification['notification_id'], 3, '0', STR_PAD_LEFT); ?></td>
                            <td>
                                <span class="recipient-type recipient-<?php echo $notification['recipient_type']; ?>">
                                    <?php echo ucfirst($notification['recipient_type']); ?>
                                </span><br>
                                <small>ID: <?php echo $notification['recipient_id']; ?></small>
                            </td>
                            <td>
                                <span class="notification-type type-<?php echo strtolower($notification['notification_type']); ?>">
                                    <?php echo $notification['notification_type']; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($notification['title']); ?></td>
                            <td class="message-preview">
                                <?php echo htmlspecialchars(substr($notification['message'], 0, 50)) . (strlen($notification['message']) > 50 ? '...' : ''); ?>
                            </td>
                            <td>
                                <?php echo $notification['sent_date']; ?><br>
                                <small><?php echo $notification['sent_time']; ?></small>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($notification['status']); ?>">
                                    <?php echo $notification['status']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <!-- Resend Form -->
                                    <form method="POST" action="notifications.php" style="display:inline;">
                                        <input type="hidden" name="action" value="resend">
                                        <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                        <button type="submit" class="btn btn-small btn-resend" onclick="return confirm('Resend this notification?')">Resend</button>
                                    </form>
                                    
                                    <!-- Delete Form -->
                                    <?php if ($notification['status'] == 'Failed'): ?>
                                    <form method="POST" action="notifications.php" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                        <button type="submit" class="btn btn-small btn-delete" onclick="return confirm('Delete this notification? This cannot be undone.')">Delete</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="main-footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Blood Donation DMS. All rights reserved.</p>
            <p>Powered by Compassion</p>
        </div>
    </footer>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>
