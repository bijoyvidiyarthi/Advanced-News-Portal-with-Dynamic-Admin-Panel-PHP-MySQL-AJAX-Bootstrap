<?php
session_start();
include "config.php";

if (isset($_POST['submit'])) {
    
    $post_id = (int)$_POST['post_id'];
    $errors = array();

     // CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Security token mismatch.";
        header("Location: add-post.php");
        mysqli_close($conn);
        exit();
    }

    // 1. Image Processing
    if (empty($_FILES['new-image']['name'])) {
        $image_name = $_POST['old-image'];
    } else {
        $file_name = $_FILES['new-image']['name'];
        $file_size = $_FILES['new-image']['size'];
        $file_tmp = $_FILES['new-image']['tmp_name'];
        
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $extensions = array("jpeg", "jpg", "png");

        if (!in_array($file_ext, $extensions)) {
            $errors[] = "Invalid file type. Please upload JPG or PNG.";
        }

        if ($file_size > 2097152) {
            $errors[] = "File size must be 2MB or lower.";
        }

        if (empty($errors)) {
            $image_name = time() . "-" . basename($file_name);
            $target = "upload/" . $image_name;
            move_uploaded_file($file_tmp, $target);
        }
    }

    // 2. Data Sanitization
    $post_title = mysqli_real_escape_string($conn, $_POST['post_title']);
    $postDesc = mysqli_real_escape_string($conn, $_POST['postdesc']);
    $category = (int)$_POST['category'];
    $old_category = (int)$_POST['old_category'];
    $author = (int)$_SESSION['user_id'];
    $date = date("d M, Y");

    // 3. Validation
    if (empty($post_title) || empty($postDesc)) {
        $errors[] = "Title and Description cannot be empty.";
    }

    // 4. Redirect if errors found (CRITICAL FIX: PASS THE ID)
    if (!empty($errors)) {
        $_SESSION['error'] = implode("|||", $errors);
        header("Location: update-post.php?id=" . $post_id);
        exit();
    }

    
    // 5. Build SQL
    $sql = "UPDATE post SET 
            title = '$post_title', 
            description = '$postDesc', 
            category = $category, 
            post_img = '$image_name',
            post_date = DATE_FORMAT(NOW(), '%d %b, %Y')
            WHERE post_id = $post_id;";

    if ($old_category != $category) {
        $sql .= "UPDATE category SET post = post - 1 WHERE category_id = $old_category;";
        $sql .= "UPDATE category SET post = post + 1 WHERE category_id = $category;";
    }

    // 6. Execute
    if (mysqli_multi_query($conn, $sql)) {
        $_SESSION['success'] = "Post updated successfully.";
        header("Location: post.php");
        exit();
    } else {
        $_SESSION['error'] = "Database Error: Could not update post.";
        header("Location: update-post.php?id=" . $post_id);
        exit();
    }

} else {
    header("Location: post.php");
    exit();
}
?>