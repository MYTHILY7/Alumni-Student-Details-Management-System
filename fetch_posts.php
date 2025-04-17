<?php
include 'db_connect.php';

$sql = "SELECT posts.id, posts.content, posts.created_at, users.name, users.role 
        FROM posts JOIN users ON posts.user_id = users.id WHERE posts.status = 'Approved'
        ORDER BY posts.created_at DESC";

$result = $conn->query($sql);
$posts = [];

while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}
echo json_encode($posts);
?>
