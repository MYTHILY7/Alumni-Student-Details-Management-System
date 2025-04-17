<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "role";
$port = 3336;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
