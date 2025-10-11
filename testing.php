<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testing Records - Blood Donation DMS</title>
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
                    < li><a href="inventory.php">Inventory</a></li>
                    <li><a href="staff.php">Staff</a></li>
                    <li><a href="events.php">Events</a></li>
                    <li><a href="sessions.php">Sessions</a></li>
                    <li><a href="testing.php" class="active">Testing</a></li>
                    <li><a href="transfusions.php">Transfusions</a></li>
                    <li><a href="notifications.php">Notifications</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="page-title">
        <div class="container">
            <h1>Testing Records</h1>
        </div>
    </section>

    <main class="container">
        <section class="mb-30">
            <h2>All Testing Records</h2>
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Test ID</th>
                            <th>Donation ID</th>
                            <th>Test Date</th>
                            <th>Test Type</th>
                            <th>Result</th>
                            <th>Staff ID</th>
                            <th>Retest Required?</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Example Testing Data (replace with PHP dynamic data) -->
                        <tr>
                            <td>9001</td>
                            <td>3001</td>
                            <td>2023-10-16 11:00</td>
                            <td>HIV</td>
                            <td>Negative</td>
                            <td>7002</td>
                            <td>No</td>
                            <td><a href="#">View</a> | <a href="#">Edit</a></td>
                        </tr>
                        <tr>
                            <td>9002</td>
                            <td>3001</td>
                            <td>2023-10-16 11:00</td>
                            <td>HBsAg</td>
                            <td>Negative</td>
                            <td>7002</td>
                            <td>No</td>
                            <td><a href="#">View</a> | <a href="#">Edit</a></td>
                        </tr>
                        <tr>
                            <td>9003</td>
                            <td>3002</td>
                            <td>2024-01-21 09:30</td>
                            <td>HCV</td>
                            <td>Negative</td>
                            <td>7002</td>
                            <td>No</td>
                            <td><a href="#">View</a> | <a href="#">Edit</a></td>
                        </tr>
                        <tr>
                            <td>9004</td>
                            <td>3003</td>
                            <td>2022-05-11 10:00</td>
                            <td>Syphilis</td>
                            <td>Indeterminate</td>
                            <td>7002</td>
                            <td>Yes</td>
                            <td><a href="#">View</a> | <a href="#">Edit</a></td>
                        </tr>
                        <!-- More rows here -->
                    </tbody>
                </table>
            </div>
        </section>

        <section class="form-container">
            <h2>Add New Testing Record</h2>
            <form action="testing.php" method="POST">
                <div class="form-group">
                    <label for="donationId">Donation ID</label>
                    <input type="number" id="donationId" name="donationId" required>
                </div>
                <div class="form-group">
                    <label for="testDate">Test Date & Time</label>
                    <input type="datetime-local" id="testDate" name="testDate" required>
                </div>
                <div class="form-group">
                    <label for="testType">Test Type</label>
                    <input type="text" id="testType" name="testType" placeholder="e.g., HIV, HBsAg, HCV, Syphilis" required>
                </div>
                <div class="form-group">
                    <label for="testResult">Test Result</label>
                    <select id="testResult" name="testResult" required>
                        <option value="">Select Result</option>
                        <option value="Positive">Positive</option>
                        <option value="Negative">Negative</option>
                        <option value="Indeterminate">Indeterminate</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="staffId">Staff ID (Who performed test)</label>
                    <input type="number" id="staffId" name="staffId">
                </div>
                <div class="form-group">
                    <label for="testNotes">Test Notes</label>
                    <textarea id="testNotes" name="testNotes" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="retestRequired">Retest Required?</label>
                    <select id="retestRequired" name="retestRequired">
                        <option value="No">No</option>
                        <option value="Yes">Yes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="retestDate">Retest Date (if required)</label>
                    <input type="datetime-local" id="retestDate" name="retestDate">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Test Record</button>
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


// testing.php