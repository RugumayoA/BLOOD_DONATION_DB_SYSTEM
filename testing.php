<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'blood_donation';

$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) { die("DB connection failed: " . $mysqli->connect_error); }

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
        <li><a href="testing.php" class="active">Testing</a></li>
        <li><a href="transfusions.php">Transfusions</a></li>
        <li><a href="notifications.php">Notifications</a></li>
      </ul>
    </nav>
  </div>
</header>

<section class="page-title"><div class="container"><h1>Testing Records</h1></div></section>

<main class="container">
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
        <label for="donationId">Donation ID</label>
        <input type="number" id="donationId" name="donationId" required
               value="<?php echo $edit_row ? (int)$edit_row['donation_id'] : ''; ?>">
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
    <p>&copy; <?php echo date("Y"); ?> Blood Donation System. All rights reserved.</p>
    <p>Powered by Group-G</p>
  </div>
</footer>
</body>
</html>
