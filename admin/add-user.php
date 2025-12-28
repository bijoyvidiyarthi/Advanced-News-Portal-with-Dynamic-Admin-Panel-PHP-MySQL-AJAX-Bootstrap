<?php
include "header.php";
// 1. Access Control
// THE SHORTCUT: This one line replaces your entire IF block
Auth::adminAccess();

//check if the form is submitted
if (isset($_POST['save'])) {
    try {
        // We only instantiate ONE class here
        $gateway = new UserGateway();

        // Pass the raw data directly
        $userData = [
            "first_name" => $_POST['fname'],
            "last_name" => $_POST['lname'],
            "username" => $_POST['user'],
            "password" => $_POST['password'],
            "role" => $_POST['role']
        ];

        // The Gateway handles validation internally!
        if ($gateway->createUser($userData)) {
            $_SESSION['success'] = "User created Successfully!";
            header("Location: users.php");
            exit();
        }

    } catch (Exception $e) {
        errors::showError("Database Error: " . $e->getMessage(), "add-user.php");
    }
}
?>
<div id="admin-content">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="admin-heading">Add User</h1>
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

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" autocomplete="off">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="fname" class="form-control" placeholder="First Name" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="lname" class="form-control" placeholder="Last Name" required>
                    </div>
                    <div class="form-group">
                        <label>User Name</label>
                        <input type="text" name="user" class="form-control" placeholder="Username" required>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <div class="form-group">
                        <label>User Role</label>
                        <select class="form-control" name="role">
                            <option value="0">Normal User</option>
                            <option value="1">Admin</option>
                        </select>
                    </div>
                    <input type="submit" name="save" class="btn btn-primary" value="Save" required />
                </form>
            </div>
        </div>
    </div>
</div>
<?php include "footer.php"; ?>