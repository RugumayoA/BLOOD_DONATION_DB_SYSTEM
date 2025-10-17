<?php
session_start();
require_once 'config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['staff_id'])) {
    header('Location: landing.php');
    exit;
}

// Fetch the next upcoming event
$sql = "SELECT * FROM donation_event 
        WHERE (status = 'Planned' OR status = 'Ongoing') 
        AND event_date >= CURDATE() 
        ORDER BY event_date ASC, start_time ASC 
        LIMIT 1";
$result = $conn->query($sql);
$upcoming_event = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Donation DMS - Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Sidebar Navigation Styles */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #E21C3D 0%, #8B0000 100%);
            color: white;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header img {
            width: 50px;
            height: 50px;
            margin-bottom: 10px;
        }
        
        .sidebar-header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .sidebar-nav {
            flex: 1;
            padding: 20px 0;
            overflow-y: auto;
        }
        
        .sidebar-content {
            flex: 1;
            overflow-y: auto;
        }
        
        .nav-section {
            margin-bottom: 20px;
        }
        
        .nav-section-title {
            padding: 0 20px 10px 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            color: rgba(255,255,255,0.7);
            letter-spacing: 1px;
        }
        
        .nav-item {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .nav-item:hover {
            background: rgba(255,255,255,0.1);
            border-left-color: white;
            transform: translateX(5px);
        }
        
        .nav-item.active {
            background: rgba(255,255,255,0.2);
            border-left-color: white;
        }
        
        .nav-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .user-info {
            padding: 15px 20px;
            background: rgba(0,0,0,0.15);
            border-top: 1px solid rgba(255,255,255,0.1);
            margin: 20px 0 0 0;
        }
        
        .user-name {
            font-weight: bold;
            margin-bottom: 3px;
            font-size: 14px;
        }
        
        .user-role {
            font-size: 11px;
            color: rgba(255,255,255,0.7);
            margin-bottom: 8px;
        }
        
        .logout-btn {
            display: block;
            width: 100%;
            padding: 8px;
            background: #8B0000;
            color: white;
            text-decoration: none;
            text-align: center;
            border-radius: 6px;
            margin-top: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .logout-btn:hover {
            background: #A52A2A;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        
        .main-content {
            margin-left: 250px;
            flex: 1;
            background: #f5f5f5;
        }
        
        .top-bar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        
        .content-area {
            padding: 30px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
            }
            .user-info {
                width: 200px;
            }
        }
        
        @media (max-width: 600px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
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
    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="images/blood-drop-heart-logo.png" alt="Blood Donation Logo">
                <h2>Blood Donation DMS</h2>
            </div>
            
            <div class="sidebar-content">
                <nav class="sidebar-nav">
                    <div class="nav-section">
                        <div class="nav-section-title">Main</div>
                        <a href="index.php" class="nav-item active">
                            Dashboard
                        </a>
                    </div>
                    
                    <div class="nav-section">
                        <div class="nav-section-title">Management</div>
                        <a href="donors.php" class="nav-item">
                            Donors
                        </a>
                        <a href="recipients.php" class="nav-item">
                            Recipients
                        </a>
                        <a href="donations.php" class="nav-item">
                            <i>🩸</i> Donations
                        </a>
                        <a href="requests.php" class="nav-item">
                            Blood Requests
                        </a>
                        <a href="inventory.php" class="nav-item">
                            Inventory
                        </a>
                        <a href="staff.php" class="nav-item">
                            Staff
                        </a>
                    </div>
                    
                    <div class="nav-section">
                        <div class="nav-section-title">Events & Sessions</div>
                        <a href="events.php" class="nav-item">
                            Events
                        </a>
                        <a href="sessions.php" class="nav-item">
                            Sessions
                        </a>
                    </div>
                    
                    <div class="nav-section">
                        <div class="nav-section-title">Medical</div>
                        <a href="testing.php" class="nav-item">
                            Testing
                        </a>
                        <a href="transfusions.php" class="nav-item">
                            Transfusions
                        </a>
                    </div>
                    
                    <div class="nav-section">
                        <div class="nav-section-title">Reports & Analytics</div>
                        <a href="insights.php" class="nav-item">
                            <i>📊</i> Insights
                        </a>
                        <a href="reports.php" class="nav-item">
                            Reports
                        </a>
                        <a href="notifications.php" class="nav-item">
                            Notifications
                        </a>
                    </div>
                    
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['staff_name']); ?></div>
                        <div class="user-role">Staff Member</div>
                        <a href="logout.php" class="logout-btn">🚪 Logout</a>
                    </div>
                </nav>
            </div>
        </div>
        
        <!-- Main Content Area -->
        <div class="main-content">
            <div class="top-bar" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="color: #333; font-size: 18px; font-weight: 500;">Dashboard</span>
                </div>
                <div style="margin-right: 100px;">
                    <span style="color: #666; font-size: 16px;">Welcome back, <?php echo htmlspecialchars($_SESSION['staff_name']); ?>!</span>
                </div>
            </div>
            
            <div class="content-area">

    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h2>Blood Donors</h2>
                <h1>Your Blood, <br>Their Hope.</h1>
                <p>Every drop counts. Join us in saving lives and making a difference in the community.</p>
                <a href="donors.php" class="btn btn-primary">Become a Donor Today</a>
            </div>
            <div class="hero-image">
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

            </div> <!-- End content-area -->
        </div> <!-- End main-content -->
    </div> <!-- End dashboard-container -->
</body>
</html>
<?php
$conn->close();
?>