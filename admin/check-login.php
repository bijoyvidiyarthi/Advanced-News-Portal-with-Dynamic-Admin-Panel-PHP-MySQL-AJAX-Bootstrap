<?php
// Define 30 days in seconds
$expire_after = 30 * 24 * 60 * 60;

if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {

    // Check if the last activity timestamp exists
    if (isset($_SESSION['last_activity'])) {
        $seconds_inactive = time() - $_SESSION['last_activity'];

        if ($seconds_inactive >= $expire_after) {
            // Too much time has passed! Logout.
            session_unset();
            session_destroy();
            header("Location: index.php");
            exit();
        }
    }

    // Update last activity time to 'now' (slidng expiration)
    $_SESSION['last_activity'] = time();
    // 2. NEW FIX: Verify user exists in the CURRENT database
    include "config.php";
    $sess_user_name = $_SESSION['username'];
    $sql_verify = "SELECT username FROM user WHERE username = '{$sess_user_name}'";
    $result_verify = mysqli_query($conn, $sql_verify);

    // print_r($result_verify);
    if (mysqli_num_rows($result_verify) == 0) {
        // User not found in this database!
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit();
    }

} else {
    $_SESSION['error'] = "Please login to access this page.";
    header("Location: index.php");
    exit();
}
?>