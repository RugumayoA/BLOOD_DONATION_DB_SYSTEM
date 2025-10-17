<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['staff_name'])) {
    header('Location: login.php');
    exit;
}

// Export key data for presentation
$export_data = array();

// 1. System Summary
$summary_sql = "SELECT 
    'Total Donors' as metric, COUNT(*) as value FROM donor
    UNION ALL
    SELECT 'Active Donors', COUNT(*) FROM donor WHERE status = 'Active'
    UNION ALL
    SELECT 'Total Recipients', COUNT(*) FROM recipient
    UNION ALL
    SELECT 'Active Recipients', COUNT(*) FROM recipient WHERE status = 'Active'
    UNION ALL
    SELECT 'Total Donations', COUNT(*) FROM donation_record
    UNION ALL
    SELECT 'Total Blood Units', COUNT(*) FROM blood_inventory
    UNION ALL
    SELECT 'Available Units', COUNT(*) FROM blood_inventory WHERE status = 'Available'
    UNION ALL
    SELECT 'Pending Requests', COUNT(*) FROM blood_request WHERE status = 'Pending'
    UNION ALL
    SELECT 'Approved Requests', COUNT(*) FROM blood_request WHERE status = 'Approved'";

$result = $conn->query($summary_sql);
$export_data['summary'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : array();

// 2. Blood Type Statistics
$blood_stats_sql = "SELECT 
    blood_type,
    COUNT(*) as donor_count,
    (SELECT COUNT(*) FROM donation_record dr JOIN donor d ON dr.donor_id = d.donor_id WHERE d.blood_type = donor.blood_type) as donation_count,
    (SELECT SUM(dr.blood_volume_ml) FROM donation_record dr JOIN donor d ON dr.donor_id = d.donor_id WHERE d.blood_type = donor.blood_type) as total_volume
    FROM donor 
    WHERE blood_type IS NOT NULL 
    GROUP BY blood_type 
    ORDER BY donor_count DESC";

$result = $conn->query($blood_stats_sql);
$export_data['blood_stats'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : array();

// 3. Recent Donations (Last 30 days)
$recent_donations_sql = "SELECT 
    d.first_name,
    d.last_name,
    d.blood_type,
    dr.donation_date,
    dr.blood_volume_ml,
    dr.hemoglobin_level
    FROM donation_record dr
    JOIN donor d ON dr.donor_id = d.donor_id
    WHERE dr.donation_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ORDER BY dr.donation_date DESC";

$result = $conn->query($recent_donations_sql);
$export_data['recent_donations'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : array();

// 4. Inventory Status
$inventory_status_sql = "SELECT 
    blood_type,
    status,
    COUNT(*) as unit_count,
    SUM(quantity_ml) as total_ml,
    MIN(expiry_date) as earliest_expiry,
    MAX(expiry_date) as latest_expiry
    FROM blood_inventory 
    GROUP BY blood_type, status
    ORDER BY blood_type, status";

$result = $conn->query($inventory_status_sql);
$export_data['inventory_status'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : array();

$conn->close();

// Set headers for download
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="blood_donation_data_' . date('Y-m-d') . '.json"');

echo json_encode($export_data, JSON_PRETTY_PRINT);
?>
