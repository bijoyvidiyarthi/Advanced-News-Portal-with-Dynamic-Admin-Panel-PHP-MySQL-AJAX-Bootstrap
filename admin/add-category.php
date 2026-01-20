<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "config.php";

//cannot access this page if not admin and without login
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    $_SESSION['error'] = "🚫 **Access Denied:** You do not have permission to access this page.";
    header("Location: post.php");
    exit();
}

if (isset($_POST['save'])) {
  
    // ---  Sanitize and Get Variables ---
    $c_name = mysqli_real_escape_string($conn, $_POST['cat']);
    // --- Input Validation Checks: ---
    // required input is submitted
    if (empty($c_name)) {
         $_SESSION['error'] = "Fill the category name";
        // Redirect once, and stop execution
        header("Location: add-category.php");
        mysqli_close($conn);
        exit();
    }

    // Check if Category Name already exists
    $select_query = "SELECT category_name FROM `category` WHERE category_name = '{$c_name}'";
    $select_result = mysqli_query($conn, $select_query) or die("Query Failed.");

    if (mysqli_num_rows($select_result) > 0) {
        $_SESSION['error'] = "This Category name already exists. Please try a different name.";
        header("Location: add-category.php");
        mysqli_close($conn);
        exit();
    }

    // --- Database Insertion ---
    $sql = "INSERT into `category`(`category_name`)
            VALUES ('$c_name')";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $_SESSION['success'] = "Category added successfully.";
        header("Location: category.php");
    } else {
        $_SESSION['error'] = "Error adding new Category.";
        header("Location: add-category.php");
    }
    mysqli_close($conn);
}

include "includes/header.php";
include "includes/sidebar.php";

?>
  <div id="admin-content">
      <div class="container">
          <div class="row">
              <div class="col-md-12">
                  <h1 class="admin-heading">Add New Category</h1>
              </div>
              <div class="col-md-offset-3 col-md-6">
                  <!-- Form Start -->
                  <form action="" method="POST" autocomplete="off">
                      <div class="form-group">
                          <label>Category Name</label>
                          <input type="text" name="cat" class="form-control" placeholder="Category Name" required>
                      </div>
                      <input type="submit" name="save" class="btn btn-primary" value="Save" required />
                  </form>
                  <!-- /Form End -->
              </div>
          </div>
      </div>
  </div>
<?php include "includes/footer.php"; ?>
