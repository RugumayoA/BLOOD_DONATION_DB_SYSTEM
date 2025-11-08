<?php
session_start();
require_once 'config.php';

// Redirect to login if not authenticated as staff
if (!isset($_SESSION['staff_id'])) {
    header('Location: login.php');
    exit;
}

$flash_ok = null;
$flash_err = null;

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Start transaction
    $conn->autocommit(FALSE);
    
    try {
        // Delete from inventory first (foreign key dependency)
        $stmt = $conn->prepare("DELETE FROM blood_inventory WHERE donation_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Delete from testing_record
        $stmt = $conn->prepare("DELETE FROM testing_record WHERE donation_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Delete the donation record
        $stmt = $conn->prepare("DELETE FROM donation_record WHERE donation_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        $conn->commit();
        header("Location: donations.php?ok=deleted");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $flash_err = "Delete failed: " . $e->getMessage();
    }
    
    $conn->autocommit(TRUE);
}

// Get record for editing
$edit_row = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM donation_record WHERE donation_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_row = $result->fetch_assoc();
    $stmt->close();
}

// Fetch all donors for dropdown
$donorsSql = "SELECT donor_id, first_name, last_name, blood_type FROM donor ORDER BY first_name, last_name";
$donorsResult = $conn->query($donorsSql);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = isset($_POST['mode']) ? $_POST['mode'] : 'create';
    $donationId = isset($_POST['donationId']) ? $_POST['donationId'] : 0;
    $donorId = $_POST['donorId'];
    $sessionId = !empty($_POST['sessionId']) ? $_POST['sessionId'] : null;
    $donationDate = $_POST['donationDate'];
    $bloodVolumeMl = $_POST['bloodVolumeMl'];
    $hemoglobinLevel = !empty($_POST['hemoglobinLevel']) ? $_POST['hemoglobinLevel'] : null;
    $bloodPressure = !empty($_POST['bloodPressure']) ? $_POST['bloodPressure'] : null;
    $pulseRate = !empty($_POST['pulseRate']) ? $_POST['pulseRate'] : null;
    $temperature = !empty($_POST['temperature']) ? $_POST['temperature'] : null;
    $staffId = !empty($_POST['staffId']) ? $_POST['staffId'] : null;
    $bagCode = $_POST['bagCode'];
    $notes = !empty($_POST['notes']) ? $_POST['notes'] : null;

    // Begin transaction to ensure both donation and inventory are updated together
    $conn->autocommit(FALSE);
    
    try {
        if ($mode === 'update' && $donationId > 0) {
            // Update existing donation record
            $stmt = $conn->prepare("UPDATE donation_record SET donor_id = ?, session_id = ?, donation_date = ?, blood_volume_ml = ?, hemoglobin_level = ?, blood_pressure = ?, pulse_rate = ?, temperature = ?, staff_id = ?, bag_code = ?, notes = ? WHERE donation_id = ?");
            $stmt->bind_param("iisssssssssi", $donorId, $sessionId, $donationDate, $bloodVolumeMl, $hemoglobinLevel, $bloodPressure, $pulseRate, $temperature, $staffId, $bagCode, $notes, $donationId);
            $stmt->execute();
            $stmt->close();
            
            // Update inventory if it exists
            $stmtCheck = $conn->prepare("SELECT inventory_id FROM blood_inventory WHERE donation_id = ?");
            $stmtCheck->bind_param("i", $donationId);
            $stmtCheck->execute();
            $result = $stmtCheck->get_result();
            if ($result->fetch_assoc()) {
                // Update existing inventory
                $collectionDate = date('Y-m-d', strtotime($donationDate));
                $expiryDate = date('Y-m-d', strtotime($collectionDate . ' + 42 days'));
                
                $stmtInventory = $conn->prepare("UPDATE blood_inventory SET quantity_ml = ?, collection_date = ?, expiry_date = ? WHERE donation_id = ?");
                $stmtInventory->bind_param("issi", $bloodVolumeMl, $collectionDate, $expiryDate, $donationId);
                $stmtInventory->execute();
                $stmtInventory->close();
            }
            $stmtCheck->close();
            
            $conn->commit();
            header("Location: donations.php?ok=updated");
            exit;
        } else {
            // Insert new donation record
            $stmt = $conn->prepare("INSERT INTO donation_record (donor_id, session_id, donation_date, blood_volume_ml, hemoglobin_level, blood_pressure, pulse_rate, temperature, staff_id, bag_code, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssssssss", $donorId, $sessionId, $donationDate, $bloodVolumeMl, $hemoglobinLevel, $bloodPressure, $pulseRate, $temperature, $staffId, $bagCode, $notes);
            $stmt->execute();
            
            // Get the donation ID that was just created
            $newDonationId = $conn->insert_id;
            $stmt->close();
            
            // Get the donor's blood type
            $stmtDonor = $conn->prepare("SELECT blood_type FROM donor WHERE donor_id = ?");
            $stmtDonor->bind_param("i", $donorId);
            $stmtDonor->execute();
            $result = $stmtDonor->get_result();
            $donor = $result->fetch_assoc();
            $stmtDonor->close();
            
            if ($donor) {
                // Calculate expiry date (42 days from collection date - standard for whole blood)
                $collectionDate = date('Y-m-d', strtotime($donationDate));
                $expiryDate = date('Y-m-d', strtotime($collectionDate . ' + 42 days'));
                
                // Automatically add to inventory with "Quarantined" status until tested
                $stmtInventory = $conn->prepare("INSERT INTO blood_inventory (donation_id, blood_type, quantity_ml, collection_date, expiry_date, status, storage_location) VALUES (?, ?, ?, ?, ?, 'Quarantined', 'Storage Area 1')");
                $stmtInventory->bind_param("isiss", $newDonationId, $donor['blood_type'], $bloodVolumeMl, $collectionDate, $expiryDate);
                $stmtInventory->execute();
                $stmtInventory->close();
                
                // Commit the transaction
                $conn->commit();
                header("Location: donations.php?ok=created");
                exit;
            } else {
                // Rollback if donor not found
                $conn->rollback();
                $flash_err = "Error: Donor not found!";
            }
        }
    } catch (Exception $e) {
        // Rollback on any error
        $conn->rollback();
        $flash_err = "Error: " . $e->getMessage();
    }
    
    $conn->autocommit(TRUE);
}

// Handle flash messages
if (isset($_GET['ok'])) {
    if ($_GET['ok'] === 'created') $flash_ok = "Donation record added successfully and inventory updated automatically!";
    if ($_GET['ok'] === 'updated') $flash_ok = "Donation record updated successfully!";
    if ($_GET['ok'] === 'deleted') $flash_ok = "Donation record deleted successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Records - Blood Donation DMS</title>
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
        
    .alert{padding:10px;border-radius:6px;margin:10px 0;position:relative;overflow:hidden}
    .alert.success{background:#e6ffed;border:1px solid #a7f3d0;color:#065f46}
    .alert.danger{background:#ffe6e6;border:1px solid #ffb3b3;color:#7f1d1d}
    .bar{position:absolute;left:0;bottom:0;height:4px;background:#10b981;animation:fill 1.2s linear forwards}
    .alert.danger .bar{background:#ef4444}
    @keyframes fill{from{width:0}to{width:100%}}
    .icon-btn{background:none;border:none;color:#b91c1c;cursor:pointer;font-size:18px}
    .icon-edit{color:#1f2937;text-decoration:none}
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
                        <a href="donations.php" class="nav-item active">
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
                    <span style="color: #333; font-size: 18px; font-weight: 500;">Donations</span>
                </div>
        </div>

            <div class="content-area">
    <main class="container">
        <?php if ($flash_ok): ?>
            <div class="alert success"><?php echo htmlspecialchars($flash_ok); ?><div class="bar"></div></div>
        <?php endif; ?>
        <?php if ($flash_err): ?>
            <div class="alert danger"><?php echo htmlspecialchars($flash_err); ?><div class="bar"></div></div>
        <?php endif; ?>

        <section class="mb-30">
            <h2>All Donations</h2>
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Donation ID</th>
                            <th>Donor ID</th>
                            <th>Session ID</th>
                            <th>Date</th>
                            <th>Volume (ml)</th>
                            <th>Bag Code</th>
                            <th>Staff ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM donation_record ORDER BY donation_id DESC");
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['donation_id'] . "</td>";
                            echo "<td>" . $row['donor_id'] . "</td>";
                            echo "<td>" . $row['session_id'] . "</td>";
                            echo "<td>" . $row['donation_date'] . "</td>";
                            echo "<td>" . $row['blood_volume_ml'] . "</td>";
                            echo "<td>" . $row['bag_code'] . "</td>";
                            echo "<td>" . $row['staff_id'] . "</td>";
                            echo "<td>";
                            echo "<a class='icon-edit' href='donations.php?action=edit&id=" . $row['donation_id'] . "'>Edit</a>";
                            echo " | ";
                            echo "<a href='donations.php?action=delete&id=" . $row['donation_id'] . "' onclick=\"return confirm('Delete this donation record and its inventory?');\">🗑</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="form-container">
            <h2><?php echo $edit_row ? 'Edit Donation Record' : 'Add New Donation Record'; ?></h2>
            <form action="donations.php" method="POST">
                <input type="hidden" name="mode" value="<?php echo $edit_row ? 'update' : 'create'; ?>" />
                <?php if ($edit_row): ?>
                    <input type="hidden" name="donationId" value="<?php echo (int)$edit_row['donation_id']; ?>" />
                <?php endif; ?>

                <div class="form-group">
                    <label for="donorId">Select Donor</label>
                    <select id="donorId" name="donorId" required>
                        <option value="">Select Donor</option>
                        <?php
                        if ($donorsResult && $donorsResult->num_rows > 0) {
                            while($donor = $donorsResult->fetch_assoc()) {
                                $bloodTypeDisplay = $donor["blood_type"] ? $donor["blood_type"] : "Unknown";
                                $selected = ($edit_row && $edit_row['donor_id'] == $donor['donor_id']) ? 'selected' : '';
                                echo "<option value='" . $donor["donor_id"] . "' data-bloodtype='" . $donor["blood_type"] . "' $selected>";
                                echo $donor["first_name"] . " " . $donor["last_name"] . " - " . $bloodTypeDisplay . " (ID: " . $donor["donor_id"] . ")";
                                echo "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sessionId">Session ID (Optional)</label>
                    <input type="number" id="sessionId" name="sessionId"
                           value="<?php echo $edit_row && $edit_row['session_id'] ? (int)$edit_row['session_id'] : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="donationDate">Donation Date & Time</label>
                    <input type="datetime-local" id="donationDate" name="donationDate" required
                           value="<?php
                                if ($edit_row && $edit_row['donation_date']) {
                                    $ts = substr($edit_row['donation_date'], 0, 16);
                                    echo htmlspecialchars(str_replace(' ', 'T', $ts));
                                }
                           ?>">
                </div>
                <div class="form-group">
                    <label for="bloodVolumeMl">Blood Volume (ml)</label>
                    <input type="number" id="bloodVolumeMl" name="bloodVolumeMl" required
                           value="<?php echo $edit_row ? (int)$edit_row['blood_volume_ml'] : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="hemoglobinLevel">Hemoglobin Level (g/dL)</label>
                    <input type="number" id="hemoglobinLevel" name="hemoglobinLevel" step="0.1"
                           value="<?php echo $edit_row && $edit_row['hemoglobin_level'] ? htmlspecialchars($edit_row['hemoglobin_level']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="bloodPressure">Blood Pressure (e.g., 120/80)</label>
                    <input type="text" id="bloodPressure" name="bloodPressure"
                           value="<?php echo $edit_row && $edit_row['blood_pressure'] ? htmlspecialchars($edit_row['blood_pressure']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="pulseRate">Pulse Rate (bpm)</label>
                    <input type="number" id="pulseRate" name="pulseRate"
                           value="<?php echo $edit_row && $edit_row['pulse_rate'] ? (int)$edit_row['pulse_rate'] : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="temperature">Temperature (°C)</label>
                    <input type="number" id="temperature" name="temperature" step="0.1"
                           value="<?php echo $edit_row && $edit_row['temperature'] ? htmlspecialchars($edit_row['temperature']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="staffId">Staff ID (Who performed donation)</label>
                    <input type="number" id="staffId" name="staffId"
                           value="<?php echo $edit_row && $edit_row['staff_id'] ? (int)$edit_row['staff_id'] : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="bagCode">Bag Code</label>
                    <input type="text" id="bagCode" name="bagCode" required
                           value="<?php echo $edit_row && $edit_row['bag_code'] ? htmlspecialchars($edit_row['bag_code']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3"><?php echo $edit_row && $edit_row['notes'] ? htmlspecialchars($edit_row['notes']) : ''; ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?php echo $edit_row ? 'Update Donation' : 'Add Donation'; ?></button>
                    <a class="btn btn-secondary" href="donations.php">Cancel</a>
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
            </div> <!-- End content-area -->
        </div> <!-- End main-content -->
    </div> <!-- End dashboard-container -->
</body>
</html>
<?php
$conn->close();
?>