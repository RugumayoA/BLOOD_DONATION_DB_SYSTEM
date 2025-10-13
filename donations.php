<?php
// donations.php
require_once 'config.php';

$flash_ok = null;
$flash_err = null;

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Delete from inventory first (foreign key dependency)
        $stmt = $pdo->prepare("DELETE FROM blood_inventory WHERE donation_id = ?");
        $stmt->execute(array($id));
        
        // Delete from testing_record
        $stmt = $pdo->prepare("DELETE FROM testing_record WHERE donation_id = ?");
        $stmt->execute(array($id));
        
        // Delete the donation record
        $stmt = $pdo->prepare("DELETE FROM donation_record WHERE donation_id = ?");
        $stmt->execute(array($id));
        
        $pdo->commit();
        header("Location: donations.php?ok=deleted");
        exit;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $flash_err = "Delete failed: " . $e->getMessage();
    }
}

// Get record for editing
$edit_row = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM donation_record WHERE donation_id = ?");
        $stmt->execute(array($id));
        $edit_row = $stmt->fetch();
    } catch (PDOException $e) {
        $flash_err = "Error fetching record: " . $e->getMessage();
    }
}

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

    try {
        // Begin transaction to ensure both donation and inventory are updated together
        $pdo->beginTransaction();
        
        if ($mode === 'update' && $donationId > 0) {
            // Update existing donation record
            $stmt = $pdo->prepare("UPDATE donation_record SET donor_id = ?, session_id = ?, donation_date = ?, blood_volume_ml = ?, hemoglobin_level = ?, blood_pressure = ?, pulse_rate = ?, temperature = ?, staff_id = ?, bag_code = ?, notes = ? WHERE donation_id = ?");
            $stmt->execute(array($donorId, $sessionId, $donationDate, $bloodVolumeMl, $hemoglobinLevel, $bloodPressure, $pulseRate, $temperature, $staffId, $bagCode, $notes, $donationId));
            
            // Update inventory if it exists
            $stmtCheck = $pdo->prepare("SELECT inventory_id FROM blood_inventory WHERE donation_id = ?");
            $stmtCheck->execute(array($donationId));
            if ($stmtCheck->fetch()) {
                // Update existing inventory
                $collectionDate = date('Y-m-d', strtotime($donationDate));
                $expiryDate = date('Y-m-d', strtotime($collectionDate . ' + 42 days'));
                
                $stmtInventory = $pdo->prepare("UPDATE blood_inventory SET quantity_ml = ?, collection_date = ?, expiry_date = ? WHERE donation_id = ?");
                $stmtInventory->execute(array($bloodVolumeMl, $collectionDate, $expiryDate, $donationId));
            }
            
            $pdo->commit();
            header("Location: donations.php?ok=updated");
            exit;
        } else {
            // Insert new donation record
            $stmt = $pdo->prepare("INSERT INTO donation_record (donor_id, session_id, donation_date, blood_volume_ml, hemoglobin_level, blood_pressure, pulse_rate, temperature, staff_id, bag_code, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute(array($donorId, $sessionId, $donationDate, $bloodVolumeMl, $hemoglobinLevel, $bloodPressure, $pulseRate, $temperature, $staffId, $bagCode, $notes));
            
            // Get the donation ID that was just created
            $newDonationId = $pdo->lastInsertId();
            
            // Get the donor's blood type
            $stmtDonor = $pdo->prepare("SELECT blood_type FROM donor WHERE donor_id = ?");
            $stmtDonor->execute(array($donorId));
            $donor = $stmtDonor->fetch();
            
            if ($donor) {
                // Calculate expiry date (42 days from collection date - standard for whole blood)
                $collectionDate = date('Y-m-d', strtotime($donationDate));
                $expiryDate = date('Y-m-d', strtotime($collectionDate . ' + 42 days'));
                
                // Automatically add to inventory with "Quarantined" status until tested
                $stmtInventory = $pdo->prepare("INSERT INTO blood_inventory (donation_id, blood_type, quantity_ml, collection_date, expiry_date, status, storage_location) VALUES (?, ?, ?, ?, ?, 'Quarantined', 'Storage Area 1')");
                $stmtInventory->execute(array($newDonationId, $donor['blood_type'], $bloodVolumeMl, $collectionDate, $expiryDate));
                
                // Commit the transaction
                $pdo->commit();
                header("Location: donations.php?ok=created");
                exit;
            } else {
                // Rollback if donor not found
                $pdo->rollBack();
                $flash_err = "Error: Donor not found!";
            }
        }
    } catch (PDOException $e) {
        // Rollback on any error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $flash_err = "Error: " . $e->getMessage();
    }
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
                    <li><a href="donations.php" class="active">Donations</a></li>
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
            <h1>Donation Records</h1>
        </div>
    </section>

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
                        $stmt = $pdo->query("SELECT * FROM donation_record ORDER BY donation_id DESC");
                        while ($row = $stmt->fetch()) {
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
                    <label for="donorId">Donor ID</label>
                    <input type="number" id="donorId" name="donorId" required
                           value="<?php echo $edit_row ? (int)$edit_row['donor_id'] : ''; ?>">
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
            <p>&copy; <?php echo date("Y"); ?> Blood Donation DMS. All rights reserved.</p>
            <p>Powered by Compassion</p>
        </div>
    </footer>
</body>
</html>