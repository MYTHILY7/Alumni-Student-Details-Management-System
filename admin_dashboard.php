<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

// Fetch total students and alumni
$students_count = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'Student'")->fetch_assoc()['total'];
$alumni_count = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'Alumni'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<style>
    /* General Styles */
body {
    font-family: 'Poppins', sans-serif;
    background: #f8f9fa;
    color: #333;
    margin: 0;
    padding: 0;
}

/* Sidebar */
#sidebar {
    background: linear-gradient(135deg, #2c3e50, #4b6584);
    box-shadow: 4px 0 10px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

#sidebar h4 {
    font-weight: 600;
    letter-spacing: 1px;
}

.nav-link {
    padding: 10px 15px;
    border-radius: 8px;
    transition: background 0.3s;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.2);
}

.btn-danger {
    border: none;
    border-radius: 8px;
    padding: 10px;
    font-weight: bold;
}

/* Main Content */
.p-4 {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

h2 {
    font-size: 28px;
    margin-bottom: 20px;
    color: #2c3e50;
}

/* Dashboard Cards */
.card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.bg-primary {
    background: linear-gradient(135deg, #3498db, #6dd5fa);
}

.bg-success {
    background: linear-gradient(135deg, #2ecc71, #55efc4);
}

.card h3 {
    font-weight: 700;
    margin: 0;
}

/* Form Styling */
form .form-control {
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease;
}

form .form-control:focus {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

button[type='submit'] {
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: bold;
}

/* Post Cards */
.card-body h5 {
    font-weight: bold;
    color: #2c3e50;
}

.card-body img {
    border-radius: 12px;
    max-width: 100%;
    height: auto;
}

.card-body small {
    font-size: 0.85rem;
    color: #7f8c8d;
}

/* Button Styling */
.btn-success, .btn-danger {
    border-radius: 8px;
    padding: 8px 16px;
    font-weight: bold;
    transition: background 0.3s;
}

.btn-success:hover {
    background: #27ae60;
}

.btn-danger:hover {
    background: #c0392b;
}

/* Responsive Design */
@media (max-width: 768px) {
    #sidebar {
        width: 100%;
        min-height: auto;
    }

    .p-4 {
        padding: 1rem;
    }

    .card {
        margin-bottom: 1rem;
    }
} 

/* Smooth Transitions */
* {
    transition: all 0.3s ease-in-out;
}
</style>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <div id="sidebar" class="bg-dark text-white p-3" style="width: 250px; min-height: 100vh;">
        <h4 class="text-center mb-4">Admin Dashboard</h4>
        <ul class="nav flex-column">
            <li class="nav-item mb-2"><a href="?page=dashboard" class="nav-link text-white">📊 Dashboard</a></li>
            <li class="nav-item mb-2"><a href="?page=create_post" class="nav-link text-white">➕ Create Admin Post</a></li>
            <li class="nav-item mb-2"><a href="?page=requests" class="nav-link text-white">📥 Requests</a></li>
            <li class="nav-item mb-2"><a href="?page=approve_alumni" class="nav-link text-white">Alumini Request</a></li>
            <li class="nav-item mb-2"><a href="?page=previous_posts" class="nav-link text-white">📃 Previous Posts</a></li>
            <li class="nav-item mb-2"><a href="?page=all_posts" class="nav-link text-white">📂 Posts</a></li>
        </ul>
        <a href="logout.php" class="btn btn-danger w-100 mt-4">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="p-4" style="flex-grow: 1;">
        <?php
        $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

        // Dashboard
        if ($page == 'dashboard') {
            echo '<h2>📊 Dashboard</h2>';
            echo "<div class='row mt-4'>
                    <div class='col-md-6'>
                        <div class='card bg-primary text-white p-4'>
                            <h3>Total Students: $students_count</h3>
                        </div>
                    </div>
                    <div class='col-md-6'>
                        <div class='card bg-success text-white p-4'>
                            <h3>Total Alumni: $alumni_count</h3>
                        </div>
                    </div>
                  </div>";
        }

        // Create Admin Post
        elseif ($page == 'create_post') {
            echo '<h2>➕ Create Admin Post</h2>';
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['content'])) {
                $content = $conn->real_escape_string($_POST['content']);
                
                // Handle File Upload
                $file_path = NULL;
                if (!empty($_FILES['file']['name'])) {
                    $upload_dir = 'uploads/';
                    $file_path = $upload_dir . basename($_FILES['file']['name']);
                    move_uploaded_file($_FILES['file']['tmp_name'], $file_path);
                }

                $insert_query = "INSERT INTO posts (user_id, content, file_path, status) VALUES ('7', '$content', '$file_path', 'Approved')";
                $conn->query($insert_query);
                echo "<div class='alert alert-success'>Post successfully published!</div>";
            }
            ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <textarea class="form-control" name="content" rows="4" placeholder="Share an announcement..." required></textarea>
                </div>
                <div class="mb-3">
                    <label for="file" class="form-label">Attach File (.doc, .png, .jpg)</label>
                    <input type="file" name="file" id="file" class="form-control" accept=".doc,.png,.jpg">
                </div>
                <button type="submit" class="btn btn-primary">Post</button>
            </form>
            <?php
        }

        // Requests: Approve or Reject Posts
        elseif ($page == 'requests') {
            echo '<h2>📥 Pending Alumni Posts</h2>';
            $pending_query = "SELECT posts.id, posts.content, users.name FROM posts JOIN users ON posts.user_id = users.id WHERE posts.status = 'Pending'";
            $result = $conn->query($pending_query);

            if ($result->num_rows > 0) {
                while ($post = $result->fetch_assoc()) {
                    echo "<div class='card mb-3'>
                            <div class='card-body'>
                                <h5 class='card-title'>{$post['name']}</h5>
                                <p>{$post['content']}</p>
                                <button class='btn btn-success me-2' onclick='approvePost({$post['id']})'>Approve</button>
                                <button class='btn btn-danger' onclick='rejectPost({$post['id']})'>Reject</button>
                            </div>
                          </div>";
                }
            } else {
                echo "<p>No pending posts for approval.</p>";
            }
        }




        elseif ($page == 'approve_alumni') {
            echo '<h2>📥 Pending Alumni Registrations</h2>';
            
            // Fetch pending alumni registrations
            $sql = "SELECT id, name, linkedin FROM users WHERE is_approved = 0 AND role = 'Alumni'";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                ?>
                <table class="table table-bordered table-striped text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>LinkedIn Profile</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?= htmlspecialchars($row['name']); ?></td>
                                <td>
                                    <a href="<?= htmlspecialchars($row['linkedin']); ?>" target="_blank">
                                        View Profile
                                    </a>
                                </td>
                                <td>
                                    <a href="?page=approve_alumni_req&id=<?= $row['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                                    <a href="?page=reject_alumni&id=<?= $row['id']; ?>" class="btn btn-danger btn-sm">Reject</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php
            } else {
                echo "<div class='alert alert-info'>No pending alumni registrations.</div>";
            }
        }
        

// Approve Request
elseif ($page == 'approve_alumni_req' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $update_query = "UPDATE alumni_users SET is_approved = 1 WHERE id = $id";
    if ($conn->query($update_query)) {
        echo "<div class='alert alert-success'>Alumni registration approved successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Failed to approve alumni registration.</div>";
    }
}

// Reject Request
elseif ($page == 'reject_alumni' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $delete_query = "DELETE FROM alumni_users WHERE id = $id";
    if ($conn->query($delete_query)) {
        echo "<div class='alert alert-warning'>Alumni registration rejected and removed.</div>";
    } else {
        echo "<div class='alert alert-danger'>Failed to reject alumni registration.</div>";
    }
}

// Admin's Previous Posts
elseif ($page == 'previous_posts') {
    echo '<h2>📃 Previous Posts</h2>';

    $admin_id = $_SESSION['user_id']; // Get the logged-in admin's ID

    $admin_posts_query = "SELECT content, file_path, created_at FROM posts WHERE user_id = '$admin_id' ORDER BY created_at DESC";
    $admin_posts_result = $conn->query($admin_posts_query);

    if ($admin_posts_result->num_rows > 0) {
        while ($post = $admin_posts_result->fetch_assoc()) {
            echo "<div class='card mb-3'>
                    <div class='card-body'>
                        <h5>{$_SESSION['name']} ✅</h5> <!-- Admin's name with blue tick -->
                        <p>{$post['content']}</p>";
            if ($post['file_path']) {
                echo "<p><a href='{$post['file_path']}' target='_blank'>📎 View Attachment</a></p>";
            }
            echo "<small>Posted on: {$post['created_at']}</small>
                    </div>
                  </div>";
        }
    } else {
        echo "<p>No previous posts found.</p>";
    }
}


elseif ($page == 'all_posts') {
    echo '<h2>📂 All Posts</h2>';
    $all_posts_query = "SELECT posts.content, posts.file_path, posts.created_at, users.id, users.name, users.role
                        FROM posts 
                        JOIN users ON posts.user_id = users.id 
                        WHERE posts.status = 'Approved' 
                        ORDER BY posts.created_at DESC";
    $all_posts_result = $conn->query($all_posts_query);

    if ($all_posts_result->num_rows > 0) {
        while ($post = $all_posts_result->fetch_assoc()) {
            // Add a blue tick for Admin
            $verifiedBadge = ($post['role'] == 'Admin') ? '✅' : '';

            echo "<div class='card mb-4'>
                    <div class='card-body'>
                        <h5>
                            <a href='#' onclick='showProfile({$post['id']})' class='text-decoration-none'>
                                {$post['name']} $verifiedBadge
                            </a>
                        </h5>
                        <p>{$post['content']}</p>";

            // Display the image if it's an image file
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $fileExtension = strtolower(pathinfo($post['file_path'], PATHINFO_EXTENSION));
            if ($post['file_path'] && in_array($fileExtension, $imageExtensions)) {
                echo "<img src='{$post['file_path']}' class='img-fluid mb-2' alt='Post Image'>";
            } elseif ($post['file_path']) {
                echo "<p><a href='{$post['file_path']}' target='_blank'>📎 View Attachment</a></p>";
            }

            echo "<small class='text-muted'>Posted on: {$post['created_at']}</small>
                    </div>
                  </div>";
        }
    } else {
        echo "<p>No posts found.</p>";
    }
}


        ?>
    </div>
</div>

<script>
// Approve or Reject Post
function approvePost(postId) {
    fetch('approve_post.php', {
        method: 'POST',
        body: new URLSearchParams({ post_id: postId })
    }).then(() => location.reload());
}

function rejectPost(postId) {
    fetch('reject_post.php', {
        method: 'POST',
        body: new URLSearchParams({ post_id: postId })
    }).then(() => location.reload());
}
</script>

</body>

</html>
