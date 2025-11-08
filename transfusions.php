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

function tf_to_mysql_dt($val) {
    if (!$val) return null;
    $val = str_replace('T', ' ', $val);
    return $val . (strlen($val) === 16 ? ':00' : '');
}

function abo_only($t) {
    $t = strtoupper(trim($t));
    if (strpos($t, 'AB') === 0) return 'AB';
    if (strpos($t, 'A') === 0)  return 'A';
    if (strpos($t, 'B') === 0)  return 'B';
    return 'O';
}

/* ABO compatibility: can DONOR be given to RECIPIENT? */
function abo_compatible($donor, $recipient) {
    $d = abo_only($donor);
    $r = abo_only($recipient);
    if ($r === 'O') return ($d === 'O');                         // O receives only O
    if ($r === 'A') return ($d === 'O' || $d === 'A');           // A receives O,A
    if ($r === 'B') return ($d === 'O' || $d === 'B');           // B receives O,B
    if ($r === 'AB') return ($d === 'O' || $d === 'A' || $d === 'B' || $d === 'AB'); // AB receives all
    return false;
}

$tf_flash_ok = null;
$tf_flash_err = null;

if (isset($_GET['action']) && $_GET['action'] === 'complete' && isset($_GET['id'])) {
    $tid = (int)$_GET['id'];
    $info = null;
    if ($st = $mysqli->prepare("SELECT request_id, inventory_id, quantity_ml FROM blood_transfusion WHERE transfusion_id=?")) {
        $st->bind_param("i", $tid);
        if ($st->execute()) {
            $res = $st->get_result(); $info = $res->fetch_assoc(); $res->free();
        }
        $st->close();
    }
    if ($info) {
        $reqId = (int)$info['request_id'];
        $invId = (int)$info['inventory_id'];

        if ($u = $mysqli->prepare("UPDATE blood_transfusion SET status='Completed' WHERE transfusion_id=?")) {
            $u->bind_param("i", $tid);
            if ($u->execute()) {
                if ($v = $mysqli->prepare("UPDATE blood_inventory SET status='Transfused', quantity_ml=0 WHERE inventory_id=?")) {
                    $v->bind_param("i", $invId); $v->execute(); $v->close();
                }

                $request_needed = null;
                if ($rq = $mysqli->prepare("SELECT quantity_ml FROM blood_request WHERE request_id=?")) {
                    $rq->bind_param("i", $reqId);
                    if ($rq->execute()) {
                        $rres = $rq->get_result();
                        if ($row = $rres->fetch_assoc()) { $request_needed = (int)$row['quantity_ml']; }
                        $rres->free();
                    }
                    $rq->close();
                }
                if ($request_needed !== null) {
                    $delivered = 0;
                    if ($rs = $mysqli->prepare("SELECT COALESCE(SUM(quantity_ml),0) AS delivered FROM blood_transfusion WHERE request_id=? AND status='Completed'")) {
                        $rs->bind_param("i", $reqId);
                        if ($rs->execute()) {
                            $r = $rs->get_result()->fetch_assoc();
                            $delivered = (int)$r['delivered'];
                        }
                        $rs->close();
                    }
                    if ($delivered >= $request_needed) {
                        if ($rf = $mysqli->prepare("UPDATE blood_request SET status='Fulfilled' WHERE request_id=?")) {
                            $rf->bind_param("i", $reqId); $rf->execute(); $rf->close();
                        }
                    }
                }
                $tf_flash_ok = "Transfusion #".$tid." marked Completed.";
            } else {
                $tf_flash_err = "Could not complete transfusion: " . $u->error;
            }
            $u->close();
        }
    } else { $tf_flash_err = "Transfusion not found."; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tf_form'])) {
    $reqId      = isset($_POST['request_id']) ? trim($_POST['request_id']) : '';
    $invId      = isset($_POST['inventory_id']) ? trim($_POST['inventory_id']) : '';
    $tfDate     = tf_to_mysql_dt(isset($_POST['transfusion_date']) ? $_POST['transfusion_date'] : '');
    $qty        = isset($_POST['quantity_ml']) ? trim($_POST['quantity_ml']) : '';
    $staffRaw   = isset($_POST['staff_id']) ? trim($_POST['staff_id']) : '';
    $hospital   = isset($_POST['hospital_name']) ? trim($_POST['hospital_name']) : '';
    $room       = isset($_POST['patient_room']) ? trim($_POST['patient_room']) : '';
    $status     = isset($_POST['status']) ? trim($_POST['status']) : 'Started';
    $compl      = isset($_POST['complications']) ? trim($_POST['complications']) : '';
    $notes      = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    $staffId = ($staffRaw === '') ? null : (int)$staffRaw;
    $room    = ($room === '') ? null : $room;
    $compl   = ($compl === '') ? null : $compl;
    $notes   = ($notes === '') ? null : $notes;

    if ($reqId === '' || $invId === '' || $tfDate === '' || $qty === '' || $hospital === '' || $status === '') {
        $tf_flash_err = "Please fill all required fields.";
    } else {
        // fetch recipient blood_type from request
        $rec_bt = null;
        if ($pr = $mysqli->prepare("SELECT blood_type FROM blood_request WHERE request_id=?")) {
            $pr->bind_param("i", $reqId);
            if ($pr->execute()) { $g = $pr->get_result(); if ($x = $g->fetch_assoc()) $rec_bt = $x['blood_type']; $g->free(); }
            $pr->close();
        }
        // fetch donor blood_type from inventory
        $don_bt = null;
        if ($pi = $mysqli->prepare("SELECT blood_type FROM blood_inventory WHERE inventory_id=?")) {
            $pi->bind_param("i", $invId);
            if ($pi->execute()) { $g2 = $pi->get_result(); if ($y = $g2->fetch_assoc()) $don_bt = $y['blood_type']; $g2->free(); }
            $pi->close();
        }
        if ($rec_bt === null || $don_bt === null) {
            $tf_flash_err = "Invalid Request ID or Inventory ID (not found).";
        } else if (!abo_compatible($don_bt, $rec_bt)) {
            $tf_flash_err = "Incompatible blood types: Donor ".$don_bt." cannot transfuse to Recipient ".$rec_bt.".";
        } else {
            $sql = "INSERT INTO blood_transfusion
                    (request_id, inventory_id, transfusion_date, quantity_ml, staff_id, hospital_name, patient_room, status, complications, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("iisiisssss", $reqId, $invId, $tfDate, $qty, $staffId, $hospital, $room, $status, $compl, $notes);
                if ($stmt->execute()) {
                    // reserve unit if starting
                    if ($status === 'Started') {
                        if ($u = $mysqli->prepare("UPDATE blood_inventory SET status='Reserved' WHERE inventory_id=? AND status IN ('Available','Reserved')")) {
                            $u->bind_param("i", $invId); $u->execute(); $u->close();
                        }
                    }
                    header("Location: transfusions.php?tf_ok=1");
                    exit;
                } else { $tf_flash_err = "Insert failed: " . $stmt->error; }
                $stmt->close();
            } else { $tf_flash_err = "Prepare failed: " . $mysqli->error; }
        }
    }
}

if (isset($_GET['tf_ok'])) { $tf_flash_ok = "Transfusion record saved."; }

/* ========= FETCH TRANSFUSIONS (JOIN INVENTORY FOR BLOOD TYPE/EXPIRY) ========= */
$rows = array();
$sql = "SELECT t.transfusion_id, t.request_id, t.inventory_id, t.transfusion_date, t.quantity_ml,
               t.hospital_name, t.status,
               i.blood_type, i.expiry_date
        FROM blood_transfusion t
        LEFT JOIN blood_inventory i ON i.inventory_id = t.inventory_id
        ORDER BY t.transfusion_date DESC, t.transfusion_id DESC
        LIMIT 200";
if ($res = $mysqli->query($sql)) {
    while ($r = $res->fetch_assoc()) { $rows[] = $r; }
    $res->free();
}

$total_count = 0;
if ($rs = $mysqli->query("SELECT COUNT(*) AS c FROM blood_transfusion")) {
    $obj = $rs->fetch_assoc(); $total_count = (int)$obj['c']; $rs->free();
}

// Fetch all blood requests for dropdown
$requestsSql = "SELECT br.request_id, br.blood_type, br.quantity_ml, br.urgency_level,
                r.first_name, r.last_name, br.hospital_name
                FROM blood_request br 
                LEFT JOIN recipient r ON br.recipient_id = r.recipient_id 
                WHERE br.status IN ('Approved', 'Pending')
                ORDER BY br.request_date DESC";
$requestsResult = $mysqli->query($requestsSql);

// Fetch all available blood inventory for dropdown
$inventorySql = "SELECT inventory_id, blood_type, quantity_ml, expiry_date, collection_date, status
                 FROM blood_inventory 
                 WHERE status IN ('Available', 'Quarantined') 
                 ORDER BY expiry_date ASC, collection_date DESC";
$inventoryResult = $mysqli->query($inventorySql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Blood Transfusions - Blood Donation DMS</title>
<link rel="stylesheet" href="style.css">
<style>
.alert{padding:10px;border-radius:6px;margin:10px 0;position:relative;overflow:hidden}
.alert.success{background:#e6ffed;border:1px solid #a7f3d0;color:#065f46}
.alert.danger{background:#ffe6e6;border:1px solid #ffb3b3;color:#7f1d1d}
.bar{position:absolute;left:0;bottom:0;height:4px;background:#10b981;animation:fill 1.2s linear forwards}
.alert.danger .bar{background:#ef4444}
@keyframes fill{from{width:0}to{width:100%}}

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
                    <a href="testing.php" class="nav-item">
                        Testing
                    </a>
                    <a href="transfusions.php" class="nav-item active">
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
                    <span style="color: #333; font-size: 18px; font-weight: 500;">Transfusions</span>
                </div>
            </div>
  <?php if ($tf_flash_ok): ?>
    <div class="alert success"><?php echo htmlspecialchars($tf_flash_ok); ?><div class="bar"></div></div>
  <?php endif; ?>
  <?php if ($tf_flash_err): ?>
    <div class="alert danger"><?php echo htmlspecialchars($tf_flash_err); ?><div class="bar"></div></div>
  <?php endif; ?>

  <section class="mb-30">
    <h2>All Transfusion Records</h2>
    <div class="data-table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Transfusion ID</th>
            <th>Request ID</th>
            <th>Inventory ID</th>
            <th>Blood Type</th>
            <th>Expiry</th>
            <th>Transfusion Date</th>
            <th>Quantity (ml)</th>
            <th>Hospital</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="10" style="text-align:center;">No transfusions recorded.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td><?php echo (int)$r['transfusion_id']; ?></td>
            <td><?php echo (int)$r['request_id']; ?></td>
            <td><?php echo (int)$r['inventory_id']; ?></td>
            <td><?php echo htmlspecialchars($r['blood_type']); ?></td>
            <td><?php echo htmlspecialchars($r['expiry_date']); ?></td>
            <td><?php echo htmlspecialchars($r['transfusion_date']); ?></td>
            <td><?php echo (int)$r['quantity_ml']; ?></td>
            <td><?php echo htmlspecialchars($r['hospital_name']); ?></td>
            <td><?php echo htmlspecialchars($r['status']); ?></td>
            <td>
              <?php if ($r['status'] !== 'Completed'): ?>
                <a href="transfusions.php?action=complete&id=<?php echo (int)$r['transfusion_id']; ?>" onclick="return confirm('Mark as Completed?');">Complete</a>
              <?php else: ?>
                <span style="color:gray;">—</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="form-container">
    <h2>Add New Transfusion</h2>
    <form action="transfusions.php" method="POST">
      <input type="hidden" name="tf_form" value="1" />
      <div class="form-group">
        <label for="request_id">Select Blood Request</label>
        <select id="request_id" name="request_id" required>
          <option value="">Select Blood Request</option>
          <?php
          if ($requestsResult && $requestsResult->num_rows > 0) {
            while($request = $requestsResult->fetch_assoc()) {
              echo "<option value='" . $request["request_id"] . "' data-bloodtype='" . $request["blood_type"] . "' data-quantity='" . $request["quantity_ml"] . "'>";
              echo "ID: " . $request["request_id"] . " - " . $request["first_name"] . " " . $request["last_name"] . " (" . $request["blood_type"] . ") - " . $request["quantity_ml"] . "ml - " . $request["urgency_level"];
              echo "</option>";
            }
          }
          ?>
        </select>
      </div>
      <div class="form-group">
        <label for="inventory_id">Select Blood Unit</label>
        <select id="inventory_id" name="inventory_id" required>
          <option value="">Select Blood Unit</option>
          <?php
          if ($inventoryResult && $inventoryResult->num_rows > 0) {
            while($inventory = $inventoryResult->fetch_assoc()) {
              echo "<option value='" . $inventory["inventory_id"] . "' data-bloodtype='" . $inventory["blood_type"] . "' data-quantity='" . $inventory["quantity_ml"] . "'>";
              echo "ID: " . $inventory["inventory_id"] . " - " . $inventory["blood_type"] . " - " . $inventory["quantity_ml"] . "ml - " . $inventory["status"] . " - Expires: " . $inventory["expiry_date"];
              echo "</option>";
            }
          }
          ?>
        </select>
      </div>
      <div class="form-group">
        <label for="transfusion_date">Transfusion Date & Time</label>
        <input type="datetime-local" id="transfusion_date" name="transfusion_date" required />
      </div>
      <div class="form-group">
        <label for="quantity_ml">Quantity (ml)</label>
        <input type="number" id="quantity_ml" name="quantity_ml" required />
      </div>
      <div class="form-group">
        <label for="staff_id">Staff ID</label>
        <input type="number" id="staff_id" name="staff_id" />
      </div>
      <div class="form-group">
        <label for="hospital_name">Hospital</label>
        <input type="text" id="hospital_name" name="hospital_name" required />
      </div>
      <div class="form-group">
        <label for="patient_room">Patient Room</label>
        <input type="text" id="patient_room" name="patient_room" />
      </div>
      <div class="form-group">
        <label for="status">Status</label>
        <select id="status" name="status" required>
          <option value="Started">Started</option>
          <option value="Completed">Completed</option>
          <option value="Complications">Complications</option>
          <option value="Cancelled">Cancelled</option>
        </select>
      </div>
      <div class="form-group">
        <label for="complications">Complications</label>
        <input type="text" id="complications" name="complications" />
      </div>
      <div class="form-group">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" rows="3"></textarea>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Add Transfusion</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
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
