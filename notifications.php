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
        
        .filters-section {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .filters-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: end;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        
        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
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
        }
        
        .btn-small {
            padding: 4px 8px;
            font-size: 0.8em;
            border-radius: 3px;
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
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            gap: 10px;
        }
        
        .pagination button {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
        }
        
        .pagination button:hover {
            background: #f8f9fa;
        }
        
        .pagination button.active {
            background: #E21C3D;
            color: white;
            border-color: #E21C3D;
        }
        
        .pagination button:disabled {
            background: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
        }
        
        .no-notifications {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-notifications i {
            font-size: 4em;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .notifications-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .notification-stats {
                justify-content: center;
            }
            
            .filters-row {
                flex-direction: column;
            }
            
            .filter-group {
                min-width: unset;
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
        <!-- Statistics Section -->
        <div class="notifications-header">
            <h2 class="notifications-title">Notifications Dashboard</h2>
            <div class="notification-stats">
                <div class="stat-card">
                    <p class="stat-number">156</p>
                    <p class="stat-label">Total Notifications</p>
                </div>
                <div class="stat-card">
                    <p class="stat-number">23</p>
                    <p class="stat-label">Queued</p>
                </div>
                <div class="stat-card">
                    <p class="stat-number">128</p>
                    <p class="stat-label">Sent</p>
                </div>
                <div class="stat-card">
                    <p class="stat-number">5</p>
                    <p class="stat-label">Failed</p>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <h3 style="margin-top: 0; color: #333;">Filter Notifications</h3>
            <div class="filters-row">
                <div class="filter-group">
                    <label for="recipient-type">Recipient Type</label>
                    <select id="recipient-type">
                        <option value="">All Types</option>
                        <option value="donor">Donor</option>
                        <option value="recipient">Recipient</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="notification-type">Notification Type</label>
                    <select id="notification-type">
                        <option value="">All Types</option>
                        <option value="SMS">SMS</option>
                        <option value="Email">Email</option>
                        <option value="Push">Push</option>
                        <option value="Call">Call</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select id="status">
                        <option value="">All Status</option>
                        <option value="Queued">Queued</option>
                        <option value="Sent">Sent</option>
                        <option value="Failed">Failed</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="date-from">From Date</label>
                    <input type="date" id="date-from">
                </div>
                <div class="filter-group">
                    <label for="date-to">To Date</label>
                    <input type="date" id="date-to">
                </div>
                <div class="filter-group">
                    <button class="btn btn-primary" onclick="applyFilters()">Apply Filters</button>
                </div>
            </div>
        </div>

        <!-- Notifications Table -->
        <div class="notifications-table-container">
            <div class="table-header">
                <h3 class="table-title">All Notifications</h3>
                <div class="table-actions">
                    <button class="btn btn-primary" onclick="sendNewNotification()">Send New Notification</button>
                    <button class="btn btn-secondary" onclick="exportNotifications()">Export</button>
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
                    <tr>
                        <td>#001</td>
                        <td>
                            <span class="recipient-type recipient-donor">Donor</span><br>
                            <small>ID: 123</small>
                        </td>
                        <td><span class="notification-type type-sms">SMS</span></td>
                        <td>Blood Donation Reminder</td>
                        <td class="message-preview">Hi John, it's been 3 months since your last donation. Your blood type O+ is urgently needed...</td>
                        <td>2024-01-15<br><small>14:30</small></td>
                        <td><span class="status-badge status-sent">Sent</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-small btn-view" onclick="viewNotification(1)">View</button>
                                <button class="btn btn-small btn-resend" onclick="resendNotification(1)">Resend</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>#002</td>
                        <td>
                            <span class="recipient-type recipient-recipient">Recipient</span><br>
                            <small>ID: 456</small>
                        </td>
                        <td><span class="notification-type type-email">Email</span></td>
                        <td>Blood Request Update</td>
                        <td class="message-preview">Your blood request has been processed. We found a compatible donor and will contact you soon...</td>
                        <td>2024-01-15<br><small>10:15</small></td>
                        <td><span class="status-badge status-sent">Sent</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-small btn-view" onclick="viewNotification(2)">View</button>
                                <button class="btn btn-small btn-resend" onclick="resendNotification(2)">Resend</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>#003</td>
                        <td>
                            <span class="recipient-type recipient-staff">Staff</span><br>
                            <small>ID: 789</small>
                        </td>
                        <td><span class="notification-type type-push">Push</span></td>
                        <td>Emergency Blood Request</td>
                        <td class="message-preview">URGENT: Emergency blood request for Type A- at City Hospital. Please respond immediately...</td>
                        <td>2024-01-15<br><small>16:45</small></td>
                        <td><span class="status-badge status-queued">Queued</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-small btn-view" onclick="viewNotification(3)">View</button>
                                <button class="btn btn-small btn-resend" onclick="resendNotification(3)">Resend</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>#004</td>
                        <td>
                            <span class="recipient-type recipient-donor">Donor</span><br>
                            <small>ID: 234</small>
                        </td>
                        <td><span class="notification-type type-call">Call</span></td>
                        <td>Follow-up Call</td>
                        <td class="message-preview">Thank you for your recent donation. How are you feeling? Any side effects to report?</td>
                        <td>2024-01-14<br><small>09:20</small></td>
                        <td><span class="status-badge status-failed">Failed</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-small btn-view" onclick="viewNotification(4)">View</button>
                                <button class="btn btn-small btn-resend" onclick="resendNotification(4)">Resend</button>
                                <button class="btn btn-small btn-delete" onclick="deleteNotification(4)">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>#005</td>
                        <td>
                            <span class="recipient-type recipient-recipient">Recipient</span><br>
                            <small>ID: 567</small>
                        </td>
                        <td><span class="notification-type type-sms">SMS</span></td>
                        <td>Appointment Confirmation</td>
                        <td class="message-preview">Your blood transfusion appointment is confirmed for tomorrow at 2:00 PM. Please arrive 15 minutes early...</td>
                        <td>2024-01-14<br><small>15:30</small></td>
                        <td><span class="status-badge status-sent">Sent</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-small btn-view" onclick="viewNotification(5)">View</button>
                                <button class="btn btn-small btn-resend" onclick="resendNotification(5)">Resend</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="pagination">
                <button onclick="changePage(1)" disabled>« Previous</button>
                <button class="active" onclick="changePage(1)">1</button>
                <button onclick="changePage(2)">2</button>
                <button onclick="changePage(3)">3</button>
                <button onclick="changePage(4)">4</button>
                <button onclick="changePage(5)">5</button>
                <button onclick="changePage(2)">Next »</button>
            </div>
        </div>
    </div>

    <footer class="main-footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Blood Donation DMS. All rights reserved.</p>
            <p>Powered by Compassion</p>
        </div>
    </footer>

    <script>
        // Filter functionality
        function applyFilters() {
            const recipientType = document.getElementById('recipient-type').value;
            const notificationType = document.getElementById('notification-type').value;
            const status = document.getElementById('status').value;
            const dateFrom = document.getElementById('date-from').value;
            const dateTo = document.getElementById('date-to').value;
            
            console.log('Applying filters:', {
                recipientType,
                notificationType,
                status,
                dateFrom,
                dateTo
            });
            
            // Here you would typically make an AJAX call to filter the notifications
            alert('Filters applied! (This would filter the notifications in a real implementation)');
        }
        
        // Notification actions
        function viewNotification(id) {
            alert(`Viewing notification #${id} details`);
            // Here you would open a modal or navigate to a detailed view
        }
        
        function resendNotification(id) {
            if (confirm(`Are you sure you want to resend notification #${id}?`)) {
                alert(`Resending notification #${id}...`);
                // Here you would make an AJAX call to resend the notification
            }
        }
        
        function deleteNotification(id) {
            if (confirm(`Are you sure you want to delete notification #${id}? This action cannot be undone.`)) {
                alert(`Deleting notification #${id}...`);
                // Here you would make an AJAX call to delete the notification
            }
        }
        
        function sendNewNotification() {
            alert('Opening new notification form...');
            // Here you would open a modal or navigate to a form to create a new notification
        }
        
        function exportNotifications() {
            alert('Exporting notifications to CSV...');
            // Here you would trigger a download of the notifications data
        }
        
        function changePage(page) {
            console.log(`Changing to page ${page}`);
            // Here you would make an AJAX call to load the new page of notifications
        }
        
        // Auto-refresh functionality
        setInterval(function() {
            // Check for new notifications every 30 seconds
            console.log('Checking for new notifications...');
        }, 30000);
    </script>
</body>
</html>
