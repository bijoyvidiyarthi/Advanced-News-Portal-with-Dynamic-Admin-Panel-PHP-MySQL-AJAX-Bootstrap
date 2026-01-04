<?php
include "header.php";
include 'config.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//csrf token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user_role = $_SESSION['user_role']; //get user role, to valid authorization
$user_id = $_SESSION['user_id']; //get user id, to access edit post

// 1. VALIDATE ID PRESENCE
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "⚠️ **No ID Provided:** Please provide a valid post ID.";
    header("Location: post.php");
    exit();
}

$post_id = mysqli_real_escape_string($conn, $_GET['id']);

// 2. VALIDATE ID NUMERIC
if (!is_numeric($post_id)) {
    $_SESSION['error'] = "⚠️ **Invalid ID:** The provided ID is not a number.";
    header("Location: post.php");
    exit();
}


// 3. FETCH RECORD
$check_sql = "SELECT p.*, c.category_name, u.username 
              FROM post p 
              LEFT JOIN category c ON p.category = c.category_id
              LEFT JOIN user u ON p.author = u.user_id
              WHERE p.post_id = {$post_id}";

$check_result = mysqli_query($conn, $check_sql);

if (!$check_result || mysqli_num_rows($check_result) == 0) {
    $_SESSION['error'] = "🔍 **Record Not Found:** Post ID $post_id does not exist.";
    header("Location: post.php");
    exit();
}

$rowData = mysqli_fetch_assoc($check_result);

// 4. AUTHORIZATION CHECK
if ($user_role != 1 && $rowData['author'] != $user_id) {
    $_SESSION['error'] = "🚫 **Access Denied:** You are not the author of this post.";
    header("Location: post.php");
    exit();
}
?>

<div id="admin-content">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="admin-heading">Update Post</h1>
            </div>
            <div class="col-md-offset-3 col-md-6">
                <?php
                // Standard Error Display
                if (isset($_SESSION['error'])) {
                    $errors_array = explode("|||", $_SESSION['error']);
                    foreach ($errors_array as $error) {
                        echo "<div class='alert alert-danger'>" . htmlspecialchars($error) . "</div>";
                    }
                    unset($_SESSION['error']);
                }
                ?>

                <form action="save-update.php" method="POST" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="post_id" value="<?php echo $rowData['post_id']; ?>">
                    <input type="hidden" name="old_category" value="<?php echo $rowData['category']; ?>">

                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="post_title" class="form-control"
                            value="<?php echo htmlspecialchars($rowData['title']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="postdesc" class="form-control" required
                            rows="5"><?php echo htmlspecialchars($rowData['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select class="form-control" name="category">
                            <?php
                            $cat_sql = "SELECT * FROM category";
                            $cat_result = mysqli_query($conn, $cat_sql);
                            while ($cat_row = mysqli_fetch_assoc($cat_result)) {
                                $selected = ($cat_row['category_id'] == $rowData['category']) ? "selected" : "";
                                echo "<option {$selected} value='{$cat_row['category_id']}'>{$cat_row['category_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Post image</label>
                        <input type="file" name="new-image">
                        <img src="upload/<?php echo $rowData['post_img']; ?>" height="150px"
                            style="margin-top:10px; display:block;">
                        <input type="hidden" name="old-image" value="<?php echo $rowData['post_img']; ?>">
                    </div>
                    <input type="submit" name="submit" class="btn btn-primary" value="Update" />
                </form>
            </div>
        </div>
    </div>
</div>
<?php
mysqli_close($conn);
include "footer.php";
?>