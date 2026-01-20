<?php
include_once __DIR__ . "/config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Something went wrong!'];

if (isset($_POST['current_pass'])) {

    //  CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['status' => 'error', 'message' => "Security token mismatch."]);
        exit();
    }

    $user_id = (int)$_SESSION['user_id'];
    $current_pass = $_POST['current_pass'];
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    // validate password
    // --- input validation conditions ---
    $uppercase = preg_match('@[A-Z]@', $new_pass);
    $lowercase = preg_match('@[a-z]@', $new_pass);
    $number    = preg_match('@[0-9]@', $new_pass);
    $special   = preg_match('@[^\w]@', $new_pass); // স্পেশাল ক্যারেক্টার চেক

    if (!$uppercase || !$lowercase || !$number || !$special || strlen($new_pass) < 8) {
        echo json_encode(['status' => 'error', 'message' => "Password must be at least 8 characters and include uppercase, lowercase, number, and a special character."]);
        exit();
    }

    if ($new_pass !== $confirm_pass) {
        echo json_encode(['status' => 'error', 'message' => "Confirm password does not match."]);
        exit();
    }

    // current password
    $sql = "SELECT password FROM user WHERE user_id = {$user_id}";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        // check password
        if (password_verify($current_pass, $row['password'])) {
            
            // password-hash
            $hashed_pass = password_hash($new_pass, PASSWORD_BCRYPT);
            
            $update_sql = "UPDATE user SET password = '{$hashed_pass}' WHERE user_id = {$user_id}";
            
            if (mysqli_query($conn, $update_sql)) {
                $response = ['status' => 'success', 'message' => 'Password changed successfully!'];
            } else {
                $response['message'] = "Database error: " . mysqli_error($conn);
            }
        } else {
            $response['message'] = "Current password is incorrect.";
        }
    }
}

echo json_encode($response);
exit();