<?php
session_start();
require_once 'config.php';

// Redirect to login if not authenticated as staff
if (!isset($_SESSION['staff_id'])) {
    header('Location: login.php');
    exit;
}

// Handle different actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$editId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle delete
if ($action == 'delete' && $editId > 0) {
    $deleteSql = "DELETE FROM recipient WHERE recipient_id = $editId";
    if ($conn->query($deleteSql) === TRUE) {
        echo "<script>alert('Recipient deleted successfully!'); window.location='recipients.php';</script>";
    } else {
        echo "<script>alert('Error deleting recipient!');</script>";
    }
}

// Handle edit update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = intval($_POST['recipient_id']);
    $firstName = $conn->real_escape_string($_POST['firstName']);
    $lastName = $conn->real_escape_string($_POST['lastName']);
    $dateOfBirth = $conn->real_escape_string($_POST['dateOfBirth']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $bloodType = $conn->real_escape_string($_POST['bloodType']);
    $phoneNumber = $conn->real_escape_string($_POST['phoneNumber']);
    $email = $conn->real_escape_string($_POST['email']);
    $address = $conn->real_escape_string($_POST['address']);
    $city = $conn->real_escape_string($_POST['city']);
    $medicalCondition = $conn->real_escape_string($_POST['medicalCondition']);
    $allergies = $conn->real_escape_string($_POST['allergies']);
    $registrationDate = $conn->real_escape_string($_POST['registrationDate']);
    $status = $conn->real_escape_string($_POST['status']);

        $updateSql = "UPDATE recipient SET 
                  first_name='$firstName', last_name='$lastName', date_of_birth='$dateOfBirth', 
                  gender='$gender', blood_type='$bloodType', phone_number='$phoneNumber', 
                  email='$email', address='$address', city='$city', 
                  medical_condition='$medicalCondition', allergies='$allergies', 
                  registration_date='$registrationDate', status='$status'
                  WHERE recipient_id=$id";
    
    if ($conn->query($updateSql) === TRUE) {
        echo "<script>alert('Recipient updated successfully!'); window.location='recipients.php';</script>";
    } else {
        echo "<script>alert('Error updating recipient!');</script>";
    }
}

// Handle add new recipient
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['update'])) {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $dateOfBirth = $_POST['dateOfBirth'];
    $gender = $_POST['gender'];
    $bloodType = $_POST['bloodType'];
    $phoneNumber = $_POST['phoneNumber'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $medicalCondition = $_POST['medicalCondition'];
    $allergies = $_POST['allergies'];
    $registrationDate = $_POST['registrationDate'];
    $status = $_POST['status'];

    $sql = "INSERT INTO recipient (first_name, last_name, date_of_birth, gender, blood_type, phone_number, email, address, city, medical_condition, allergies, registration_date, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssss", $firstName, $lastName, $dateOfBirth, $gender, $bloodType, $phoneNumber, $email, $address, $city, $medicalCondition, $allergies, $registrationDate, $status);
    
    if ($stmt->execute()) {
        echo "<script>alert('Recipient added successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }
    
    $stmt->close();
}

// Fetch recipient for edit/view
$editRecipient = null;
if (($action == 'edit' || $action == 'view') && $editId > 0) {
    $editSql = "SELECT * FROM recipient WHERE recipient_id = $editId";
    $editResult = $conn->query($editSql);
    $editRecipient = $editResult->fetch_assoc();
}

// Fetch all recipients
$sql = "SELECT recipient_id, first_name, last_name, blood_type, medical_condition, status FROM recipient";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipient Management - Blood Donation DMS</title>
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
                        <a href="recipients.php" class="nav-item active">
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
            <div class="top-bar">
                <div>
                    <span style="color: #333; font-size: 18px; font-weight: 500;">Recipients</span>
                </div>
        </div>

            <div class="content-area">
    <main class="container">
        <?php if ($action == 'view' && $editRecipient): ?>
            <section class="form-container">
                <h2>Recipient Information</h2>
                <div class="form-group"><strong>Recipient ID:</strong> <?php echo $editRecipient['recipient_id']; ?></div>
                <div class="form-group"><strong>First Name:</strong> <?php echo $editRecipient['first_name']; ?></div>
                <div class="form-group"><strong>Last Name:</strong> <?php echo $editRecipient['last_name']; ?></div>
                <div class="form-group"><strong>Date of Birth:</strong> <?php echo $editRecipient['date_of_birth']; ?></div>
                <div class="form-group"><strong>Gender:</strong> <?php echo $editRecipient['gender']; ?></div>
                <div class="form-group"><strong>Blood Type:</strong> <?php echo $editRecipient['blood_type'] ? $editRecipient['blood_type'] : 'Unknown'; ?></div>
                <div class="form-group"><strong>Phone Number:</strong> <?php echo $editRecipient['phone_number']; ?></div>
                <div class="form-group"><strong>Email:</strong> <?php echo $editRecipient['email']; ?></div>
                <div class="form-group"><strong>Address:</strong> <?php echo $editRecipient['address']; ?></div>
                <div class="form-group"><strong>City:</strong> <?php echo $editRecipient['city']; ?></div>
                <div class="form-group"><strong>Medical Condition:</strong> <?php echo $editRecipient['medical_condition']; ?></div>
                <div class="form-group"><strong>Allergies:</strong> <?php echo $editRecipient['allergies']; ?></div>
                <div class="form-group"><strong>Registration Date:</strong> <?php echo $editRecipient['registration_date']; ?></div>
                <div class="form-group"><strong>Status:</strong> <?php echo $editRecipient['status']; ?></div>
                <div class="form-actions">
                    <a href="recipients.php?action=edit&id=<?php echo $editRecipient['recipient_id']; ?>" class="btn btn-primary">Edit</a>
                    <a href="recipients.php" class="btn btn-secondary">Back to List</a>
                </div>
            </section>
        <?php elseif ($action == 'edit' && $editRecipient): ?>
            <section class="form-container">
                <h2>Edit Recipient Information</h2>
                <form action="recipients.php" method="POST">
                    <input type="hidden" name="recipient_id" value="<?php echo $editRecipient['recipient_id']; ?>">
                    <input type="hidden" name="update" value="1">
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" name="firstName" value="<?php echo $editRecipient['first_name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" name="lastName" value="<?php echo $editRecipient['last_name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="dateOfBirth">Date of Birth</label>
                        <input type="date" id="dateOfBirth" name="dateOfBirth" value="<?php echo $editRecipient['date_of_birth']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender">
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo ($editRecipient['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($editRecipient['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($editRecipient['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="bloodType">Blood Type</label>
                        <select id="bloodType" name="bloodType">
                            <option value="">Select Blood Type (Optional)</option>
                            <option value="O-" <?php echo ($editRecipient['blood_type'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                            <option value="O+" <?php echo ($editRecipient['blood_type'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                            <option value="A-" <?php echo ($editRecipient['blood_type'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                            <option value="A+" <?php echo ($editRecipient['blood_type'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                            <option value="B-" <?php echo ($editRecipient['blood_type'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                            <option value="B+" <?php echo ($editRecipient['blood_type'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                            <option value="AB-" <?php echo ($editRecipient['blood_type'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                            <option value="AB+" <?php echo ($editRecipient['blood_type'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="phoneNumber">Phone Number</label>
                        <input type="text" id="phoneNumber" name="phoneNumber" value="<?php echo $editRecipient['phone_number']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo $editRecipient['email']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" value="<?php echo $editRecipient['address']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" value="<?php echo $editRecipient['city']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="medicalCondition">Medical Condition</label>
                        <textarea id="medicalCondition" name="medicalCondition" rows="3"><?php echo $editRecipient['medical_condition']; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="allergies">Allergies</label>
                        <textarea id="allergies" name="allergies" rows="3"><?php echo $editRecipient['allergies']; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="registrationDate">Registration Date</label>
                        <input type="date" id="registrationDate" name="registrationDate" value="<?php echo $editRecipient['registration_date']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="Active" <?php echo ($editRecipient['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo ($editRecipient['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update Recipient</button>
                        <a href="recipients.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </section>
        <?php else: ?>
        <section class="mb-30">
            <h2>Current Recipients</h2>
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Blood Type</th>
                            <th>Medical Condition</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row["recipient_id"] . "</td>";
                                echo "<td>" . $row["first_name"] . "</td>";
                                echo "<td>" . $row["last_name"] . "</td>";
                                echo "<td>" . ($row["blood_type"] ? $row["blood_type"] : "Unknown") . "</td>";
                                echo "<td>" . $row["medical_condition"] . "</td>";
                                echo "<td>" . $row["status"] . "</td>";
                                echo "<td>";
                                echo "<a href='recipients.php?action=view&id=" . $row["recipient_id"] . "'>View</a> | ";
                                echo "<a href='recipients.php?action=edit&id=" . $row["recipient_id"] . "'>Edit</a> | ";
                                echo "<a href='recipients.php?action=delete&id=" . $row["recipient_id"] . "' onclick='return confirm(\"Are you sure you want to delete this recipient?\")'>Delete</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>No recipients found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="form-container">
            <h2>Add New Recipient</h2>
            <form action="recipients.php" method="POST">
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" id="firstName" name="firstName" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" id="lastName" name="lastName" required>
                </div>
                <div class="form-group">
                    <label for="dateOfBirth">Date of Birth</label>
                    <input type="date" id="dateOfBirth" name="dateOfBirth">
                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender">
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="bloodType">Blood Type</label>
                    <select id="bloodType" name="bloodType">
                        <option value="">Select Blood Type (Optional)</option>
                        <option value="O-">O-</option>
                        <option value="O+">O+</option>
                        <option value="A-">A-</option>
                        <option value="A+">A+</option>
                        <option value="B-">B-</option>
                        <option value="B+">B+</option>
                        <option value="AB-">AB-</option>
                        <option value="AB+">AB+</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="phoneNumber">Phone Number</label>
                    <input type="text" id="phoneNumber" name="phoneNumber">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address">
                </div>
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city">
                </div>
                <div class="form-group">
                    <label for="medicalCondition">Medical Condition</label>
                    <textarea id="medicalCondition" name="medicalCondition" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="allergies">Allergies</label>
                    <textarea id="allergies" name="allergies" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="registrationDate">Registration Date</label>
                    <input type="date" id="registrationDate" name="registrationDate">
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Recipient</button>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </div>
            </form>
        </section>
        <?php endif; ?>
    </main>

    <footer class="main-footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Blood Donation DMS. All rights reserved.</p>
            <p>Powered by Compassion</p>
        </div>
    </footer>
            </div> <!-- End content-area -->
        </div> <!-- End main-content -->
    </div> <!-- End dashboard-container -->
</body>
</html>
<?php
$conn->close();
?>
