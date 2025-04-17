<?php
require 'db_connect.php'; // Include database connection

$id = $_GET['id'];
$sql = "UPDATE alumni_users SET is_approved = 1 WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
echo "Alumni approved!";
?>
