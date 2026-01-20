<?php
include_once __DIR__ . "/config.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $platform_name = mysqli_real_escape_string($conn, $_POST['platform_name']);
    $icon_class = mysqli_real_escape_string($conn, $_POST['icon_class']);
    $platform_url = mysqli_real_escape_string($conn, $_POST['platform_url']);
    $csrf_token = $_POST['csrf_token'];

    // validation
    if ($csrf_token !== $_SESSION['csrf_token']) {
        echo json_encode(['status' => 'error', 'message' => 'Security Error: CSRF token mismatch!']);
        exit;
    }

    if (empty($platform_name) || empty($icon_class) || !filter_var($platform_url, FILTER_VALIDATE_URL)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill all fields correctly.']);
        exit;
    }

    $sql = "INSERT INTO social_links (platform_name, icon_class, platform_url, status) 
            VALUES ('$platform_name', '$icon_class', '$platform_url', 1)";

    if (mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'success', 'message' => 'New social platform added!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
}