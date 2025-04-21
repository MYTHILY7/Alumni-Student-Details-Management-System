<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Student') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';
$page = isset($_GET['page'])?$_GET['page']:'home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Arial', sans-serif;
            color: #343a40;
        }
        .navbar {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card {
        height: 100%; /* Ensure all cards have equal height */
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .card-body {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .post-content {
        flex-grow: 1; /* Push buttons to bottom */
    }
    .post-image {
        max-width: 100%;
        height: 200px; /* Fixed image height */
        object-fit: cover; /* Maintain aspect ratio */
        border-radius: 10px;
    }
        .card-title {
            font-weight: bold;
            color: #212529;
        }
        .post-image {
            width: 100%;
            max-height: 250px;
            object-fit: cover;
            border-radius: 10px;
        }
        .btn-outline-primary, .btn-outline-secondary {
            border-radius: 20px;
        }
        .btn-outline-primary:hover, .btn-outline-secondary:hover {
            transform: scale(1.1);
        }
        .container {
            max-width: 1200px;
        }
        .no-posts {
            text-align: center;
            padding: 50px;
        }
        .post-content {
        flex-grow: 1;
        font-size: 14px;
    }

    .post-image {
        width: 100%;
        height: 250px;
        object-fit: cover;
        border-radius: 10px;
    }

    .actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
    }

    .like-btn, .comment-btn {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
    }

    .like-btn:hover, .comment-btn:hover {
        color: red;
    }

    .comment-section {
        padding: 10px;
        border-top: 1px solid #ddd;
    }

    .comment-box {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 10px;
    }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
    <a class="navbar-brand" href="student_dashboard.php">Student Dashboard</a>
        <div class="ms-auto">
            <!-- Alumni List Link -->
            <a href="student_dashboard.php?page=alumni_list" class="btn btn-outline-light me-3">Alumni List</a>
                <!-- Logout Button -->
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    


    <?php
    if ($page === 'alumni_list') {
        include 'alumni_list.php';
    } else {
        ?>
        <h2 class="text-center mb-4">📢 Latest Alumni Posts</h2>
        <?php
$sql = "SELECT posts.id, posts.content, posts.file_path, users.name, users.role, users.linkedin 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        WHERE posts.status = 'Approved' 
        ORDER BY posts.created_at DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo '<div class="row g-4">';
    while ($row = $result->fetch_assoc()) {
        $postId = $row['id'];
        echo "<div class='col-md-6 col-lg-4 d-flex'>
                <div class='card p-3 mb-3 w-100'>
                    <div class='card-body'>
                        <h5 class='card-title'>";
        
        // Show LinkedIn only for Alumni
        if ($row['role'] == 'Alumni' && !empty($row['linkedin'])) {
            echo "<a href='{$row['linkedin']}' target='_blank' style='text-decoration: none;'>
                    {$row['name']} ({$row['role']})
                  </a>";
        } else {
            echo "{$row['name']} ({$row['role']})";
        }
        echo "</h5>";

        // Display Image if Available
        if (!empty($row['file_path'])) {
            echo "<img src='{$row['file_path']}' class='post-image mb-3' alt='Post Image'>";
        }

        echo "<p class='post-content'>{$row['content']}</p>";

        // Action buttons (Like & Comment)
        echo "<div class='actions'>
                <button class='like-btn' onclick='likePost({$postId})'>❤️</button>
                <button class='comment-btn' onclick='toggleCommentBox({$postId})'>💬</button>
              </div>";

        // Comment input section (Hidden initially)
        echo "<div id='comments_{$postId}' class='comment-section' style='display: none;'>
                <input type='text' id='comment_{$postId}' class='comment-box' placeholder='Add a comment...'>
                <button class='btn btn-primary btn-sm mt-2' onclick='commentPost({$postId})'>Post</button>
              </div>";

        // Fetch Likes from Reactions Table
        $likeQuery = "SELECT users.name FROM reactions 
                      JOIN users ON reactions.user_id = users.id 
                      WHERE reactions.post_id = $postId AND reactions.reaction_type = 'Like'";
        $likeResult = $conn->query($likeQuery);
        
        if ($likeResult->num_rows > 0) {
            echo "<p><strong>Liked by:</strong> ";
            $likes = [];
            while ($likeRow = $likeResult->fetch_assoc()) {
                $likes[] = $likeRow['name'];
            }
            echo implode(", ", $likes);
            echo "</p>";
        }

        // Fetch Comments from Comments Table
        $commentQuery = "SELECT users.name, comments.comment FROM comments 
                         JOIN users ON comments.user_id = users.id 
                         WHERE comments.post_id = $postId 
                         ORDER BY comments.created_at ASC";
        $commentResult = $conn->query($commentQuery);

        if ($commentResult->num_rows > 0) {
            echo "<div class='comments-section'>";
            echo "<strong>Comments:</strong><ul class='comment-list'>";
            while ($commentRow = $commentResult->fetch_assoc()) {
                echo "<li><strong>{$commentRow['name']}:</strong> {$commentRow['comment']}</li>";
            }
            echo "</ul></div>";
        } else {
            echo "<p>No comments yet.</p>";
        }

        echo "</div>
        </div>
    </div>";
    }
    echo '</div>';
} else {
    echo "<div class='no-posts'>
            <h4>No posts available yet. 📭</h4>
          </div>";
}}
?>




</div>

<script>
function likePost(postId) {
    fetch('add_reaction.php', {
        method: 'POST',
        body: new URLSearchParams({ post_id: postId, reaction: "Like" })
    }).then(() => {
        alert('❤️ Liked!');
    });
}

function toggleCommentBox(postId) {
    let commentSection = document.getElementById(`comments_${postId}`);
    commentSection.style.display = (commentSection.style.display === "none") ? "block" : "none";
}

function commentPost(postId) {
    let comment = document.getElementById(`comment_${postId}`).value;
    if (comment) {
        fetch('add_comment.php', {
            method: 'POST',
            body: new URLSearchParams({ post_id: postId, comment: comment })
        }).then(() => {
            alert('💬 Comment added!');
            location.reload();
        });
    }
}
</script>


</body>
</html>
