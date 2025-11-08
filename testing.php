<?php
session_start();

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'blood_donation';

$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) { die("DB connection failed: " . $mysqli->connect_error); }

// Redirect to login if not authenticated as staff
if (!isset($_SESSION['staff_id'])) {
    header('Location: login.php');
    exit;
}

function to_mysql_dt($val) {
    if (!$val) return null;
    $val = str_replace('T', ' ', $val);
    return $val . (strlen($val) === 16 ? ':00' : '');
}

$flash_ok = null; 
$flash_err = null;

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($stm = $mysqli->prepare("DELETE FROM testing_record WHERE test_id=?")) {
        $stm->bind_param("i", $id);
        if ($stm->execute()) {
            header("Location: testing.php?ok=deleted");
            exit;
        } else { $flash_err = "Delete failed: " . $stm->error; }
        $stm->close();
    } else { $flash_err = "Prepare failed: " . $mysqli->error; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode          = isset($_POST['mode']) ? $_POST['mode'] : 'create';
    $test_id       = isset($_POST['test_id']) ? (int)$_POST['test_id'] : 0;

    $donationId     = isset($_POST['donationId']) ? trim($_POST['donationId']) : '';
    $testDate       = to_mysql_dt(isset($_POST['testDate']) ? $_POST['testDate'] : '');
    $testType       = isset($_POST['testType']) ? trim($_POST['testType']) : '';
    $testResult     = isset($_POST['testResult']) ? trim($_POST['testResult']) : '';
    $staffIdRaw     = isset($_POST['staffId']) ? trim($_POST['staffId']) : '';
    $testNotes      = isset($_POST['testNotes']) ? trim($_POST['testNotes']) : '';
    $retestRequired = isset($_POST['retestRequired']) ? trim($_POST['retestRequired']) : 'No';
    $retestDate     = to_mysql_dt(isset($_POST['retestDate']) ? $_POST['retestDate'] : '');

    $staffId    = ($staffIdRaw === '') ? null : (int)$staffIdRaw;
    $testNotes  = ($testNotes === '') ? null : $testNotes;
    $retestDate = ($retestDate === '') ? null : $retestDate;

    if ($donationId === '' || $testDate === '' || $testType === '' || $testResult === '') {
        $flash_err = "Please fill all required fields.";
    } else if ($mode === 'update' && $test_id > 0) {
        $sql = "UPDATE testing_record
                SET donation_id=?, test_date=?, test_type=?, test_result=?, staff_id=?, test_notes=?, retest_required=?, retest_date=?
                WHERE test_id=?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("isssisssi", $donationId, $testDate, $testType, $testResult, $staffId, $testNotes, $retestRequired, $retestDate, $test_id);
            if ($stmt->execute()) {
                header("Location: testing.php?ok=updated");
                exit;
            } else { $flash_err = "Update failed: " . $stmt->error; }
            $stmt->close();
        } else { $flash_err = "Prepare failed: " . $mysqli->error; }
    } else {
        $sql = "INSERT INTO testing_record
                (donation_id, test_date, test_type, test_result, staff_id, test_notes, retest_required, retest_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("isssisss", $donationId, $testDate, $testType, $testResult, $staffId, $testNotes, $retestRequired, $retestDate);
            if ($stmt->execute()) {
                header("Location: testing.php?ok=created");
                exit;
            } else { $flash_err = "Insert failed: " . $stmt->error; }
            $stmt->close();
        } else { $flash_err = "Prepare failed: " . $mysqli->error; }
    }
}

if (isset($_GET['ok'])) {
    if ($_GET['ok'] === 'created') $flash_ok = "Test record added successfully!";
    if ($_GET['ok'] === 'updated') $flash_ok = "Test record updated successfully!";
    if ($_GET['ok'] === 'deleted') $flash_ok = "Test record deleted successfully!";
}

$edit_row = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($st = $mysqli->prepare("SELECT * FROM testing_record WHERE test_id=?")) {
        $st->bind_param("i", $id);
        if ($st->execute()) { $res = $st->get_result(); $edit_row = $res->fetch_assoc(); $res->free(); }
        $st->close();
    }
}

$rows = array();
$listSql = "SELECT test_id, donation_id, test_date, test_type, test_result, staff_id, retest_required
            FROM testing_record
            ORDER BY test_date DESC, test_id DESC
            LIMIT 200";
if ($res = $mysqli->query($listSql)) {
    while ($r = $res->fetch_assoc()) { $rows[] = $r; }
    $res->free();
}

// Fetch all donations for dropdown
$donationsSql = "SELECT d.donation_id, d.donation_date, d.blood_volume_ml, 
                 dn.first_name, dn.last_name, dn.blood_type 
                 FROM donation_record d 
                 LEFT JOIN donor dn ON d.donor_id = dn.donor_id 
                 ORDER BY d.donation_date DESC";
$donationsResult = $mysqli->query($donationsSql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Testing Records - Blood Donation DMS</title>
<link rel="stylesheet" href="style.css">
<style>
.alert{padding:10px;border-radius:6px;margin:10px 0;position:relative;overflow:hidden}
.alert.success{background:#e6ffed;border:1px solid #a7f3d0;color:#065f46}
.alert.danger{background:#ffe6e6;border:1px solid #ffb3b3;color:#7f1d1d}
.bar{position:absolute;left:0;bottom:0;height:4px;background:#10b981;animation:fill 1.2s linear forwards}
.alert.danger .bar{background:#ef4444}
@keyframes fill{from{width:0}to{width:100%}}
.icon-btn{background:none;border:none;color:#b91c1c;cursor:pointer;font-size:18px}
.icon-edit{color:#1f2937;text-decoration:none}

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
                    <a href="sessions.php" class="nav-item">
                        Sessions
                    </a>
                    <a href="testing.php" class="nav-item active">
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
                    <span style="color: #333; font-size: 18px; font-weight: 500;">Testing</span>
                </div>
            </div>
  <?php if ($flash_ok): ?>
    <div class="alert success"><?php echo htmlspecialchars($flash_ok); ?><div class="bar"></div></div>
  <?php endif; ?>
  <?php if ($flash_err): ?>
    <div class="alert danger"><?php echo htmlspecialchars($flash_err); ?><div class="bar"></div></div>
  <?php endif; ?>

  <section class="mb-30">
    <h2>All Testing Records</h2>
    <div class="data-table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Test ID</th>
            <th>Donation ID</th>
            <th>Test Date</th>
            <th>Test Type</th>
            <th>Result</th>
            <th>Staff ID</th>
            <th>Retest Required?</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="8" style="text-align:center;">No records yet.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td><?php echo (int)$r['test_id']; ?></td>
            <td><?php echo (int)$r['donation_id']; ?></td>
            <td><?php echo htmlspecialchars($r['test_date']); ?></td>
            <td><?php echo htmlspecialchars($r['test_type']); ?></td>
            <td><?php echo htmlspecialchars($r['test_result']); ?></td>
            <td><?php echo ($r['staff_id'] !== null ? (int)$r['staff_id'] : ''); ?></td>
            <td><?php echo htmlspecialchars($r['retest_required']); ?></td>
            <td>
              <a class="icon-edit" href="testing.php?action=edit&id=<?php echo (int)$r['test_id']; ?>">Edit</a>
              &nbsp;|&nbsp;
              <a href="testing.php?action=delete&id=<?php echo (int)$r['test_id']; ?>" onclick="return confirm('Delete this test record?');" title="Delete">🗑</a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="form-container">
    <h2><?php echo $edit_row ? 'Edit Testing Record' : 'Add New Testing Record'; ?></h2>
    <form action="testing.php" method="POST">
      <input type="hidden" name="mode" value="<?php echo $edit_row ? 'update' : 'create'; ?>" />
      <?php if ($edit_row): ?>
        <input type="hidden" name="test_id" value="<?php echo (int)$edit_row['test_id']; ?>" />
      <?php endif; ?>

      <div class="form-group">
        <label for="donationId">Select Donation</label>
        <select id="donationId" name="donationId" required>
          <option value="">Select Donation</option>
          <?php
          if ($donationsResult && $donationsResult->num_rows > 0) {
            while($donation = $donationsResult->fetch_assoc()) {
              $bloodTypeDisplay = $donation["blood_type"] ? $donation["blood_type"] : "Unknown";
              $selected = ($edit_row && $edit_row['donation_id'] == $donation['donation_id']) ? 'selected' : '';
              echo "<option value='" . $donation["donation_id"] . "' data-bloodtype='" . $donation["blood_type"] . "' $selected>";
              echo "ID: " . $donation["donation_id"] . " - " . $donation["first_name"] . " " . $donation["last_name"] . " (" . $bloodTypeDisplay . ") - " . $donation["donation_date"];
              echo "</option>";
            }
          }
          ?>
        </select>
      </div>
      <div class="form-group">
        <label for="testDate">Test Date & Time</label>
        <input type="datetime-local" id="testDate" name="testDate" required
               value="<?php
                    if ($edit_row && $edit_row['test_date']) {
                        // convert "YYYY-MM-DD HH:MM:SS" -> "YYYY-MM-DDTHH:MM"
                        $ts = substr($edit_row['test_date'], 0, 16);
                        echo htmlspecialchars(str_replace(' ', 'T', $ts));
                    }
               ?>">
      </div>
      <div class="form-group">
        <label for="testType">Test Type</label>
        <input type="text" id="testType" name="testType" required
               value="<?php echo $edit_row ? htmlspecialchars($edit_row['test_type']) : ''; ?>">
      </div>
      <div class="form-group">
        <label for="testResult">Test Result</label>
        <select id="testResult" name="testResult" required>
          <?php
            $cur = $edit_row ? $edit_row['test_result'] : '';
            $opts = array('Positive','Negative','Indeterminate');
            foreach ($opts as $o) {
                $sel = ($cur===$o)?' selected':'';
                echo '<option value="'.$o.'"'.$sel.'>'.$o.'</option>';
            }
          ?>
        </select>
      </div>
      <div class="form-group">
        <label for="staffId">Staff ID (Who performed test)</label>
        <input type="number" id="staffId" name="staffId"
               value="<?php echo $edit_row && $edit_row['staff_id']!==null ? (int)$edit_row['staff_id'] : ''; ?>">
      </div>
      <div class="form-group">
        <label for="testNotes">Test Notes</label>
        <textarea id="testNotes" name="testNotes" rows="3"><?php echo $edit_row ? htmlspecialchars($edit_row['test_notes']) : ''; ?></textarea>
      </div>
      <div class="form-group">
        <label for="retestRequired">Retest Required?</label>
        <select id="retestRequired" name="retestRequired">
          <?php
            $cur2 = $edit_row ? $edit_row['retest_required'] : 'No';
            $opts2 = array('No','Yes');
            foreach ($opts2 as $o2) {
                $sel2 = ($cur2===$o2)?' selected':'';
                echo '<option value="'.$o2.'"'.$sel2.'>'.$o2.'</option>';
            }
          ?>
        </select>
      </div>
      <div class="form-group">
        <label for="retestDate">Retest Date (if required)</label>
        <input type="datetime-local" id="retestDate" name="retestDate"
               value="<?php
                    if ($edit_row && $edit_row['retest_date']) {
                        $ts2 = substr($edit_row['retest_date'], 0, 16);
                        echo htmlspecialchars(str_replace(' ', 'T', $ts2));
                    }
               ?>">
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?php echo $edit_row ? 'Update' : 'Add Test Record'; ?></button>
        <a class="btn btn-secondary" href="testing.php">Reset</a>
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
        </div>
    </div>
</div>
</body>
</html>
