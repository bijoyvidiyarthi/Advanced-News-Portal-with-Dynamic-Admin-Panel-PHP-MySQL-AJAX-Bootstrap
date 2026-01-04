<?php
/**
 * 1. INITIALIZATION & SESSION
 */
include "header.php";
include "config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//csrf token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * 2. ACCESS CONTROL
 */
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    $_SESSION['error'] = "🚫 Access Denied: Admin permission required.";
    header("Location: post.php");
    exit();
}

/**
 * 3. FORM PROCESSING LOGIC
 */
if (isset($_POST['save'])) {

    $errors = [];

    // CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Security token mismatch.";
    }

    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $user  = trim($_POST['user']);
    $role  = (int)$_POST['role'];
    $raw_password     = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // --- Validation Logic ---
    if (empty($fname) || empty($lname) || empty($user) || empty($raw_password)) {
        $errors[] = "All fields are required.";
    }

    if (!preg_match("/^[a-z0-9_]+$/", $user)) {
        $errors[] = "Username: lowercase, numbers, and underscores only.";
    }

    // New: Check if passwords match
    if ($raw_password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Password Strength
    if (!empty($raw_password)) {
        if (strlen($raw_password) < 8) $errors[] = "Password must be at least 8 characters.";
        if (!preg_match('/\d/', $raw_password)) $errors[] = "Password needs at least one number.";
        if (!preg_match('/[A-Z]/', $raw_password)) $errors[] = "Password needs at least one uppercase letter.";
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
        $hashed_password = password_hash($raw_password, PASSWORD_BCRYPT);

        $insert_sql = "INSERT INTO user (first_name, last_name, username, password, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $fname, $lname, $user, $hashed_password, $role);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "🎉 User added successfully!";
            mysqli_stmt_close($stmt);
            header("Location: users.php"); 
            exit();
        } else {
            $_SESSION['error'] = "⚠️ Database error.";
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<div id="admin-content">
    <div class="container">
        <div class="row">
            <div class="col-md-offset-3 col-md-6">
                <h1 class="admin-heading">Add User</h1>

                <?php
                if (isset($_SESSION['success'])) {
                    echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
                    unset($_SESSION['success']);
                }

                if (isset($_SESSION['error'])):
                    $errors_array = explode("|||", $_SESSION['error']);
                    foreach ($errors_array as $err) {
                        echo "<div class='alert alert-danger'>$err</div>";
                    }
                    unset($_SESSION['error']);
                endif;
                ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" autocomplete="off">  
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="fname" class="form-control" value="<?php echo isset($fname) ? htmlspecialchars($fname) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="lname" class="form-control" value="<?php echo isset($lname) ? htmlspecialchars($lname) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>User Name</label>
                        <input type="text" name="user" class="form-control" value="<?php echo isset($user) ? htmlspecialchars($user) : ''; ?>" required>
                    </div>

                    <div class="form-group password-container">
                        <label>Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                        <i class="fa-solid fa-eye toggle-password" onclick="toggleVisibility('password', this)"></i>
                        <small class="text-muted">Min 8 chars, 1 uppercase, 1 number.</small>
                    </div>

                    <div class="form-group password-container">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                        <i class="fa-solid fa-eye toggle-password" onclick="toggleVisibility('confirm_password', this)"></i>
                    </div>

                    <div class="form-group">
                        <label>User Role</label>
                        <select class="form-control" name="role">
                            <option value="0" <?php echo (isset($role) && $role == 0) ? 'selected' : ''; ?>>Normal User</option>
                            <option value="1" <?php echo (isset($role) && $role == 1) ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>

                    <input type="submit" name="save" class="btn btn-primary" value="Save" />
                    <a href="users.php" class="btn btn-default">Cancel</a>
                </form>

            </div>
        </div>
    </div>
</div>



<?php
if (isset($conn)) {
    mysqli_close($conn);
}
include "footer.php";
?>