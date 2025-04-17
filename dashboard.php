<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] == 'Admin') {
    header("Location: admin_dashboard.php");
} elseif ($_SESSION['role'] == 'Alumni') {
    header("Location: alumni_dashboard.php");
} else {
    header("Location: student_dashboard.php");
}
exit();
?>
