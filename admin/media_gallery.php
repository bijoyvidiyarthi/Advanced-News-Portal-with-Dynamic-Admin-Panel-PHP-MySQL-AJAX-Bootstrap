<?php
include "config.php";

//  ====== session check =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//--- Login Authentication ------
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

//  ======== Pagination ============
$limit = 12; // 12 images per page
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

//---- Fetch all Images -----
$sql = "SELECT m.*, u.username 
        FROM media m 
        LEFT JOIN user u ON m.uploaded_by = u.user_id 
        ORDER BY m.id DESC LIMIT {$offset}, {$limit}";
$result = mysqli_query($conn, $sql);

//------ Count Total Images ------
$sql_count = "SELECT COUNT(id) as total FROM media";
$res_count = mysqli_query($conn, $sql_count);
$total_records = mysqli_fetch_assoc($res_count)['total'];
$total_pages = ceil($total_records / $limit);

include "includes/header.php";
include "includes/sidebar.php";
?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">All Uploaded Images (<?php echo $total_records; ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="row g-3">
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="card h-100 shadow-sm">
                                        <div class="ratio ratio-1x1">
                                            <img src="<?php echo $row['image_path']; ?>" class="card-img-top object-fit-cover"
                                                alt="Image">
                                        </div>
                                        <div class="card-body p-2 text-center">
                                            <small class="d-block text-truncate" title="<?php echo $row['image_name']; ?>">
                                                <?php echo $row['image_name']; ?>
                                            </small>
                                            <span class="badge bg-secondary" style="font-size: 0.6rem;">
                                                By: <?php echo $row['username']; ?>
                                            </span>
                                        </div>
                                        <div class="card-footer p-1 text-center bg-transparent border-top-0">
                                            <?php if ($_SESSION['user_role'] == 1): ?>
                                                <a href="delete-media.php?id=<?php echo $row['id']; ?>"
                                                    onclick="return confirm('Delete this image?')" class="btn btn-xs btn-danger">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <?php if ($total_pages > 1): ?>
                            <div class="mt-4">

                                <ul class="pagination pagination-sm justify-content-center">
                                    <?php if ($total_pages > 1):
                                        // URL Parameter Retention Logic
                                        $params = $_GET;
                                        unset($params['page']);
                                        $query_str = http_build_query($params);
                                        $link_prefix = $_SERVER['PHP_SELF'] . "?" . ($query_str ? $query_str . "&" : "");
                                        ?>
                                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link"
                                                href="<?php echo $link_prefix . "page=" . ($page - 1); ?>">&laquo;</a>
                                        </li>

                                        <?php
                                        $range = 2;
                                        $start = max(1, $page - $range);
                                        $end = min($total_pages, $page + $range);

                                        if ($start > 1) {
                                            echo '<li class="page-item"><a class="page-link" href="' . $link_prefix . 'page=1">1</a></li>';
                                            if ($start > 2)
                                                echo '<li class="page-item disabled">' . " <<< " . '</li>';
                                        }

                                        for ($i = $start; $i <= $end; $i++) {
                                            $active = ($i == $page) ? "active" : "";
                                            echo '<li class="page-item ' . $active . '"><a class="page-link" href="' . $link_prefix . 'page=' . $i . '">' . $i . '</a></li>';
                                        }

                                        if ($end < $total_pages) {
                                            if ($end < $total_pages - 1)
                                                echo '<li class="page-item disabled">' . " >>> " . '</li>';
                                            echo '<li class="page-item"><a class="page-link" href="' . $link_prefix . 'page=' . $total_pages . '">' . $total_pages . '</a></li>';
                                        }
                                        ?>

                                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                            <a class="page-link"
                                                href="<?php echo $link_prefix . "page=" . ($page + 1); ?>">&raquo;</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <p class="text-center text-muted">No images found in library.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include "includes/footer.php"; ?>