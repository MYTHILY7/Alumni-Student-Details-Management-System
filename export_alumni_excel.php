<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and has the 'Admin' role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    // If not an admin, deny access
    http_response_code(403); // Forbidden
    echo "Access denied. You do not have permission to download this file.";
    exit;
}

// Include database connection
include 'db_connect.php';

// Set headers to prompt download of Excel file
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=alumni_list.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Output column headers
echo "S.No.\tName\tDepartment\tPass-out Year\tCompany\tLinkedIn\n";

// Fetch alumni data
$query = "SELECT name, department, passout_year, company, linkedin FROM users WHERE role = 'Alumni'";
$result = $conn->query($query);

// Initialize serial number
$serialNo = 1;

// Output data rows
while ($row = $result->fetch_assoc()) {
    echo $serialNo++ . "\t" .
        $row['name'] . "\t" .
        $row['department'] . "\t" .
        $row['passout_year'] . "\t" .
        $row['company'] . "\t" .
        $row['linkedin'] . "\n";
}

// Close database connection
$conn->close();