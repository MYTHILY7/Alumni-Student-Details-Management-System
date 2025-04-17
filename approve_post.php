<?php
include 'db_connect.php';

$post_id = $_POST['post_id'];
$sql = "UPDATE posts SET status='Approved' WHERE id='$post_id'";

if ($conn->query($sql)) {
    echo "Post approved!";
} else {
    echo "Error: " . $conn->error;
}
?>
