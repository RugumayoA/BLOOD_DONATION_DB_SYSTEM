<?php
// donations.php
require_once 'config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $stmt = $pdo->prepare("INSERT INTO donation_record (donor_id, session_id, donation_date, blood_volume_ml, hemoglobin_level, blood_pressure, pulse_rate, temperature, staff_id, bag_code, notes) VALUES (:donor_id, :session_id, :donation_date, :blood_volume_ml, :hemoglobin_level, :blood_pressure, :pulse_rate, :temperature, :staff_id, :bag_code, :notes)");
        $stmt->execute(array(
            ':donor_id' => $donorId,
            ':session_id' => $sessionId,
            ':donation_date' => $donationDate,
            ':blood_volume_ml' => $bloodVolumeMl,
            ':hemoglobin_level' => $hemoglobinLevel,
            ':blood_pressure' => $bloodPressure,
            ':pulse_rate' => $pulseRate,
            ':temperature' => $temperature,
            ':staff_id' => $staffId,
            ':bag_code' => $bagCode,
            ':notes' => $notes
        ));
        echo '<script>alert("Donation record added successfully!");</script>';
    } catch (PDOException $e) {
        echo '<script>alert("Error adding record: ' . addslashes($e->getMessage()) . '");</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Records - Blood Donation DMS</title>
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
                            echo "<td><a href='#'>Edit</a> | <a href='#'>Delete</a></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="form-container">
            <h2>Add New Donation Record</h2>
            <form action="donations.php" method="POST">
                <div class="form-group">
                    <label for="donorId">Donor ID</label>
                    <input type="number" id="donorId" name="donorId" required>
                </div>
                <div class="form-group">
                    <label for="sessionId">Session ID (Optional)</label>
                    <input type="number" id="sessionId" name="sessionId">
                </div>
                <div class="form-group">
                    <label for="donationDate">Donation Date & Time</label>
                    <input type="datetime-local" id="donationDate" name="donationDate" required>
                </div>
                <div class="form-group">
                    <label for="bloodVolumeMl">Blood Volume (ml)</label>
                    <input type="number" id="bloodVolumeMl" name="bloodVolumeMl" required>
                </div>
                <div class="form-group">
                    <label for="hemoglobinLevel">Hemoglobin Level (g/dL)</label>
                    <input type="number" id="hemoglobinLevel" name="hemoglobinLevel" step="0.1">
                </div>
                <div class="form-group">
                    <label for="bloodPressure">Blood Pressure (e.g., 120/80)</label>
                    <input type="text" id="bloodPressure" name="bloodPressure">
                </div>
                <div class="form-group">
                    <label for="pulseRate">Pulse Rate (bpm)</label>
                    <input type="number" id="pulseRate" name="pulseRate">
                </div>
                <div class="form-group">
                    <label for="temperature">Temperature (°C)</label>
                    <input type="number" id="temperature" name="temperature" step="0.1">
                </div>
                <div class="form-group">
                    <label for="staffId">Staff ID (Who performed donation)</label>
                    <input type="number" id="staffId" name="staffId">
                </div>
                <div class="form-group">
                    <label for="bagCode">Bag Code</label>
                    <input type="text" id="bagCode" name="bagCode" required>
                </div>
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Donation</button>
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