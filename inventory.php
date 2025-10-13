<?php
// inventory.php
require_once 'config.php';
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
                                // Blood type is stored as a single value (e.g., 'A+', 'O-') in the database
                                $bloodTypeFull = $row['blood_type'];
                                echo "<tr>";
                                echo "<td>" . $row['inventory_id'] . "</td>";
                                echo "<td>" . ($row['donation_id'] ? $row['donation_id'] : 'N/A') . "</td>";
                                echo "<td>" . $bloodTypeFull . "</td>";
                                echo "<td>" . $row['quantity_ml'] . "</td>";
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