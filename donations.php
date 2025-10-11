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
                        <!-- Example Donation Data (replace with PHP dynamic data) -->
                        <tr>
                            <td>3001</td>
                            <td>1001</td>
                            <td>501</td>
                            <td>2023-10-15 10:30</td>
                            <td>450</td>
                            <td>BAG-XYZ-001</td>
                            <td>7001</td>
                            <td><a href="#">Edit</a> | <a href="#">Delete</a></td>
                        </tr>
                        <tr>
                            <td>3002</td>
                            <td>1002</td>
                            <td>502</td>
                            <td>2024-01-20 14:00</td>
                            <td>400</td>
                            <td>BAG-ABC-002</td>
                            <td>7002</td>
                            <td><a href="#">Edit</a> | <a href="#">Delete</a></td>
                        </tr>
                        <tr>
                            <td>3003</td>
                            <td>1003</td>
                            <td>501</td>
                            <td>2022-05-10 11:00</td>
                            <td>500</td>
                            <td>BAG-DEF-003</td>
                            <td>7001</td>
                            <td><a href="#">Edit</a> | <a href="#">Delete</a></td>
                        </tr>
                        <!-- More rows here -->
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