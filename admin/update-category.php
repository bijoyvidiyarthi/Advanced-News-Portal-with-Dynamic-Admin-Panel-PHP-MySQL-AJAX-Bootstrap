<?php 
include "header.php";

$user_role = htmlspecialchars($_SESSION['user_role']);
if ($user_role != 1) {
    // If the user is not an admin, redirect to post.php or another appropriate page
    header("Location: post.php");
    exit();
}

if (isset($_GET['id'])) {

    //include connection
    include 'config.php';

    //--- Check connection error ---
    if (!isset($conn)) {
        $_SESSION['error'] = "⚠️ **Connection Error:** Something Going Wrong. Please try again later.";
        header("Location: category.php");
        exit();
    }

    $category_id = mysqli_real_escape_string($conn, $_GET['id']);

    // --- Input Validation ---
    // Error Message for Empty/Invalid Input 

    if (empty($category_id) || !is_numeric($category_id)) {
        $_SESSION['error'] = "⚠️ **Invalid Id. Please try with a valid Id/ id = " . $category_id . "is not a valid id";
        header("Location: category.php");
        mysqli_close($conn);
        exit();
    }

    // --- Check existance of ID in Database and Show Record ---
    $check_sql = "SELECT *  from category where category_id = {$category_id}";
    $check_result = mysqli_query($conn, $check_sql);

    //Show Error if id is not  found on Database 
    if (!$check_result || mysqli_num_rows($check_result) == 0) {
        $_SESSION['error'] = "🔍 **Record Not Found:** We could not find a category with ID **" . htmlspecialchars($category_id) . "** to delete. Please verify the ID.";
        header("Location: category.php");
        mysqli_close($conn);
        exit();
    } else {
        $rowData = mysqli_fetch_assoc($check_result);
        $cat_name = $rowData['category_name'];
    }
} else {
    $_SESSION['error'] = "⚠️ **No ID Provided:** Please provide a valid category ID to update.";
    header("Location: category.php");
    exit();
}
?>
  <div id="admin-content">
      <div class="container">
          <div class="row">
              <div class="col-md-12">
                  <h1 class="adin-heading"> Update Category</h1>
              </div>
              <div class="col-md-offset-3 col-md-6">
                <?php 
               
                if (isset($_SESSION['error'])) {
                    echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
                    unset($_SESSION['error']);
                }
                ?>
                  <form action="save-update-category.php" method ="POST"> 
                      <div class="form-group">
                          <input type="hidden" name="cat_id"  class="form-control" value="<?php echo htmlspecialchars($category_id); ?>" placeholder="">
                      </div>
                      <div class="form-group">
                          <label>Category Name</label>
                          <input type="text" name="cat_name" class="form-control" value="<?php echo htmlspecialchars($cat_name); ?>"  placeholder="" required>
                      </div>
                      <input type="submit" name="submit" class="btn btn-primary" value="Update" required />
                  </form>
                </div>
              </div>
            </div>
          </div>
<?php include "footer.php"; ?>
