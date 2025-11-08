<?php
session_start();
require_once 'config.php';

// Redirect to login if not authenticated as staff
if (!isset($_SESSION['staff_id'])) {
    header('Location: login.php');
    exit;
}

// Handle form submission for creating new session
if ($_POST && !isset($_POST['action'])) {
    $sql = "INSERT INTO donation_session (session_date, start_time, end_time, location, staff_id, status, max_donors, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssisss",
        $_POST['sessionDate'],
        $_POST['startTime'],
        $_POST['endTime'],
        $_POST['location'],
        $_POST['staffId'],
        $_POST['status'],
        $_POST['maxDonors'],
        $_POST['notes']
    );

    if ($stmt->execute()) {
        $success_message = "Donation session created successfully!";
    } else {
        $error_message = "Error: " . $conn->error;
    }
    $stmt->close();
}

// Handle edit action
if (isset($_POST['action']) && $_POST['action'] == 'edit') {
    $sql = "UPDATE donation_session SET session_date=?, start_time=?, end_time=?, location=?, staff_id=?, status=?, max_donors=?, notes=? WHERE session_id=?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssisssi",
        $_POST['sessionDate'],
        $_POST['startTime'],
        $_POST['endTime'],
        $_POST['location'],
        $_POST['staffId'],
        $_POST['status'],
        $_POST['maxDonors'],
        $_POST['notes'],
        $_POST['sessionId']
    );
    
    if ($stmt->execute()) {
        $success_message = "Donation session updated successfully!";
    } else {
        $error_message = "Error: " . $conn->error;
    }
    $stmt->close();
}

// Handle cancel action
if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['id'])) {
    $sql = "UPDATE donation_session SET status='Cancelled' WHERE session_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_GET['id']);
    if ($stmt->execute()) {
        $success_message = "Donation session cancelled successfully!";
    } else {
        $error_message = "Error: " . $conn->error;
    }
    $stmt->close();
}

// Get session for editing (if requested)
$edit_session = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $sql = "SELECT * FROM donation_session WHERE session_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_session = $result->fetch_assoc();
    $stmt->close();
}

// Fix any sessions with invalid staff_id values (ensure they point to valid staff)
// This handles cases where staff_id doesn't match any staff record
// First, get the default staff_id (SG001 = staff_id 1)
$default_staff_sql = "SELECT staff_id FROM staff WHERE employee_id = 'SG001' LIMIT 1";
$default_result = $conn->query($default_staff_sql);
$default_staff_id = 1; // Default fallback
if ($default_result && $default_row = $default_result->fetch_assoc()) {
    $default_staff_id = $default_row['staff_id'];
}

// Update sessions with invalid staff_id to point to a valid staff member
$fix_sql = "UPDATE donation_session ds 
            LEFT JOIN staff s ON ds.staff_id = s.staff_id 
            SET ds.staff_id = " . intval($default_staff_id) . "
            WHERE ds.staff_id IS NOT NULL AND s.staff_id IS NULL";
$conn->query($fix_sql);

// Get all donation sessions from database
$sql = "SELECT ds.*, s.first_name, s.last_name, s.employee_id 
        FROM donation_session ds 
        LEFT JOIN staff s ON ds.staff_id = s.staff_id 
        ORDER BY ds.session_date DESC, ds.start_time";
$result = $conn->query($sql);
if (!$result) {
    $error_message = "Database query error: " . $conn->error;
    $sessions = array();
} else {
    $sessions = $result->fetch_all(MYSQLI_ASSOC);
}

// Get all staff members for the dropdown
$staff_sql = "SELECT staff_id, first_name, last_name, employee_id FROM staff WHERE status = 'Active' ORDER BY last_name, first_name";
$staff_result = $conn->query($staff_sql);
$staff_members = $staff_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Sessions - Blood Donation DMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
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

        /* Custom Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 0;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 500px;
            animation: modalopen 0.4s;
        }

        @keyframes modalopen {
            from {
                opacity: 0;
                transform: translateY(-60px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            padding: 20px;
            background-color: #E21C3D;
            color: white;
            border-radius: 8px 8px 0 0;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-radius: 0 0 8px 8px;
            text-align: right;
        }

        .btn-modal {
            padding: 8px 16px;
            margin-left: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-confirm {
            background-color: #E21C3D;
            color: white;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }

        .btn-modal:hover {
            opacity: 0.9;
        }
        
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
                        <a href="events.php" class="nav-item">
                            Events
                        </a>
                        <a href="sessions.php" class="nav-item active">
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
                        <span style="color: #333; font-size: 18px; font-weight: 500;">Sessions</span>
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
                        <?php if (empty($sessions)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px; color: #666;">
                                    <p>No donation sessions found in database. Create your first session below!</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sessions as $session): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($session['session_id']); ?></td>
                                    <td><?php echo htmlspecialchars($session['session_date']); ?></td>
                                    <td><?php echo htmlspecialchars($session['start_time']); ?></td>
                                    <td><?php echo htmlspecialchars($session['end_time']); ?></td>
                                    <td><?php echo htmlspecialchars($session['location']); ?></td>
                                    <td>
                                        <?php
                                        // Check if staff data exists from the JOIN
                                        if (!empty($session['first_name']) && !empty($session['last_name']) && !empty($session['employee_id'])) {
                                            echo htmlspecialchars($session['employee_id'] . ' - ' . $session['first_name'] . ' ' . $session['last_name']);
                                        } elseif (!empty($session['staff_id'])) {
                                            // Staff ID exists but JOIN didn't find a match - try to get staff info directly
                                            $staff_check = "SELECT employee_id, first_name, last_name FROM staff WHERE staff_id = " . intval($session['staff_id']);
                                            $staff_result = $conn->query($staff_check);
                                            if ($staff_result && $staff_row = $staff_result->fetch_assoc()) {
                                                echo htmlspecialchars($staff_row['employee_id'] . ' - ' . $staff_row['first_name'] . ' ' . $staff_row['last_name']);
                                            } else {
                                                echo htmlspecialchars('Staff ID: ' . $session['staff_id'] . ' (Not Found)');
                                            }
                                        } else {
                                            echo 'Not assigned';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($session['max_donors']); ?></td>
                                    <td><?php echo htmlspecialchars($session['status']); ?></td>
                                    <td>
                                        <a href="#" onclick="openEditModal(<?php echo $session['session_id']; ?>); return false;">Edit</a> |
                                        <a href="#" onclick="openCancelModal(<?php echo $session['session_id']; ?>, '<?php echo htmlspecialchars($session['session_date']); ?>'); return false;">Cancel</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="form-container">
            <h2><?php echo $edit_session ? 'Edit Donation Session' : 'Create New Donation Session'; ?></h2>
            <form action="sessions.php" method="POST">
                <?php if ($edit_session): ?>
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="sessionId" value="<?php echo $edit_session['session_id']; ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label for="sessionDate">Session Date</label>
                    <input type="date" id="sessionDate" name="sessionDate" value="<?php echo $edit_session ? htmlspecialchars($edit_session['session_date']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="startTime">Start Time</label>
                    <input type="time" id="startTime" name="startTime" value="<?php echo $edit_session ? htmlspecialchars($edit_session['start_time']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="endTime">End Time</label>
                    <input type="time" id="endTime" name="endTime" value="<?php echo $edit_session ? htmlspecialchars($edit_session['end_time']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" value="<?php echo $edit_session ? htmlspecialchars($edit_session['location']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="staffId">Staff Lead</label>
                    <select id="staffId" name="staffId">
                        <option value="">Select Staff Member</option>
                        <?php foreach ($staff_members as $staff): ?>
                            <option value="<?php echo $staff['staff_id']; ?>" <?php echo ($edit_session && $edit_session['staff_id'] == $staff['staff_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($staff['employee_id'] . ' - ' . $staff['first_name'] . ' ' . $staff['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="Scheduled" <?php echo ($edit_session && $edit_session['status'] == 'Scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="Ongoing" <?php echo ($edit_session && $edit_session['status'] == 'Ongoing') ? 'selected' : ''; ?>>Ongoing</option>
                        <option value="Completed" <?php echo ($edit_session && $edit_session['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                        <option value="Cancelled" <?php echo ($edit_session && $edit_session['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="maxDonors">Max Donors</label>
                    <input type="number" id="maxDonors" name="maxDonors" min="1" value="<?php echo $edit_session ? htmlspecialchars($edit_session['max_donors']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3"><?php echo $edit_session ? htmlspecialchars($edit_session['notes']) : ''; ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?php echo $edit_session ? 'Update Session' : 'Create Session'; ?></button>
                    <?php if ($edit_session): ?>
                        <a href="sessions.php" class="btn btn-secondary">Cancel</a>
                    <?php else: ?>
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

    <!-- Cancel Confirmation Modal -->
    <div id="cancelModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Confirm Cancellation</h2>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel the donation session on <strong id="sessionDate"></strong>?</p>
                <p>This will mark the session as "Cancelled" in the system.</p>
            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-cancel" onclick="closeCancelModal()">Cancel</button>
                <button class="btn-modal btn-confirm" id="confirmCancelBtn">Cancel Session</button>
            </div>
        </div>
    </div>

    <script>
        let sessionIdToCancel = null;

        function openCancelModal(sessionId, sessionDate) {
            sessionIdToCancel = sessionId;
            document.getElementById('sessionDate').textContent = sessionDate;
            document.getElementById('confirmCancelBtn').onclick = function() {
                window.location.href = '?action=cancel&id=' + sessionIdToCancel;
            };
            document.getElementById('cancelModal').style.display = 'block';
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
        }

        function openEditModal(sessionId) {
            window.location.href = '?action=edit&id=' + sessionId;
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const cancelModal = document.getElementById('cancelModal');
            if (event.target == cancelModal) {
                closeCancelModal();
            }
        }
    </script>
            </div>
        </div>
    </div>
</body>

</html>
<?php
$conn->close();
?>