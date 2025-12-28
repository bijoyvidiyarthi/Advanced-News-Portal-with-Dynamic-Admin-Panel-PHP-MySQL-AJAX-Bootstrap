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
                        $sql = "SELECT p.post_id, p.title, p.description, p.post_date, p.post_img, 
                            u.username, p.author, c.category_name, p.category
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
                                    $date = $row['post_date'];
                                    $post_img = $row['post_img'];
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
                                                <?php echo htmlspecialchars($date); ?>
                                            </span>
                                        </div>
                                        <img class="single-feature-image" src="admin/upload/<?php echo htmlspecialchars($post_img); ?>"
                                            alt="<?php echo htmlspecialchars($title); ?>" />
                                        <p class="description">
                                            <?php                        
                                             echo nl2br(htmlspecialchars($description)); 
                                             ?>
                                        </p>
                                    </div>
                                    <?php
                                endwhile;
                            else:
                                echo "<h2>No Record Found.</h2>";
                            endif;
                            //close connection
                            mysqli_close($conn);
                        endif;
                    endif;
                    ?>
                </div>
                <!-- /post-container -->
            </div>
            <?php include 'sidebar.php'; ?>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>