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
                <!-- A summary section could go here -->
                <div class="info-card">
                    <h3>Total Available Units</h3>
                    <p class="large-number">125</p>
                </div>
                <div class="info-card">
                    <h3>Units Expiring Soon (30 days)</h3>
                    <p class="large-number warning-text">15</p>
                </div>
                <div class="info-card">
                    <h3>Critical Blood Types</h3>
                    <p class="critical-types">O-, AB-</p>
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