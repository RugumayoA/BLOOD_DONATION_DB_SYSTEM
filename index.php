\<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Donors: Your Blood, Their Hope</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <!-- Inspired by the blood drop heart logo -->
                <img src="images/blood-drop-heart-logo.png" alt="Donate Blood Logo">
                <h1>Blood Donation DMS</h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="donors.php">Donors</a></li>
                    <li><a href="recipients.php">Recipients</a></li>
                    <li><a href="donations.php">Donations</a></li>
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

    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h2>Blood Donors</h2>
                <h1>Your Blood, <br>Their Hope.</h1>
                <p>Every drop counts. Join us in saving lives and making a difference in the community.</p>
                <a href="donors.php" class="btn btn-primary">Become a Donor Today</a>
            </div>
            <div class="hero-image">
                <!-- Placeholder for a graphic inspired by the blood bag/test tubes from the poster -->
                <img src="images/hero-blood-elements.png" alt="Blood donation elements">
            </div>
        </div>
    </section>

    <section class="info-section">
        <div class="container">
            <div class="info-card">
                <h3>Upcoming Events</h3>
                <p>November 20, 2024</p>
                <p>123 Anywhere St., Anytown, ST 12345</p>
                <a href="events.php" class="btn btn-secondary">View All Events</a>
            </div>
            <div class="info-card">
                <h3>Why Donate?</h3>
                <p>Your selfless act can give someone a second chance at life. Blood is constantly needed for surgeries, accidents, and chronic illnesses.</p>
                <a href="#" class="btn btn-secondary">Learn More</a>
            </div>
            <div class="info-card">
                <h3>Current Inventory</h3>
                <p>Check the current stock of blood types and help us identify critical needs.</p>
                <a href="inventory.php" class="btn btn-secondary">View Inventory</a>
            </div>
        </div>
    </section>

    <footer class="main-footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Blood Donation DMS. All rights reserved.</p>
            <p>Powered by Compassion</p>
        </div>
    </footer>
</body>
</html>