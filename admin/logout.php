<?php

include_once "/config.php";

// Step 1: Empty the $_SESSION array in the current script
$_SESSION = array();

// Step 2: Delete the browser cookie (The code you asked about)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Step 3: Destroy the session file on the server
session_destroy();

// Step 4: Send the user to the login page
header("Location: " . BASE_URL . "index.php");
exit();
?>

?>