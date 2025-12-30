<?php
include "DATABASE2.php";

// Top of add-user.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//--- Pagination Setup---
$limit = 5;

// Set the current page number, ensuring it's an integer and at least 1
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1)
    $page = 1;


//Calculate Offset
$offset = ($page - 1) * $limit;
//--- Calculate total records --

try {

    $db = new database();

    // check connection with DB
    if (!$db->mysqli || $db->mysqli->connect_error) {
        throw new Exception("⚠️ **Database Connection Error:** Connection failed.");
    }

    // --- Calculate total records for Pagination ---
    // Use the select method: select($table, $columns)
    $countResult = $db->select("students", "COUNT(id) AS total_users");
    $total_users = $db->getResult()[0]['total_users'];
    $total_Pages = ceil($total_users / $limit);

    //Adjust page if out of bounds
    if ($page > $total_Pages && $total_Pages > 0) {
        $page = $total_Pages;
        $offset = ($page - 1) * $limit;
    }

    // 2. Get Users using your new OOP method
    // Note: We pass the LIMIT and OFFSET as parameters
    if (
        $db->select(
            "students",
            "*",
            "",
            "",
            "id",
            $limit . " OFFSET " . $offset
        )
    ) {
        $results = $db->getResult();
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: users.php");
    exit();
}

include 'header.php';
?>

<div id="admin-content">
    <div class="container">
        <div class="row">
            <div class="col-md-10">
                <h1 class="admin-heading">All Users</h1>
            </div>
            <div class="col-md-2">
                <a class="add-new" href="add-user.php">add user</a>
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

                if (count($results) > 0) {
                    ?>
                    <table class="content-table">
                        <thead>
                            <th>S.No.</th>
                            <th>Name</th>
                            <th>City</th>
                            <th>Age</th>
                            <th>Edit</th>
                            <th>Delete</th>
                        </thead>
                        <tbody>
                            <?php
                            $id_no = $offset + 1;
                            foreach ($results as $row) {

                                $id = $row['id'];
                                $name = $row['student_name'];
                                $city = $row['city'];
                                $age = $row['age'];
                                ?>
                                <tr>
                                    <td class='id'><?php echo htmlspecialchars($id_no); ?></td>
                                    <td><?php echo htmlspecialchars($name); ?></td>
                                    <td><?php echo htmlspecialchars($city); ?></td>
                                    <td><?php echo htmlspecialchars($age); ?></td>

                                     <td class='edit'><a href='update-user.php?aid=<?php echo urlencode($id); ?>'><i
                                                class='fa fa-edit'></i></a></td>
                                    <td class='delete'><a href='delete-user.php?aid=<?php echo urlencode($id); ?>'><i
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
                        if ($total_Pages > 1):
                            //Previous Page
                            if ($page > 1):
                                echo '<li class="prev"><a href=" ' . $_SERVER['PHP_SELF'] . '?page=' . ($page - 1) . ' " >Prev</a></li>';
                            endif;

                            //Show Page numbers
                            $range = 2;
                            //eg. if current page is 3, start will 3-2 = 1 (pagination: ..12 3 45)
                            $start = max(1, $page - $range);
                            $end = min($total_Pages, $page + $range);

                            // Start Ellipsis
                            if ($start > 1):
                                echo '<li><a href="' . $_SERVER['PHP_SELF'] . '?page=1">1</a></li>';
                                if ($start > 2)
                                    echo '<li><span>...</span></li>';
                            endif;

                            // Truncation logic (first page and ellipsis)
                            for ($i = $start; $i <= $end; $i++):
                                $active = ($i == $page) ? "active" : "";
                                echo '<li class=" ' . $active . ' "><a href=" ' . $_SERVER['PHP_SELF'] . '?page=' . $i . ' " >' . $i . '</a></li>';
                            endfor;


                            // End Ellipsis
                            if ($end < $total_Pages):
                                if ($end < $total_Pages - 1)
                                    echo '<li><span>...</span></li>';
                                echo '<li><a href="' . $_SERVER['PHP_SELF'] . '?page=' . $total_Pages . '">' . $total_Pages . '</a></li>';
                            endif;

                            //next page
                            if (1 < $total_Pages && $page < $total_Pages):
                                echo '<li class="next"><a href=" ' . $_SERVER['PHP_SELF'] . '?page=' . ($page + 1) . ' " >Next</a></li>';
                            endif;
                        else:
                            echo "<p> All Records Are Shown</p>";
                        endif;
                        ?>
                    </ul>
                    <?php
                } else {
                    echo "<p>No records found.</p>";
                }
                ?>
            </div>
        </div>
    </div>
</div>
<?php include "footer.php"; ?>