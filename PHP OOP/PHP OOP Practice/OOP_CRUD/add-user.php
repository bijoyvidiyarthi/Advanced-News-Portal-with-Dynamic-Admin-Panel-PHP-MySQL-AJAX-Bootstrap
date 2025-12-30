<?php
include "DATABASE2.php";

// Top of add-user.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
try {
    $db = new database();

    // check connection with DB
    if (!$db->mysqli || $db->mysqli->connect_error) {
        throw new Exception("⚠️ **Database Connection Error:** Connection failed.");
    }

    //check if the form is submitted
    if (isset($_POST['save'])) {

        // --- 1. Sanitize Inputs ---
        $st_name = trim($_POST['st_name']);
        $city = trim($_POST['city']);

        // Validate that age is an actual integer
        $age = filter_var($_POST['age'], FILTER_VALIDATE_INT);

        // --- 2. Validation ---
        if (!$st_name || !$city || $age === false) {
            throw new Exception("Please provide a valid name, city, and numeric age.");
        }


        // --- Check existance of 'student_name' in Database ---
        $db->select("students", "*", null, "student_name  = '$st_name'");
        $check_result = $db->getResult();

        //Show Error if id is not  found on Database 
        if (count($check_result) > 0) {
            throw new Exception("🔍Username Already used, try a new username.");
        }

        $data = [
            "student_name" => "$st_name",
            "age" => (int) $age,
            "city" => "$city"
        ];

        if ($db->insert('students', $data)) {
            $_SESSION['success'] = "Data Inserted Successfully";
            header("Location: users.php");
            exit();
        }
    }

} catch (Exception $e) {

    $_SESSION['error'] = $e->getMessage();
    header("Location: add-user.php");
    exit();
}

include 'header.php';
?>
<div id="admin-content">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="admin-heading">Add User</h1>
            </div>
            <div class="col-md-offset-3 col-md-6">
                <?php

                if (isset($_SESSION['success'])) {
                    $success_msg = htmlspecialchars($_SESSION['success']);
                    echo "<div class='alert alert-success'>$success_msg</div>";
                    unset($_SESSION['success']);
                }
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
                            <div id="alert-box" class="alert alert-danger"
                                style="margin-bottom: 10px; border: 1px solid #a94442; padding: 10px;">
                                <?php echo htmlspecialchars(trim($error)); ?>
                            </div>
                            <?php
                        endif;
                    endforeach;
                    // Crucially, unset the session error after displaying
                    unset($_SESSION['error']);
                endif;
                ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" autocomplete="off">
                    <div class="form-group">
                        <label>Student Name</label>
                        <input type="text" name="st_name" class="form-control" placeholder="Student Name" required>
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="text" name="age" class="form-control" placeholder="Age" required>
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" class="form-control" placeholder="City" required>
                    </div>

                    <input type="submit" name="save" class="btn btn-primary" value="Save" required />
                </form>
            </div>
        </div>
    </div>
</div>
<?php include "footer.php"; ?>