<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Requests - Blood Donation DMS</title>
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
                    <li><a href="requests.php" class="active">Requests</a></li>
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
            <h1>Blood Requests</h1>
        </div>
    </section>

    <main class="container">
        <section class="mb-30">
            <h2>All Blood Requests</h2>
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Recipient ID</th>
                            <th>Request Date</th>
                            <th>Blood Type</th>
                            <th>Quantity (ml)</th>
                            <th>Urgency</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Example Request Data (replace with PHP dynamic data) -->
                        <tr>
                            <td>4001</td>
                            <td>2001</td>
                            <td>2024-03-01 09:00</td>
                            <td>O+</td>
                            <td>900</td>
                            <td>High</td>
                            <td>Pending</td>
                            <td><a href="#">View</a> | <a href="#">Edit</a> | <a href="#">Approve</a></td>
                        </tr>
                        <tr>
                            <td>4002</td>
                            <td>2002</td>
                            <td>2024-02-25 11:30</td>
                            <td>AB-</td>
                            <td>450</td>
                            <td>Medium</td>
                            <td>Fulfilled</td>
                            <td><a href="#">View</a></td>
                        </tr>
                        <tr>
                            <td>4003</td>
                            <td>2003</td>
                            <td>2024-03-05 16:00</td>
                            <td>A-</td>
                            <td>1350</td>
                            <td>Critical</td>
                            <td>Approved</td>
                            <td><a href="#">View</a> | <a href="#">Edit</a> | <a href="#">Fulfill</a></td>
                        </tr>
                        <!-- More rows here -->
                    </tbody>
                </table>
            </div>
        </section>

        <section class="form-container">
            <h2>Create New Blood Request</h2>
            <form action="requests.php" method="POST">
                <div class="form-group">
                    <label for="recipientId">Recipient ID</label>
                    <input type="number" id="recipientId" name="recipientId" required>
                </div>
                <div class="form-group">
                    <label for="requestDate">Request Date & Time</label>
                    <input type="datetime-local" id="requestDate" name="requestDate" required>
                </div>
                <div class="form-group">
                    <label for="bloodType">Required Blood Type</label>
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
                    <label for="quantityMl">Quantity (ml)</label>
                    <input type="number" id="quantityMl" name="quantityMl" required>
                </div>
                <div class="form-group">
                    <label for="urgencyLevel">Urgency Level</label>
                    <select id="urgencyLevel" name="urgencyLevel">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="hospitalName">Hospital Name</label>
                    <input type="text" id="hospitalName" name="hospitalName">
                </div>
                <div class="form-group">
                    <label for="doctorName">Requesting Doctor's Name</label>
                    <input type="text" id="doctorName" name="doctorName">
                </div>
                <div class="form-group">
                    <label for="diagnosis">Diagnosis</label>
                    <textarea id="diagnosis" name="diagnosis" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="notes">Internal Notes</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>
                <!-- Status, Approved By, Approved Date, Fulfillment Date