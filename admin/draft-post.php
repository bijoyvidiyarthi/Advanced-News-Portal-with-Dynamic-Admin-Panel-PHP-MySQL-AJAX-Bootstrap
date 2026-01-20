<?php
/* =========================
   Session & Authentication
========================= */
include_once __DIR__ . "/config.php";
include __DIR__ . "/includes/auth.php";

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;


// Draft filter (Status = Draft)
$filter_sql = " WHERE p.status = 'draft'";
if ($_SESSION['user_role'] != 1) {
    $filter_sql .= " AND p.author = {$_SESSION['user_id']}";
}

// Count total draft posts
$countSql = "SELECT COUNT(post_id) AS total FROM post p $filter_sql";
$countResult = mysqli_query($conn, $countSql);
$total_posts = 0;
$total_pages = 0;

if ($countResult && mysqli_num_rows($countResult) > 0) {
    $countrow = mysqli_fetch_assoc($countResult);
    $total_posts = $countrow['total'];
    $total_pages = ceil($total_posts / $limit);
}


// Page validation
if ($page < 1) {
    $_SESSION['error'] = "⚠️ **Page Not Found:** Redirected to page 1.";
    header("Location: " . $_SERVER['PHP_SELF'] . "?page=1");
    exit();
}

if ($total_pages > 0 && $page > $total_pages) {
    $_SESSION['error'] = "⚠️ **Page Not Found:** Redirected to the last available page.";
    header("Location: " . $_SERVER['PHP_SELF'] . "?page=" . $total_pages);
    exit();
}

$offset = ($page - 1) * $limit;

include __DIR__ . "/includes/header.php";
include __DIR__ . "/includes/sidebar.php";
?>

<div class="app-content">
    <div class="container-fluid">

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success'];
                unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Manage Your Drafts</h3>
            </div>
            <div class="card-body p-0">
                <?php
                if (isset($_SESSION['error'])) {
                    echo "<div class='alert alert-danger m-3'>" . htmlspecialchars($_SESSION['error']) . "</div>";
                    unset($_SESSION['error']);
                }
                ?>
                <table class="table table-hover" id="draft-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT p.*, c.category_name FROM post p
                                    LEFT JOIN category c ON p.category = c.category_id
                                    $filter_sql
                                    ORDER BY p.post_id DESC
                                    LIMIT $offset, $limit";
                        $result = mysqli_query($conn, $sql);

                        if (mysqli_num_rows($result) > 0) {
                            $serial = $offset + 1;
                            while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?= $serial++ ?></td>
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= $row['category_name'] ?></td>
                                    <td><?= date('d M, Y', strtotime($row['created_date'])) ?></td>
                                    <td class="text-center">
                                        <a href="preview.php?id=<?= $row['post_id'] ?>" target="_blank"
                                            class="btn btn-sm btn-secondary">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="update-post.php?id=<?= $row['post_id'] ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <?php if ($_SESSION['user_role'] == 1): ?>
                                            <a href="change-status.php?id=<?= $row['post_id'] ?>&to=approve"
                                                class="btn btn-sm btn-success">
                                                <i class="bi bi-check-all"></i> Publish
                                            </a>
                                        <?php else: ?>
                                            <a href="change-status.php?id=<?= $row['post_id'] ?>&to=pending"
                                                class="btn btn-sm btn-warning">
                                                <i class="bi bi-send"></i> Submit
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>No drafts found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                <ul class="pagination pagination-sm m-0 float-end">
                    <?php if ($total_pages > 1):

                        // Keep existing URL parameters
                        $params = $_GET;
                        unset($params['page']);
                        $query_str = http_build_query($params);
                        $link_prefix = $_SERVER['PHP_SELF'] . "?" . ($query_str ? $query_str . "&" : "");
                        ?>

                        <!-- Previous -->
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?= $link_prefix . "page=" . ($page - 1); ?>">&laquo;</a>
                        </li>

                        <?php
                        $range = 2;
                        $start = max(1, $page - $range);
                        $end = min($total_pages, $page + $range);

                        if ($start > 1) {
                            echo '<li class="page-item"><a class="page-link" href="' . $link_prefix . 'page=1">1</a></li>';
                            if ($start > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }

                        for ($i = $start; $i <= $end; $i++) {
                            $active = ($i == $page) ? 'active' : '';
                            echo '<li class="page-item ' . $active . '">
                                   <a class="page-link" href="' . $link_prefix . 'page=' . $i . '">' . $i . '</a>
                                  </li>';
                        }

                        if ($end < $total_pages) {
                            if ($end < $total_pages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="' . $link_prefix . 'page=' . $total_pages . '">' . $total_pages . '</a></li>';
                        }
                        ?>

                        <!-- Next -->
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?= $link_prefix . "page=" . ($page + 1); ?>">&raquo;</a>
                        </li>

                    <?php endif; ?>
                </ul>
            </div>

        </div>
    </div>
</div>

<?php include_once __DIR__ . "/includes/footer.php"; ?>