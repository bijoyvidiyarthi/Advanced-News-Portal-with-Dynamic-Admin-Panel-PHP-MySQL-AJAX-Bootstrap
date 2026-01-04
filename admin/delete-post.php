<?php
session_start();
//cannot access this page without login
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    $_SESSION['error'] = "🚫 **Access Denied:** Login to access this page.";
    header("Location: index.php");
    exit();
} else {
    $user_role = htmlspecialchars($_SESSION['user_role']);
}

if (isset($_GET['id'])) {

    //include connection
    include 'config.php';


    //--- Check connection error ---
    if (!isset($conn)) {
        $_SESSION['error'] = "⚠️ **Database Connection Error:** We are currently unable to connect to the database. Please try again later.";
        header("Location: post.php");
        exit();
    }

    $post_id = mysqli_real_escape_string($conn, $_GET['id']);
    $cat_id = mysqli_real_escape_string($conn, $_GET['catid']);

    // --- Input Validation ---
    //  Error Message for Empty/Invalid Input ---

    if (empty($post_id) || !is_numeric($post_id)) {
        $_SESSION['error'] = "⚠️ **Invalid Id. Please try with a valid Id/ id = " . $post_id . "is not a valid id";
        header("Location: post.php");
        mysqli_close($conn);
        exit();
    }


    // --- Check existence of ID in Database ---

    $check_sql = "SELECT *  from post where post_id = {$post_id}";
    $check_result = mysqli_query($conn, $check_sql);


    //Show Error if id is not  found on Database 
    if (!$check_result || mysqli_num_rows($check_result) == 0) {
        echo "ERROR:" . $_SESSION['error'];
        $_SESSION['error'] = "🔍 **Record Not Found:** We could not find a student with ID **" . htmlspecialchars($post_id) . "** to delete. Please verify the ID.";
        header("Location: post.php");
        mysqli_close($conn);
        exit();
    } else {
        $row = mysqli_fetch_assoc($check_result);
        $author_id = $row['author'];

        //------ restrict unauthorized deletion ---------
        //check if the logged in user is the author or admin
        if ($user_role != 1 && $author_id != $_SESSION['user_id']) {
            $_SESSION['error'] = "🚫 **You have no post with this ID.**";
            header("Location: post.php");
            mysqli_close($conn);
            exit();
        }

        //Delete Image File from folder
        $image_path = "upload/" . $row['post_img'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    // --- Delete Records---
    
    //Delete SQL
    $sql = "DELETE FROM post where post_id = {$post_id};";
    $sql .= "UPDATE category SET post = post - 1 WHERE category_id = {$cat_id};";

    $del_result = mysqli_multi_query($conn, $sql);

    // Store the result of the deletion attempt
    if ($del_result) {
        //check if any Rows/recodrds Has been Deleted
        $deleted_count = mysqli_affected_rows($conn);

        if ($deleted_count > 0) {
            $_SESSION['success'] = "✅ **Success!** Record for ID **" . htmlspecialchars($post_id) . "** has been successfully deleted.";
        } else {
            // This handles a successful query that deleted 0 rows 
            // (e.g., if ID was deleted between check and delete)
            $_SESSION['error'] = "❌ **Deletion Failed:** The record for ID **" . htmlspecialchars($post_id) . "** could not be deleted at this time, possibly due to a recent change.";
        }
    } else {
        $_SESSION['error'] = "🚫 **Server Error:** An unexpected error occurred while processing the request. Details: " . mysqli_error($conn);
    }

    // Close connection and redirect regardless of success/error
    mysqli_close($conn);
    header("Location: post.php");
    exit();

} else {
    $_SESSION['error'] = "⚠️ **Invalid Access:** No post ID provided. Please select a post to delete.";
    header("Location: post.php");
    exit();
}

?>