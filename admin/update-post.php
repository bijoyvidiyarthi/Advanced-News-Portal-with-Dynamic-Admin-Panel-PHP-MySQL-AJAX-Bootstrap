<?php
include "header.php";

if (isset($_SESSION['user_role'])) {
    $user_role = htmlspecialchars($_SESSION['user_role']);
    $user_id = $_SESSION['user_id'];
} else {
    $_SESSION['error'] = "⚠️ Log in To access editing Post";
    header("Location: index.php");
    exit();
}

//No access without id
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "⚠️ **No ID Provided:** Please provide a valid post ID to update.";
    header("Location: post.php");
    exit();
} else {
    //include connection
    include 'config.php';
    //--- Check connection error ---
    if (!isset($conn)) {
        $_SESSION['error'] = "⚠️ **Database Connection Error:** We are currently unable to connect to the database. Please try again later.";
        header("Location: users.php");
        exit();
    }

    $post_id = mysqli_real_escape_string($conn, $_GET['id']);

    // --- Input Validation ---
    // Error Message for Empty/Invalid Input 
    if (empty($post_id) && !is_numeric($post_id)) {
        $_SESSION['error'] = "⚠️ **Invalid Id. Please try with a valid Id/ id = " . $post_id . "is not a valid id";
        header("Location: post.php");
        mysqli_close($conn);
        exit();
    }

    // --- Check existance of ID in Database and Show Record ---
    $check_sql = "SELECT *  
                  from post p 
                  LEFT JOIN category c ON p.category = c.category_id
                  LEFT JOIN user u ON p.author = u.user_id
                  Where post_id = {$post_id}";

    $check_result = mysqli_query($conn, $check_sql);

    //Authentication

    //Show Error if id is not  found on Database 
    if (!$check_result || mysqli_num_rows($check_result) == 0) {
        $_SESSION['error'] = "🔍 **Record Not Found:** We could not find a post with ID **" . htmlspecialchars($user_id) . "** to delete. Please verify the ID.";
        header("Location: post.php");
        mysqli_close($conn);
        exit();
    } else {
        $rowData = mysqli_fetch_assoc($check_result);
        $post_id = $rowData['post_id'];
        $post_title = $rowData['title'];
        $post_desc = $rowData['description'];
        $category_id = $rowData['category'];
        $category_name = $rowData['category_name'];
        $author_id = $rowData['author'];
        $author_name = $rowData['username'];
        $post_date = $rowData['post_date'];
        $post_image = $rowData['post_img'];

        //check if the logged in user is the author or admin
        if ($user_role != 1 && $author_id != $_SESSION['user_id']) {
            $_SESSION['error'] = "🚫 **You have no post with this ID.**";
            header("Location: post.php");
            mysqli_close($conn);
            exit();
        }
    }
}

// --- Update Post Details ---

?>

<div id="admin-content">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="admin-heading">Update Post</h1>
            </div>
            <div class="col-md-offset-3 col-md-6">
                <!-- Form for show edit-->
                <?php
                if (!empty($_SESSION['error'])):

                    // Use the custom separator (|||) to split the errors back into an array
                    $errors_array = explode("|||", $_SESSION['error']);
                    $err_length = count($errors_array);

                    //if errors length is greater than 1
                    // Display a general heading for context
                    if ($err_length > 1):
                        echo '<div class="alert alert-danger" style="margin-bottom: 10px; border: none; font-weight: bold;">';
                        echo 'Please correct the following issues:';
                        echo '</div>';
                    endif;

                    // Loop through the array to display each error separately
                    foreach ($errors_array as $error):
                        // Ensure the message is not empty before displaying
                        if (trim($error) !== ''):
                            ?>
                            <div class="alert alert-danger" style="margin-bottom: 10px; border: 1px solid #a94442; padding: 10px;">
                                <?php echo htmlspecialchars(trim($error)); ?>
                            </div>
                            <?php
                        endif;
                    endforeach;
                    // Crucially, unset the session error after displaying
                    unset($_SESSION['error']);
                endif;
                ?>
                <form action="save-update.php" method="POST" enctype="multipart/form-data" autocomplete="off">
                    <div class="form-group">
                        <input type="hidden" name="post_id" class="form-control"
                            value="<?php echo htmlspecialchars($post_id); ?>" placeholder="">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputTile">Title</label>
                        <input type="text" name="post_title" class="form-control" id="exampleInputUsername"
                            value="<?php echo htmlspecialchars($post_title); ?>">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1"> Description</label>
                        <textarea name="postdesc" class="form-control" required rows="5">
                    <?php echo htmlspecialchars($post_desc); ?>
                </textarea>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputCategory">Category</label>
                        <select class="form-control" name="category">
                            <?php
                            // Fetch categories from the database
                            if (!isset($conn)) {
                                include "config.php";
                            }

                            $cat_sql = "SELECT * FROM category";
                            $cat_result = mysqli_query($conn, $cat_sql);

                            if ($cat_result) {
                                //now get the categories one by one
                                while ($cat_row = mysqli_fetch_assoc($cat_result)) {
                                    $cat_id = $cat_row['category_id'];
                                    $cat_name = $cat_row['category_name'];
                                    // Check if this category is the one associated with the post
                                    $selected = ($cat_id == $category_id) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($cat_id) . "' $selected >" . htmlspecialchars($cat_name) . "</option>";
                                }
                                //get the new category and old category for update purpose
                                echo "<input type='hidden' name='old_category' value='" . htmlspecialchars($category_id) . "'>";
                            } else {
                                echo "<option value=''>No categories found</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="">Post image</label>
                        <input type="file" name="new-image">
                        <img src="upload/<?php echo htmlspecialchars($post_image); ?>" height="150px"
                            alt="<?php echo htmlspecialchars($post_title); ?>">
                        <input type="hidden" name="old-image" value="<?php echo htmlspecialchars($post_image); ?>">
                    </div>
                    <input type="submit" name="submit" class="btn btn-primary" value="Update" />
                </form>
                <!-- Form End -->
                <!-- /Form -->
                <?php
                mysqli_close($conn);
                ?>
            </div>
        </div>
    </div>
</div>
<?php include "footer.php"; ?>