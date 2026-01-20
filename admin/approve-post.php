<?php
include 'config.php';

// 1. Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Role check (only admin can access)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    $_SESSION['error'] = "You do not have permission to approve posts.";
    header("Location: dashboard.php");
    exit();
}

// 3. Process request
if (isset($_GET['id'])) {
    $post_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Store page number for redirect
    $redirect_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

    // 4. Update post status query
    $sql = "UPDATE post SET status = 'approved' WHERE post_id = {$post_id}";

    if (mysqli_query($conn, $sql)) {
        // Success message
        $_SESSION['success'] = "✅ Post ID: #{$post_id} has been approved successfully!";
    } else {
        // Error message
        $_SESSION['error'] = "❌ Error: Could not update status. " . mysqli_error($conn);
    }

    // 5. Redirect back to previous page
    header("Location: pending-post.php?page={$redirect_page}");
    exit();

} else {
    // If post ID is missing
    $_SESSION['error'] = "Invalid Post ID.";
    header("Location: pending-post.php");
    exit();
}

mysqli_close($conn);
?>