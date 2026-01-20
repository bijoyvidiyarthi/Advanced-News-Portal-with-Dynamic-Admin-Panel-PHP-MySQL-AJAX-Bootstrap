<?php
session_start(); // 1. Start session at the very top
include "config.php";

//check if the form is submitted
if (isset($_POST['submit'])) {
    // Initialize filename in case upload fails or isn't present
    $errors = array();
    $image_name = "";

    // === Security: CSRF Validation ===
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Security token mismatch.";
        header("Location: post.php");
        mysqli_close($conn);
        exit();
    }

    //=== Image Upload Logic & Media Table ====

    if (isset($_FILES['fileToUpload']) && $_FILES['fileToUpload']['error'] === 0) {

        $file_name = $_FILES['fileToUpload']['name'];
        $file_size = $_FILES['fileToUpload']['size'];
        $file_tmp = $_FILES['fileToUpload']['tmp_name'];
        $file_type = $_FILES['fileToUpload']['type'];
        //here the explode function will return an array (separated by '.') and end function will get the last element of that array
        // $file_ext = strtolower(end(explode('.', $file_name)));
        //pathinfo function will return an array containing information about the path.then we use PATHINFO_EXTENSION to get only the extension part
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));


        $extensions = array("jpeg", "jpg", "png"); //allowed extensions

        if (!in_array($file_ext, $extensions)) {
            $errors[] = "This extension file not allowed, Please choose a JPG or PNG file.";
        }

        if ($file_size > 2097152) {
            $errors[] = "File size must be 2mb or lower.";
        }

        if (empty($errors)) {
            $new_name = time() . "-" . basename($file_name);
            $target = "upload/" . $new_name;
            move_uploaded_file($file_tmp, $target);
            $image_name = $new_name;
            //we can't input the $new_name as image name directly, cause, it will change by time every moment. 

            //Media Table Insertion
            $author_id = $_SESSION['user_id'];
            $sql_media = "INSERT INTO media(image_name, image_path, uploaded_by) VALUES ('$image_name', '$target', '$author_id')";
            mysqli_query($conn, $sql_media);

        }
    } elseif (!empty($_POST['selected_image'])) {
        // path from gallary_image 'upload/filename.jpg' ===>  filename.jpg 
        $image_name = basename(mysqli_real_escape_string($conn, $_POST['selected_image']));
    }

    // Data Sanitization & Slug Generation
    $post_title = mysqli_real_escape_string($conn, $_POST['post_title']);
    $postDesc = mysqli_real_escape_string($conn, $_POST['postdesc']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $tags = mysqli_real_escape_string($conn, $_POST['tags']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);


    // Role protection for status
    if ($_SESSION['user_role'] != '1' && $status == 'approved') {
        $status = 'pending';
        // Set the warning message in session
        $_SESSION['error'] = "You are trying unnecessary things, don't try it.. Or you will be blocked!";
        header("Location: add-post.php");
        exit(); // Stop any further script execution
    }

    $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // Slug: "Hello World" -> "hello-world"
    $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $post_title));
    $date = date("Y-m-d H:i:s"); // Better for DB sorting

    //set published date if status is approved
    $published_date = null;
    if ($status === 'approved') {
        $published_date = $date;
    }

    $published_sql = $published_date
        ? "'$published_date'"
        : "NULL";

    //ensure user id is set to avoid undefined index notice
    $author = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

    //input Validation
    if (empty($post_title) || empty($postDesc) || empty($category) || empty($author) || empty($image_name)) {
        $errors[] = "All fields are required.";
    }

    //if there are errors, store them in session and redirect back to add-post.php


    if (empty($errors)) {

        $sql = "INSERT INTO post (title, slug, description, category, created_date, published_at, author, post_img, status, is_breaking, is_featured, tags, viewCount) 
            VALUES ('$post_title', '$slug', '$postDesc','$category','$date', $published_sql, '$author','$image_name', '$status', '$is_breaking', '$is_featured', '$tags', 0);";

        $sql .= "UPDATE category SET post = post + 1 WHERE category_id = $category";

        //-- Mistake fixed here ---
        // *1* I made a mistake here using "+=" this, sql doesn't not work with  the operator "+="
        // *2* 'post' doesn't work, table name shoudn't be in quotes, it can be backticks (`) or just the name (post) but not single quotes (')
        // *3* using mysqli_multi_query, I didn't separate the two SQL statements with a semicolon (;).
        //

        if (mysqli_multi_query($conn, $sql)) {
            $_SESSION['success'] = "Post added successfully.";
            $location = "post.php";
        } else {
            $_SESSION['error'] = "Error adding post.";
            //$_SESSION['error'] = "Database Error: " . mysqli_error($conn);
            $location = "add-post.php";
        }
    } else {
        $_SESSION['error'] = implode("|||", $errors);
        $location = "add-post.php";
    }
    mysqli_close($conn);
    header("Location: $location");
    exit();

} else {
    // If someone tries to access the file directly or image upload has error
    mysqli_close($conn);
    header("Location: add-post.php");
    exit();
}
?>