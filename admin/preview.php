<?php
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}


if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Post ID!");
}

$post_id = mysqli_real_escape_string($conn, $_GET['id']);

$sql = "SELECT p.*, c.category_name, u.username FROM post p 
        LEFT JOIN category c ON p.category = c.category_id 
        LEFT JOIN user u ON p.author = u.user_id 
        WHERE p.post_id = {$post_id}";

$result = mysqli_query($conn, $sql) or die("Query Failed.");

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
} else {
    die("Post not found!");
}

include "includes/header.php";
include "includes/sidebar.php";
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Post Preview</h3>
                <p class="text-muted">Viewing:
                    <?php echo ($row['status'] == 'draft') ? '<span class="badge bg-warning">Draft</span>' : '<span class="badge bg-info">Pending</span>'; ?>
                </p>
            </div>
            <div class="col-sm-6 text-end">
                <a href="draft-post.php" class="btn btn-outline-secondary me-2">Back to List</a>
                <a href="update-post.php?id=<?php echo $row['post_id']; ?>" class="btn btn-info">Edit This Post</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-9 mx-auto">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($row['title']); ?></h1>

                        <div class="mb-4 text-muted small border-bottom pb-2">
                            <span><i class="bi bi-person"></i> <?php echo $row['username']; ?></span> |
                            <span><i class="bi bi-folder"></i> <?php echo $row['category_name']; ?></span> |
                            <span><i class="bi bi-calendar"></i>
                                <?php echo date('d M, Y', strtotime($row['created_date'])); ?></span>
                        </div>

                        <div class="post-image mb-4 text-center">
                            <img src="upload/<?php echo $row['post_img']; ?>" class="img-fluid rounded shadow-sm"
                                style="max-height: 450px; width: 100%; object-fit: cover;" alt="Post Image">
                        </div>

                        <div class="post-description lead" style="line-height: 1.8; text-align: justify;">
                            <?php echo nl2br($row['description']); ?>
                        </div>

                        <hr class="my-5">

                        <div class="text-center pb-4">
                            <?php if ($_SESSION['user_role'] == 1): ?>
                                <p class="text-muted mb-2">Everything looks good?</p>
                                <a href="approve-post.php?id=<?php echo $row['post_id']; ?>&to=approve"
                                    class="btn btn-lg btn-success px-5">
                                    <i class="bi bi-check-circle"></i> Approve & Publish Now
                                </a>
                            <?php else: ?>
                                <a href="change-status.php?id=<?php echo $row['post_id']; ?>&to=pending"
                                    class="btn btn-lg btn-warning px-5">
                                    <i class="bi bi-send"></i> Submit for Approval
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>