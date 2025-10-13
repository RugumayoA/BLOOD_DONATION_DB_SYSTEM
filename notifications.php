<?php
// Include database connection
require_once 'config.php';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    try {
        // CREATE: Send new notification
        if ($action == 'send_notification') {
            $sql = "INSERT INTO notification (recipient_type, recipient_id, notification_type, title, message, sent_date, sent_time, status, delivery_method) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(
                $_POST['recipient_type'],
                $_POST['recipient_id'],
                $_POST['notification_type'],
                $_POST['title'],
                $_POST['message'],
                date('Y-m-d'),
                date('H:i:s'),
                'Queued',
                $_POST['notification_type']
            ));
            
            $success_message = "Notification sent successfully!";
        }
        
        // UPDATE: Resend notification
        if ($action == 'resend' && isset($_POST['notification_id'])) {
            $sql = "UPDATE notification 
                    SET status = 'Queued', sent_date = ?, sent_time = ? 
                    WHERE notification_id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(
                date('Y-m-d'),
                date('H:i:s'),
                $_POST['notification_id']
            ));
            
            $success_message = "Notification resent successfully!";
        }
        
        // DELETE: Remove notification
        if ($action == 'delete' && isset($_POST['notification_id'])) {
            $sql = "DELETE FROM notification WHERE notification_id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array($_POST['notification_id']));
            
            $success_message = "Notification deleted successfully!";
        }
        
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// READ: Get all notifications
$sql = "SELECT * FROM notification ORDER BY sent_date DESC, sent_time DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$notifications = $stmt->fetchAll();

// READ: Get statistics
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Queued' THEN 1 ELSE 0 END) as queued,
    SUM(CASE WHEN status = 'Sent' THEN 1 ELSE 0 END) as sent,
    SUM(CASE WHEN status = 'Failed' THEN 1 ELSE 0 END) as failed
    FROM notification";
$stats_stmt = $pdo->prepare($stats_sql);
$stats_stmt->execute();
$stats = $stats_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Blood Donation DMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
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
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <img src="images/blood-drop-heart-logo.png" alt="Donate Blood Logo">
                <h1>Blood Donation DMS</h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="donors.php">Donors</a></li>
                    <li><a href="recipients.php">Recipients</a></li>
                    <li><a href="donations.php">Donations</a></li>
                    <li><a href="requests.php">Requests</a></li>
                    <li><a href="inventory.php">Inventory</a></li>
                    <li><a href="staff.php">Staff</a></li>
                    <li><a href="events.php">Events</a></li>
                    <li><a href="sessions.php">Sessions</a></li>
                    <li><a href="testing.php">Testing</a></li>
                    <li><a href="transfusions.php">Transfusions</a></li>
                    <li><a href="notifications.php" class="active">Notifications</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="page-title">
        <div class="container">
            <h1>Notification Management</h1>
        </div>
    </div>

    <div class="notifications-container">
        <!-- Success/Error Messages -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="alert alert-success">✅ Notification sent successfully!</div>
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
</body>
</html>
