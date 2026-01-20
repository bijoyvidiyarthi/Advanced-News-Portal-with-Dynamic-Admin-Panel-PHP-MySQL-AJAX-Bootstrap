<?php
include_once __DIR__ . "/config.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_switch') {

    $csrf = $_POST['csrf_token'] ?? '';
    $type = $_POST['type'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    $column = mysqli_real_escape_string($conn, $_POST['column'] ?? '');
    $value = (int) ($_POST['value'] ?? 0);

    // check Sequrity
    if ($csrf !== $_SESSION['csrf_token']) {
        echo json_encode(['status' => 'error', 'message' => 'CSRF Token Mismatch']);
        exit;
    }

    $sql = "";
    if ($type === 'category') {
        // column validation
        $allowed = ['show_in_header', 'show_in_footer'];
        if (in_array($column, $allowed)) {
            $sql = "UPDATE category SET $column = $value WHERE category_id = $id";
        }
    } elseif ($type === 'social') {
        if ($column === 'status') {
            $sql = "UPDATE social_links SET status = $value WHERE id = $id";
        }
    } else {
        if (!in_array($_POST['column'], $allowed)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid column name!']);
            exit;
        }
    }

    // run command
    if ($sql !== "" && mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database update failed']);
    }
}