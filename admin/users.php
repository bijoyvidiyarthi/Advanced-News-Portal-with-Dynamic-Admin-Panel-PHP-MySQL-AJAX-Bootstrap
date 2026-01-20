<?php

/* =========================
   Session & Authentication
========================= */
include_once __DIR__ . "/config.php";
include __DIR__ . "/includes/auth.php";

/**
 *  ADMIN ACCESS CONTROL
 * Restricts this page to Admin users (role = 1) only.
 */
if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] != 1) {
        header("Location:" . BASE_URL);
        exit();
    }
}

/**
 *  PAGINATION PARAMETERS
 */
$limit = 5;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

// Boundary Check: Prevents pages less than 1
if ($page < 1) {
    $_SESSION['error'] = "⚠️ Page $page does not exist. Redirected to Page 1.";
    header("Location: " . $_SERVER['PHP_SELF'] . "?page=1");
    exit();
}

$offset = ($page - 1) * $limit;

/**
 * 4. DATABASE OPERATIONS
 */
if (!$conn) {
    $_SESSION['error'] = "⚠️ **Something went Wrong, we will fix this soon. Please try again later.";
    header("Location: users.php");
    exit();
}

// 4a. Calculate Total Records for Pagination
$countSql = "SELECT COUNT(user_id) AS total_users FROM user";
$countResult = mysqli_query($conn, $countSql);

//count total pages according total users and limit
$countRow = mysqli_fetch_assoc($countResult);
$total_users = $countRow['total_users'];
$total_pages = ceil($total_users / $limit);

// Boundary Check: Prevents accessing a page higher than the total exists
if ($page > $total_pages && $total_pages > 0) {
    $_SESSION['error'] = "⚠️ Page $page does not exist. Showing the last available page.";
    header("Location: " . $_SERVER['PHP_SELF'] . "?page=" . $total_pages);
    exit();
}

// 4b. Fetch User Data for Current Page
$sql = "SELECT * FROM user ORDER BY user_id DESC LIMIT {$offset}, {$limit}";
$result = mysqli_query($conn, $sql) or die("Query Failed (Fetch).");

/**
 * 1. INITIALIZATION & CONFIGURATION
 */
include_once __DIR__ . "/includes/header.php";      // Includes session_start(), header template, etc.
include_once __DIR__ . "/includes/sidebar.php";

?>

<div id="admin-content">
    <div class="container">
        <!-- HEADER SECTION -->
        <div class="row">
            <div class="col-md-10">

                <!-- 5. NOTIFICATION MESSAGES (Success/Error) -->
                <?php
                if (isset($_SESSION['success'])) {
                    echo "<div class='alert alert-success'>" . htmlspecialchars($_SESSION['success']) . "</div>";
                    unset($_SESSION['success']);
                }
                if (isset($_SESSION['error'])) {
                    echo "<div class='alert alert-danger'>" . htmlspecialchars($_SESSION['error']) . "</div>";
                    unset($_SESSION['error']);
                }
                ?>
            </div>
            <div class="col-md-2">
                <a class="add-new" href="add-user.php">Add User</a>
            </div>
        </div>

        <!-- DATA TABLE SECTION -->
        <div class="row">
            <div class="col-md-12">
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <table class="content-table">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Full Name</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $serial_no = $offset + 1;
                            while ($row = mysqli_fetch_assoc($result)):
                                ?>
                                <tr>
                                    <td class='id'><?php echo $serial_no; ?></td>
                                    <td><?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo ($row['role'] == 1) ? "Admin" : "Normal User"; ?></td>
                                    <td class='edit'>
                                        <a href='update-user.php?aid=<?php echo $row['user_id']; ?>'>
                                            <i class='fa fa-edit'></i>
                                        </a>
                                    </td>
                                    <td class='delete'>
                                        <a href='delete-user.php?aid=<?php echo $row['user_id']; ?>'>
                                            <i class='fa fa-trash-o'></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                $serial_no++;
                            endwhile;
                            ?>
                        </tbody>
                    </table>

                    <!-- 6. PAGINATION UI -->
                    <ul class='pagination admin-pagination'>
                        <?php
                        if ($total_pages > 1):
                            // Prev Button
                            if ($page > 1) {
                                echo '<li><a href="?page=' . ($page - 1) . '">Prev</a></li>';
                            }

                            // Smart Page Numbers with Range
                            $range = 2;
                            $start = max(1, $page - $range);
                            $end = min($total_pages, $page + $range);

                            // Show First Page & Ellipsis
                            if ($start > 1) {
                                echo '<li><a href="?page=1">1</a></li>';
                                if ($start > 2)
                                    echo "<li><span>...</span></li>";
                            }

                            // Loop through range
                            for ($i = $start; $i <= $end; $i++) {
                                $active = ($i == $page) ? "active" : "";
                                echo "<li class='$active'><a href='?page=$i'>$i</a></li>";
                            }

                            // Show Last Page & Ellipsis
                            if ($end < $total_pages) {
                                if ($end < $total_pages - 1)
                                    echo "<li><span>...</span></li>";
                                echo '<li><a href="?page=' . $total_pages . '">' . $total_pages . '</a></li>';
                            }

                            // Next Button
                            if ($page < $total_pages) {
                                echo '<li><a href="?page=' . ($page + 1) . '">Next</a></li>';
                            }
                        endif;
                        ?>
                    </ul>
                <?php else: ?>
                    <div class="alert alert-info">No users found in the database.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// 7. CLEANUP

include_once __DIR__ . "/includes/footer.php";
?>