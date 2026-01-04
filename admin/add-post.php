<?php
/**
 * 1. INITIALIZATION & SESSION
 */
include "header.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//csrf token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<div id="admin-content">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="admin-heading">Add New Post</h1>
                <p class="text-muted">Posting as: <b><?php echo $_SESSION['username']; ?></b></p>
            </div>
            <div class="col-md-offset-3 col-md-6">

                <?php
                if (isset($_SESSION['error'])):
                    $errors_array = explode("|||", $_SESSION['error']);
                    foreach ($errors_array as $error):
                        if (trim($error) !== ''):
                            echo '<div class="alert alert-danger">' . htmlspecialchars(trim($error)) . '</div>';
                        endif;
                    endforeach;
                    unset($_SESSION['error']);
                endif;
                ?>

                <!-- Form -->
                <form action="save-post.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="form-group">
                        <label for="post_title">Title</label>
                        <input type="text" name="post_title" class="form-control" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="postdesc" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" class="form-control" required>
                            <option value="" selected disabled>Select Category</option>
                            <?php
                            include "config.php";
                            $select_query = "SELECT * FROM category";
                            $select_result = mysqli_query($conn, $select_query);
                            while ($row = mysqli_fetch_assoc($select_result)) {
                                echo "<option value='{$row['category_id']}'>{$row['category_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Post image</label>
                        <input type="file" name="fileToUpload" required>
                    </div>
                    <input type="submit" name="submit" class="btn btn-primary" value="Save" />
                </form>
            </div>
        </div>
    </div>
</div>
<?php include "footer.php"; ?>