<?php
require_once 'config.php';

// Handle form submission for adding new staff
if ($_POST && !isset($_POST['action'])) {
    try {
        $sql = "INSERT INTO staff (first_name, last_name, employee_id, position, department, phone_number, email, hire_date, status, qualifications, license_number) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
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
            $_POST['licenseNumber']
        ));

        $success_message = "Staff member added successfully!";
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Handle edit action
if (isset($_POST['action']) && $_POST['action'] == 'edit') {
    try {
        $sql = "UPDATE staff SET first_name=?, last_name=?, employee_id=?, position=?, department=?, phone_number=?, email=?, hire_date=?, status=?, qualifications=?, license_number=? WHERE staff_id=?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
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
        ));

        $success_message = "Staff member updated successfully!";
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    try {
        $sql = "DELETE FROM staff WHERE staff_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($_GET['id']));
        $success_message = "Staff member deleted successfully!";
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Get staff member for editing (if requested)
$edit_staff = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $sql = "SELECT * FROM staff WHERE staff_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($_GET['id']));
    $edit_staff = $stmt->fetch();
}

// Get all staff members from database
$sql = "SELECT * FROM staff ORDER BY last_name, first_name";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$staff_members = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - Blood Donation DMS</title>
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
                    <li><a href="staff.php" class="active">Staff</a></li>
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
            <h1>Staff Management</h1>
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
            <p>&copy; <?php echo date("Y"); ?> Blood Donation DMS. All rights reserved.</p>
            <p>Powered by Compassion</p>
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
</body>

</html>