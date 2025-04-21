<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

// Fetch alumni data
$query = "SELECT id, name, department, passout_year, company, linkedin FROM users WHERE role = 'Alumni'";
$result = $conn->query($query);
?>

<div class="container mt-4">
    <h2>🎓 Alumni List</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>S.No.</th>
                <th>Name</th>
                <th>Department</th>
                <th>Pass-out Year</th>
                <th>Company</th>
                <th>LinkedIn</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $serialNo = 1;
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $serialNo++ . '</td>';
                echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                echo '<td>' . htmlspecialchars($row['department']) . '</td>';
                echo '<td>' . htmlspecialchars($row['passout_year']) . '</td>';
                echo '<td>' . htmlspecialchars($row['company']) . '</td>';
                echo '<td><a href="' . htmlspecialchars($row['linkedin']) . '" target="_blank">LinkedIn Profile</a></td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
        <div class="d-flex justify-content-end mt-3">
            <a href="export_alumni_excel.php" class="btn btn-outline-primary">Download Excel</a>
        </div>
    <?php endif; ?>
</div>

<?php
$conn->close();
?>