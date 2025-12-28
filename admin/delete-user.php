<?php
session_start();
// check if user is admin
//cannot access this page if not admin and without login
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    $_SESSION['error'] = "🚫 **Access Denied:** You do not have permission to access this page.";
    header("Location: post.php");
    exit();
} else {
    $user_role = htmlspecialchars($_SESSION['user_role']);
}

// Check if 'id' parameter is set or not
if (isset($_GET['id'])) {

    //include connection
    include 'config.php';
    
   //--- Check connection error ---
    if (!isset($conn)) {
        $_SESSION['error'] = "⚠️ **Database Connection Error:** We are currently unable to connect to the database. Please try again later.";
        header("Location: users.php");
        exit();
    }

    $user_id = mysqli_real_escape_string($conn, $_GET['id']);

    // --- Input Validation ---
    //  Error Message for Empty/Invalid Input ---

    if (empty($user_id) || !is_numeric($user_id)) {
        $_SESSION['error'] = "⚠️ **Invalid Id. Please try with a valid Id/ id = " . $user_id . "is not a valid id";
        header("Location: users.php");
        mysqli_close($conn);
        exit();
    }

    // --- Check existence of ID in Database ---
    $check_sql = "SELECT *  from user where user_id = {$user_id}";
    $check_result = mysqli_query($conn, $check_sql);


    //Show Error if id is not  found on Database 
    if (!$check_result || mysqli_num_rows($check_result) == 0) {
        echo "ERROR:" . $_SESSION['error'];
        $_SESSION['error'] = "🔍 **Record Not Found:** We could not find a student with ID **" . htmlspecialchars($user_id) . "** to delete. Please verify the ID.";
        header("Location: users.php");
        mysqli_close($conn);
        exit();
    } else {
        echo "Record Found. Proceeding to delete.";
    }

    // --- Delete Records---
    //Delete SQL
    $delSql = "DELETE FROM user where user_id = {$user_id}";
    $del_result = mysqli_query($conn, $delSql);

    // Store the result of the deletion attempt
    if ($del_result) {

        //check if any Rows/recodrds Has been Deleted
        $deleted_count = mysqli_affected_rows($conn);

        // echo "deleted count:" . $deleted_count;

        if ($deleted_count > 0) {
            $_SESSION['success'] = "✅ **Success!** Record for ID **" . htmlspecialchars($user_id) . "** has been successfully deleted.";
        } else {
            // This handles a successful query that deleted 0 rows 
            // (e.g., if ID was deleted between check and delete)
            $_SESSION['error'] = "❌ **Deletion Failed:** The record for ID **" . htmlspecialchars($user_id) . "** could not be deleted at this time, possibly due to a recent change.";
        }
    } else {
        $_SESSION['error'] = "🚫 **Server Error:** An unexpected error occurred while processing the request. Details: " . mysqli_error($conn);
    }

    // Close connection and redirect regardless of success/error
    mysqli_close($conn);
    header("Location: users.php");
    exit();
}

?>