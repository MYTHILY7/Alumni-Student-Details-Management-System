<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Alumni') {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch alumni details
$sql = "SELECT name, email, company, passout_year, education, department, linkedin FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $company = trim($_POST['company']);
    $passout_year = trim($_POST['passout_year']);
    $education = trim($_POST['education']);
    $department = trim($_POST['department']);
    $linkedin = trim($_POST['linkedin']);

    // Validate required fields
    if (empty($name) || empty($company) || empty($passout_year) || empty($education) || empty($department) || empty($linkedin)) {
        $error = "All fields are required.";
    } else {
        // Update alumni details
        $update_sql = "UPDATE users SET name = ?, company = ?, passout_year = ?, education = ?, department = ?, linkedin = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssssi", $name, $company, $passout_year, $education, $department, $linkedin, $user_id);

        if ($update_stmt->execute()) {
            $success = "Profile updated successfully!";
            $_SESSION['name'] = $name; // Update session name
        } else {
            $error = "Profile update failed. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Alumni Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .profile-container { max-width: 500px; margin: 50px auto; padding: 20px; background: white; border-radius: 10px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body>

<div class="container">
    <div class="profile-container">
        <h3 class="text-center">Edit Profile</h3>
        
        <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
        <?php if (isset($success)) { echo "<div class='alert alert-success'>$success</div>"; } ?>
        
        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email (Non-editable)</label>
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
        
        <p class="text-center mt-3">
            <a href="dashboard.php">Back to Dashboard</a>
        </p>
    </div>
</div>

</body>
</html>
