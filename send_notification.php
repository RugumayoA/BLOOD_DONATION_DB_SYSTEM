<?php
// Include database connection
require_once 'config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Get recipient_id based on recipient_type
        if ($_POST['recipient_type'] == 'donor') {
            $recipient_id = $_POST['donor_id'];
        } elseif ($_POST['recipient_type'] == 'recipient') {
            $recipient_id = $_POST['recipient_id'];
        } else {
            $recipient_id = $_POST['staff_id'];
        }
        
        // Insert notification
        $sql = "INSERT INTO notification (recipient_type, recipient_id, notification_type, title, message, sent_date, sent_time, status, delivery_method) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
            $_POST['recipient_type'],
            $recipient_id,
            $_POST['notification_type'],
            $_POST['title'],
            $_POST['message'],
            date('Y-m-d'),
            date('H:i:s'),
            'Queued',
            $_POST['notification_type']
        ));
        
        // Redirect back to notifications page with success message
        header("Location: notifications.php?success=1");
        exit;
        
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Get all donors for dropdown
$donors_sql = "SELECT donor_id, first_name, last_name, blood_type FROM donor ORDER BY last_name, first_name";
$donors_stmt = $pdo->prepare($donors_sql);
$donors_stmt->execute();
$donors = $donors_stmt->fetchAll();

// Get all recipients for dropdown
$recipients_sql = "SELECT recipient_id, first_name, last_name FROM recipient ORDER BY last_name, first_name";
$recipients_stmt = $pdo->prepare($recipients_sql);
$recipients_stmt->execute();
$recipients = $recipients_stmt->fetchAll();

// Get all staff for dropdown
$staff_sql = "SELECT staff_id, first_name, last_name FROM staff ORDER BY last_name, first_name";
$staff_stmt = $pdo->prepare($staff_sql);
$staff_stmt->execute();
$staff_members = $staff_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notification - Blood Donation DMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .send-notification-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .recipient-dropdown {
            display: none;
        }
        
        .recipient-dropdown.active {
            display: block;
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
                    <li><a href="sessions.php">Sessions</a></li>
                    <li><a href="testing.php">Testing</a></li>
                    <li><a href="transfusions.php">Transfusions</a></li>
                    <li><a href="notifications.php" class="active">Notifications</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="page-title">
        <div class="container">
            <h1>Send New Notification</h1>
        </div>
    </div>

    <div class="send-notification-container">
        <?php if (isset($error_message)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="send_notification.php">
                <div class="form-group">
                    <label for="recipient_type">Recipient Type *</label>
                    <select id="recipient_type" name="recipient_type" required onchange="showRecipientDropdown()">
                        <option value="">Select Recipient Type</option>
                        <option value="donor">Donor</option>
                        <option value="recipient">Recipient</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>

                <!-- Donor Dropdown -->
                <div class="form-group recipient-dropdown" id="donor-dropdown">
                    <label for="donor_id">Select Donor *</label>
                    <select id="donor_id" name="donor_id">
                        <option value="">Choose a donor</option>
                        <?php foreach ($donors as $donor): ?>
                            <option value="<?php echo $donor['donor_id']; ?>">
                                <?php echo htmlspecialchars($donor['first_name'] . ' ' . $donor['last_name']) . ' - Blood Type: ' . $donor['blood_type'] . ' (ID: ' . $donor['donor_id'] . ')'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Recipient Dropdown -->
                <div class="form-group recipient-dropdown" id="recipient-dropdown">
                    <label for="recipient_id">Select Recipient *</label>
                    <select id="recipient_id" name="recipient_id">
                        <option value="">Choose a recipient</option>
                        <?php foreach ($recipients as $recipient): ?>
                            <option value="<?php echo $recipient['recipient_id']; ?>">
                                <?php echo htmlspecialchars($recipient['first_name'] . ' ' . $recipient['last_name']) . ' (ID: ' . $recipient['recipient_id'] . ')'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Staff Dropdown -->
                <div class="form-group recipient-dropdown" id="staff-dropdown">
                    <label for="staff_id">Select Staff *</label>
                    <select id="staff_id" name="staff_id">
                        <option value="">Choose a staff member</option>
                        <?php foreach ($staff_members as $staff): ?>
                            <option value="<?php echo $staff['staff_id']; ?>">
                                <?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) . ' (ID: ' . $staff['staff_id'] . ')'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notification_type">Notification Type *</label>
                    <select id="notification_type" name="notification_type" required>
                        <option value="Email">Email</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="title">Notification Title *</label>
                    <input type="text" id="title" name="title" required placeholder="e.g., Blood Donation Reminder">
                </div>

                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" rows="6" required placeholder="Enter your notification message here..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Send Notification</button>
                    <a href="notifications.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <footer class="main-footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Blood Donation DMS. All rights reserved.</p>
            <p>Powered by Compassion</p>
        </div>
    </footer>

    <script>
        function showRecipientDropdown() {
            // Hide all dropdowns
            document.getElementById('donor-dropdown').classList.remove('active');
            document.getElementById('recipient-dropdown').classList.remove('active');
            document.getElementById('staff-dropdown').classList.remove('active');
            
            // Clear all dropdown values
            document.getElementById('donor_id').value = '';
            document.getElementById('recipient_id').value = '';
            document.getElementById('staff_id').value = '';
            
            // Remove required attribute from all
            document.getElementById('donor_id').removeAttribute('required');
            document.getElementById('recipient_id').removeAttribute('required');
            document.getElementById('staff_id').removeAttribute('required');
            
            // Show selected dropdown
            var recipientType = document.getElementById('recipient_type').value;
            
            if (recipientType === 'donor') {
                document.getElementById('donor-dropdown').classList.add('active');
                document.getElementById('donor_id').setAttribute('required', 'required');
            } else if (recipientType === 'recipient') {
                document.getElementById('recipient-dropdown').classList.add('active');
                document.getElementById('recipient_id').setAttribute('required', 'required');
            } else if (recipientType === 'staff') {
                document.getElementById('staff-dropdown').classList.add('active');
                document.getElementById('staff_id').setAttribute('required', 'required');
            }
        }
        
        // Form validation before submit
        document.querySelector('form').addEventListener('submit', function(e) {
            var recipientType = document.getElementById('recipient_type').value;
            var selectedId = null;
            
            if (recipientType === 'donor') {
                selectedId = document.getElementById('donor_id').value;
            } else if (recipientType === 'recipient') {
                selectedId = document.getElementById('recipient_id').value;
            } else if (recipientType === 'staff') {
                selectedId = document.getElementById('staff_id').value;
            }
            
            if (!selectedId) {
                e.preventDefault();
                alert('Please select a ' + recipientType + ' from the dropdown.');
                return false;
            }
        });
    </script>
</body>
</html>

