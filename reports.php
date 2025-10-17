<?php
session_start();
require_once 'config.php';

// Redirect to login if not authenticated as staff
if (!isset($_SESSION['staff_id'])) {
    header('Location: login.php');
    exit;
}

// Handle different report types
$report_type = isset($_GET['type']) ? $_GET['type'] : 'summary';

// Common queries for different reports
$queries = array(
    'summary' => "SELECT 
        'Total Donors' as metric, COUNT(*) as value FROM donor
        UNION ALL
        SELECT 'Active Donors', COUNT(*) FROM donor WHERE last_donation_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
        UNION ALL
        SELECT 'Blood Units in Stock', COUNT(*) FROM blood_inventory
        UNION ALL
        SELECT 'Total Donations', COUNT(*) FROM donation_record
        UNION ALL
        SELECT 'Donations This Month', COUNT(*) FROM donation_record WHERE donation_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)",
    
    'blood_types' => "SELECT 
        blood_type,
        COUNT(*) as donor_count,
        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM donor), 2) as percentage
        FROM donor 
        GROUP BY blood_type 
        ORDER BY donor_count DESC",
    
    'monthly_donations' => "SELECT 
        DATE_FORMAT(donation_date, '%Y-%m') as month,
        COUNT(*) as donation_count,
        SUM(blood_volume_ml) as total_volume,
        AVG(blood_volume_ml) as avg_volume
        FROM donation_record 
        WHERE donation_date >= DATE_SUB(NOW(), INTERVAL 12 MONTHS)
        GROUP BY DATE_FORMAT(donation_date, '%Y-%m')
        ORDER BY month",
    
    'inventory_status' => "SELECT 
        status,
        COUNT(*) as unit_count,
        SUM(quantity_ml) as total_volume
        FROM blood_inventory 
        GROUP BY status",
    
    'staff_performance' => "SELECT 
        s.first_name,
        s.last_name,
        s.department,
        COUNT(dr.donation_id) as donations_handled,
        SUM(dr.blood_volume_ml) as total_volume_handled,
        AVG(dr.blood_volume_ml) as avg_volume_per_donation
        FROM staff s
        LEFT JOIN donation_record dr ON s.staff_id = dr.staff_id
        GROUP BY s.staff_id, s.first_name, s.last_name, s.department
        ORDER BY donations_handled DESC",
    
    'donor_demographics' => "SELECT 
        CASE 
            WHEN age < 18 THEN 'Under 18'
            WHEN age BETWEEN 18 AND 25 THEN '18-25'
            WHEN age BETWEEN 26 AND 35 THEN '26-35'
            WHEN age BETWEEN 36 AND 45 THEN '36-45'
            WHEN age BETWEEN 46 AND 55 THEN '46-55'
            WHEN age > 55 THEN 'Over 55'
        END as age_group,
        COUNT(*) as donor_count
        FROM donor 
        GROUP BY age_group
        ORDER BY MIN(age)",
    
    'expiring_blood' => "SELECT 
        inventory_id,
        blood_type,
        quantity_ml,
        collection_date,
        expiry_date,
        DATEDIFF(expiry_date, NOW()) as days_until_expiry
        FROM blood_inventory 
        WHERE expiry_date <= DATE_ADD(NOW(), INTERVAL 30 DAYS)
        ORDER BY expiry_date",
    
    'donation_frequency' => "SELECT 
        donor_id,
        CONCAT(first_name, ' ', last_name) as donor_name,
        COUNT(dr.donation_id) as donation_count,
        MAX(dr.donation_date) as last_donation,
        DATEDIFF(NOW(), MAX(dr.donation_date)) as days_since_last_donation
        FROM donor d
        LEFT JOIN donation_record dr ON d.donor_id = dr.donor_id
        GROUP BY d.donor_id, d.first_name, d.last_name
        HAVING donation_count > 0
        ORDER BY donation_count DESC"
);

// Execute the selected query
$query = isset($queries[$report_type]) ? $queries[$report_type] : $queries['summary'];
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$data = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Blood Donation DMS</title>
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
        
        .reports-container {
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
        
        .report-nav {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .report-nav a {
            display: inline-block;
            padding: 0.5rem 1rem;
            margin: 0.25rem;
            background: #E21C3D;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        
        .report-nav a:hover, .report-nav a.active {
            background: #8B0000;
        }
        
        .report-content {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .report-table th,
        .report-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .report-table th {
            background: #E21C3D;
            color: white;
            font-weight: bold;
        }
        
        .report-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .report-table tr:hover {
            background: #e9ecef;
        }
        
        .export-buttons {
            text-align: center;
            margin: 2rem 0;
        }
        
        .export-btn {
            background: #E21C3D;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            margin: 0 0.5rem;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s ease;
        }
        
        .export-btn:hover {
            background: #8B0000;
        }
        
        .no-data {
            text-align: center;
            color: #666;
            padding: 2rem;
            font-style: italic;
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
                        <a href="reports.php" class="nav-item active">
                            Reports
                        </a>
                        <a href="notifications.php" class="nav-item">
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
                <span style="color: #333; font-size: 18px; font-weight: 500;">Reports</span>
            </div>
            <div>
                <a href="comprehensive_report.php" style="background: #E21C3D; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                    📊 Comprehensive Report
                </a>
                <a href="export_data.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-left: 10px;">
                    📥 Export Data
                </a>
            </div>
        </div>
                <div class="reports-container">
                    <!-- Page Header -->
                    <div class="page-header">
                        <h1>📊 Database Reports & Queries</h1>
                        <p>Comprehensive data analysis and reporting tools</p>
                    </div>

        <!-- Report Navigation -->
        <div class="report-nav">
            <a href="?type=summary" class="<?php echo $report_type === 'summary' ? 'active' : ''; ?>">Summary</a>
            <a href="?type=blood_types" class="<?php echo $report_type === 'blood_types' ? 'active' : ''; ?>">Blood Types</a>
            <a href="?type=monthly_donations" class="<?php echo $report_type === 'monthly_donations' ? 'active' : ''; ?>">Monthly Trends</a>
            <a href="?type=inventory_status" class="<?php echo $report_type === 'inventory_status' ? 'active' : ''; ?>">Inventory Status</a>
            <a href="?type=staff_performance" class="<?php echo $report_type === 'staff_performance' ? 'active' : ''; ?>">Staff Performance</a>
            <a href="?type=donor_demographics" class="<?php echo $report_type === 'donor_demographics' ? 'active' : ''; ?>">Donor Demographics</a>
            <a href="?type=expiring_blood" class="<?php echo $report_type === 'expiring_blood' ? 'active' : ''; ?>">Expiring Blood</a>
            <a href="?type=donation_frequency" class="<?php echo $report_type === 'donation_frequency' ? 'active' : ''; ?>">Donation Frequency</a>
        </div>

        <!-- Report Content -->
        <div class="report-content">
            <h2>
                <?php
                $titles = array(
                    'summary' => 'System Summary Report',
                    'blood_types' => 'Blood Type Distribution',
                    'monthly_donations' => 'Monthly Donation Trends',
                    'inventory_status' => 'Blood Inventory Status',
                    'staff_performance' => 'Staff Performance Analysis',
                    'donor_demographics' => 'Donor Demographics',
                    'expiring_blood' => 'Expiring Blood Units',
                    'donation_frequency' => 'Donation Frequency Analysis'
                );
                echo isset($titles[$report_type]) ? $titles[$report_type] : 'Report';
                ?>
            </h2>
            
            <?php if (empty($data)): ?>
                <div class="no-data">
                    No data available for this report. Start adding donors and donations to see data here!
                </div>
            <?php else: ?>
                <table class="report-table">
                    <thead>
                        <tr>
                            <?php foreach (array_keys($data[0]) as $header): ?>
                                <th><?php echo ucwords(str_replace('_', ' ', $header)); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                    <td><?php echo htmlspecialchars($value); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Export Buttons -->
        <div class="export-buttons">
            <button class="export-btn" onclick="window.print()">🖨️ Print Report</button>
            <button class="export-btn" onclick="exportToCSV()">📊 Export to CSV</button>
            <button class="export-btn" onclick="window.location.href='insights.php'">📈 View Insights</button>
        </div>
    </div>

    <script>
        function exportToCSV() {
            const table = document.querySelector('.report-table');
            if (!table) return;
            
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = [], cols = rows[i].querySelectorAll('td, th');
                
                for (let j = 0; j < cols.length; j++) {
                    row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
                }
                
                csv.push(row.join(','));
            }
            
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'blood_donation_report_<?php echo $report_type; ?>_<?php echo date('Y-m-d'); ?>.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
    </script>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>
