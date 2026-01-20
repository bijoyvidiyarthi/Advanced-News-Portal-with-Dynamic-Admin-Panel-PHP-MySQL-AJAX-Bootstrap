<?php
include_once __DIR__ . "/config.php";

// responce header
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Something went wrong!'];

if (isset($_POST['f_name'])) {

    // ১. CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['status' => 'error', 'message' => "Security token mismatch."]);
        exit();
    }

    $user_id = (int) $_SESSION['user_id'];
    $f_name = mysqli_real_escape_string($conn, $_POST['f_name']);
    $l_name = mysqli_real_escape_string($conn, $_POST['l_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    $facebook = mysqli_real_escape_string($conn, $_POST['facebook']);
    $twitter = mysqli_real_escape_string($conn, $_POST['twitter']);
    $instagram = mysqli_real_escape_string($conn, $_POST['instagram']);

    $image_sql = "";
    $new_name = null;

    // 2. Image Proccessing
    if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === 0) {
        $file_ext = strtolower(pathinfo($_FILES['new_image']['name'], PATHINFO_EXTENSION));
        $extensions = array("jpeg", "jpg", "png");

        if (in_array($file_ext, $extensions)) {
            if ($_FILES['new_image']['size'] <= 2097152) {
                $new_name = time() . "-" . rand(100, 999) . "." . $file_ext;
                if (move_uploaded_file($_FILES['new_image']['tmp_name'], "upload/users/" . $new_name)) {
                    $image_sql = ", user_img = '{$new_name}'";
                }
            }
        }
    }

    // Run Update Query
    $update_sql = "UPDATE user SET 
                   first_name='{$f_name}', 
                   last_name='{$l_name}', 
                   email='{$email}', 
                   phone='{$phone}', 
                   bio='{$bio}', 
                   facebook='{$facebook}',
                   twitter='{$twitter}',
                   instagram='{$instagram}'
                   $image_sql 
                   WHERE user_id = {$user_id}";

    if (mysqli_query($conn, $update_sql)) {
        $response = [
            'status' => 'success',
            'message' => 'Profile updated successfully!',
            // frontend update
            'full_name' => $f_name . " " . $l_name,
            'new_img' => $new_name ? "upload/users/" . $new_name : null
        ];
    } else {
        $response['message'] = "Database Error: " . mysqli_error($conn);
    }
}

echo json_encode($response);
exit();