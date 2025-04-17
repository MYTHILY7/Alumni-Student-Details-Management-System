<?php
// Database connection
include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4">Post Feed</h2>
        
        <!-- Post Submission Form (For Admin & Alumni) -->
        <div class="card p-3 mb-4">
            <h4>Create a Post</h4>
            <textarea id="postContent" class="form-control" placeholder="Write something..."></textarea>
            <button id="submitPost" class="btn btn-primary mt-2">Post</button>
        </div>

        <!-- Display Posts -->
        <div id="postFeed">
            <!-- Posts will be loaded here dynamically -->
        </div>
    </div>

    <script>
    $(document).ready(function() {
        loadPosts();
        
        // Submit a Post
        $('#submitPost').click(function() {
            let content = $('#postContent').val();
            if (content.trim() === '') {
                alert('Post content cannot be empty!');
                return;
            }
            $.post('submit_post.php', { content: content }, function(response) {
                alert(response);
                $('#postContent').val('');
                loadPosts();
            });
        });
        
        // Load Posts
        function loadPosts() {
            $.get('fetch_posts.php', function(data) {
                $('#postFeed').html(data);
            });
        }
    });
    </script>
</body>
</html>
