<?php
session_start();
include "config.php";

//=== check if the form is submitted ===
if (isset($_POST['submit'])) {
    // Initialize filename in case upload fails or isn't present
    $post_id = (int) $_POST['post_id'];
    $errors = array();

    // === Security: CSRF Validation ===
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Security token mismatch.";
        header("Location: update-post.php");
        mysqli_close($conn);
        exit();
    }

    // ==== Image Processing ===
    if (empty($_FILES['new-image']['name'])) {
        // Keep the old image if no new file is uploaded
        $image_name = $_POST['old-image'];
    } else {
        $file_name = $_FILES['new-image']['name'];
        $file_size = $_FILES['new-image']['size'];
        $file_tmp = $_FILES['new-image']['tmp_name'];

        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $extensions = array("jpeg", "jpg", "png");

        // ---- Validation checks ----
        if (!in_array($file_ext, $extensions)) {
            $errors[] = "Invalid file type. Please upload JPG or PNG.";
        }

        if ($file_size > 2097152) {
            $errors[] = "File size must be 2MB or lower.";
        }

        if (empty($errors)) {

            // Delete the OLD image file from folder
            $old_image = $_POST['old-image'];
            $old_image_path = "upload/" . $old_image;

            if (!empty($old_image) && file_exists($old_image_path)) {
                unlink($old_image_path); // Physically removes the file from the server
            }

            // Upload the NEW image
            $new_image_name = time() . "-" . basename($file_name);
            $target = "upload/" . $new_image_name;

            if (move_uploaded_file($file_tmp, $target)) {
                $image_name = $new_image_name;

                // --- NEW: MEDIA TABLE INSERTION ---
                $author_id = $_SESSION['user_id'];
                $sql_media = "INSERT INTO media(image_name, image_path, uploaded_by) 
                              VALUES ('$image_name', '$target', '$author_id')";
                mysqli_query($conn, $sql_media);
            } else {
                $errors[] = "Failed to upload new image.";
            }
        }
    }

    // ==== Data Sanitization ====
    $post_title = mysqli_real_escape_string($conn, $_POST['post_title']);
    $postDesc = mysqli_real_escape_string($conn, $_POST['postdesc']);
    $category = (int) $_POST['category'];
    $old_category = (int) $_POST['old_category'];
    $tags = mysqli_real_escape_string($conn, $_POST['tags']);



    // Checkboxes logic (1 if checked, 0 if not)
    $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // Status logic with Role Security
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    if ($_SESSION['user_role'] != 1 && $status == 'approved') {
        $status = 'pending'; // Force pending if a non-admin tries to approve
    }

    // ==== Validation ===
    if (empty($post_title) || empty($postDesc)) {
        $errors[] = "Title and Description cannot be empty.";
    }

    // ==== Redirect if errors found (FIX: PASS THE ID) ====
    if (!empty($errors)) {
        $_SESSION['error'] = implode("|||", $errors);
        header("Location: update-post.php?id=" . $post_id);
        exit();
    }


    $date = date('Y-m-d H:i:s');
    $updated_at = $date;

    $published_date = $_POST['published-date'] ?? '';
    $published_date = trim($published_date);

    if ($status === 'approved' && empty($published_date)) {
        $published_date = $date;
    }
    $published_sql = empty($published_date)
        ? "NULL"
        : "'$published_date'";
        
    //  ==== Build SQL ====
    $sql = "UPDATE post SET 
            title = '$post_title', 
            description = '$postDesc', 
            category = $category, 
            post_img = '$image_name',
            status = '$status',
            updated_at = '$updated_at',
            tags = '$tags',
            published_at = $published_sql,
            is_breaking = $is_breaking,
            is_featured = $is_featured
            WHERE post_id = $post_id;";

    // === Category Count Update (if category changed) ====         
    if ($old_category != $category) {
        $sql .= "UPDATE category SET post = post - 1 WHERE category_id = $old_category;";
        $sql .= "UPDATE category SET post = post + 1 WHERE category_id = $category;";
    }

    // ==== Execution ===
    if (mysqli_multi_query($conn, $sql)) {
        $_SESSION['success'] = "Post updated successfully.";
        $location = "post.php";
    } else {
        $_SESSION['error'] = "Database Error: Could not update post.";
        $location = "update-post.php?id=" . $post_id;
    }

    mysqli_close($conn);
    header("Location: $location ");
    exit();

} else {
    header("Location: post.php");
    exit();
}
?>