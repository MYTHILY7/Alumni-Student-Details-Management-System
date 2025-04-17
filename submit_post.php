<?php
include 'db_connect.php';
session_start();

$user_id = $_SESSION['user_id']; // Get logged-in user ID
$role = $_SESSION['role']; // Get user role (Admin, Alumni)
$content = $_POST['content'];

$status = ($role == "Admin") ? "Approved" : "Pending";

$sql = "INSERT INTO posts (user_id, content, status) VALUES ('$user_id', '$content', '$status')";
if ($conn->query($sql)) {
    echo "Post submitted successfully!";
} else {
    echo "Error: " . $conn->error;
}
?>
