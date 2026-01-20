<?php
//  ============== Start session and check connection ===============
include_once __DIR__ . "/config.php";
include __DIR__ . "/includes/auth.php";

// ======== Immediate Database Connection Check ==========
if (!$conn) {
    $_SESSION['error'] = "⚠️ **Database Connection Error:** Unable to connect. Please try again later.";
    // Ensure no HTML is sent before this header
    header("Location: index.php");
    exit();
}

//==========   Filter Logic Initialization ==============
$search_term = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$filter_cat = isset($_GET['cat_filter']) ? (int) $_GET['cat_filter'] : "";
$filter_date = isset($_GET['date_filter']) ? mysqli_real_escape_string($conn, $_GET['date_filter']) : "";

// Base WHERE clause logic
$where_clauses = [];

// Role-based restriction
if ($_SESSION['user_role'] != 1) {
    $where_clauses[] = "p.author = {$_SESSION['user_id']}";
}

// Apply Search Filter
if (!empty($search_term)) {
    $where_clauses[] = "(p.title LIKE '%{$search_term}%' OR p.description LIKE '%{$search_term}%')";
}

// Apply Category Filter
if (!empty($filter_cat)) {
    $where_clauses[] = "p.category = {$filter_cat}";
}

// Apply Date Filter
if (!empty($filter_date)) {
    $where_clauses[] = "DATE(p.created_date) = '{$filter_date}'";
}

// Construct Final SQL WHERE String
$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}


//  ========== Pagination Setup ==============
$limit = 5;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;


// 5. Calculate Total Records & Pages
$countSql = "SELECT COUNT(post_id) AS total_posts FROM post p $where_sql";
$countResult = mysqli_query($conn, $countSql);
$total_posts = 0;
$total_pages = 0;

if ($countResult && mysqli_num_rows($countResult) > 0) {
    $countrow = mysqli_fetch_assoc($countResult);
    $total_posts = $countrow['total_posts'];
    $total_pages = ceil($total_posts / $limit);
}

// 6. Page Validation (Before any HTML output)
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

// ======== Include UI headers ========== 
include 'includes/header.php';
include 'includes/sidebar.php';
?>
<div class="app-content">
    <div class="container-fluid">

        <div class="card mb-4">
            <div class="card-body">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" class="row g-3">

                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search by title..."
                                value="<?php echo htmlspecialchars($search_term); ?>">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <select name="cat_filter" class="form-select">
                            <option value="">All Categories</option>
                            <?php
                            $cat_sql = "SELECT * FROM category";
                            $cat_result = mysqli_query($conn, $cat_sql);
                            while ($cat_row = mysqli_fetch_assoc($cat_result)) {
                                $selected = ($cat_row['category_id'] == $filter_cat) ? "selected" : "";
                                echo "<option value='{$cat_row['category_id']}' {$selected}>{$cat_row['category_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <input type="date" name="date_filter" class="form-control" value="<?php echo $filter_date; ?>">
                    </div>

                    <div class="col-md-2 d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                        <a href="post.php" class="btn btn-secondary" title="Reset"><i
                                class="bi bi-arrow-counterclockwise"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">News List</h3>
                        <div class="card-tools">
                            <a class="btn btn-sm btn-primary" href="add-post.php">
                                <i class="bi bi-plus-lg"></i> Add New Post
                            </a>
                        </div>
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

                        // 8. Fetch Post Data with Filters
                        $sql = "SELECT p.post_id, p.title, p.description, p.created_date, 
                                           u.username, c.category_name, p.category, p.status
                                    FROM post p
                                    LEFT JOIN category c ON p.category = c.category_id
                                    LEFT JOIN user u ON p.author = u.user_id
                                    $where_sql
                                    ORDER BY (p.status = 'pending') DESC, p.created_date DESC 
                                    LIMIT {$limit} OFFSET {$offset}";

                        $result = mysqli_query($conn, $sql);

                        if (!$result):
                            echo "<div class='alert alert-danger m-3'>Error fetching posts: " . mysqli_error($conn) . "</div>";
                        else:
                            ?>
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 10px">#</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Author</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (mysqli_num_rows($result) > 0):
                                        $id_no = $offset + 1;
                                        while ($row = mysqli_fetch_assoc($result)):
                                            ?>
                                            <tr>
                                                <td><?php echo $id_no; ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars(substr($row['title'], 0, 50)) . (strlen($row['title']) > 50 ? '...' : ''); ?>
                                                </td>
                                                <td><span
                                                        class="badge text-bg-secondary"><?php echo htmlspecialchars($row['category_name']); ?></span>
                                                </td>
                                                <td>
                                                    <?php
                                                    if ($row['status'] == 'approved') {
                                                        echo '<span class="badge text-bg-success">Active</span>';
                                                    } elseif ($row['status'] == 'pending') {
                                                        echo '<span class="badge text-bg-warning">Pending</span>';
                                                    } else {
                                                        echo '<span class="badge text-bg-secondary">Draft</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo date("d M, Y", strtotime($row['created_date'])); ?></td>
                                                <td><small
                                                        class="text-muted fw-bold"><?php echo htmlspecialchars($row['username']); ?></small>
                                                </td>

                                                <td class="text-center">
                                                    <a href="update-post.php?id=<?php echo $row['post_id']; ?>"
                                                        class="btn btn-sm btn-info text-white">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <a onclick="return confirm('Are you sure you want to delete this post?')"
                                                        href="delete-post.php?id=<?php echo $row['post_id']; ?>&catid=<?php echo $row['category']; ?>"
                                                        class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php
                                            $id_no++;
                                        endwhile;
                                    else:
                                        echo "<tr><td colspan='7' class='text-center py-4 text-muted'>No news found matching your criteria.</td></tr>";
                                    endif;
                                    ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
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
include "includes/footer.php";
?>