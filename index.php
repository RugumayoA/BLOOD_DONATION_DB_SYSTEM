\<?php
require_once 'config.php';

// Fetch the next upcoming event
$sql = "SELECT * FROM donation_event 
        WHERE (status = 'Planned' OR status = 'Ongoing') 
        AND event_date >= CURDATE() 
        ORDER BY event_date ASC, start_time ASC 
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$upcoming_event = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Donors: Your Blood, Their Hope</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .upcoming-event-message {
            font-size: 0.95em;
            line-height: 1.6;
            color: #555;
            margin: 15px 0;
        }
        .event-highlight {
            background: #fff8f8;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #E21C3D;
        }
        .event-highlight strong {
            color: #E21C3D;
            font-size: 1.1em;
        }
        .no-event-message {
            color: #666;
            font-style: italic;
            padding: 15px 0;
            line-height: 1.6;
        }
    </style>
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
                <?php if ($upcoming_event): ?>
                    <p class="upcoming-event-message">
                        <strong>Be a hero in someone's story.</strong> Just one hour of your time can save up to three lives. 
                        Join us at our upcoming blood donation event and make a lasting impact in your community.
                    </p>
                    <div class="event-highlight">
                        <strong><?php echo htmlspecialchars($upcoming_event['event_name']); ?></strong>
                        <p style="margin: 8px 0;">📅 <?php echo date('F j, Y', strtotime($upcoming_event['event_date'])); ?>
                        <?php if ($upcoming_event['start_time']): ?>
                            at <?php echo date('g:i A', strtotime($upcoming_event['start_time'])); ?>
                        <?php endif; ?>
                        </p>
                        <?php if ($upcoming_event['location']): ?>
                            <p style="margin: 8px 0;">📍 <?php echo htmlspecialchars($upcoming_event['location']); ?></p>
                        <?php endif; ?>
                    </div>
                    <p style="font-size: 0.9em; color: #666; margin: 10px 0;">
                        Every donation matters. Every donor is a lifesaver. Will you be the one?
                    </p>
                    <a href="events.php" class="btn btn-secondary">View All Events</a>
                <?php else: ?>
                    <p class="no-event-message">
                        While we don't have any events scheduled at the moment, blood is always needed. 
                        Someone, somewhere, needs blood every two seconds. Your donation today could be their 
                        second chance at life. Check back soon for upcoming donation drives, or contact us 
                        to schedule a donation.
                    </p>
                    <a href="events.php" class="btn btn-secondary">View All Events</a>
                <?php endif; ?>
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