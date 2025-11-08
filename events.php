<?php
session_start();
require_once 'config.php';

// Redirect to login if not authenticated as staff
if (!isset($_SESSION['staff_id'])) {
    header('Location: login.php');
    exit;
}

// Handle DELETE action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $sql = "DELETE FROM donation_event WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_GET['id']);
    if ($stmt->execute()) {
        $success_message = "Event deleted successfully!";
    } else {
        $error_message = "Error deleting event: " . $conn->error;
    }
    $stmt->close();
}

// Handle UPDATE action
if (isset($_POST['update_event'])) {
    $sql = "UPDATE donation_event SET 
            event_name = ?, 
            event_date = ?, 
            start_time = ?, 
            location = ?, 
            staff_id = ?, 
            number_of_participants = ?, 
            status = ?, 
            target_blood = ?, 
            notes = ?
            WHERE event_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssiisssi",
        $_POST['eventName'],
        $_POST['eventDate'],
        $_POST['startTime'],
        $_POST['location'],
        $_POST['staffId'] ? $_POST['staffId'] : null,
        $_POST['numberOfParticipants'] ? $_POST['numberOfParticipants'] : null,
        $_POST['status'],
        $_POST['targetBlood'] ? $_POST['targetBlood'] : null,
        $_POST['notes'],
        $_POST['event_id']
    );
    
    if ($stmt->execute()) {
        $success_message = "Event updated successfully!";
    } else {
        $error_message = "Error updating event: " . $conn->error;
    }
    $stmt->close();
}

// Handle CREATE (INSERT) action
if (isset($_POST['add_event'])) {
    $sql = "INSERT INTO donation_event (event_name, event_date, start_time, location, staff_id, number_of_participants, status, target_blood, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssiisss",
        $_POST['eventName'],
        $_POST['eventDate'],
        $_POST['startTime'],
        $_POST['location'],
        $_POST['staffId'] ? $_POST['staffId'] : null,
        $_POST['numberOfParticipants'] ? $_POST['numberOfParticipants'] : null,
        $_POST['status'],
        $_POST['targetBlood'] ? $_POST['targetBlood'] : null,
        $_POST['notes']
    );
    
    if ($stmt->execute()) {
        $success_message = "Event added successfully!";
    } else {
        $error_message = "Error adding event: " . $conn->error;
    }
    $stmt->close();
}

// Get event for editing
$edit_event = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $sql = "SELECT * FROM donation_event WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_event = $result->fetch_assoc();
    $stmt->close();
}

// READ - Get all events from database
$sql = "SELECT e.*, s.first_name, s.last_name 
        FROM donation_event e 
        LEFT JOIN staff s ON e.staff_id = s.staff_id 
        ORDER BY e.event_date DESC";
$result = $conn->query($sql);
$events = $result->fetch_all(MYSQLI_ASSOC);

// Get event statistics
$stats_sql = "SELECT 
    COUNT(*) as total_events,
    COUNT(CASE WHEN status = 'Planned' THEN 1 END) as planned_events,
    COUNT(CASE WHEN status = 'Ongoing' THEN 1 END) as ongoing_events,
    COUNT(CASE WHEN status = 'Completed' THEN 1 END) as completed_events,
    SUM(CASE WHEN status = 'Completed' THEN target_blood ELSE 0 END) as total_blood_collected
    FROM donation_event";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Events - Blood Donation DMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Dashboard-style layout */
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
            font-weight: 600;
        }
        
        .sidebar-content {
            flex: 1;
            overflow-y: auto;
        }
        
        .sidebar-nav {
            flex: 1;
            padding: 20px 0;
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
            border-left-color: #fff;
        }
        
        .nav-item.active {
            background: rgba(255,255,255,0.2);
            border-left-color: #fff;
            font-weight: 600;
        }
        
        .nav-item i {
            margin-right: 10px;
            font-size: 16px;
        }
        
        .user-info {
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.1);
        }
        
        .user-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .user-role {
            font-size: 12px;
            color: rgba(255,255,255,0.7);
            margin-bottom: 15px;
        }
        
        .logout-btn {
            display: inline-block;
            padding: 8px 15px;
            background: rgba(255,255,255,0.1);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 12px;
            transition: background 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            background: #f5f5f5;
            min-height: 100vh;
        }
        
        .content-area {
            padding: 30px;
        }
        
        .events-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #E21C3D;
            flex: 1;
            min-width: 180px;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #E21C3D;
            margin: 0;
        }
        
        .stat-label {
            font-size: 0.9em;
            color: #666;
            margin: 5px 0 0 0;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: 500;
        }
        
        .status-planned {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-ongoing {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .action-links a {
            margin: 0 5px;
            color: #E21C3D;
            text-decoration: none;
        }
        
        .action-links a:hover {
            text-decoration: underline;
        }
        
        .btn-add-event {
            background: #E21C3D;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            margin-bottom: 20px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-add-event:hover {
            background: #c01830;
        }
        
        .form-container {
            display: none;
        }
        
        .form-container.active {
            display: block;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="images/blood-drop-heart-logo.png" alt="Blood Donation DMS">
                <h2>Blood Donation DMS</h2>
            </div>
            
            <div class="sidebar-content">
                <nav class="sidebar-nav">
                    <div class="nav-section">
                        <div class="nav-section-title">Main</div>
                        <a href="index.php" class="nav-item">
                            Dashboard
                        </a>
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
                        <div class="nav-section-title">Management</div>
                        <a href="events.php" class="nav-item active">
                            Events
                        </a>
                        <a href="sessions.php" class="nav-item">
                            Sessions
                        </a>
                        <a href="testing.php" class="nav-item">
                            Testing
                        </a>
                        <a href="transfusions.php" class="nav-item">
                            Transfusions
                        </a>
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
            </nav>
        </div>
            
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($_SESSION['staff_name']); ?></div>
                <div class="user-role">Staff Member</div>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-area">
                <div class="top-bar">
                    <div>
                        <span style="color: #333; font-size: 18px; font-weight: 500;">Events</span>
                    </div>
                </div>
        <!-- Statistics Section -->
        <div class="events-stats">
            <div class="stat-card">
                <p class="stat-number"><?php echo $stats['total_events']; ?></p>
                <p class="stat-label">Total Events</p>
            </div>
            <div class="stat-card">
                <p class="stat-number"><?php echo $stats['planned_events']; ?></p>
                <p class="stat-label">Planned Events</p>
            </div>
            <div class="stat-card">
                <p class="stat-number"><?php echo $stats['ongoing_events']; ?></p>
                <p class="stat-label">Ongoing Events</p>
            </div>
            <div class="stat-card">
                <p class="stat-number"><?php echo $stats['completed_events']; ?></p>
                <p class="stat-label">Completed Events</p>
            </div>
            <div class="stat-card">
                <p class="stat-number"><?php echo number_format($stats['total_blood_collected']); ?> ml</p>
                <p class="stat-label">Blood Collected (Target)</p>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <section class="mb-30">
            <div class="section-header">
                <h2>All Events</h2>
                <button class="btn-add-event" onclick="toggleForm()">
                    <?php echo $edit_event ? 'Cancel Edit' : '+ Add New Event'; ?>
                </button>
            </div>
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
                            <th>Participants</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($events)): ?>
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 40px; color: #666;">
                                    <p>No events found in database. Add your first event below!</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?php echo $event['event_id']; ?></td>
                                <td><?php echo htmlspecialchars($event['event_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                                <td><?php echo $event['start_time'] ? date('h:i A', strtotime($event['start_time'])) : 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($event['location']); ?></td>
                                <td>
                                    <?php 
                                    if ($event['staff_id']) {
                                        echo $event['staff_id'] . ' (' . htmlspecialchars($event['first_name'] . ' ' . substr($event['last_name'], 0, 1) . '.') . ')';
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($event['status']); ?>">
                                        <?php echo $event['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo $event['target_blood'] ? number_format($event['target_blood']) : 'N/A'; ?></td>
                                <td><?php echo $event['number_of_participants'] ? $event['number_of_participants'] : 'N/A'; ?></td>
                                <td class="action-links">
                                    <a href="events.php?action=edit&id=<?php echo $event['event_id']; ?>">Edit</a> | 
                                    <a href="events.php?action=delete&id=<?php echo $event['event_id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Add/Edit Event Form -->
        <section class="form-container <?php echo $edit_event ? 'active' : ''; ?>" id="eventForm">
            <h2><?php echo $edit_event ? 'Edit Event' : 'Create New Donation Event'; ?></h2>
            <form action="events.php" method="POST">
                <?php if ($edit_event): ?>
                    <input type="hidden" name="event_id" value="<?php echo $edit_event['event_id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="eventName">Event Name *</label>
                    <input type="text" id="eventName" name="eventName" 
                           value="<?php echo $edit_event ? htmlspecialchars($edit_event['event_name']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="eventDate">Event Date *</label>
                    <input type="date" id="eventDate" name="eventDate" 
                           value="<?php echo $edit_event ? $edit_event['event_date'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="startTime">Start Time</label>
                    <input type="time" id="startTime" name="startTime" 
                           value="<?php echo $edit_event ? $edit_event['start_time'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" 
                           value="<?php echo $edit_event ? htmlspecialchars($edit_event['location']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="staffId">Staff Lead ID</label>
                    <input type="number" id="staffId" name="staffId" 
                           value="<?php echo $edit_event ? $edit_event['staff_id'] : ''; ?>">
                    <small>e.g., 7001 for Grace Taylor</small>
                </div>
                
                <div class="form-group">
                    <label for="numberOfParticipants">Number of Expected Participants</label>
                    <input type="number" id="numberOfParticipants" name="numberOfParticipants" 
                           value="<?php echo $edit_event ? $edit_event['number_of_participants'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="Planned" <?php echo ($edit_event && $edit_event['status'] == 'Planned') ? 'selected' : ''; ?>>Planned</option>
                        <option value="Ongoing" <?php echo ($edit_event && $edit_event['status'] == 'Ongoing') ? 'selected' : ''; ?>>Ongoing</option>
                        <option value="Completed" <?php echo ($edit_event && $edit_event['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                        <option value="Cancelled" <?php echo ($edit_event && $edit_event['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="targetBlood">Target Blood Collection (ml)</label>
                    <input type="number" id="targetBlood" name="targetBlood" 
                           value="<?php echo $edit_event ? $edit_event['target_blood'] : ''; ?>">
                    <small>Expected amount of blood to collect</small>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="4"><?php echo $edit_event ? htmlspecialchars($edit_event['notes']) : ''; ?></textarea>
                </div>
                
                <div class="form-actions">
                    <?php if ($edit_event): ?>
                        <button type="submit" name="update_event" class="btn btn-primary">Update Event</button>
                        <a href="events.php" class="btn btn-secondary">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="add_event" class="btn btn-primary">Add Event</button>
                        <button type="reset" class="btn btn-secondary">Reset</button>
                    <?php endif; ?>
                </div>
            </form>
        </section>
    </main>

    <footer class="main-footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Blood Donation Database Management System. All rights reserved.</p>
            <p>Powered by Group G</p>
        </div>
    </footer>

    <script>
        function toggleForm() {
            var form = document.getElementById('eventForm');
            form.classList.toggle('active');
            
            // Scroll to form if showing
            if (form.classList.contains('active')) {
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
        
        // Auto-scroll to form if editing
        <?php if ($edit_event): ?>
        window.addEventListener('load', function() {
            document.getElementById('eventForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
        <?php endif; ?>
    </script>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>
