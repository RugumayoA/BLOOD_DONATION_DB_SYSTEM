<?php
require_once 'config.php';

// Handle form submission for creating new session
if ($_POST && !isset($_POST['action'])) {
    try {
        $sql = "INSERT INTO donation_session (session_date, start_time, end_time, location, staff_id, status, max_donors, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
            $_POST['sessionDate'],
            $_POST['startTime'],
            $_POST['endTime'],
            $_POST['location'],
            $_POST['staffId'],
            $_POST['status'],
            $_POST['maxDonors'],
            $_POST['notes']
        ));

        $success_message = "Donation session created successfully!";
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Handle edit action
if (isset($_POST['action']) && $_POST['action'] == 'edit') {
    try {
        $sql = "UPDATE donation_session SET session_date=?, start_time=?, end_time=?, location=?, staff_id=?, status=?, max_donors=?, notes=? WHERE session_id=?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
            $_POST['sessionDate'],
            $_POST['startTime'],
            $_POST['endTime'],
            $_POST['location'],
            $_POST['staffId'],
            $_POST['status'],
            $_POST['maxDonors'],
            $_POST['notes'],
            $_POST['sessionId']
        ));

        $success_message = "Donation session updated successfully!";
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Handle cancel action
if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['id'])) {
    try {
        $sql = "UPDATE donation_session SET status='Cancelled' WHERE session_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($_GET['id']));
        $success_message = "Donation session cancelled successfully!";
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Get session for editing (if requested)
$edit_session = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $sql = "SELECT * FROM donation_session WHERE session_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($_GET['id']));
    $edit_session = $stmt->fetch();
}

// Get all donation sessions from database
$sql = "SELECT ds.*, s.first_name, s.last_name, s.employee_id 
        FROM donation_session ds 
        LEFT JOIN staff s ON ds.staff_id = s.staff_id 
        ORDER BY ds.session_date DESC, ds.start_time";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$sessions = $stmt->fetchAll();

// Get all staff members for the dropdown
$staff_sql = "SELECT staff_id, first_name, last_name, employee_id FROM staff WHERE status = 'Active' ORDER BY last_name, first_name";
$staff_stmt = $pdo->prepare($staff_sql);
$staff_stmt->execute();
$staff_members = $staff_stmt->fetchAll();
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
    </style>
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
                                        if ($session['staff_id']) {
                                            echo htmlspecialchars($session['employee_id'] . ' (' . $session['first_name'] . ' ' . substr($session['last_name'], 0, 1) . '.)');
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
            <p>&copy; <?php echo date("Y"); ?> Blood Donation DMS. All rights reserved.</p>
            <p>Powered by Compassion</p>
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
</body>

</html>