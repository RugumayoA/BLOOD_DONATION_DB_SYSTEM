<?php
// WampServer 2.0 Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "blood_donation";

// Create MySQLi connection for WampServer 2.0
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8 for proper character encoding
$conn->set_charset("utf8");

echo "<!-- Database connection successful -->";
?>