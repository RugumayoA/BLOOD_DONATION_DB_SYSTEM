<?php
// WampServer 2.0 Database configuration
$host = 'localhost';
$dbname = 'blood_donation'; // Your database name
$username = 'root'; // WampServer 2.0 default username
$password = ''; // WampServer 2.0 default password (empty)

try {
    // Create PDO connection for WampServer 2.0
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    echo "<!-- Database connection successful -->";
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>