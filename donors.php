<?php
require_once 'config.php';

// Handle form submission - Using old array syntax for PHP 5.3 compatibility
if ($_POST) {
    try {
        $sql = "INSERT INTO donor (first_name, last_name, date_of_birth, gender, blood_type, phone_number, email, address, city, medical_conditions, medications, last_donation_date, registration_date, marital_status, weight, height) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
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
        ));
        
        $success_message = "Donor added successfully!";
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Get all donors from database
$sql = "SELECT * FROM donor ORDER BY last_name, first_name";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$donors = $stmt->fetchAll();

// Get donor statistics
$stats_sql = "SELECT 
    COUNT(*) as total_donors,
    COUNT(CASE WHEN last_donation_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR) THEN 1 END) as active_donors,
    COUNT(CASE WHEN blood_type = 'O-' THEN 1 END) as o_negative,
    COUNT(CASE WHEN blood_type = 'O+' THEN 1 END) as o_positive
    FROM donor";
$stats_stmt = $pdo->prepare($stats_sql);
$stats_stmt->execute();
$stats = $stats_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Management - Blood Donation DMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .donors-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #E21C3D;
            flex: 1;
            min-width: 200px;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #E21C3D;
            margin: 0;
        }
        
        .stat-label {
            font-size: 0.9em;
            color: #666;
            margin: 5px 0 0 0;
        }
        
        .blood-type {
            font-weight: bold;
            color: #E21C3D;
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
                    <li><a href="donors.php" class="active">Donors</a></li>
                    <li><a href="recipients.php">Recipients</a></li>
                    <li><a href="donations.php">Donations</a></li>
                    <li><a href="requests.php">Requests</a></li>
                    <li><a href="inventory.php">Inventory</a></li>
                    <li><a href="staff.php">Staff</a></li>
                    <li><a href="events.php">Events</a></li>
                    <li><a href="sessions.php">Sessions</a></li>
                    <li><a href="testing.php">Testing</a></li>
                    <li><a href="transfusions.php">Transfusions</a></li>
                    <li><a href="notifications.php">Notifications</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="page-title">
        <div class="container">
            <h1>Donor Management</h1>
        </div>
    </section>

    <main class="container">
        <!-- Statistics Section -->
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

        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <section class="mb-30">
            <h2>Current Donors</h2>
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
        </section>

        <!-- Add New Donor Form -->
        <section class="form-container">
            <h2>Add New Donor</h2>
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
            <p>&copy; <?php echo date("Y"); ?> Blood Donation DMS. All rights reserved.</p>
            <p>Powered by Compassion</p>
        </div>
    </footer>
</body>
</html>