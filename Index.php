<!DOCTYPE html>
<html lang="en">
<head>
    <title>Feed System</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">

    <h2>Post Feed</h2>

    <div id="postSection">
        <textarea id="postContent" class="form-control mb-2" placeholder="Write something..."></textarea>
        <button id="submitPost" class="btn btn-primary">Post</button>
    </div>

    <hr>

    <h3>Recent Posts</h3>
    <div id="posts"></div>

    <script>
    $(document).ready(function () {
        function loadPosts() {
            $.get("fetch_posts.php", function (data) {
                let posts = JSON.parse(data);
                $("#posts").html("");
                posts.forEach(post => {
                    $("#posts").append(`
                        <div class="card mt-2">
                            <div class="card-body">
                                <h5>${post.name} (${post.role})</h5>
                                <p>${post.content}</p>
                                <button class="btn btn-sm btn-outline-primary react-btn" data-post="${post.id}">Like</button>
                                <button class="btn btn-sm btn-outline-secondary comment-btn" data-post="${post.id}">Comment</button>
                            </div>
                        </div>
                    `);
                });
            });
        }

        $("#submitPost").click(function () {
            let content = $("#postContent").val();
            $.post("submit_post.php", { content: content }, function (response) {
                alert(response);
                loadPosts();
            });
        });

        $(document).on("click", ".react-btn", function () {
            let postId = $(this).data("post");
            $.post("add_reaction.php", { post_id: postId, reaction: "Like" }, function (response) {
                alert(response);
            });
        });

        loadPosts();
    });
    </script>

</body>
</html>
