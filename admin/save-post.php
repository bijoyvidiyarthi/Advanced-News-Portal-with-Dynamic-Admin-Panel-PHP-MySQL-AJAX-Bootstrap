<?php
session_start(); // 1. Start session at the very top
include "config.php";

//check if the form is submitted
if (isset($_POST['submit']) && $_FILES['fileToUpload']['error'] === 0) {

    // Initialize filename in case upload fails or isn't present
    $file_name = "";

    //check if file is uploaded or not
    if (isset($_FILES['fileToUpload'])) {

        $errors = array();

        $file_name = $_FILES['fileToUpload']['name'];
        $file_size = $_FILES['fileToUpload']['size'];
        $file_tmp = $_FILES['fileToUpload']['tmp_name'];
        $file_type = $_FILES['fileToUpload']['type'];


        //here the explode function will return an array (separated by '.') and end function will get the last element of that array

        // $file_ext = strtolower(end(explode('.', $file_name)));
        //pathinfo function will return an array containing information about the path.then we use PATHINFO_EXTENSION to get only the extension part
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));


        $extensions = array("jpeg", "jpg", "png"); //allowed extensions

        if (in_array($file_ext, $extensions) === false) {
            $errors[] = "This extension file not allowed, Please choose a JPG or PNG file.";
        }

        if ($file_size > 2097152) {
            $errors[] = "File size must be 2mb or lower.";
        }

        $new_name = time() . "-". basename($file_name);
        $target = "upload/" . $new_name ;
        $image_name = $new_name;
    }

    //now set all the values from the form to variables
    $post_title = mysqli_real_escape_string($conn, $_POST['post_title']);
    $postDesc = mysqli_real_escape_string($conn, $_POST['postdesc']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $date = date("d M, Y");

    //ensure user id is set to avoid undefined index notice
    $author = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

    //input Validation
    if (empty($post_title) || empty($postDesc) || empty($category) || empty($author) || empty($image_name)) {
        $errors[] = "All fields are required.";
    }
    
    //if there are no errors then proceed the insert query
    if (empty($errors) == true) {
        move_uploaded_file($file_tmp, $target);
    } else {
        //if there are errors, store them in session and redirect back to add-post.php
        if (!empty($errors)) {
            $_SESSION['error'] = implode("|||", $errors);
            header("Location: add-post.php");
            mysqli_close($conn);
            exit();
        }
    }
    $sql = "INSERT INTO post (title, description, category, post_date, author, post_img) 
            VALUES ('$post_title', '$postDesc','$category','$date','$author','$image_name');";

    $sql .= "UPDATE category SET post = post + 1 WHERE category_id = $category";

    //-- Mistake fixed here ---
    // *1* I made a mistake here using "+=" this, sql doesn't not work with  the operator "+="
    // *2* 'post' doesn't work, table name shoudn't be in quotes, it can be backticks (`) or just the name (post) but not single quotes (')
    // *3* using mysqli_multi_query, I didn't separate the two SQL statements with a semicolon (;).
    //

    if (mysqli_multi_query($conn, $sql)) {
        $_SESSION['success'] = "Post added successfully.";
        header("Location: post.php");
    } else {
        $_SESSION['error'] = "Error adding post.";
        header("Location: add-post.php");
        exit();
    }
    mysqli_close($conn);

} else {
    header("Location: add-post.php");
}
?>