<?php
session_start();
require_once 'config.php';

// Redirect to login if not authenticated as staff
if (!isset($_SESSION['staff_id'])) {
    header('Location: login.php');
    exit;
}

// Staff-only mode
$is_admin = true;

// Handle form submission for adding new donor
if ($_POST) {
    $sql = "INSERT INTO donor (first_name, last_name, date_of_birth, gender, blood_type, phone_number, email, address, city, medical_conditions, medications, last_donation_date, registration_date, marital_status, weight, height) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssssssdd",
        $_POST['firstName'],
        $_POST['lastName'],
        $_POST['dateOfBirth'],
        $_POST['gender'],
        $_POST['bloodType'],
        $_POST['phoneNumber'],
        $_POST['email'],
        $_POST['address'],
        $_POST['city'],
        $_POST['medicalConditions'],
        $_POST['medications'],
        $_POST['lastDonationDate'],
        $_POST['registrationDate'],
        $_POST['maritalStatus'],
        $_POST['weight'],
        $_POST['height']
    );

    if ($stmt->execute()) {
        $success_message = "Donor added successfully!";
    } else {
        $error_message = "Error: " . $conn->error;
    }
    $stmt->close();
}

// Get all donors from database
$sql = "SELECT * FROM donor ORDER BY last_name, first_name";
$result = $conn->query($sql);
$donors = $result->fetch_all(MYSQLI_ASSOC);

// Get donor statistics
$stats_sql = "SELECT 
    COUNT(*) as total_donors,
    COUNT(CASE WHEN last_donation_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR) THEN 1 END) as active_donors,
    COUNT(CASE WHEN blood_type = 'O-' THEN 1 END) as o_negative,
    COUNT(CASE WHEN blood_type = 'O+' THEN 1 END) as o_positive
    FROM donor";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

// Blood type distribution for chart
$bt_sql = "SELECT blood_type, COUNT(*) as cnt FROM donor GROUP BY blood_type";
$bt_result = $conn->query($bt_sql);
$bt_rows = $bt_result ? $bt_result->fetch_all(MYSQLI_ASSOC) : array();
$blood_labels = array();
$blood_data = array();
if ($bt_rows) {
    foreach ($bt_rows as $r) {
        $blood_labels[] = $r['blood_type'];
        $blood_data[] = (int) $r['cnt'];
    }
}

// Active vs Inactive donors
$act_sql = "SELECT 
    SUM(CASE WHEN last_donation_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR) THEN 1 ELSE 0 END) AS active,
    SUM(CASE WHEN last_donation_date IS NULL OR last_donation_date < DATE_SUB(NOW(), INTERVAL 1 YEAR) THEN 1 ELSE 0 END) AS inactive
    FROM donor";
$act_result = $conn->query($act_sql);
$act_row = $act_result->fetch_assoc();
$activity_data = array((int)$act_row['active'], (int)$act_row['inactive']);

// Donations by month for histogram
$hist_sql = "SELECT 
    DATE_FORMAT(donation_date, '%Y-%m') as month,
    COUNT(*) as donations
    FROM donation_record 
    WHERE donation_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(donation_date, '%Y-%m')
    ORDER BY month";
$hist_result = $conn->query($hist_sql);
$hist_rows = $hist_result ? $hist_result->fetch_all(MYSQLI_ASSOC) : array();
$hist_labels = array();
$hist_data = array();
if ($hist_rows) {
    foreach ($hist_rows as $row) {
        $hist_labels[] = $row['month'];
        $hist_data[] = (int) $row['donations'];
    }
}

// Top donors by donation count
$top_donors_sql = "SELECT 
    d.first_name, d.last_name, d.blood_type,
    COUNT(dr.donation_id) as donation_count
    FROM donor d
    LEFT JOIN donation_record dr ON d.donor_id = dr.donor_id
    GROUP BY d.donor_id
    ORDER BY donation_count DESC
    LIMIT 5";
$top_donors_result = $conn->query($top_donors_sql);
$top_donors = $top_donors_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Management - Blood Donation DMS</title>
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
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .dashboard-header h1 {
            margin: 0;
        }
        
        .donors-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            text-align: center;
            border-left: 5px solid #E21C3D;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.12);
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #E21C3D;
            margin: 0;
        }
        
        .stat-label {
            font-size: 1em;
            color: #666;
            margin: 10px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        .blood-type {
            font-weight: bold;
            color: #E21C3D;
        }
        
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .chart-card h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .reports-container {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .reports-container h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
        }
        
        .top-donors-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .top-donors-table th, .top-donors-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .top-donors-table th {
            background-color: #f8f8f8;
            font-weight: 600;
            color: #555;
        }
        
        .top-donors-table tr:hover {
            background-color: #f9f9f9;
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
        
        .section-title {
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
            margin-top: 40px;
        }
        
        @media (max-width: 768px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
            
            .donors-stats {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
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
                        <a href="donors.php" class="nav-item active">
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
                        <a href="insights.php" class="nav-item">
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
                    <span style="color: #333; font-size: 18px; font-weight: 500;">Donors</span>
                </div>
        </div>

            <div class="content-area">
    <main class="container">
            <!-- Statistics Section (admin) -->
            <div class="dashboard-header">
                <h1>Donor Overview</h1>
                <div>
                    <button class="btn btn-primary">Export Report</button>
                </div>
            </div>
            
            <div class="donors-stats">
                <div class="stat-card">
                    <p class="stat-number"><?php echo $stats['total_donors']; ?></p>
                    <p class="stat-label">Total Donors</p>
                </div>
                <div class="stat-card">
                    <p class="stat-number"><?php echo $stats['active_donors']; ?></p>
                    <p class="stat-label">Active Donors</p>
                </div>
                <div class="stat-card">
                    <p class="stat-number"><?php echo $stats['o_negative']; ?></p>
                    <p class="stat-label">O- Donors</p>
                </div>
                <div class="stat-card">
                    <p class="stat-number"><?php echo $stats['o_positive']; ?></p>
                    <p class="stat-label">O+ Donors</p>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-container">
                <div class="chart-card">
                    <h2>Blood Type Distribution</h2>
                    <div class="chart-container">
                        <canvas id="bloodTypeChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h2>Donation Activity (Last 12 Months)</h2>
                    <div class="chart-container">
                        <canvas id="donationActivityChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Reports Section -->
            <div class="reports-container">
                <h2>Top Donors Report</h2>
                <table class="top-donors-table">
                    <thead>
                        <tr>
                            <th>Donor Name</th>
                            <th>Blood Type</th>
                            <th>Total Donations</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($top_donors)): ?>
                            <tr>
                                <td colspan="3" style="text-align: center;">No donor data available</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($top_donors as $donor): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($donor['first_name'] . ' ' . $donor['last_name']); ?></td>
                                <td><span class="blood-type"><?php echo $donor['blood_type']; ?></span></td>
                                <td><?php echo $donor['donation_count']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Success/Error Messages -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <h2 class="section-title">Current Donors</h2>
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Blood Type</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Last Donation</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($donors)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #666;">
                                    <p>No donors found in database. Add your first donor below!</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($donors as $donor): ?>
                            <tr>
                                <td><?php echo $donor['donor_id']; ?></td>
                                <td><?php echo htmlspecialchars($donor['first_name'] . ' ' . $donor['last_name']); ?></td>
                                <td><span class="blood-type"><?php echo $donor['blood_type']; ?></span></td>
                                <td><?php echo htmlspecialchars($donor['phone_number']); ?></td>
                                <td><?php echo htmlspecialchars($donor['email']); ?></td>
                                <td><?php echo $donor['last_donation_date'] ? $donor['last_donation_date'] : 'Never'; ?></td>
                                <td>
                                    <?php 
                                    $last_donation = $donor['last_donation_date'];
                                    if ($last_donation && strtotime($last_donation) >= strtotime('-1 year')) {
                                        echo '<span style="background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; font-size: 0.8em;">Active</span>';
                                    } else {
                                        echo '<span style="background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 4px; font-size: 0.8em;">Inactive</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Add New Donor Form -->
            <h2 class="section-title">Add New Donor</h2>
            <section class="form-container">
                <form action="donors.php" method="POST">
                    <div class="form-group">
                        <label for="firstName">First Name *</label>
                        <input type="text" id="firstName" name="firstName" required>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name *</label>
                        <input type="text" id="lastName" name="lastName" required>
                    </div>
                    <div class="form-group">
                        <label for="dateOfBirth">Date of Birth *</label>
                        <input type="date" id="dateOfBirth" name="dateOfBirth" required>
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender *</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="bloodType">Blood Type *</label>
                        <select id="bloodType" name="bloodType" required>
                            <option value="">Select Blood Type</option>
                            <option value="O-">O-</option>
                            <option value="O+">O+</option>
                            <option value="A-">A-</option>
                            <option value="A+">A+</option>
                            <option value="B-">B-</option>
                            <option value="B+">B+</option>
                            <option value="AB-">AB-</option>
                            <option value="AB+">AB+</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="phoneNumber">Phone Number</label>
                        <input type="text" id="phoneNumber" name="phoneNumber">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address">
                    </div>
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city">
                    </div>
                    <div class="form-group">
                        <label for="medicalConditions">Medical Conditions</label>
                        <textarea id="medicalConditions" name="medicalConditions" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="medications">Medications</label>
                        <textarea id="medications" name="medications" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="lastDonationDate">Last Donation Date</label>
                        <input type="date" id="lastDonationDate" name="lastDonationDate">
                    </div>
                    <div class="form-group">
                        <label for="registrationDate">Registration Date *</label>
                        <input type="date" id="registrationDate" name="registrationDate" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="maritalStatus">Marital Status</label>
                        <select id="maritalStatus" name="maritalStatus">
                            <option value="">Select Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Divorced">Divorced</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="weight">Weight (kg)</label>
                        <input type="number" id="weight" name="weight" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="height">Height (cm)</label>
                        <input type="number" id="height" name="height" step="0.01">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Add Donor</button>
                        <button type="reset" class="btn btn-secondary">Reset</button>
                    </div>
                </form>
            </section>

    </main>

    <footer class="main-footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Blood Donation Database Management System. All rights reserved.</p>
            <p>Powered by Group G</p>
        </div>
    </footer>
    
    <script>
        // Blood Type Distribution Chart
        const bloodTypeCtx = document.getElementById('bloodTypeChart').getContext('2d');
        const bloodTypeChart = new Chart(bloodTypeCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($blood_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($blood_data); ?>,
                    backgroundColor: [
                        '#E21C3D',
                        '#FF6B6B',
                        '#4ECDC4',
                        '#45B7D1',
                        '#96CEB4',
                        '#FFEAA7',
                        '#DDA0DD',
                        '#98D8C8'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.raw;
                            }
                        }
                    }
                }
            }
        });

        // Donation Activity Chart
        const donationActivityCtx = document.getElementById('donationActivityChart').getContext('2d');
        const donationActivityChart = new Chart(donationActivityCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($hist_labels); ?>,
                datasets: [{
                    label: 'Donations',
                    data: <?php echo json_encode($hist_data); ?>,
                    backgroundColor: '#E21C3D',
                    borderColor: '#C01833',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        title: {
                            display: true,
                            text: 'Number of Donations'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Donations: ' + context.raw;
                            }
                        }
                    }
                }
            }
        });
    </script>
            </div> <!-- End content-area -->
        </div> <!-- End main-content -->
    </div> <!-- End dashboard-container -->
</body>
</html>
<?php
$conn->close();
?>