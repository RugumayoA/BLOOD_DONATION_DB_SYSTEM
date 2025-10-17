<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['staff_name'])) {
    header('Location: login.php');
    exit;
}

// Comprehensive Blood Donation System Report
$report_data = array();

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// 1. System Overview
$overview_sql = "SELECT 
    (SELECT COUNT(*) FROM donor) as active_donors,
    (SELECT COUNT(*) FROM recipient WHERE status = 'Active') as active_recipients,
    (SELECT COUNT(*) FROM donation_record) as total_donations,
    (SELECT COUNT(*) FROM blood_request WHERE status = 'Approved') as approved_requests,
    (SELECT COUNT(*) FROM blood_inventory WHERE status = 'Available') as available_units,
    (SELECT COUNT(*) FROM staff WHERE status = 'Active') as active_staff";

$result = $conn->query($overview_sql);
if (!$result) {
    // If query fails, log error and use defaults
    error_log("Database query failed: " . $conn->error);
    $report_data['overview'] = array();
} else {
    $report_data['overview'] = $result->fetch_assoc();
}

// Ensure all required keys exist with default values
$default_overview = array(
    'active_donors' => 0,
    'active_recipients' => 0,
    'total_donations' => 0,
    'approved_requests' => 0,
    'available_units' => 0,
    'active_staff' => 0
);

$report_data['overview'] = array_merge($default_overview, $report_data['overview']);

// 2. Blood Type Distribution
$blood_types_sql = "SELECT 
    blood_type,
    COUNT(*) as count,
    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM donor)), 2) as percentage
    FROM donor 
    WHERE blood_type IS NOT NULL 
    GROUP BY blood_type 
    ORDER BY count DESC";

$result = $conn->query($blood_types_sql);
if (!$result) {
    error_log("Blood types query failed: " . $conn->error);
    $report_data['blood_types'] = array();
} else {
    $report_data['blood_types'] = $result->fetch_all(MYSQLI_ASSOC);
}

// 3. Monthly Donation Trends (Last 12 months)
$monthly_sql = "SELECT 
    DATE_FORMAT(donation_date, '%Y-%m') as month,
    COUNT(*) as donation_count,
    SUM(blood_volume_ml) as total_volume,
    AVG(blood_volume_ml) as avg_volume
    FROM donation_record 
    WHERE donation_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(donation_date, '%Y-%m')
    ORDER BY month DESC";

$result = $conn->query($monthly_sql);
if (!$result) {
    error_log("Monthly trends query failed: " . $conn->error);
    $report_data['monthly_trends'] = array();
} else {
    $report_data['monthly_trends'] = $result->fetch_all(MYSQLI_ASSOC);
}

// 4. Top Donors
$top_donors_sql = "SELECT 
    d.first_name,
    d.last_name,
    d.blood_type,
    COUNT(dr.donation_id) as donation_count,
    SUM(dr.blood_volume_ml) as total_donated,
    MAX(dr.donation_date) as last_donation
    FROM donor d
    LEFT JOIN donation_record dr ON d.donor_id = dr.donor_id
    GROUP BY d.donor_id, d.first_name, d.last_name, d.blood_type
    HAVING donation_count > 0
    ORDER BY donation_count DESC, total_donated DESC
    LIMIT 10";

$result = $conn->query($top_donors_sql);
if (!$result) {
    error_log("Top donors query failed: " . $conn->error);
    $report_data['top_donors'] = array();
} else {
    $report_data['top_donors'] = $result->fetch_all(MYSQLI_ASSOC);
}

// 5. Blood Inventory Status
$inventory_sql = "SELECT 
    blood_type,
    status,
    COUNT(*) as unit_count,
    SUM(quantity_ml) as total_ml,
    MIN(expiry_date) as earliest_expiry,
    MAX(expiry_date) as latest_expiry
    FROM blood_inventory 
    GROUP BY blood_type, status
    ORDER BY blood_type, status";

$result = $conn->query($inventory_sql);
if (!$result) {
    error_log("Inventory query failed: " . $conn->error);
    $report_data['inventory'] = array();
} else {
    $report_data['inventory'] = $result->fetch_all(MYSQLI_ASSOC);
}

// 6. Blood Request Analysis
$requests_sql = "SELECT 
    blood_type,
    urgency_level,
    status,
    COUNT(*) as request_count,
    SUM(quantity_ml) as total_requested
    FROM blood_request 
    GROUP BY blood_type, urgency_level, status
    ORDER BY blood_type, urgency_level, status";

$result = $conn->query($requests_sql);
if (!$result) {
    error_log("Requests query failed: " . $conn->error);
    $report_data['requests'] = array();
} else {
    $report_data['requests'] = $result->fetch_all(MYSQLI_ASSOC);
}

// 7. Recent Activity (Last 30 days)
$recent_activity_sql = "SELECT 
    'Donation' as activity_type,
    CONCAT(d.first_name, ' ', d.last_name) as person_name,
    dr.donation_date as activity_date,
    dr.blood_volume_ml as quantity,
    dr.blood_type
    FROM donation_record dr
    JOIN donor d ON dr.donor_id = d.donor_id
    WHERE dr.donation_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    
    UNION ALL
    
    SELECT 
    'Request' as activity_type,
    CONCAT(r.first_name, ' ', r.last_name) as person_name,
    br.request_date as activity_date,
    br.quantity_ml as quantity,
    br.blood_type
    FROM blood_request br
    JOIN recipient r ON br.recipient_id = r.recipient_id
    WHERE br.request_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    
    ORDER BY activity_date DESC
    LIMIT 20";

$result = $conn->query($recent_activity_sql);
if (!$result) {
    error_log("Recent activity query failed: " . $conn->error);
    $report_data['recent_activity'] = array();
} else {
    $report_data['recent_activity'] = $result->fetch_all(MYSQLI_ASSOC);
}

// 8. Critical Alerts
$alerts = array();

// Expiring blood units (within 7 days)
$expiring_sql = "SELECT COUNT(*) as count FROM blood_inventory 
                 WHERE expiry_date <= DATE_ADD(NOW(), INTERVAL 7 DAY) 
                 AND status = 'Available'";
$result = $conn->query($expiring_sql);
if (!$result) {
    error_log("Expiring units query failed: " . $conn->error);
    $expiring_count = 0;
} else {
    $expiring_data = $result->fetch_assoc();
    $expiring_count = isset($expiring_data['count']) ? $expiring_data['count'] : 0;
}
if ($expiring_count > 0) {
    $alerts[] = "⚠️ $expiring_count blood units expiring within 7 days";
}

// Low inventory (less than 5 units of any blood type)
$low_inventory_sql = "SELECT blood_type, COUNT(*) as count 
                      FROM blood_inventory 
                      WHERE status = 'Available' 
                      GROUP BY blood_type 
                      HAVING count < 5";
$result = $conn->query($low_inventory_sql);
if (!$result) {
    error_log("Low inventory query failed: " . $conn->error);
    $low_inventory = array();
} else {
    $low_inventory = $result->fetch_all(MYSQLI_ASSOC);
}

// Ensure $low_inventory is an array before foreach
if (is_array($low_inventory)) {
    foreach ($low_inventory as $item) {
        $alerts[] = "🔴 Low inventory: Only {$item['count']} units of {$item['blood_type']} available";
    }
}

// Pending requests
$pending_sql = "SELECT COUNT(*) as count FROM blood_request WHERE status = 'Pending'";
$result = $conn->query($pending_sql);
if (!$result) {
    error_log("Pending requests query failed: " . $conn->error);
    $pending_count = 0;
} else {
    $pending_data = $result->fetch_assoc();
    $pending_count = isset($pending_data['count']) ? $pending_data['count'] : 0;
}
if ($pending_count > 0) {
    $alerts[] = "⏳ $pending_count blood requests pending approval";
}

$report_data['alerts'] = $alerts;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprehensive Blood Donation Report</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .report-header {
            background: linear-gradient(135deg, #E21C3D, #8B0000);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .report-title {
            font-size: 2.5rem;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        
        .report-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .report-section {
            background: white;
            margin-bottom: 30px;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section-title {
            color: #E21C3D;
            font-size: 1.8rem;
            margin-bottom: 20px;
            border-bottom: 2px solid #E21C3D;
            padding-bottom: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #E21C3D, #8B0000);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }
        
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .data-table th {
            background-color: #E21C3D;
            color: white;
            font-weight: bold;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .alert-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        .alert-box.warning {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .alert-box.danger {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .print-btn {
            background: #E21C3D;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            margin-bottom: 20px;
        }
        
        .print-btn:hover {
            background: #8B0000;
        }
        
        @media print {
            .print-btn { display: none; }
            .report-container { max-width: none; }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="report-header">
            <h1 class="report-title">🩸 Blood Donation System Report</h1>
            <p class="report-subtitle">Comprehensive Analysis - <?php echo date('F j, Y'); ?></p>
        </div>
        
        <button class="print-btn" onclick="window.print()">🖨️ Print Report</button>
        
        <!-- System Overview -->
        <div class="report-section">
            <h2 class="section-title">📊 System Overview</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $report_data['overview']['active_donors']; ?></div>
                    <div class="stat-label">Active Donors</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $report_data['overview']['active_recipients']; ?></div>
                    <div class="stat-label">Active Recipients</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $report_data['overview']['total_donations']; ?></div>
                    <div class="stat-label">Total Donations</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $report_data['overview']['approved_requests']; ?></div>
                    <div class="stat-label">Approved Requests</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $report_data['overview']['available_units']; ?></div>
                    <div class="stat-label">Available Units</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $report_data['overview']['active_staff']; ?></div>
                    <div class="stat-label">Active Staff</div>
                </div>
            </div>
        </div>
        
        <!-- Critical Alerts -->
        <?php if (!empty($report_data['alerts'])): ?>
        <div class="report-section">
            <h2 class="section-title">🚨 Critical Alerts</h2>
            <?php if (is_array($report_data['alerts'])): foreach ($report_data['alerts'] as $alert): ?>
                <div class="alert-box <?php echo strpos($alert, '🔴') !== false ? 'danger' : 'warning'; ?>">
                    <?php echo $alert; ?>
                </div>
            <?php endforeach; endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Blood Type Distribution -->
        <div class="report-section">
            <h2 class="section-title">🩸 Blood Type Distribution</h2>
            <div class="chart-container">
                <canvas id="bloodTypeChart"></canvas>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Blood Type</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (is_array($report_data['blood_types'])): foreach ($report_data['blood_types'] as $type): ?>
                        <tr>
                            <td><?php echo $type['blood_type']; ?></td>
                            <td><?php echo $type['count']; ?></td>
                            <td><?php echo $type['percentage']; ?>%</td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Monthly Trends -->
        <div class="report-section">
            <h2 class="section-title">📈 Monthly Donation Trends</h2>
            <div class="chart-container">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
        
        <!-- Top Donors -->
        <div class="report-section">
            <h2 class="section-title">🏆 Top Donors</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Blood Type</th>
                            <th>Donations</th>
                            <th>Total Volume (ml)</th>
                            <th>Last Donation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (is_array($report_data['top_donors'])): foreach ($report_data['top_donors'] as $donor): ?>
                        <tr>
                            <td><?php echo $donor['first_name'] . ' ' . $donor['last_name']; ?></td>
                            <td><?php echo $donor['blood_type']; ?></td>
                            <td><?php echo $donor['donation_count']; ?></td>
                            <td><?php echo number_format($donor['total_donated']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($donor['last_donation'])); ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Inventory Status -->
        <div class="report-section">
            <h2 class="section-title">📦 Blood Inventory Status</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Blood Type</th>
                            <th>Status</th>
                            <th>Units</th>
                            <th>Total Volume (ml)</th>
                            <th>Earliest Expiry</th>
                            <th>Latest Expiry</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (is_array($report_data['inventory'])): foreach ($report_data['inventory'] as $item): ?>
                        <tr>
                            <td><?php echo $item['blood_type']; ?></td>
                            <td><?php echo $item['status']; ?></td>
                            <td><?php echo $item['unit_count']; ?></td>
                            <td><?php echo number_format($item['total_ml']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($item['earliest_expiry'])); ?></td>
                            <td><?php echo date('M j, Y', strtotime($item['latest_expiry'])); ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="report-section">
            <h2 class="section-title">⚡ Recent Activity (Last 30 Days)</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Person</th>
                            <th>Date</th>
                            <th>Blood Type</th>
                            <th>Volume (ml)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (is_array($report_data['recent_activity'])): foreach ($report_data['recent_activity'] as $activity): ?>
                        <tr>
                            <td><?php echo $activity['activity_type']; ?></td>
                            <td><?php echo $activity['person_name']; ?></td>
                            <td><?php echo date('M j, Y', strtotime($activity['activity_date'])); ?></td>
                            <td><?php echo $activity['blood_type']; ?></td>
                            <td><?php echo number_format($activity['quantity']); ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Blood Type Distribution Chart
        const bloodTypeData = <?php echo json_encode($report_data['blood_types']); ?>;
        const bloodTypeCtx = document.getElementById('bloodTypeChart').getContext('2d');
        new Chart(bloodTypeCtx, {
            type: 'pie',
            data: {
                labels: bloodTypeData.map(item => item.blood_type),
                datasets: [{
                    data: bloodTypeData.map(item => item.count),
                    backgroundColor: [
                        '#E21C3D', '#FF6B6B', '#4ECDC4', '#45B7D1',
                        '#96CEB4', '#FFEAA7', '#DDA0DD', '#98D8C8'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Blood Type Distribution'
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Monthly Trends Chart
        const monthlyData = <?php echo json_encode($report_data['monthly_trends']); ?>;
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: monthlyData.map(item => item.month),
                datasets: [{
                    label: 'Donations',
                    data: monthlyData.map(item => item.donation_count),
                    backgroundColor: '#E21C3D',
                    borderColor: '#8B0000',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Donation Count'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
