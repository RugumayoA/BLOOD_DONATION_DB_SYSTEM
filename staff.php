<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "blood_donation";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Redirect to login if not authenticated as staff
if (!isset($_SESSION['staff_id'])) {
    header('Location: login.php');
    exit;
}

// Handle form submission for adding new staff
if ($_POST && !isset($_POST['action'])) {
    // Hash the password
    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO staff (first_name, last_name, employee_id, position, department, phone_number, email, password, hire_date, status, qualifications, license_number) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssss", 
        $_POST['firstName'],
        $_POST['lastName'],
        $_POST['employeeId'],
        $_POST['position'],
        $_POST['department'],
        $_POST['phoneNumber'],
        $_POST['email'],
        $password_hash,
        $_POST['hireDate'],
        $_POST['status'],
        $_POST['qualifications'],
        $_POST['licenseNumber']
    );

    if ($stmt->execute()) {
        $success_message = "Staff member added successfully!";
    } else {
        $error_message = "Error: " . $conn->error;
    }
    $stmt->close();
}

// Handle edit action
if (isset($_POST['action']) && $_POST['action'] == 'edit') {
    $sql = "UPDATE staff SET first_name=?, last_name=?, employee_id=?, position=?, department=?, phone_number=?, email=?, hire_date=?, status=?, qualifications=?, license_number=? WHERE staff_id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssi",
        $_POST['firstName'],
        $_POST['lastName'],
        $_POST['employeeId'],
        $_POST['position'],
        $_POST['department'],
        $_POST['phoneNumber'],
        $_POST['email'],
        $_POST['hireDate'],
        $_POST['status'],
        $_POST['qualifications'],
        $_POST['licenseNumber'],
        $_POST['staffId']
    );

    if ($stmt->execute()) {
        $success_message = "Staff member updated successfully!";
    } else {
        $error_message = "Error: " . $conn->error;
    }
    $stmt->close();
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $sql = "DELETE FROM staff WHERE staff_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_GET['id']);
    if ($stmt->execute()) {
        $success_message = "Staff member deleted successfully!";
    } else {
        $error_message = "Error: " . $conn->error;
    }
    $stmt->close();
}

// Get staff member for editing (if requested)
$edit_staff = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $sql = "SELECT * FROM staff WHERE staff_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_staff = $result->fetch_assoc();
    $stmt->close();
}

// Get all staff members from database
$sql = "SELECT * FROM staff ORDER BY last_name, first_name";
$result = $conn->query($sql);
$staff_members = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - Blood Donation DMS</title>
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
                        <a href="index.php" class="nav-item">
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
                        <a href="staff.php" class="nav-item active">
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
            <div class="top-bar">
                <div>
                    <span style="color: #333; font-size: 18px; font-weight: 500;">Staff</span>
                </div>
        </div>

            <div class="content-area">
    <main class="container">
        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <section class="mb-30">
            <h2>Current Staff Members</h2>
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Staff ID</th>
                            <th>Employee ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($staff_members)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px; color: #666;">
                                    <p>No staff members found in database. Add your first staff member below!</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($staff_members as $staff): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($staff['staff_id']); ?></td>
                                    <td><?php echo htmlspecialchars($staff['employee_id']); ?></td>
                                    <td><?php echo htmlspecialchars($staff['first_name']); ?></td>
                                    <td><?php echo htmlspecialchars($staff['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($staff['position']); ?></td>
                                    <td><?php echo htmlspecialchars($staff['department']); ?></td>
                                    <td><?php echo htmlspecialchars($staff['status']); ?></td>
                                    <td>
                                        <a href="#" onclick="openEditModal(<?php echo $staff['staff_id']; ?>); return false;">Edit</a> |
                                        <a href="#" onclick="openDeleteModal(<?php echo $staff['staff_id']; ?>, '<?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?>'); return false;">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="form-container">
            <h2><?php echo $edit_staff ? 'Edit Staff Member' : 'Add New Staff Member'; ?></h2>
            <form action="staff.php" method="POST">
                <?php if ($edit_staff): ?>
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="staffId" value="<?php echo $edit_staff['staff_id']; ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" id="firstName" name="firstName" value="<?php echo $edit_staff ? htmlspecialchars($edit_staff['first_name']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" id="lastName" name="lastName" value="<?php echo $edit_staff ? htmlspecialchars($edit_staff['last_name']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="employeeId">Employee ID</label>
                    <input type="text" id="employeeId" name="employeeId" value="<?php echo $edit_staff ? htmlspecialchars($edit_staff['employee_id']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="position">Position</label>
                    <input type="text" id="position" name="position" value="<?php echo $edit_staff ? htmlspecialchars($edit_staff['position']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="department">Department</label>
                    <input type="text" id="department" name="department" value="<?php echo $edit_staff ? htmlspecialchars($edit_staff['department']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="phoneNumber">Phone Number</label>
                    <input type="text" id="phoneNumber" name="phoneNumber" value="<?php echo $edit_staff ? htmlspecialchars($edit_staff['phone_number']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo $edit_staff ? htmlspecialchars($edit_staff['email']) : ''; ?>">
                </div>
                <?php if (!$edit_staff): ?>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="hireDate">Hire Date</label>
                    <input type="date" id="hireDate" name="hireDate" value="<?php echo $edit_staff ? htmlspecialchars($edit_staff['hire_date']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="Active" <?php echo ($edit_staff && $edit_staff['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo ($edit_staff && $edit_staff['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                        <option value="On Leave" <?php echo ($edit_staff && $edit_staff['status'] == 'On Leave') ? 'selected' : ''; ?>>On Leave</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="qualifications">Qualifications</label>
                    <textarea id="qualifications" name="qualifications" rows="3"><?php echo $edit_staff ? htmlspecialchars($edit_staff['qualifications']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="licenseNumber">License Number</label>
                    <input type="text" id="licenseNumber" name="licenseNumber" value="<?php echo $edit_staff ? htmlspecialchars($edit_staff['license_number']) : ''; ?>">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?php echo $edit_staff ? 'Update Staff' : 'Add Staff'; ?></button>
                    <?php if ($edit_staff): ?>
                        <a href="staff.php" class="btn btn-secondary">Cancel</a>
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

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Confirm Deletion</h2>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="staffName"></strong> from the staff database?</p>
                <p>This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button class="btn-modal btn-confirm" id="confirmDeleteBtn">Delete Staff Member</button>
            </div>
        </div>
    </div>

    <script>
        let staffIdToDelete = null;

        function openDeleteModal(staffId, staffName) {
            staffIdToDelete = staffId;
            document.getElementById('staffName').textContent = staffName;
            document.getElementById('confirmDeleteBtn').onclick = function() {
                window.location.href = '?action=delete&id=' + staffIdToDelete;
            };
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        function openEditModal(staffId) {
            window.location.href = '?action=edit&id=' + staffId;
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const deleteModal = document.getElementById('deleteModal');
            if (event.target == deleteModal) {
                closeDeleteModal();
            }
        }
    </script>
            </div> <!-- End content-area -->
        </div> <!-- End main-content -->
    </div> <!-- End dashboard-container -->
</body>
</html>
<?php
$conn->close();
?>