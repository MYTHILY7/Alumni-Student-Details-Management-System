<?php
session_start();
include 'db_connect.php';

$user_id = $_SESSION['user_id'];
$sql = "SELECT message FROM notifications WHERE user_id = '$user_id' AND status = 'Unread'";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    echo "<p>{$row['message']}</p>";
}

$sql = "UPDATE notifications SET status = 'Read' WHERE user_id = '$user_id'";
$conn->query($sql);
?>
