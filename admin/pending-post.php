<?php
/* =========================
   Session & Authentication
========================= */
include_once __DIR__ . "/config.php";
include_once __DIR__ . "/includes/auth.php";


//  ========== Pagination Setup ==============
$limit = 5;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;


// 4. Query filter based on role
// Admin (role 1) can see all pending posts, others see only their own
$filter_sql = " WHERE p.status = 'pending'"; // assuming 0 means pending
if ($_SESSION['user_role'] != 1) {
    $filter_sql .= " AND p.author = {$_SESSION['user_id']}";
}

// 5. Count total records
$countSql = "SELECT COUNT(post_id) AS total FROM post p $filter_sql";
$countResult = mysqli_query($conn, $countSql);
$total_posts = mysqli_fetch_assoc($countResult)['total'];
$total_pages = ceil($total_posts / $limit);

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

include_once __DIR__ . "/includes/header.php";
include_once __DIR__ . "/includes/sidebar.php";
?>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">All Pending News</h3>
                    </div>
                    <div class="card-body p-0">
                        <?php
                        // Display Messages
                        if (isset($_SESSION['success'])) {
                            echo "<div class='alert alert-success m-3'>" . htmlspecialchars($_SESSION['success']) . "</div>";
                            unset($_SESSION['success']);
                        }
                        if (isset($_SESSION['error'])) {
                            echo "<div class='alert alert-danger m-3'>" . htmlspecialchars($_SESSION['error']) . "</div>";
                            unset($_SESSION['error']);
                        }
                        ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Author</th>
                                    <th>Date</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // 6. Fetch data query
                                $sql = "SELECT p.post_id, p.title, p.created_date, 
                                            c.category_name, u.username 
                                            FROM post p
                                            LEFT JOIN category c ON p.category = c.category_id
                                            LEFT JOIN user u ON p.author = u.user_id
                                            $filter_sql
                                            ORDER BY p.post_id DESC LIMIT {$offset}, {$limit}";

                                $result = mysqli_query($conn, $sql);

                                if (mysqli_num_rows($result) > 0) {
                                    $serial = $offset + 1;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        ?>
                                        <tr>
                                            <td><?php echo $serial++; ?></td>
                                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                                            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                                            <td><?php echo date('d M, Y', strtotime($row['created_date'])); ?></td>
                                            <td class="text-center">
                                                <a href='update-post.php?id=<?php echo $row['post_id']; ?>'
                                                    class="btn btn-sm btn-info">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </a>
                                                <?php if ($_SESSION['user_role'] == 1): ?>
                                                    <a href='approve-post.php?id=<?php echo $row['post_id']; ?>'
                                                        class="btn btn-sm btn-success">
                                                        <i class="bi bi-check-circle"></i> Approve
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>No pending posts found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer clearfix">
                        <ul class="pagination pagination-sm m-0 float-end">
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
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }

                                for ($i = $start; $i <= $end; $i++) {
                                    $active = ($i == $page) ? "active" : "";
                                    echo '<li class="page-item ' . $active . '"><a class="page-link" href="' . $link_prefix . 'page=' . $i . '">' . $i . '</a></li>';
                                }

                                if ($end < $total_pages) {
                                    if ($end < $total_pages - 1)
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
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
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once __DIR__ . "/includes/footer.php";
?>