<?php
require 'db_connect.php';

$id = $_GET['id'];
$sql = "DELETE FROM alumni_users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
echo "Alumni rejected!";
?>
