<?php 
// --- Update Category Details ---
session_start();
$user_role = htmlspecialchars($_SESSION['user_role']);

if ($user_role != 1 || !isset($_SESSION['user_role'])) {
    // If the user is not an admin, redirect to post.php or another appropriate page
    header("Location: post.php");
    exit();
}

if (isset($_POST['submit'])) {
    include "config.php";

    $c_id = mysqli_real_escape_string($conn, $_POST['cat_id']);
    $category_name = mysqli_real_escape_string($conn, $_POST['cat_name']);

    if (empty($category_name) || !is_string($category_name) || empty($c_id) || !is_numeric($c_id)) {
        $_SESSION['error'] = "⚠️ **Category id and Category Name Required:** Please enter a valid category name.";
        header("Location: update-category.php?id=" . $c_id);
        mysqli_close($conn);
        exit();
    }

    $sql_update = "UPDATE `category` SET category_name='$category_name'
            WHERE category_id={$c_id}";

    $result_update = mysqli_query($conn, $sql_update) or die("Query Failed.");

    if ($result_update) {
        $_SESSION['success'] = "Category updated successfully.";
        header("Location: category.php");
        mysqli_close($conn);
    } else {
        $_SESSION['error'] = "Could not update category.";
        //if error show this error message in this page
        header("Location: $_SERVER[PHP_SELF]");
        mysqli_close($conn);
    }
} else {
    $_SESSION['error'] = "⚠️ **No Data Submitted:** Please submit the form to update the category.";
    header("Location: category.php");
    exit();
}
?>