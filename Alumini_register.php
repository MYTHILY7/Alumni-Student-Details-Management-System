<?php
// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';
include 'send_email.php'; // Include email sending function

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $company = trim($_POST['company']);
    $passout_year = trim($_POST['passout_year']);
    $education = trim($_POST['education']);
    $department = trim($_POST['department']);
    $linkedin = trim($_POST['linkedin']); // Added LinkedIn field

    // Check if all fields are filled
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($company) || empty($passout_year) || empty($education) || empty($department) || empty($linkedin)) {
        header("Location: alumni_register.html?error=Please fill all fields");
        exit();
    }

    // Password validation
    if ($password !== $confirm_password) {
        header("Location: alumni_register.html?error=Passwords do not match");
        exit();
    }

    // Check if email is already registered
    $sql_check = "SELECT id FROM users WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        header("Location: alumni_register.html?error=Email already registered");
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    $sql = "INSERT INTO users (name, email, password, role, company, passout_year, education, department, linkedin, is_approved) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);

    $role = "Alumni"; // Set role explicitly

    $stmt->bind_param("sssssssss", $name, $email, $hashed_password, $role, $company, $passout_year, $education, $department, $linkedin);

    if ($stmt->execute()) {
        // ✅ Send Email Notification
        $subject = "Alumni Registration Pending Approval";
        $message = "Dear $name,<br><br>
                    Thank you for registering as an Alumni! Your registration is currently **pending approval** by the admin.<br>
                    You will receive an email once your account is approved.<br><br>
                    Regards,<br>
                    <b>Alumni Management Team</b>";

        sendEmail($email, $subject, $message);

        header("Location: login.html?success=Alumni registered successfully! Await admin approval.");
        exit();
    } else {
        die("Error: " . $stmt->error); // Show actual SQL error
    }
}
?>
