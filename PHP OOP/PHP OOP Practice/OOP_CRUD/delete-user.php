<?php
include "DATABASE2.php";

// Top of add-user.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
//cannot access this page if not admin and without login
try {
    //include connection
    $db = new database();

    // Explicit safety check for the mysqli object
    if (!$db->mysqli || $db->mysqli->connect_error) {
        throw new Exception("⚠️ **Database Connection Error:** Connection failed.");
    }

    $aid = $_GET['aid'] ?? $_POST['user_id'] ?? null;
    $user_id = filter_var($aid, FILTER_VALIDATE_INT);

    // --- Id Validation ---
    if ($user_id === false || $user_id <= 0) {
        // Using $aid here shows the actual bad input (like "abc" or "-5")
        throw new Exception("⚠️ **Invalid Id:** '" . htmlspecialchars($aid) . "' is not a valid ID.");
    }

    // --- Check existance of ID in Database and Show Record ---
    $db->select("students", "*", null, "id = $user_id");
    $check_result = $db->getResult();

    //Show Error if id is not  found on Database 
    if (!$check_result || count($check_result) == 0) {
        throw new Exception("🔍 **Record Not Found:** We could not find a user with ID **" . htmlspecialchars($user_id) . "** to delete. Please verify the ID.");
    }

    // Perform Update - Ensure 'id' matches your DB column name
    if ($db->delete("students", "id = '$user_id'")) {
        $_SESSION['success'] = "User Deleted successfully.";
        header("Location: add-user.php");
        exit();
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: add-user.php");
    exit();
}
?>