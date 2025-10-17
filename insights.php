<?php
session_start();
require_once 'config.php';

// Redirect to login if not authenticated as staff
if (!isset($_SESSION['staff_id'])) {
    header('Location: login.php');
    exit;
}

// Get statistics for reports
$stats = array();

// Donor Statistics
$donor_stats_sql = "SELECT 
    COUNT(*) as total_donors,
    COUNT(CASE WHEN last_donation_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR) THEN 1 END) as active_donors,
    COUNT(CASE WHEN blood_type = 'O-' THEN 1 END) as o_negative,
    COUNT(CASE WHEN blood_type = 'O+' THEN 1 END) as o_positive,
    COUNT(CASE WHEN blood_type = 'A-' THEN 1 END) as a_negative,
    COUNT(CASE WHEN blood_type = 'A+' THEN 1 END) as a_positive,
    COUNT(CASE WHEN blood_type = 'B-' THEN 1 END) as b_negative,
    COUNT(CASE WHEN blood_type = 'B+' THEN 1 END) as b_positive,
    COUNT(CASE WHEN blood_type = 'AB-' THEN 1 END) as ab_negative,
    COUNT(CASE WHEN blood_type = 'AB+' THEN 1 END) as ab_positive
    FROM donor";
$result = $conn->query($donor_stats_sql);
$stats['donors'] = $result ? $result->fetch_assoc() : array();

// Blood Inventory Statistics
$inventory_stats_sql = "SELECT 
    COUNT(*) as total_units,
    SUM(quantity_ml) as total_volume,
    COUNT(CASE WHEN status = 'Available' THEN 1 END) as available_units,
    COUNT(CASE WHEN status = 'Quarantined' THEN 1 END) as quarantined_units,
    COUNT(CASE WHEN status = 'Reserved' THEN 1 END) as reserved_units,
    COUNT(CASE WHEN expiry_date < DATE_ADD(NOW(), INTERVAL 7 DAYS) THEN 1 END) as expiring_soon
    FROM blood_inventory";
$result = $conn->query($inventory_stats_sql);
$stats['inventory'] = $result ? $result->fetch_assoc() : array();

// Donation Statistics
$donation_stats_sql = "SELECT 
    COUNT(*) as total_donations,
    SUM(blood_volume_ml) as total_blood_collected,
    COUNT(CASE WHEN donation_date >= DATE_SUB(NOW(), INTERVAL 30 DAYS) THEN 1 END) as donations_this_month,
    AVG(blood_volume_ml) as avg_donation_volume
    FROM donation_record";
$result = $conn->query($donation_stats_sql);
$stats['donations'] = $result ? $result->fetch_assoc() : array();

// Blood Type Distribution for Charts
$blood_type_sql = "SELECT blood_type, COUNT(*) as count FROM donor GROUP BY blood_type ORDER BY count DESC";
$result = $conn->query($blood_type_sql);
$blood_type_data = $result ? $result->fetch_all(MYSQLI_ASSOC) : array();

// Monthly Donation Trends
$monthly_trends_sql = "SELECT 
    DATE_FORMAT(donation_date, '%Y-%m') as month,
    COUNT(*) as donation_count,
    SUM(blood_volume_ml) as total_volume
    FROM donation_record 
    GROUP BY DATE_FORMAT(donation_date, '%Y-%m')
    ORDER BY month";
$result = $conn->query($monthly_trends_sql);
$monthly_trends = $result ? $result->fetch_all(MYSQLI_ASSOC) : array();

// Staff Performance
$staff_performance_sql = "SELECT 
    s.first_name,
    s.last_name,
    s.department,
    COUNT(dr.donation_id) as donations_handled,
    SUM(dr.blood_volume_ml) as total_volume_handled
    FROM staff s
    LEFT JOIN donation_record dr ON s.staff_id = dr.staff_id
    GROUP BY s.staff_id, s.first_name, s.last_name, s.department
    ORDER BY donations_handled DESC";
$result = $conn->query($staff_performance_sql);
$staff_performance = $result ? $result->fetch_all(MYSQLI_ASSOC) : array();

// Recent Activity
$recent_activity_sql = "SELECT 
    'donation' as type,
    CONCAT('New donation by donor #', dr.donor_id) as description,
    dr.donation_date as activity_date,
    dr.blood_volume_ml as volume
    FROM donation_record dr
    WHERE dr.donation_date >= DATE_SUB(NOW(), INTERVAL 7 DAYS)
    UNION ALL
    SELECT 
    'inventory' as type,
    CONCAT('Blood unit #', bi.inventory_id, ' - ', bi.blood_type) as description,
    bi.collection_date as activity_date,
    bi.quantity_ml as volume
    FROM blood_inventory bi
    WHERE bi.collection_date >= DATE_SUB(NOW(), INTERVAL 7 DAYS)
    ORDER BY activity_date DESC
    LIMIT 10";
$result = $conn->query($recent_activity_sql);
$recent_activity = $result ? $result->fetch_all(MYSQLI_ASSOC) : array();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insights & Reports - Blood Donation DMS</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        .insights-container {
            padding: 2rem;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .page-header {
            background: linear-gradient(135deg, #E21C3D, #8B0000);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .page-header h1 {
            margin: 0;
            font-size: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #E21C3D;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 1.1rem;
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .chart-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .chart-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .reports-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .report-item {
            padding: 1rem;
            border: 1px solid #E0E0E0;
            border-radius: 10px;
            background: #f8f9fa;
        }
        
        .report-title {
            font-weight: bold;
            color: #E21C3D;
            margin-bottom: 0.5rem;
        }
        
        .report-value {
            font-size: 1.2rem;
            color: #333;
        }
        
        .activity-list {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .activity-item {
            padding: 1rem;
            border-bottom: 1px solid #E0E0E0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-type {
            background: #E21C3D;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .activity-description {
            flex: 1;
            margin-left: 1rem;
        }
        
        .activity-date {
            color: #666;
            font-size: 0.9rem;
        }
        
        .export-buttons {
            text-align: center;
            margin: 2rem 0;
        }
        
        .export-btn {
            background: #E21C3D;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            margin: 0 0.5rem;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .export-btn:hover {
            background: #8B0000;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="images/blood-drop-heart-logo.png" alt="Blood Donation Logo">
                <h2>Blood Donation DMS</h2>
            </div>
            
            <div class="sidebar-content">
                <nav class="sidebar-nav">
                    <div class="nav-section">
                        <div class="nav-section-title">Main</div>
                        <a href="index.php" class="nav-item">
                            Dashboard
                        </a>
                    </div>
                    
                    <div class="nav-section">
                        <div class="nav-section-title">Management</div>
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
                        <div class="nav-section-title">Events & Sessions</div>
                        <a href="events.php" class="nav-item">
                            Events
                        </a>
                        <a href="sessions.php" class="nav-item">
                            Sessions
                        </a>
                    </div>
                    
                    <div class="nav-section">
                        <div class="nav-section-title">Medical</div>
                        <a href="testing.php" class="nav-item">
                            Testing
                        </a>
                        <a href="transfusions.php" class="nav-item">
                            Transfusions
                        </a>
                    </div>
                    
                    <div class="nav-section">
                        <div class="nav-section-title">Reports & Analytics</div>
                        <a href="insights.php" class="nav-item active">
                            <i>📊</i> Insights
                        </a>
                        <a href="reports.php" class="nav-item">
                            Reports
                        </a>
                        <a href="notifications.php" class="nav-item">
                            Notifications
                        </a>
                    </div>
                    
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['staff_name']); ?></div>
                        <div class="user-role">Staff Member</div>
                        <a href="logout.php" class="logout-btn">🚪 Logout</a>
                    </div>
                </nav>
            </div>
        </div>
        
        <!-- Main Content Area -->
        <div class="main-content">
            <div class="top-bar">
                <div>
                    <span style="color: #333; font-size: 18px; font-weight: 500;">Insights</span>
                </div>
            </div>
            
            <div class="content-area">
    <div class="insights-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>
                <span>📊</span>
                Insights & Reports
            </h1>
            <p>Comprehensive analytics and reporting for your blood donation management system</p>
        </div>

        <!-- Key Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo isset($stats['donors']['total_donors']) ? $stats['donors']['total_donors'] : 0; ?></div>
                <div class="stat-label">Total Donors</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo isset($stats['donors']['active_donors']) ? $stats['donors']['active_donors'] : 0; ?></div>
                <div class="stat-label">Active Donors</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo isset($stats['inventory']['total_units']) ? $stats['inventory']['total_units'] : 0; ?></div>
                <div class="stat-label">Blood Units in Stock</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format((isset($stats['inventory']['total_volume']) ? $stats['inventory']['total_volume'] : 0) / 1000, 1); ?>L</div>
                <div class="stat-label">Total Blood Volume</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo isset($stats['donations']['donations_this_month']) ? $stats['donations']['donations_this_month'] : 0; ?></div>
                <div class="stat-label">Donations This Month</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo isset($stats['inventory']['expiring_soon']) ? $stats['inventory']['expiring_soon'] : 0; ?></div>
                <div class="stat-label">Units Expiring Soon</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-grid">
            <!-- Blood Type Distribution Pie Chart -->
            <div class="chart-container">
                <div class="chart-title">Blood Type Distribution</div>
                <canvas id="bloodTypeChart" width="400" height="300"></canvas>
            </div>

            <!-- Monthly Donation Trends -->
            <div class="chart-container">
                <div class="chart-title">Monthly Donation Trends</div>
                <canvas id="monthlyTrendsChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- Reports Section -->
        <div class="reports-section">
            <h2 style="text-align: center; margin-bottom: 2rem; color: #E21C3D;">📈 Detailed Reports</h2>
            <div class="reports-grid">
                <div class="report-item">
                    <div class="report-title">Blood Type Breakdown</div>
                    <div class="report-value">
                        O+: <?php echo isset($stats['donors']['o_positive']) ? $stats['donors']['o_positive'] : 0; ?> | 
                        O-: <?php echo isset($stats['donors']['o_negative']) ? $stats['donors']['o_negative'] : 0; ?> | 
                        A+: <?php echo isset($stats['donors']['a_positive']) ? $stats['donors']['a_positive'] : 0; ?> | 
                        A-: <?php echo isset($stats['donors']['a_negative']) ? $stats['donors']['a_negative'] : 0; ?>
                    </div>
                </div>
                <div class="report-item">
                    <div class="report-title">Inventory Status</div>
                    <div class="report-value">
                        Available: <?php echo isset($stats['inventory']['available_units']) ? $stats['inventory']['available_units'] : 0; ?> | 
                        Quarantined: <?php echo isset($stats['inventory']['quarantined_units']) ? $stats['inventory']['quarantined_units'] : 0; ?> | 
                        Reserved: <?php echo isset($stats['inventory']['reserved_units']) ? $stats['inventory']['reserved_units'] : 0; ?>
                    </div>
                </div>
                <div class="report-item">
                    <div class="report-title">Average Donation Volume</div>
                    <div class="report-value"><?php echo number_format(isset($stats['donations']['avg_donation_volume']) ? $stats['donations']['avg_donation_volume'] : 0, 1); ?> ml</div>
                </div>
                <div class="report-item">
                    <div class="report-title">Total Blood Collected</div>
                    <div class="report-value"><?php echo number_format((isset($stats['donations']['total_blood_collected']) ? $stats['donations']['total_blood_collected'] : 0) / 1000, 1); ?> Liters</div>
                </div>
            </div>
        </div>

        <!-- Staff Performance -->
        <div class="reports-section">
            <h2 style="text-align: center; margin-bottom: 2rem; color: #E21C3D;">👥 Staff Performance</h2>
            <div class="reports-grid">
                <?php foreach ($staff_performance as $staff): ?>
                <div class="report-item">
                    <div class="report-title"><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></div>
                    <div class="report-value">
                        Department: <?php echo htmlspecialchars($staff['department']); ?><br>
                        Donations Handled: <?php echo $staff['donations_handled']; ?><br>
                        Total Volume: <?php echo number_format($staff['total_volume_handled'] / 1000, 1); ?>L
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="activity-list">
            <h2 style="text-align: center; margin-bottom: 2rem; color: #E21C3D;">🕒 Recent Activity</h2>
            <?php if (empty($recent_activity)): ?>
                <p style="text-align: center; color: #666; padding: 2rem;">No recent activity to display. Start adding donors and donations to see activity here!</p>
            <?php else: ?>
                <?php foreach ($recent_activity as $activity): ?>
                <div class="activity-item">
                    <span class="activity-type"><?php echo strtoupper($activity['type']); ?></span>
                    <div class="activity-description">
                        <div><?php echo htmlspecialchars($activity['description']); ?></div>
                        <div class="activity-date"><?php echo date('M j, Y g:i A', strtotime($activity['activity_date'])); ?></div>
                    </div>
                    <div><?php echo $activity['volume']; ?>ml</div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Export Buttons -->
        <div class="export-buttons">
            <button class="export-btn" onclick="exportToPDF()">📄 Export to PDF</button>
            <button class="export-btn" onclick="exportToExcel()">📊 Export to Excel</button>
            <button class="export-btn" onclick="printReport()">🖨️ Print Report</button>
        </div>
    </div>

    <script>
        // Blood Type Distribution Pie Chart
        const bloodTypeData = <?php echo json_encode($blood_type_data); ?>;
        const bloodTypeLabels = bloodTypeData.map(item => item.blood_type);
        const bloodTypeCounts = bloodTypeData.map(item => parseInt(item.count));

        const bloodTypeCtx = document.getElementById('bloodTypeChart').getContext('2d');
        new Chart(bloodTypeCtx, {
            type: 'pie',
            data: {
                labels: bloodTypeLabels,
                datasets: [{
                    data: bloodTypeCounts,
                    backgroundColor: [
                        '#E21C3D', '#8B0000', '#DC143C', '#B22222',
                        '#FF6347', '#FF4500', '#FF1493', '#C71585'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Monthly Trends Chart
        const monthlyData = <?php echo json_encode($monthly_trends); ?>;
        const monthlyLabels = monthlyData.map(item => item.month);
        const monthlyCounts = monthlyData.map(item => parseInt(item.donation_count));

        const monthlyCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Donations',
                    data: monthlyCounts,
                    backgroundColor: '#E21C3D',
                    borderColor: '#8B0000',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Export Functions
        function exportToPDF() {
            window.print();
        }

        function exportToExcel() {
            alert('Excel export functionality would be implemented here!');
        }

        function printReport() {
            window.print();
        }
    </script>
            </div> <!-- End content-area -->
        </div> <!-- End main-content -->
    </div> <!-- End dashboard-container -->
</body>
</html>
<?php
$conn->close();
?>
