<?php
// inventory.php
require_once 'config.php';

// Calculate inventory statistics
$totalUnits = 0;
$expiringUnits = 0;
$criticalTypes = array();

try {
    // Total available units
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM blood_inventory WHERE status = 'Available'");
    $result = $stmt->fetch();
    $totalUnits = $result['total'];

    // Units expiring in 30 days
    $stmt = $pdo->query("SELECT COUNT(*) as expiring FROM blood_inventory WHERE status = 'Available' AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
    $result = $stmt->fetch();
    $expiringUnits = $result['expiring'];

    // Critical blood types (less than 10 units)
    $stmt = $pdo->query("SELECT CONCAT(blood_type, rh_factor) as type_full, COUNT(*) as count FROM blood_inventory WHERE status = 'Available' GROUP BY blood_type, rh_factor HAVING count < 10");
    while ($row = $stmt->fetch()) {
        $criticalTypes[] = $row['type_full'];
    }
} catch (PDOException $e) {
    // Handle error silently or log it
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Inventory - Blood Donation DMS</title>
    <link rel="stylesheet" href="style.css">
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
                    <li><a href="inventory.php" class="active">Inventory</a></li>
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
            <h1>Blood Inventory</h1>
        </div>
    </section>

    <main class="container">
        <section class="mb-30">
            <h2>Current Blood Stock Overview</h2>
            <div class="inventory-overview">
                <div class="info-card">
                    <h3>Total Available Units</h3>
                    <p class="large-number"><?php echo $totalUnits; ?></p>
                </div>
                <div class="info-card">
                    <h3>Units Expiring Soon (30 days)</h3>
                    <p class="large-number warning-text"><?php echo $expiringUnits; ?></p>
                </div>
                <div class="info-card">
                    <h3>Critical Blood Types</h3>
                    <p class="critical-types">
                        <?php 
                        if (count($criticalTypes) > 0) {
                            echo implode(', ', $criticalTypes);
                        } else {
                            echo 'None';
                        }
                        ?>
                    </p>
                </div>
            </div>
        </section>

        <section class="mb-30">
            <h2>Detailed Inventory List</h2>
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Inventory ID</th>
                            <th>Donation ID</th>
                            <th>Blood Type</th>
                            <th>Quantity (ml)</th>
                            <th>Collection Date</th>
                            <th>Expiry Date</th>
                            <th>Storage Location</th>
                            <th>Status</th>
                            <th>Test Results</th>
                            <th>Processing Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT * FROM blood_inventory ORDER BY inventory_id DESC");
                            while ($row = $stmt->fetch()) {
                                $bloodTypeFull = $row['blood_type'] . $row['rh_factor'];
                                echo "<tr>";
                                echo "<td>" . $row['inventory_id'] . "</td>";
                                echo "<td>" . $row['donation_id'] . "</td>";
                                echo "<td>" . $bloodTypeFull . "</td>";
                                echo "<td>" . $row['volume_ml'] . "</td>";
                                echo "<td>" . $row['collection_date'] . "</td>";
                                echo "<td>" . $row['expiry_date'] . "</td>";
                                echo "<td>" . ($row['storage_location'] ? $row['storage_location'] : 'N/A') . "</td>";
                                echo "<td>" . $row['status'] . "</td>";
                                echo "<td>" . ($row['test_results'] ? $row['test_results'] : 'Pending') . "</td>";
                                echo "<td>" . ($row['processing_date'] ? $row['processing_date'] : 'N/A') . "</td>";
                                echo "</tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='10'>Error loading inventory: " . $e->getMessage() . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
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