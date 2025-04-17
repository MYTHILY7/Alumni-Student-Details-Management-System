<?php
require 'db_connect.php';

// Fetch pending alumni registrations
$sql = "SELECT * FROM alumni_users WHERE is_approved = 0";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Alumni Registrations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="d-flex">
        <!-- Sidebar -->
        <div id="sidebar" class="bg-dark text-white p-3" style="width: 250px; min-height: 100vh;">
            <h4 class="text-center mb-4">Admin Dashboard</h4>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a href="?page=dashboard" class="nav-link text-white">📊 Dashboard</a></li>
                <li class="nav-item mb-2"><a href="?page=create_post" class="nav-link text-white">➕ Create Admin Post</a></li>
                <li class="nav-item mb-2"><a href="?page=requests" class="nav-link text-white">📥 Requests</a></li>
                <li class="nav-item mb-2"><a href="pending_alumni.php" class="nav-link text-white">📂 Pending Alumni</a></li>
                <li class="nav-item mb-2"><a href="?page=previous_posts" class="nav-link text-white">📃 Previous Posts</a></li>
                <li class="nav-item mb-2"><a href="?page=all_posts" class="nav-link text-white">📂 All Posts</a></li>
            </ul>
            <a href="logout.php" class="btn btn-danger w-100 mt-4">Logout</a>
        </div>

        <!-- Main Content -->
        <div class="container mt-5">
            <h2 class="text-center mb-4">Pending Alumni Registrations</h2>
            <table class="table table-bordered table-striped text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Username</th>
                        <th>LinkedIn Profile</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['username']); ?></td>
                            <td><a href="<?= htmlspecialchars($row['linkedin_id']); ?>" target="_blank">View Profile</a></td>
                            <td>
                                <a href="approve.php?id=<?= $row['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                                <a href="reject.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm">Reject</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
