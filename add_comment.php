<?php
include 'db_connect.php';
session_start();

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];
$comment = $_POST['comment'];

$sql = "INSERT INTO comments (post_id, user_id, comment) VALUES ('$post_id', '$user_id', '$comment')";
if ($conn->query($sql)) {
    echo "Comment added!";
} else {
    echo "Error: " . $conn->error;
}
?>
