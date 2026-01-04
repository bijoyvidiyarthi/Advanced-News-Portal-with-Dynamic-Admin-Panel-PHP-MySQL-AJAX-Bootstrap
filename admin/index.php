<?php
// 1. Set session cookie to last 1 month (2,592,000 seconds)
$session_lifetime = 30 * 24 * 60 * 60;
session_set_cookie_params($session_lifetime);
session_start();

// Redirect if already logged in
if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    $_SESSION['error'] = "You are logged in.";
    header("Location: post.php");
    exit();
}
?>
<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ADMIN | Login</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css" />
    <link rel="stylesheet" href="font/font-awesome-4.7.0/css/font-awesome.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div id="wrapper-admin" class="body-content">
        <div class="container">
            <div class="row">
                <div class="col-md-offset-4 col-md-4">
                    <img class="logo" src="images/news.jpg">
                    <h3 class="heading">Admin</h3>
                    <?php
                    if (isset($_SESSION['success'])) {
                        $success_msg = htmlspecialchars($_SESSION['success']);
                        echo "<div class='alert alert-success'>$success_msg</div>";
                        unset($_SESSION['success']);
                    }

                    if (isset($_SESSION['error'])) {
                        $error_message = htmlspecialchars($_SESSION['error']);
                        echo "<div class='alert alert-danger'>$error_message</div>";
                        unset($_SESSION['error']);
                    }
                    ?>
                    <!-- Form Start -->
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" placeholder="" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" placeholder="" required>
                        </div>
                        <input type="submit" name="login" class="btn btn-primary" value="login" />

                        <div class="text-center">
                            New here? <a href="registration.php">Create an account</a>
                        </div>
                    </form>
                    <!-- /Form  End -->
                    <?php
                    $_SESSION['is_logged_in'] = false;

                    if (isset($_POST['login'])) {
                        include "config.php";
                        $username = mysqli_real_escape_string($conn, $_POST['username']);
                        $password = $_POST['password'];


                        // Use Prepared Statement for login
                        $sql = "SELECT user_id, username, password, role FROM user WHERE username = ?";
                        $stmt = mysqli_prepare($conn, $sql);
                        mysqli_stmt_bind_param($stmt, "s", $username);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);




                        if ($row = mysqli_fetch_assoc($result)) {
                            // --- DEBUGGING START ---
                            // echo "Entered: " . $password . "<br>";
                            // echo "From DB: " . $row['password'] . "<br>";
                            // --- DEBUGGING END ---
                            // SECURE CHECK: Compare raw password with hashed password from DB
                            if (password_verify($password, $row['password'])) {

                                $_SESSION['user_id'] = $row['user_id'];
                                $_SESSION['username'] = $row['username'];
                                $_SESSION['user_role'] = $row['role'];
                                $_SESSION['is_logged_in'] = true;

                                // Set initial activity timestamp
                                $_SESSION['last_activity'] = time();

                                header("Location: post.php");
                                exit();
                            } else {

                                $_SESSION['error'] = "Invalid Username or Password.";
                                header("Location: index.php");
                                exit();
                            }
                        } else {


                            $_SESSION['error'] = "Invalid Username or Password.";
                            header("Location: index.php");
                            exit();
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>