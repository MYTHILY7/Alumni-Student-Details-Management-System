<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Alumni') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$user_id = intval($_SESSION['user_id']);
$profile_query = "SELECT name, email, company, passout_year, roll_number, education FROM users WHERE id = ?";
$stmt = $conn->prepare($profile_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile_result = $stmt->get_result();
$profile = $profile_result->fetch_assoc();
$stmt->close();

function sendEmailToStudents($conn, $postContent) {
    $student_query = "SELECT email FROM users WHERE role = 'Student'";
    $result = $conn->query($student_query);

    $emails = [];
    while ($row = $result->fetch_assoc()) {
        $emails[] = $row['email'];
    }

    if (empty($emails)) return;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'novacodetechhub@gmail.com';
        $mail->Password = 'okxm xvht kggd chjw';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('novacodetechhub@gmail.com', 'Alumni Portal');
        $mail->Subject = 'New Alumni Post Notification';

        foreach ($emails as $email) {
            $mail->addAddress($email);
        }

        $mail->Body = "Dear Student,\n\nA new post has been shared by an Alumni:\n\n" . $postContent . "\n\nCheck it out on the portal!";

        $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>

<div class="d-flex">

    <div id="sidebar" class="bg-dark text-white p-3" style="width: 250px; min-height: 100vh;">
        <ul class="nav flex-column">
            <li class="nav-item mb-2"><a href="?page=dashboard" class="nav-link text-white">📊 Dashboard</a></li>
            <li class="nav-item mb-2"><a href="?page=create_post" class="nav-link text-white">➕ Create New Post</a></li>
            <li class="nav-item mb-2"><a href="?page=my_posts" class="nav-link text-white">📃 My Posts</a></li>
            <li class="nav-item mb-2"><a href="?page=profile" class="nav-link text-white">👤 Profile</a></li>
            <li class="nav-item mb-2"><a href="?page=alumni_list" class="nav-link text-white">🎓 Alumni List</a></li>
        </ul>
        <a href="logout.php" class="btn btn-danger w-100 mt-4">Logout</a>
    </div>


    <div class="p-4" style="flex-grow: 1;">
        <?php
        $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

        if ($page == 'dashboard') {
            echo '<h2>📊 Dashboard</h2>';

            $post_query = "SELECT p.id, p.content, p.file_path, p.created_at, u.id AS user_id, u.name, u.role
                           FROM posts p 
                           JOIN users u ON p.user_id = u.id 
                           WHERE p.status = 'Approved' 
                           ORDER BY p.created_at DESC";
            $post_result = $conn->query($post_query);

            if ($post_result->num_rows > 0) {
                while ($post = $post_result->fetch_assoc()) {
                    $userId = $post['user_id'];
                    $userName = htmlspecialchars($post['name']);
                    $verifiedBadge = ($post['role'] == 'Admin') ? "✅" : "";

                    echo "<div class='card mb-4'>
                            <div class='card-body'>
                                <h5>
                                    <a href='#' onclick='showProfile($userId)' class='text-decoration-none'>
                                        $userName $verifiedBadge
                                    </a>
                                </h5>
                                <p>" . nl2br(htmlspecialchars($post['content'])) . "</p>";

                    if (!empty($post['file_path'])) {
                        $file_path = htmlspecialchars($post['file_path']);
                        $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

                        if (in_array($file_extension, ['png', 'jpg', 'jpeg'])) {
                            echo "<img src='{$file_path}' class='img-fluid my-2' style='max-width: 400px;'><br>";
                        } elseif ($file_extension == 'pdf') {
                            echo "<a href='{$file_path}' target='_blank'>📎 View PDF Attachment</a><br>";
                        }
                    }

                    echo "<small class='text-muted'>Posted on: " . $post['created_at'] . "</small>
                          </div>
                          </div>";
                }
            } else {
                echo "<p>No posts available yet.</p>";
            }
        } elseif ($page == 'create_post') {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            echo '<h2>➕ Create Alumni Post</h2>';

            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['content'])) {
                $content = $conn->real_escape_string($_POST['content']);
                $file_path = NULL;

                if (!empty($_FILES['file']['name'])) {
                    $upload_dir = 'uploads/';
                    $file_name = basename($_FILES['file']['name']);
                    $file_path = $upload_dir . time() . "_" . $file_name;

                    if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
                        chmod($file_path, 0644);
                    } else {
                        echo "<div class='alert alert-danger'>File upload failed.</div>";
                    }
                }

                $insert_query = "INSERT INTO posts (user_id, content, file_path, status) VALUES (?, ?, ?, 'Pending')";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("iss", $user_id, $content, $file_path);
                $stmt->execute();
                echo "<div class='alert alert-success'>Post successfully submitted for approval!</div>";
                $stmt->close();
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
        
        

        elseif ($page == 'my_posts') {
            echo '<h2>📃 My Posts</h2>';
            $my_posts_query = "SELECT content, file_path, status, created_at FROM posts WHERE user_id = ? ORDER BY created_at DESC";
            $stmt = $conn->prepare($my_posts_query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $my_posts_result = $stmt->get_result();

            if ($my_posts_result->num_rows > 0) {
                while ($post = $my_posts_result->fetch_assoc()) {
                    echo "<div class='card mb-3'><div class='card-body'>";
                    echo "<p>" . htmlspecialchars($post['content']) . "</p>";
                    if (!empty($post['file_path'])) {
                        echo "<a href='" . htmlspecialchars($post['file_path']) . "' target='_blank'>📎 View Attachment</a><br>";
                    }
                    echo "<small class='text-muted'>Status: " . $post['status'] . " | Posted on: " . $post['created_at'] . "</small>";
                    echo "</div></div>";
                }
            } else {
                echo "<p>You have not posted anything yet.</p>";
            }
            $stmt->close();
        }


        elseif ($page == 'edit_profile') {
            echo '<h2>📝 Edit Profile</h2>';
            include 'send_email.php'; 
            // Fetch current user details
            $profile_query = "SELECT name, email, company, passout_year, education, department, linkedin FROM users WHERE id = ?";
            $stmt = $conn->prepare($profile_query);
        
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
        
                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
        
                    // Handle form submission
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        $user_id = $_SESSION['user_id'];
                        $name = trim($_POST['name']);
                        $company = trim($_POST['company']);
                        $passout_year = trim($_POST['passout_year']);
                        $education = trim($_POST['education']);
                        $department = trim($_POST['department']);
                        $linkedin = trim($_POST['linkedin']);
                    
                        $update_query = "UPDATE users SET name=?, company=?, passout_year=?, education=?, department=?, linkedin=? WHERE id=?";
                        $stmt = $conn->prepare($update_query);
                        $stmt->bind_param("ssssssi", $name, $company, $passout_year, $education, $department, $linkedin, $user_id);
                    
                        if ($stmt->execute()) {
                            // Fetch user email
                            $email_query = "SELECT email FROM users WHERE id=?";
                            $stmt_email = $conn->prepare($email_query);
                            $stmt_email->bind_param("i", $user_id);
                            $stmt_email->execute();
                            $stmt_email->bind_result($email);
                            $stmt_email->fetch();
                            $stmt_email->close();
                    
                            // Send Success Email
                            $subject = "Profile Update Successful";
                            $message = "
                                <h2>Profile Updated Successfully</h2>
                                <p>Dear <b>$name</b>,</p>
                                <p>Your profile has been updated successfully in the Alumni Management System.</p>
                                <p>If you did not make this change, please contact the admin immediately.</p>
                                <br>
                                <p>Best Regards,<br><b>Alumni Management Team</b></p>
                            ";
                    
                            sendEmail($email, $subject, $message);
                    
                            header("Location: ?page=profile&success=Profile updated successfully!");
                            exit();
                        } else {
                            header("Location: ?page=edit_profile&error=Profile update failed. Try again.");
                            exit();
                        }
                    }
                    ?>
        
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email (Cannot be changed)</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Company</label>
                            <input type="text" class="form-control" name="company" value="<?= htmlspecialchars($user['company']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Passout Year</label>
                            <input type="number" class="form-control" name="passout_year" value="<?= htmlspecialchars($user['passout_year']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Education</label>
                            <input type="text" class="form-control" name="education" value="<?= htmlspecialchars($user['education']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" name="department" value="<?= htmlspecialchars($user['department']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">LinkedIn Profile</label>
                            <input type="url" class="form-control" name="linkedin" value="<?= htmlspecialchars($user['linkedin']); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Profile</button>
                    </form>
        
                    <?php
                } else {
                    echo "<p class='alert alert-warning'>User profile not found.</p>";
                }
                $stmt->close();
            } else {
                echo "<p class='alert alert-danger'>Error fetching profile details. Please try again later.</p>";
            }
        }
        
        
        // Profile: Display Alumni's profile
        elseif ($page == 'profile') {
            echo '<h2>👤 Profile</h2>';
            ?>
            <div class="card p-4">
                <h4><?php echo htmlspecialchars($profile['name']); ?></h4>
                <hr>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($profile['email']); ?></p>
                <p><strong>Company:</strong> <?php echo htmlspecialchars($profile['company']); ?></p>
                <p><strong>Pass-out Year:</strong> <?php echo htmlspecialchars($profile['passout_year']); ?></p>
                <p><strong>Education:</strong> <?php echo htmlspecialchars($profile['education']); ?></p>
        
                <!-- Styled Edit Profile Button -->
                <a href="?page=edit_profile" class="btn btn-primary w-100 mt-3">✎ Edit Profile</a>
            </div>
            <?php
        }
        elseif ($page == 'alumni_list'){
            include 'alumni_list.php';
        }
        ?>
        

<script>
function showProfile(userId) {
    fetch(`fetch_profile.php?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                alert(`Name: ${data.name}\nEmail: ${data.email}\nRole: ${data.role}\nCompany: ${data.company}\nPass-out Year: ${data.passout_year}`);
            }
        })
        .catch(error => console.error('Error fetching profile:', error));
}
</script>

</body>
</html>
