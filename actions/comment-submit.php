<?php
include_once __DIR__ . "/../admin/config.php";
header('Content-Type: application/json');

/* =========================
   Request Validation
========================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request!'
    ]);
    exit;
}

/* =========================
   Input Sanitize
========================= */
$post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$comment = trim($_POST['comment'] ?? '');

/* =========================
   Required Validation
========================= */
if ($post_id <= 0 || $name === '' || $email === '' || $comment === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'All fields are required!'
    ]);
    exit;
}

/* =========================
   Email Validation
========================= */
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid email address!'
    ]);
    exit;
}

/* =========================
   Comment Status Logic
   Admin => approved
   Viewer => pending
========================= */
$status = 'pending';

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1) {
    $status = 'approved';
} else {
    $status = 'pending';
}

/* =========================
   Insert Comment
========================= */
$stmt = $conn->prepare("
    INSERT INTO comments (post_id, name, email, comment, status)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "issss",
    $post_id,
    $name,
    $email,
    $comment,
    $status
);

if ($stmt->execute()) {

    /* =========================
       Response for AJAX
       Only approved comment
       should be returned
    ========================= */
    $response = [
        'status' => 'success',
        'message' => ($status === 'approved')
            ? 'Comment posted successfully!'
            : 'Comment submitted & waiting for approval.',
        'approved' => ($status === 'approved')
    ];

    if ($status === 'approved') {
        $response['comment'] = [
            'name' => htmlspecialchars($name),
            'comment' => nl2br(htmlspecialchars($comment)),
            'created_at' => date("d M Y H:i")
        ];
    }

    echo json_encode($response);
    exit;

} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error! Please try again.'
    ]);
    exit;
}
