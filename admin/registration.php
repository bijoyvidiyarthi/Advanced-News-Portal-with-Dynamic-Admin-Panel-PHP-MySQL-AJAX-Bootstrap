<?php
/**
 * 1. INITIALIZATION & SESSION
 */
include "config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    header("Location: post.php");
    exit();
}

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * 2. FORM PROCESSING LOGIC
 */
if (isset($_POST['register'])) {

    $errors = [];

    // CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Security token mismatch.";
    }

    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $user = trim($_POST['user']);
    $raw_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 0; //Default: Normal User.

    // --- Validation Logic ---
    if (empty($fname) || empty($lname) || empty($user) || empty($raw_password)) {
        $errors[] = "All fields are required.";
    }

    if (!preg_match("/^[a-z0-9_]+$/", $user)) {
        $errors[] = "Username: lowercase, numbers, and underscores only.";
    }

    // Check if passwords match
    if ($raw_password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Password Strength
    if (!empty($raw_password)) {
        if (strlen($raw_password) < 8)
            $errors[] = "Password must be at least 8 characters.";
        if (!preg_match('/\d/', $raw_password))
            $errors[] = "Password needs at least one number.";
        if (!preg_match('/[A-Z]/', $raw_password))
            $errors[] = "Password needs at least one uppercase letter.";
        if (!preg_match('/[a-z]/', $raw_password))
            $errors[] = "Password needs at least one lowercase letter.";
        if (!preg_match('/\W/', $raw_password))
            $errors[] = "Password needs at least one special character.";
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
            $_SESSION['success'] = "🎉 Registration successful! You can now login.";
            mysqli_stmt_close($stmt);
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "⚠️ Database error.";
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registration | News Site</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css" />
    <!-- Including Font Awesome for the eye icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div id="wrapper-admin" class="registration-container">
        <div class="container">
            <div class="row">
                <!-- Larger width: col-md-8 instead of col-md-4 -->
                <div class="col-md-offset-2 col-md-8">
                    <div class="registration-card">
                        <img class="logo-center" src="images/news.jpg" alt="Logo">
                        <h3 class="heading text-center" style="margin-bottom: 30px;">Register New Account</h3>

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

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>First Name</label>
                                        <input type="text" name="fname" class="form-control" value="<?php echo isset($fname) ? htmlspecialchars($fname) : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Last Name</label>
                                        <input type="text" name="lname" class="form-control" value="<?php echo isset($lname) ? htmlspecialchars($lname) : ''; ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>User Name</label>
                                        <input type="text" name="user" class="form-control" value="<?php echo isset($user) ? htmlspecialchars($user) : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>User Role</label>
                                        <select class="form-control" name="role" disabled>
                                            <option value="0" selected>Normal User</option>
                                        </select>
                                        <small class="text-muted">Public registrations are assigned 'Normal User' role.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group password-container">
                                        <div style="width: 100%;">
                                            <label>Password</label>
                                            <input type="password" name="password" id="password" class="form-control" required>
                                            <i class="fa-solid fa-eye toggle-password" onclick="toggleVisibility('password', this)"></i>
                                            <small class="text-muted">Min 8 chars, 1 uppercase, 1 special char.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group password-container">
                                        <div style="width: 100%;">
                                            <label>Confirm Password</label>
                                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                                            <i class="fa-solid fa-eye toggle-password" onclick="toggleVisibility('confirm_password', this)"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row" style="margin-top: 20px;">
                                <div class="col-md-12">
                                    <input type="submit" name="register" class="btn btn-primary btn-block" value="Register" />
                                </div>
                            </div>

                            <p class="text-center">Already have an account? <a href="index.php">Login</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    < <?php
    if (isset($conn)) {
        mysqli_close($conn);
    }
    include "footer.php";
    ?>