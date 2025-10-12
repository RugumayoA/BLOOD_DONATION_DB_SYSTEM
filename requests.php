<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "blood_donation";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle different actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$editId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle approve
if ($action == 'approve' && $_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['request_id']);
    $approvedBy = intval($_POST['approvedBy']);
    $approvedDate = date('Y-m-d H:i:s');
    
    $updateSql = "UPDATE blood_request SET status='Approved', approved_by=$approvedBy, approved_date='$approvedDate' WHERE request_id=$id";
    
    if ($conn->query($updateSql) === TRUE) {
        echo "<script>alert('Request approved successfully!'); window.location='requests.php';</script>";
    } else {
        echo "<script>alert('Error approving request!');</script>";
    }
}

// Handle edit update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = intval($_POST['request_id']);
    $requestDate = $conn->real_escape_string($_POST['requestDate']);
    $bloodType = $conn->real_escape_string($_POST['bloodType']);
    $quantityMl = intval($_POST['quantityMl']);
    $urgencyLevel = $conn->real_escape_string($_POST['urgencyLevel']);
    $hospitalName = $conn->real_escape_string($_POST['hospitalName']);
    $doctorName = $conn->real_escape_string($_POST['doctorName']);
    $diagnosis = $conn->real_escape_string($_POST['diagnosis']);
    $notes = $conn->real_escape_string($_POST['notes']);

    $updateSql = "UPDATE blood_request SET 
                  request_date='$requestDate', blood_type='$bloodType', quantity_ml=$quantityMl, 
                  urgency_level='$urgencyLevel', hospital_name='$hospitalName', doctor_name='$doctorName', 
                  diagnosis='$diagnosis', notes='$notes'
                  WHERE request_id=$id";
    
    if ($conn->query($updateSql) === TRUE) {
        echo "<script>alert('Request updated successfully!'); window.location='requests.php';</script>";
    } else {
        echo "<script>alert('Error updating request!');</script>";
    }
}

// Handle add new request
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['update']) && $action != 'approve') {
    $recipientId = intval($_POST['recipientId']);
    $requestDate = $conn->real_escape_string($_POST['requestDate']);
    $bloodType = $conn->real_escape_string($_POST['bloodType']);
    $quantityMl = intval($_POST['quantityMl']);
    $urgencyLevel = $conn->real_escape_string($_POST['urgencyLevel']);
    $hospitalName = $conn->real_escape_string($_POST['hospitalName']);
    $doctorName = $conn->real_escape_string($_POST['doctorName']);
    $diagnosis = $conn->real_escape_string($_POST['diagnosis']);
    $notes = $conn->real_escape_string($_POST['notes']);
    $status = 'Pending';

    $sql = "INSERT INTO blood_request (recipient_id, request_date, blood_type, quantity_ml, urgency_level, hospital_name, doctor_name, diagnosis, notes, status) 
            VALUES ($recipientId, '$requestDate', '$bloodType', $quantityMl, '$urgencyLevel', '$hospitalName', '$doctorName', '$diagnosis', '$notes', '$status')";
    
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Blood request created successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}

// Fetch request for edit/view/approve
$editRequest = null;
if (($action == 'edit' || $action == 'view' || $action == 'approve') && $editId > 0) {
    $editSql = "SELECT br.*, r.first_name, r.last_name FROM blood_request br LEFT JOIN recipient r ON br.recipient_id = r.recipient_id WHERE br.request_id = $editId";
    $editResult = $conn->query($editSql);
    $editRequest = $editResult->fetch_assoc();
}

// Fetch all requests
$sql = "SELECT request_id, recipient_id, request_date, blood_type, quantity_ml, urgency_level, status FROM blood_request";
$result = $conn->query($sql);

// Fetch all recipients for dropdown
$recipientsSql = "SELECT recipient_id, first_name, last_name, blood_type FROM recipient WHERE status='Active'";
$recipientsResult = $conn->query($recipientsSql);

// Fetch staff for approval
$staffSql = "SELECT staff_id, first_name, last_name FROM staff WHERE status='Active'";
$staffResult = $conn->query($staffSql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Requests - Blood Donation DMS</title>
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
                    <li><a href="requests.php" class="active">Requests</a></li>
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

    <section class="page-title">
        <div class="container">
            <h1>Blood Requests</h1>
        </div>
    </section>

    <main class="container">
        <?php if ($action == 'view' && $editRequest): ?>
            <section class="form-container">
                <h2>Blood Request Information</h2>
                <div class="form-group"><strong>Request ID:</strong> <?php echo $editRequest['request_id']; ?></div>
                <div class="form-group"><strong>Recipient:</strong> <?php echo $editRequest['first_name'] . ' ' . $editRequest['last_name']; ?> (ID: <?php echo $editRequest['recipient_id']; ?>)</div>
                <div class="form-group"><strong>Request Date:</strong> <?php echo $editRequest['request_date']; ?></div>
                <div class="form-group"><strong>Blood Type:</strong> <?php echo $editRequest['blood_type']; ?></div>
                <div class="form-group"><strong>Quantity (ml):</strong> <?php echo $editRequest['quantity_ml']; ?></div>
                <div class="form-group"><strong>Urgency Level:</strong> <?php echo $editRequest['urgency_level']; ?></div>
                <div class="form-group"><strong>Hospital Name:</strong> <?php echo $editRequest['hospital_name']; ?></div>
                <div class="form-group"><strong>Doctor Name:</strong> <?php echo $editRequest['doctor_name']; ?></div>
                <div class="form-group"><strong>Diagnosis:</strong> <?php echo $editRequest['diagnosis']; ?></div>
                <div class="form-group"><strong>Status:</strong> <?php echo $editRequest['status']; ?></div>
                <div class="form-group"><strong>Approved By:</strong> <?php echo $editRequest['approved_by'] ? $editRequest['approved_by'] : 'N/A'; ?></div>
                <div class="form-group"><strong>Approved Date:</strong> <?php echo $editRequest['approved_date'] ? $editRequest['approved_date'] : 'N/A'; ?></div>
                <div class="form-group"><strong>Fulfillment Date:</strong> <?php echo $editRequest['fulfillment_date'] ? $editRequest['fulfillment_date'] : 'N/A'; ?></div>
                <div class="form-group"><strong>Notes:</strong> <?php echo htmlspecialchars($editRequest['notes']); ?></div>
                <div class="form-actions">
                    <a href="requests.php?action=edit&id=<?php echo $editRequest['request_id']; ?>" class="btn btn-primary">Edit</a>
                    <?php if ($editRequest['status'] == 'Pending'): ?>
                        <a href="requests.php?action=approve&id=<?php echo $editRequest['request_id']; ?>" class="btn btn-primary">Approve</a>
                    <?php endif; ?>
                    <a href="requests.php" class="btn btn-secondary">Back to List</a>
                </div>
            </section>
        <?php elseif ($action == 'approve' && $editRequest): ?>
            <section class="form-container">
                <h2>Approve Blood Request</h2>
                <div class="form-group"><strong>Request ID:</strong> <?php echo $editRequest['request_id']; ?></div>
                <div class="form-group"><strong>Recipient:</strong> <?php echo $editRequest['first_name'] . ' ' . $editRequest['last_name']; ?></div>
                <div class="form-group"><strong>Blood Type:</strong> <?php echo $editRequest['blood_type']; ?></div>
                <div class="form-group"><strong>Quantity:</strong> <?php echo $editRequest['quantity_ml']; ?> ml</div>
                <div class="form-group"><strong>Urgency:</strong> <?php echo $editRequest['urgency_level']; ?></div>
                <div class="form-group"><strong>Hospital:</strong> <?php echo $editRequest['hospital_name']; ?></div>
                
                <h3>Approval Information</h3>
                <form action="requests.php?action=approve" method="POST">
                    <input type="hidden" name="request_id" value="<?php echo $editRequest['request_id']; ?>">
                    <div class="form-group">
                        <label for="approvedBy">Approved By (Staff)</label>
                        <select id="approvedBy" name="approvedBy" required>
                            <option value="">Select Staff Member</option>
                            <?php
                            if ($staffResult->num_rows > 0) {
                                while($staff = $staffResult->fetch_assoc()) {
                                    echo "<option value='" . $staff["staff_id"] . "'>";
                                    echo $staff["first_name"] . " " . $staff["last_name"];
                                    echo "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <p><strong>Approval Date:</strong> <?php echo date('Y-m-d H:i:s'); ?> (Current Time)</p>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to approve this request?')">Approve Request</button>
                        <a href="requests.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </section>
        <?php elseif ($action == 'edit' && $editRequest): ?>
            <section class="form-container">
                <h2>Edit Blood Request</h2>
                <form action="requests.php" method="POST">
                    <input type="hidden" name="request_id" value="<?php echo $editRequest['request_id']; ?>">
                    <input type="hidden" name="update" value="1">
                    <div class="form-group">
                        <label for="requestDate">Request Date & Time</label>
                        <input type="datetime-local" id="requestDate" name="requestDate" value="<?php echo date('Y-m-d\TH:i', strtotime($editRequest['request_date'])); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="bloodType">Required Blood Type</label>
                        <select id="bloodType" name="bloodType" required>
                            <option value="">Select Blood Type</option>
                            <option value="O-" <?php echo ($editRequest['blood_type'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                            <option value="O+" <?php echo ($editRequest['blood_type'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                            <option value="A-" <?php echo ($editRequest['blood_type'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                            <option value="A+" <?php echo ($editRequest['blood_type'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                            <option value="B-" <?php echo ($editRequest['blood_type'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                            <option value="B+" <?php echo ($editRequest['blood_type'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                            <option value="AB-" <?php echo ($editRequest['blood_type'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                            <option value="AB+" <?php echo ($editRequest['blood_type'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantityMl">Quantity (ml)</label>
                        <input type="number" id="quantityMl" name="quantityMl" value="<?php echo $editRequest['quantity_ml']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="urgencyLevel">Urgency Level</label>
                        <select id="urgencyLevel" name="urgencyLevel">
                            <option value="Low" <?php echo ($editRequest['urgency_level'] == 'Low') ? 'selected' : ''; ?>>Low</option>
                            <option value="Medium" <?php echo ($editRequest['urgency_level'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                            <option value="High" <?php echo ($editRequest['urgency_level'] == 'High') ? 'selected' : ''; ?>>High</option>
                            <option value="Critical" <?php echo ($editRequest['urgency_level'] == 'Critical') ? 'selected' : ''; ?>>Critical</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="hospitalName">Hospital Name</label>
                        <input type="text" id="hospitalName" name="hospitalName" value="<?php echo $editRequest['hospital_name']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="doctorName">Requesting Doctor's Name</label>
                        <input type="text" id="doctorName" name="doctorName" value="<?php echo $editRequest['doctor_name']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="diagnosis">Diagnosis</label>
                        <textarea id="diagnosis" name="diagnosis" rows="3"><?php echo $editRequest['diagnosis']; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="notes">Internal Notes</label>
                        <textarea id="notes" name="notes" rows="3"><?php echo $editRequest['notes']; ?></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update Request</button>
                        <a href="requests.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </section>
        <?php else: ?>
        <section class="mb-30">
            <h2>All Blood Requests</h2>
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Recipient ID</th>
                            <th>Request Date</th>
                            <th>Blood Type</th>
                            <th>Quantity (ml)</th>
                            <th>Urgency</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row["request_id"] . "</td>";
                                echo "<td>" . $row["recipient_id"] . "</td>";
                                echo "<td>" . $row["request_date"] . "</td>";
                                echo "<td>" . $row["blood_type"] . "</td>";
                                echo "<td>" . $row["quantity_ml"] . "</td>";
                                echo "<td>" . $row["urgency_level"] . "</td>";
                                echo "<td>" . $row["status"] . "</td>";
                                echo "<td>";
                                echo "<a href='requests.php?action=view&id=" . $row["request_id"] . "'>View</a> | ";
                                echo "<a href='requests.php?action=edit&id=" . $row["request_id"] . "'>Edit</a>";
                                if ($row["status"] == "Pending") {
                                    echo " | <a href='requests.php?action=approve&id=" . $row["request_id"] . "'>Approve</a>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8'>No requests found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="form-container">
            <h2>Create New Blood Request</h2>
            <form action="requests.php" method="POST">
                <div class="form-group">
                    <label for="recipientId">Recipient ID</label>
                    <select id="recipientId" name="recipientId" required>
                        <option value="">Select Recipient</option>
                        <?php
                        if ($recipientsResult->num_rows > 0) {
                            while($recipient = $recipientsResult->fetch_assoc()) {
                                $bloodTypeDisplay = $recipient["blood_type"] ? $recipient["blood_type"] : "Unknown";
                                echo "<option value='" . $recipient["recipient_id"] . "' data-bloodtype='" . $recipient["blood_type"] . "'>";
                                echo $recipient["first_name"] . " " . $recipient["last_name"] . " - " . $bloodTypeDisplay . " (ID: " . $recipient["recipient_id"] . ")";
                                echo "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="requestDate">Request Date & Time</label>
                    <input type="datetime-local" id="requestDate" name="requestDate" required>
                </div>
                <div class="form-group">
                    <label for="bloodType">Required Blood Type</label>
                    <select id="bloodType" name="bloodType" required style="display:none;">
                        <option value="">Select Blood Type</option>
                        <option value="O-">O-</option>
                        <option value="O+">O+</option>
                        <option value="A-">A-</option>
                        <option value="A+">A+</option>
                        <option value="B-">B-</option>
                        <option value="B+">B+</option>
                        <option value="AB-">AB-</option>
                        <option value="AB+">AB+</option>
                    </select>
                    <input type="text" id="bloodTypeReadonly" readonly style="display:none;">
                </div>
                <div class="form-group">
                    <label for="quantityMl">Quantity (ml)</label>
                    <input type="number" id="quantityMl" name="quantityMl" required>
                </div>
                <div class="form-group">
                    <label for="urgencyLevel">Urgency Level</label>
                    <select id="urgencyLevel" name="urgencyLevel">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="hospitalName">Hospital Name</label>
                    <input type="text" id="hospitalName" name="hospitalName">
                </div>
                <div class="form-group">
                    <label for="doctorName">Requesting Doctor's Name</label>
                    <input type="text" id="doctorName" name="doctorName">
                </div>
                <div class="form-group">
                    <label for="diagnosis">Diagnosis</label>
                    <textarea id="diagnosis" name="diagnosis" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="notes">Internal Notes</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>
                <!-- Status, Approved By, Approved Date, Fulfillment Date
                     are handled by the system/staff after request is submitted -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Submit Request</button>
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

    <script>
        // Auto-fill blood type when recipient is selected
        document.getElementById('recipientId').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var bloodType = selectedOption.getAttribute('data-bloodtype');
            
            var bloodTypeSelect = document.getElementById('bloodType');
            var bloodTypeReadonly = document.getElementById('bloodTypeReadonly');
            
            if (bloodType && bloodType !== '') {
                // Blood type exists - show readonly, hide dropdown
                bloodTypeReadonly.value = bloodType;
                bloodTypeSelect.value = bloodType;
                bloodTypeReadonly.style.display = 'block';
                bloodTypeSelect.style.display = 'none';
            } else {
                // Blood type unknown - show dropdown, hide readonly
                bloodTypeSelect.value = '';
                bloodTypeReadonly.value = '';
                bloodTypeReadonly.style.display = 'none';
                bloodTypeSelect.style.display = 'block';
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>