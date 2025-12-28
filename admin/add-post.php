<?php
include "header.php";
//cannot access this page if not admin and without login
?>
<div id="admin-content">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="admin-heading">Add New Post</h1>
            </div>
            <div class="col-md-offset-3 col-md-6">

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
                <!-- Form -->
                <form action="save-post.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="post_title">Title</label>
                        <input type="text" name="post_title" class="form-control" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1"> Description</label>
                        <textarea name="postdesc" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Category</label>
                        <select name="category" class="form-control">
                            <option disabled> Select Category</option>
                            <?php
                            include "config.php";
                            $select_query = "SELECT * FROM category";
                            $select_result = mysqli_query($conn, $select_query) or die("Query Failed.");

                            if (!$select_result) {
                                $_SESSION['error'] = "Something went wrong.";
                                header("Location: add-post.php");
                                mysqli_close($conn);
                                exit();
                            }

                            if (mysqli_num_rows($select_result) > 0) {
                                while ($row = mysqli_fetch_assoc($select_result)) {
                                    $id = $row['category_id'];
                                    $category_name = $row['category_name'];
                                    echo "<option value='{$id}'>{$category_name}</option>";
                                }

                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Post image</label>
                        <input type="file" name="fileToUpload" required>
                    </div>
                    <input type="submit" name="submit" class="btn btn-primary" value="Save" required />
                </form>
                <!--/Form -->
            </div>
        </div>
    </div>
</div>
<?php include "footer.php"; ?>