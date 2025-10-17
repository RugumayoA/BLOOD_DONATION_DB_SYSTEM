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

// Basic validation
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    header('Location: login.php?error=' . urlencode('Please enter your Username and Password.'));
    exit;
}

$username = trim($_POST['username']);
$password = $_POST['password'];

// Look up staff by username
$sql = "SELECT staff_id, first_name, last_name, email, password, status, employee_id FROM staff WHERE username = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();

    if ($staff && $staff['status'] === 'Active') {
        // Verify password using simple comparison (compatible with WampServer 2.0)
        if ($staff['password'] === hash('sha256', $password)) {
            // Successful login: store info in session
            $_SESSION['staff_id'] = $staff['staff_id'];
            $_SESSION['staff_name'] = $staff['first_name'] . ' ' . $staff['last_name'];
            $_SESSION['staff_email'] = $staff['email'];
            $_SESSION['staff_employee_id'] = $staff['employee_id'];
            $_SESSION['staff_username'] = $username;
            header('Location: index.php');
            exit;
        } else {
            header('Location: login.php?error=' . urlencode('Invalid credentials.'));
            exit;
        }
    } else {
        header('Location: login.php?error=' . urlencode('Invalid credentials or account inactive.'));
        exit;
    }

$conn->close();
