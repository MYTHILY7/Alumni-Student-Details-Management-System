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

        <!-- Admin Approval Panel -->
        <div id="adminPanel" class="mb-4">
            <h4>Pending Posts (Admin Only)</h4>
            <div id="pendingPosts"></div>
        </div>

        <!-- Display Approved Posts -->
        <div id="postFeed">
            <!-- Posts will be loaded here dynamically -->
        </div>
    </div>

    <script>
    $(document).ready(function() {
        loadPosts();
        loadPendingPosts();
        
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
        
        // Load Approved Posts
        function loadPosts() {
            $.get('fetch_posts.php', function(data) {
                $('#postFeed').html(data);
            });
        }

        // Load Pending Posts for Admin
        function loadPendingPosts() {
            $.get('fetch_pending_posts.php', function(data) {
                $('#pendingPosts').html(data);
            });
        }

        // Approve or Reject Post (Admin Action)
        $(document).on('click', '.approvePost', function() {
            let postId = $(this).data('id');
            $.post('approve_post.php', { post_id: postId }, function(response) {
                alert(response);
                loadPendingPosts();
                loadPosts();
            });
        });

        $(document).on('click', '.rejectPost', function() {
            let postId = $(this).data('id');
            $.post('reject_post.php', { post_id: postId }, function(response) {
                alert(response);
                loadPendingPosts();
            });
        });

        // Add a Comment
        $(document).on('click', '.addComment', function() {
            let postId = $(this).data('id');
            let comment = $('#commentInput_' + postId).val();
            if (comment.trim() === '') {
                alert('Comment cannot be empty!');
                return;
            }
            $.post('add_comment.php', { post_id: postId, comment: comment }, function(response) {
                alert(response);
                loadPosts();
            });
        });

        // Add a Reaction
        $(document).on('click', '.reactButton', function() {
            let postId = $(this).data('id');
            let reactionType = $(this).data('reaction');
            $.post('add_reaction.php', { post_id: postId, reaction_type: reactionType }, function(response) {
                alert(response);
                loadPosts();
            });
        });
    });
    </script>
</body>
</html>
