<?php
include 'db_connect.php';
session_start();

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];
$reaction = $_POST['reaction'];

$sql = "INSERT INTO reactions (post_id, user_id, reaction_type) VALUES ('$post_id', '$user_id', '$reaction')";
if ($conn->query($sql)) {
    echo "Reaction added!";
} else {
    echo "Error: " . $conn->error;
}
?>
