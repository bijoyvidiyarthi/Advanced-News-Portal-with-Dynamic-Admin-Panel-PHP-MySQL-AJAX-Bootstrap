<?php
include "header.php";
include "config.php";
?>
<div id="admin-content">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="admin-heading">Website Settings</h1>
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

                //collect data from Settings table In Database
                //ensure connection first
                if (!$conn) {
                    $_SESSION['error'] = "⚠️ There is something wrong in Setting Page";
                    header("Location: post.php");
                    mysqli_close($conn);
                    exit();
                }

                $sql = "SELECT * FROM settings";
                $result = mysqli_query($conn, $sql) or die("Query Failed.");
                if (!$result) {
                    $_SESSION['error'] = "Error fetching settings: " . mysqli_error($conn);
                    //this will redirect to same page again and again if error persists
                    //so we can think of better error handling later
                    // header("Location: settings.php");
                    header("Location: post.php");
                    exit();
                }
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <!-- Form -->
                        <form action="save-settings.php" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="website_name">Website Name</label>
                                <input type="text" name="website_name" value="<?php echo $row['websitename']; ?>"
                                    class="form-control" autocomplete="off" required>
                            </div>
                            <div class="form-group">
                                <label for="logo">Website Logo</label>
                                <input type="file" name="logo">
                                <img src="images/<?php echo $row['logo']; ?>">
                                <input type="hidden" name="old_logo" value="<?php echo $row['logo']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="footer_desc">Footer Description</label>
                                <textarea name="footer_desc" class="form-control" rows="5"
                                    required><?php echo $row['footerdesc']; ?></textarea>
                            </div>
                            <input type="submit" name="submit" class="btn btn-primary" value="Save" required />
                        </form>
                        <!--/Form -->
                        <?php
                    }
                } else {
                    echo "<h3>No Settings Found.</h3>";
                }
                mysqli_close($conn);
                ?>
            </div>
        </div>
    </div>
</div>
<?php include "footer.php"; ?>