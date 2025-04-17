<?php
include 'db_connect.php';

$post_id = $_POST['post_id'];
$sql = "UPDATE posts SET status='Rejected' WHERE id='$post_id'";

if ($conn->query($sql)) {
    echo "Post rejected!";
} else {
    echo "Error: " . $conn->error;
}
?>
