<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Sessions - Blood Donation DMS</title>
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
                    <li><a href="inventory.php">Inventory</a></li>
                    <li><a href="staff.php">Staff</a></li>
                    <li><a href="events.php">Events</a></li>
                    <li><a href="sessions.php" class="active">Sessions</a></li>
                    <li><a href="testing.php">Testing</a></li>
                    <li><a href="transfusions.php">Transfusions</a></li>
                    <li><a href="notifications.php">Notifications</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="page-title">
        <div class="container">
            <h1>Donation Sessions</h1>
        </div>
    </section>

    <main class="container">
        <section class="mb-30">
            <h2>All Donation Sessions</h2>
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Session ID</th>
                            <th>Date</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Location</th>
                            <th>Staff Lead</th>
                            <th>Max Donors</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Example Session Data (replace with PHP dynamic data) -->
                        <tr>
                            <td>501</td>
                            <td>2024-04-10</td>
                            <td>09:00</td>
                            <td>12:00</td>
                            <td>Town Hall - Rm 1</td>
                            <td>7001 (Grace T.)</td>
                            <td>30</td>
                            <td>Scheduled</td>
                            <td><a href="#">Edit</a> | <a href="#">Cancel</a></td>
                        </tr>
                        <tr>
                            <td>502</td>
                            <td>2024-04-10</td>
                            <td>13:00</td>
                            <td>16:00</td>
                            <td>Town Hall - Rm 2</td>
                            <td>7002 (Henry W.)</td>
                            <td>25</td>
                            <td>Scheduled</td>
                            <td><a href="#">Edit</a> | <a href="#">Cancel</a></td>
                        </tr>
                        <tr>
                            <td>503</td>
                            <td>2024-03-20</td>
                            <td>10:00</td>
                            <td>13:00</td>
                            <td>University Gym - East</td>
                            <td>7001 (Grace T.)</td>
                            <td>40</td>
                            <td>Completed</td>
                            <td><a href="#">View Details</a></td>
                        </tr>
                        <!-- More rows here -->
                    </tbody>
                </table>
            </div>
        </section>

        <section class="form-container">
            <h2>Create New Donation Session</h2>
            <form action="sessions.php" method="POST">
                <div class="form-group">
                    <label for="sessionDate">Session Date</label>