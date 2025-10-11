<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Transfusions - Blood Donation DMS</title>
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
                    <li><a href="sessions.php">Sessions</a></li>
                    <li><a href="testing.php">Testing</a></li>
                    <li><a href="transfusions.php" class="active">Transfusions</a></li>
                    <li><a href="notifications.php">Notifications</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="page-title">
        <div class="container">
            <h1>Blood Transfusions</h1>
        </div>
    </section>

    <main class="container">
        <section class="mb-30">
            <h2>All Transfusion Records</h2>
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Transfusion ID</th>
                            <th>Request ID</th>
                            <th>Inventory ID</th>
                            <th>Transfusion Date</th>
                            <th>Quantity (ml)</th>
                            <th>Hospital</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Example Transfusion Data (replace with PHP dynamic data) -->
                        <tr>
                            <td>10001</td>
                            <td>4002</td>
                            <td>6003</td>
                            <td>2024-02-26 10:00</td>
                            <td>450</td>
                            <td>City General</td>
                            <td>Completed</td>
                            <td><a href="#">View</a></td>
                        </tr>
                        <tr>
                            <td>10002</td>
                            <td>4003</td>
                            <td>6001</td>
                            <td>2024-03-06 14:30</td>
                            <td>900</td>
                            <td>St. Jude's Hospital</td>
                            <td>Completed</td>
                            <td><a href="#">View</a></td>