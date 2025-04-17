<?php
// Database connection
include 'db_connect.php';

// Tables needed:
// 1. users (id, name, email, password, role [Admin, Alumni, Student])
// 2. posts (id, user_id, content, status [Pending, Approved, Rejected], created_at)
// 3. comments (id, post_id, user_id, comment, created_at)
// 4. reactions (id, post_id, user_id, reaction_type, created_at)
// 5. notifications (id, user_id, message, status [Unread, Read], created_at)

// Sample function to submit a post
function submitPost($userId, $content, $role) {
    global $conn;
    $status = ($role === 'Admin') ? 'Approved' : 'Pending'; // Admin posts auto-approved
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, status, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $userId, $content, $status);
    $stmt->execute();
    $postId = $stmt->insert_id;
    $stmt->close();
    
    if ($role === 'Alumni') {
        sendNotificationToAdmin($postId, $userId);
    }
}

// Function to send a notification to Admin for post approval
function sendNotificationToAdmin($postId, $userId) {
    global $conn;
    $message = "New Alumni post pending approval.";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, status, created_at) VALUES ((SELECT id FROM users WHERE role='Admin' LIMIT 1), ?, 'Unread', NOW())");
    $stmt->bind_param("s", $message);
    $stmt->execute();
    $stmt->close();
}

// Function for Admin to approve/reject a post
function reviewPost($postId, $status) {
    global $conn;
    $stmt = $conn->prepare("UPDATE posts SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $postId);
    $stmt->execute();
    $stmt->close();
    sendApprovalNotification($postId, $status);
}

// Function to notify Alumni when their post is approved/rejected
function sendApprovalNotification($postId, $status) {
    global $conn;
    $stmt = $conn->prepare("SELECT user_id FROM posts WHERE id=?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $userId = $row['user_id'];
    $stmt->close();
    
    $message = ($status === 'Approved') ? "Your post has been approved." : "Your post was rejected.";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, status, created_at) VALUES (?, ?, 'Unread', NOW())");
    $stmt->bind_param("is", $userId, $message);
    $stmt->execute();
    $stmt->close();
}

// Function to add a comment
function addComment($postId, $userId, $comment) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $postId, $userId, $comment);
    $stmt->execute();
    $stmt->close();
    sendCommentNotification($postId, $userId);
}

// Function to notify post owner about a new comment
function sendCommentNotification($postId, $commenterId) {
    global $conn;
    $stmt = $conn->prepare("SELECT user_id FROM posts WHERE id=?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $ownerId = $row['user_id'];
    $stmt->close();
    
    if ($ownerId != $commenterId) {
        $message = "Someone commented on your post.";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, status, created_at) VALUES (?, ?, 'Unread', NOW())");
        $stmt->bind_param("is", $ownerId, $message);
        $stmt->execute();
        $stmt->close();
    }
}

// Function to add a reaction (like, love, etc.)
function addReaction($postId, $userId, $reactionType) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO reactions (post_id, user_id, reaction_type, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $postId, $userId, $reactionType);
    $stmt->execute();
    $stmt->close();
}

// Function to fetch all approved posts with comments and reactions
function fetchApprovedPosts() {
    global $conn;
    $stmt = $conn->prepare("SELECT posts.id, posts.content, posts.created_at, users.name, users.role FROM posts JOIN users ON posts.user_id = users.id WHERE posts.status='Approved' ORDER BY posts.created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>
