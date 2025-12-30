<?php
include "DATABASE2.php";

// Top of add-user.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {

    $db = new database();
    //calculate and get offset;
    $offset = $db->setPagination(5);

    // check connection with DB
    if (!$db->mysqli || $db->mysqli->connect_error) {
        throw new Exception("⚠️ **Database Connection Error:** Connection failed.");
    }

    // Get Users using your new OOP method

    $db->select(
        "students",
        "*",
        null,
        null,
        "id",
        true
    );

    $results = $db->getResult();

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
                        $db->pagination("students");
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