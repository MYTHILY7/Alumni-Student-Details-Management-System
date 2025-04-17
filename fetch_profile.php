<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db_connect.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = intval($_GET['id']);

    $query = "SELECT name, email, role, company, passout_year, roll_number, education 
              FROM users 
              WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Prepare the response
        $profile = [
            'name' => htmlspecialchars($user['name']),
            'email' => htmlspecialchars($user['email']),
            'role' => htmlspecialchars($user['role']),
            'company' => $user['company'] ? htmlspecialchars($user['company']) : 'N/A',
            'passout_year' => $user['passout_year'] ? htmlspecialchars($user['passout_year']) : 'N/A',
            'roll_number' => $user['roll_number'] ? htmlspecialchars($user['roll_number']) : 'N/A',
            'education' => $user['education'] ? htmlspecialchars($user['education']) : 'N/A'
        ];

        echo json_encode($profile);
    } else {
        echo json_encode(['error' => 'User not found']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
