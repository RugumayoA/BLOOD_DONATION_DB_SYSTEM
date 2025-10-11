<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Events - Blood Donation DMS</title>
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
                    <li><a href="events.php" class="active">Events</a></li>
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
            <h1>Donation Events</h1>
        </div>
    </section>

    <main class="container">
        <section class="mb-30">
            <h2>Upcoming & Past Events</h2>
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event ID</th>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Staff Lead</th>
                            <th>Status</th>
                            <th>Target Blood (ml)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Example Event Data (replace with PHP dynamic data) -->
                        <tr>
                            <td>8001</td>
                            <td>Community Blood Drive</td>
                            <td>2024-04-10</td>
                            <td>09:00 - 16:00</td>
                            <td>Town Hall</td>
                            <td>7001 (Grace T.)</td>
                            <td>Planned</td>
                            <td>5000</td>
                            <td><a href="#">Edit</a> | <a href="#">Cancel</a></td>
                        </tr>
                        <tr>
                            <td>8002</td>
                            <td>University Blood Day</td>
                            <td>2024-03-20</td>
                            <td>10:00 - 17:00</td>
                            <td>University Gym</td>
                            <td>7002 (Henry W.)</td>
                            <td>Completed</td>
                            <td>8000</td>
                            <td><a href="#">View Details</a></td>
                        </tr>
                        <tr>
                            <td>8003</td>
                            <td>Corporate Wellness Drive</td>
                            <td>2024-05-01</td>
                            <td>08:00 - 12:00</td>
                            <td>ABC Corp Lobby</td>
                            <td>7001 (Grace T.)</td>
                            <td>Planned</td>
                            <td>3000</td>
                            <td><a href="#">Edit</a> | <a href="#">Cancel</a></td>
                        </tr>
                        <!-- More rows here -->
                    </tbody>
                </table>
            </div>
        </section>

        <section class="form-container">
            <h2>Create New Donation Event</h2>
            <form action="events.php" method="POST">
                <div class="form-group">
                    <label for="eventName">Event Name</label>
                    <input type="text" id="eventName" name="eventName" required>
                </div>
                <div class="form-group">
                    <label for="eventDate">Event Date</label>
                    <input type="date" id="eventDate" name="eventDate" required>
                </div>
                <div class="form-group">
                    <label for="startTime">Start Time</label>
                    <input type="time" id="startTime" name="startTime">
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location">
                </div>
                <div class="form-group">
                    <label for="staffId">Staff Lead ID</label>
                    <input type="number" id="staffId" name="staffId">
                    <small>e.g., 7001 for Grace Taylor</small>
                </div>
                <div class="form-group">
                    <label for="numberOfParticipants">Number of Expected Participants</label>
                    <input type="number" id="numberOfParticipants" name="numberOfParticipants">
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="Planned">Planned</option>
                        <option value="Ongoing">Ongoing</option>