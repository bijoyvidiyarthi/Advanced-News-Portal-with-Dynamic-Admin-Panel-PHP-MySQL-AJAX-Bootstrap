<?php
include "header.php";
include "config.php";

//cannot access this page if not admin and without login
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    $_SESSION['error'] = "🚫 **Access Denied:** You do not have permission to access this page.";
    header("Location: post.php");
    exit();
} else {
    $user_role = htmlspecialchars($_SESSION['user_role']);
}

//--- Pagination Setup---
$limit = 5;

// Set the current page number, ensuring it's an integer and at least 1
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

//Calculate Offset
$offset = ($page - 1) * $limit;

//--- Calculate total records --

//check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
} else {
    //count SQL for categories
    $countSql = "SELECT COUNT(category_id) AS total_categories FROM category";
    $countResult = mysqli_query($conn, $countSql);

    //By default total records and pages will be 0
    $total_records = 0;
    $total_Pages = 0;

    if ($countResult && mysqli_num_rows($countResult) > 0) {
        // Get total number of users
        $countrow = mysqli_fetch_assoc($countResult); //here row is an associative array that contains total_users as key
        $total_categories = $countrow['total_categories'];
        $total_pages = ceil($total_categories / $limit); //eg: 45/10 =4.5 =>5

        // Ensure the current page doesn't exceed the total pages 
        // (e.g., if a record was deleted)
        if ($page > $total_Pages && $total_Pages > 0) {
            $page = $total_Pages;
            $offset = ($page - 1) * $limit; // Recalculate offset for the last page
        }
    }
}
?>
<div id="admin-content">
    <div class="container">
        <div class="row">
            <div class="col-md-10">
                <h1 class="admin-heading">All Categories</h1>
            </div>
            <div class="col-md-2">
                <a class="add-new" href="add-category.php">add category</a>
            </div>
            <div class="col-md-12">
                <?php
                if (isset($_SESSION['success'])) {
                    $success_msg = htmlspecialchars($_SESSION['success']);
                    echo "<div class='alert alert-success'>$success_msg</div>";
                    unset($_SESSION['success']);
                }

                if (isset($_SESSION['error'])) {
                    $error_message = htmlspecialchars($_SESSION['error']);
                    echo "<div class='alert alert-danger'>$error_message</div>";
                    unset($_SESSION['error']);
                }

                //first check if connection is established or not
                if ($conn) {
                    // Fetch users from database and display here
                    $sql = "SELECT * 
                            FROM category
                            ORDER BY category_id ASC
                            LIMIT {$limit} OFFSET {$offset}";

                    $result = mysqli_query($conn, $sql);

                    if (!$result) {
                        die("Query Failed: " . mysqli_error($connection));
                    } else {
                        if (mysqli_num_rows($result) > 0) {
                            ?>
                            <table class="content-table">
                                <thead>
                                    <th>S.No.</th>
                                    <th>Category Name</th>
                                    <th>No. of Posts</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $id_no = $offset + 1;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $id = $row['category_id'];
                                        $category_name = $row['category_name'];
                                        $post = $row['post'];
                                        ?>
                                        <tr>
                                            <td class='id'><?php echo htmlspecialchars($id_no); ?></td>
                                            <td><?php echo htmlspecialchars($category_name); ?></td>
                                            <td><?php echo htmlspecialchars($post); ?></td>
                                            <td class='edit'><a href='update-category.php?id=<?php echo urlencode($id); ?>'><i
                                                        class='fa fa-edit'></i></a></td>
                                            <td class='delete'><a href='delete-category.php?id=<?php echo urlencode($id); ?>'><i
                                                        class='fa fa-trash-o'></i></a></td>
                                        </tr>
                                        <?php $id_no++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <ul class='pagination admin-pagination'>
                                <!-- pagination links code  -->
                                <?php
                                if ($total_pages > 1) {
                                    //Previous Page
                                    if ($page > 1) {
                                        echo '<li class="prev"><a href=" ' . $_SERVER['PHP_SELF'] . '?page=' . ($page - 1) . ' " >Prev</a></li>';
                                    }

                                    //Show Page numbers
                                    $range = 2;
                                    $start_loop = max(1, $page - $range);
                                    $end_loop = min($total_Pages, $page + $range);

                                    // Truncation logic (first page and ellipsis)
                    
                                    for ($i = 1; $i <= $total_pages; $i++) {
                                        if ($i == $page) {
                                            $active = "active";
                                        } else {
                                            $active = "";
                                        }
                                        echo '<li class=" ' . $active . ' "><a href=" ' . $_SERVER['PHP_SELF'] . '?page=' . $i . ' " >' . $i . '</a></li>';
                                    }

                                    //next page
                                    if (1 < $total_pages && $page < $total_pages):
                                        echo '<li class="next"><a href=" ' . $_SERVER['PHP_SELF'] . '?page=' . ($page + 1) . ' " >Next</a></li>';
                                    endif;
                                } else {
                                    echo "<li> All Records Are Shown</li>";
                                }
                                ?>
                            </ul>
                            <?php
                        } else {
                            echo "<p>No records found.</p>";
                        }
                        mysqli_close($conn);
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>
<?php include "footer.php"; ?>