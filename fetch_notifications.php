<?php
include 'db_connect.php';
session_start();

$user_id = $_SESSION['user_id'];

$sql = "SELECT message FROM notifications WHERE user_id = '$user_id' AND status = 'Unread'";
$result = $conn->query($sql);
$notifications = [];

while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

echo json_encode($notifications);
?>
