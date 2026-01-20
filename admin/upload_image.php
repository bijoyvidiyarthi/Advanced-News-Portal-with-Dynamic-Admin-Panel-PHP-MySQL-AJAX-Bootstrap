<?php
session_start();
include "config.php";

// Authentication check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => ['message' => 'Unauthorized access! Please login.']]);
    exit;
}

$errors = array();

// Upload Logic
if (isset($_FILES['upload'])) {

    // CKEditor default key: 'upload'
    $file = $_FILES['upload'];

    $file_name  = $file['name'];
    $file_tmp   = $file['tmp_name'];
    $file_size  = $file['size'];
    $file_error = $file['error'];

    // File extension check
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_ext = array("jpg", "jpeg", "png", "gif", "webp");

    if ($file_error !== UPLOAD_ERR_OK) {
        $errors[] = "File upload error occurred.";
    }

    if (!in_array($file_ext, $allowed_ext)) {
        $errors[] = "This file extension is not allowed. Please choose JPG, PNG, GIF or WEBP.";
    }

    if ($file_size > 2097152) {
        $errors[] = "File size must be 2MB or lower.";
    }

    // If no errors, upload file
    if (empty($errors)) {

        // Generate unique file name
        $new_file_name = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
        $upload_dir = "upload/";

        // Ensure upload directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $upload_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp, $upload_path)) {

            // // === upload it to media gallery ===
            // $author_id = $_SESSION['user_id'];
            
            // $sql_media = "INSERT INTO media(image_name, image_path, uploaded_by) 
            //               VALUES ('$new_file_name', '$upload_path', '$author_id')";
            
            // mysqli_query($conn, $sql_media);

            // Success response for CKEditor
            echo json_encode([
                'url' => $upload_path
            ]);
            exit;

        } else {
            $errors[] = "Could not move uploaded file. Check folder permissions.";
        }
    }

} else {
    $errors[] = "No file received for upload.";
}

// Handle errors
if (!empty($errors)) {
    // CKEditor error response
    echo json_encode([
        'error' => [
            'message' => implode(" | ", $errors)
        ]
    ]);
    exit;
}

mysqli_close($conn);
?>