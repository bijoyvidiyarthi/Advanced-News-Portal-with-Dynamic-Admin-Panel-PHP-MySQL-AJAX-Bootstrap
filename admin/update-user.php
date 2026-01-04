<?php

include "header.php";
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * 2. ACCESS CONTROL
 */
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    $_SESSION['error'] = "🚫 Access Denied: Admin permission required.";
    header("Location: post.php");
    exit();
}

//csrf token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

//check if user id provided or not
if (!isset($_GET['aid'])) {
    $_SESSION['error'] = "⚠️ **No ID Provided:** Please provide a valid post ID to update.";
    header("Location: post.php");
    exit();
}

//--- Check connection error ---
if (!isset($conn)) {
    $_SESSION['error'] = "⚠️ **Database Connection Error:** We are currently unable to connect to the database. Please try again later.";
    header("Location: users.php");
    exit();
}

$user_id = mysqli_real_escape_string($conn, $_GET['aid']);

// --- Input Validation ---
// Error Message for Empty/Invalid Input 

if (empty($user_id) || !is_numeric($user_id)) {
    $_SESSION['error'] = "⚠️ **Invalid Id. Please try with a valid Id/ id = " . $user_id . "is not a valid id";
    header("Location: users.php");
    mysqli_close($conn);
    exit();
}


// --- Check existance of ID in Database and Show Record ---
$check_sql = "SELECT *  from user where user_id = {$user_id}";
$check_result = mysqli_query($conn, $check_sql);



//Show Error if id is not  found on Database 
if (!$check_result || mysqli_num_rows($check_result) == 0) {
    $_SESSION['error'] = "🔍 **Record Not Found:** We could not find a student with ID **" . htmlspecialchars($user_id) . "** to delete. Please verify the ID.";
    header("Location: users.php");
    mysqli_close($conn);
    exit();
} else {
    //fill data form with this data
    $rowData = mysqli_fetch_assoc($check_result);
    $u_id = $rowData['user_id'];
    $f_name = $rowData['first_name'];
    $l_name = $rowData['last_name'];
    $user_name = $rowData['username'];
    $Role = $rowData['role'];
}

// --- Update User Details ---
if (isset($_POST['submit'])) {

    $errors = [];
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Security token mismatch.";
    }

    //updated data submitted to the Form
    $userid = mysqli_real_escape_string($conn, $_POST['user_id']);
    $fname = mysqli_real_escape_string($conn, $_POST['f_name']);
    $lname = mysqli_real_escape_string($conn, $_POST['l_name']);
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $role = (int) $_POST['role'];

    // --- Validation Logic ---
    if (empty($fname) || empty($lname) || empty($user)) {
        $errors[] = "All fields are required.";
    }

    if (!preg_match("/^[a-z0-9_]+$/", $user)) {
        $errors[] = "Username: lowercase, numbers, and underscores only.";
    }

    // Check for Existing Username
    $check_stmt = mysqli_prepare($conn, "SELECT username FROM user WHERE username = ?");
    mysqli_stmt_bind_param($check_stmt, "s", $user);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        $errors[] = "Username '$user' already exists.";
    }
    mysqli_stmt_close($check_stmt);

    // --- Execution ---
    if (!empty($errors)) {
        $_SESSION['error'] = implode("|||", $errors);
    } else {
        // --- Execution ---
        $sql_update = "UPDATE `user` SET first_name='$fname', last_name='$lname', username='$user', role='$role'
            WHERE user_id={$userid}";

        $result_update = mysqli_query($conn, $sql_update) or die("Query Failed.");

        if ($result_update) {
            $_SESSION['success'] = "User updated successfully.";
            header("Location: users.php");
            mysqli_close($conn);
        } else {
            $_SESSION['error'] = "Could not update user.";
            //if error show this error message in this page
            header("Location: $_SERVER[PHP_SELF]");
            mysqli_close($conn);
        }
    }
}
?>
<div id="admin-content">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="admin-heading">Modify User Details</h1>
            </div>
            <div class="col-md-offset-4 col-md-4">
                <!-- Form Start -->
                <?php
                //first check connection is established or not
                if (isset($_SESSION['error'])) {
                    echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
                    unset($_SESSION['error']);
                }
                ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="form-group">
                        <input type="hidden" name="user_id" class="form-control" value="<?php echo $u_id; ?>"
                            placeholder="">
                    </div>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="f_name" class="form-control" value="<?php echo $f_name; ?>"
                            placeholder="" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="l_name" class="form-control" value="<?php echo $l_name; ?>"
                            placeholder="" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" value="<?php echo $user_name; ?>"
                            placeholder="" required>
                    </div>
                    <div class="form-group">
                        <label>User Role</label>
                        <select class="form-control" name="role" value="<<?php echo $Role; ?>">
                            <option value="0" <?php if ($Role == 0)
                                echo 'selected' ?>>normal User</option>
                                <option value="1" <?php if ($Role == 1)
                                echo 'selected' ?>>Admin</option>
                            </select>
                        </div>
                        <input type="submit" name="submit" class="btn btn-primary" value="Update" required />
                    </form>
                    <!-- /Form -->
                    <?php
                            mysqli_close($conn);
                            ?>
            </div>
        </div>
    </div>
</div>
<?php include "footer.php"; ?>