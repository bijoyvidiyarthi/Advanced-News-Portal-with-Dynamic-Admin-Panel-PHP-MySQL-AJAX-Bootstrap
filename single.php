<?php
include 'header.php';
include 'config.php';

//get post id from url
if (isset($_GET['id'])) {
    $post_id = mysqli_real_escape_string($conn, $_GET['id']);
} else {
    //if no id found redirect to home page
    $_SESSION['error'] = "⚠️ **Post Not Found:** No post ID provided.";
    //if no id found redirect to home page
    header("Location: index.php");
    exit();
}

// --- Input Validation ---
//  Error Message for Empty/Invalid Input ---
if (empty($post_id) || !is_numeric($post_id)) {
    $_SESSION['error'] = "⚠️ **Invalid Post ID:** The post ID provided is not valid.";
    //if there is any error redirect to home page
    header("Location: index.php");
    exit();
} else {
    $post_id = (int) $post_id; //cast to integer for safety

    //--- Check existence of post ID in Database ---
    $check_sql = "SELECT * from post where post_id = {$post_id}";
    $check_result = mysqli_query($conn, $check_sql);

    if (!$check_result || mysqli_num_rows($check_result) == 0) {
        $_SESSION['error'] = "⚠️ **Post Not Found:** The requested post does not exist.";
        //if there is any error redirect to home page
        header("Location: index.php");
        exit();
    }
}

$post_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$encoded_url = urlencode($post_url);
$encoded_title = urlencode($title);

?>
<div id="main-content">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <!-- post-container -->
                <div class="post-container">
                    <?php
                    //first check if connection is established or not
                    if ($conn):
                        // Fetch users from database and display here 
                        $sql = "SELECT p.post_id, p.title, p.description, p.created_date, p.post_img, 
                            u.username, u.user_img, u.bio, p.author, c.category_name, p.category
                            FROM post p
                            LEFT JOIN category c ON p.category = c.category_id
                            LEFT JOIN user u ON p.author = u.user_id
                            WHERE p.post_id = {$post_id}";

                        $result = mysqli_query($conn, $sql);

                        if (!$result):
                            $_SESSION['error'] = "Error fetching posts: " . mysqli_error($conn);
                            header("Location: $_SERVER[PHP_SELF]");
                            mysqli_close($conn);
                        else:
                            if (mysqli_num_rows($result) > 0):
                                while ($row = mysqli_fetch_assoc($result)):
                                    $id = $row['post_id'];
                                    $title = $row['title'];
                                    $description = $row['description'];
                                    $author = $row['username'];
                                    $user_id = $row['author'];
                                    $category_id = $row['category'];
                                    $category = $row['category_name'];
                                    $date = $row['created_date'];
                                    $post_img = $row['post_img'];
                                    $user_bio = $row['bio'];
                                    $user_img = $row['user_img'];
                                    ?>

                                    <div class="post-content single-post">
                                        <h3><?php echo htmlspecialchars($title); ?></h3>
                                        <div class="post-information">
                                            <span>
                                                <i class="fa fa-tags" aria-hidden="true"></i>
                                                <a
                                                    href='category.php?c_id=<?php echo htmlspecialchars($category_id); ?>'><?php echo htmlspecialchars($category); ?></a>
                                            </span>
                                            <span>
                                                <i class="fa fa-user" aria-hidden="true"></i>
                                                <a
                                                    href='author.php?aid=<?php echo htmlspecialchars($user_id); ?>'><?php echo htmlspecialchars($author); ?></a>
                                            </span>
                                            <span>
                                                <i class="fa fa-calendar" aria-hidden="true"></i>
                                                <?php echo htmlspecialchars(date("d M, Y", strtotime($date))); ?>
                                            </span>
                                        </div>
                                        <div class="featured_img_container">
                                            <img class="single-feature-image"
                                                src="admin/upload/<?php echo htmlspecialchars($post_img); ?>"
                                                alt="<?php echo htmlspecialchars($title); ?>" />
                                        </div>

                                        <div class="description">
                                            <?php
                                            $description_finale = str_replace('upload/', 'admin/upload/', $description);
                                            echo $description_finale;
                                            ?>
                                        </div>

                                        <div class="author-box">
                                            <div class="author-avatar">

                                                <img src="admin/upload/users/<?php echo htmlspecialchars($user_img); ?>" alt="Author">
                                            </div>
                                            <div id="author-info" class="author-info">
                                                <h5>
                                                    <?php echo htmlspecialchars($author); ?>
                                                </h5>
                                                <p class="author-bio" id="bio">
                                                    <?php echo htmlspecialchars($user_bio); ?>
                                                </p>
                                                <a id="author-visit" href="author.php?aid=<?php echo htmlspecialchars($user_id); ?>">
                                                    More articles by this author →
                                                </a>
                                            </div>
                                        </div>

                                    </div>
                                    <?php
                                endwhile;
                            else:
                                echo "<h2>No Record Found.</h2>";
                            endif;

                        endif;
                    endif;
                    ?>
                    <div class="social-share-inline">
                        <span>Share:</span>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $encoded_url; ?>"
                            target="_blank">
                            <i class="fab fa-facebook-f"></i> </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo $encoded_url; ?>&text=<?php echo $encoded_title; ?>"
                            target="_blank">
                            <i class="fab fa-twitter"></i> </a>
                        <a href="https://wa.me/?text=<?php echo $encoded_title . '%20' . $encoded_url; ?>"
                            target="_blank">
                            <i class="fab fa-whatsapp"></i> </a>
                        <a href="javascript:void(0)" onclick="copyPostLink()">
                            <i class="fas fa-link"></i> </a>
                    </div>
                    <!-- comment-section -->
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="comment-section-container" id="comments">
                        <?php
                        $comment_sql = "SELECT name, comment, created_at 
                                        FROM comments 
                                        WHERE post_id = $post_id AND status='approved'
                                        ORDER BY created_at DESC";

                        $res = mysqli_query($conn, $comment_sql);
                        $comment_count = ($res) ? mysqli_num_rows($res) : 0;
                        ?>


                        <h4 class="comment-title">
                            Comments <span class="comment-count">(<?php echo $comment_count; ?>)</span>

                        </h4>

                        <!-- Alert -->
                        <div id="commentAlert"></div>

                        <!-- Comment Form -->
                        <form id="commentForm" class="comment-form">
                            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">

                            <div class="form-row">
                                <input type="text" name="name" placeholder="Your name" required>
                                <input type="email" name="email" placeholder="Your email" required>
                            </div>

                            <textarea name="comment" rows="4" placeholder="Write your comment..." required></textarea>

                            <button type="submit">
                                <i class="fa fa-paper-plane" id="comment-submit"></i> Post Comment
                            </button>
                        </form>

                        <!-- Comments List -->
                        <div class="comments-list mt-4" id="commentList">
                            <?php

                            if ($res && mysqli_num_rows($res) > 0):
                                while ($c = mysqli_fetch_assoc($res)):
                                    ?>
                                    <div class="single-comment">
                                        <div class="comment-avatar">
                                            <?php echo strtoupper(substr($c['name'], 0, 1)); ?>
                                        </div>
                                        <div class="comment-content">
                                            <div class="comment-header">
                                                <strong><?= htmlspecialchars($c['name']) ?></strong>
                                                <span><?= date("d M Y · H:i", strtotime($c['created_at'])) ?></span>
                                            </div>
                                            <p><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
                                        </div>
                                    </div>
                                <?php endwhile; else: ?>
                                <p class="no-comment">No comments yet. Be the first to comment!</p>
                            <?php endif; ?>
                        </div>

                    </div>
                    <!-- </comment-section -->

                </div>
                <!-- /post-container -->
            </div>
            <?php include 'sidebar.php'; ?>
        </div>
    </div>
    <div class="social-share-floating">
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $encoded_url; ?>" target="_blank">
            <i class="fab fa-facebook-f"></i>
        </a>
        <a href="https://twitter.com/intent/tweet?url=<?php echo $encoded_url; ?>" target="_blank">
            <i class="fab fa-twitter"></i>
        </a>
        <a href="https://wa.me/?text=<?php echo $encoded_url; ?>" target="_blank">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>
</div>

<script>
    function copyPostLink() {
        navigator.clipboard.writeText(window.location.href);
        alert("Link copied!");
    }
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        const form = document.getElementById("commentForm");
        const alertBox = document.getElementById("commentAlert");
        const commentList = document.getElementById("commentList");

        if (!form) return;

        form.addEventListener("submit", function (e) {
            e.preventDefault();

            alertBox.innerHTML = "";

            const formData = new FormData(form);

            fetch("actions/comment-submit.php", {
                method: "POST",
                body: formData
            })
                .then(response => response.json())
                .then(data => {

                    alertBox.innerHTML = `
                <div class="alert ${data.status === 'success' ? 'alert-success' : 'alert-danger'}">
                    ${data.message}
                </div>
            `;

                    if (data.status === "success") {

                        // Admin comment → instantly show
                        if (data.approved === true && data.comment) {

                            const commentHTML = `
                        <div class="single-comment">
                            <div class="comment-avatar">
                                ${data.comment.name.charAt(0).toUpperCase()}
                            </div>
                            <div class="comment-content">
                                <div class="comment-header">
                                    <strong>${escapeHtml(data.comment.name)}</strong>
                                    <span>${data.comment.created_at}</span>
                                </div>
                                <p>${escapeHtml(data.comment.comment).replace(/\n/g, "<br>")}</p>
                            </div>
                        </div>
                    `;

                            commentList.insertAdjacentHTML("afterbegin", commentHTML);

                            // remove "No comments yet"
                            const noComment = commentList.querySelector(".no-comment");
                            if (noComment) noComment.remove();
                        }

                        form.reset();
                    }
                })
                .catch(() => {
                    alertBox.innerHTML = `
                <div class="alert alert-danger">
                    Server error! Please try again later.
                </div>
            `;
                });
        });

        // Basic XSS protection
        function escapeHtml(text) {
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

    });
</script>



<?php

include 'footer.php';
?>