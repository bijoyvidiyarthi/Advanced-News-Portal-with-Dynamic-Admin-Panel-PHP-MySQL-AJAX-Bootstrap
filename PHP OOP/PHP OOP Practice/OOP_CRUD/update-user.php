<?php
include "DATABASE2.php";

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    //include connection
    $db = new database();

    // Explicit safety check for the mysqli object
    if (!$db->mysqli || $db->mysqli->connect_error) {
        throw new Exception("⚠️ **Database Connection Error:** Connection failed.");
    }

    $aid = $_GET['aid'] ?? $_POST['user_id'] ?? null;
    $user_id = filter_var($aid, FILTER_VALIDATE_INT);

    // --- Id Validation ---
    if ($user_id === false || $user_id <= 0) {
        // Using $aid here shows the actual bad input (like "abc" or "-5")
        throw new Exception("⚠️ **Invalid Id:** '" . htmlspecialchars($aid) . "' is not a valid ID.");
    }

    // 3. FORM SUBMISSION (UPDATE LOGIC)
    if (isset($_POST['submit'])) {
        $st_name = $db->escapeString($_POST['name']);
        $age = filter_var($_POST['age'], FILTER_VALIDATE_INT);
        $city = $db->escapeString($_POST['city']);

        //Validate Input
        if (empty($st_name) || empty($city) || $age === false) {
            throw new Exception("Please provide a valid name, city, and numeric age.");
        }

        //fill data array to update
        $data = [
            "student_name" => $st_name,
            "age" => $age,
            "city" => $city
        ];

        // Perform Update - Ensure 'id' matches your DB column name
        if ($db->update("students", "id = {$user_id}", $data)) {
            $_SESSION['success'] = "User updated successfully.";
            header("Location: add-user.php");
            exit();
        }
    }

    // 4. FETCH CURRENT DATA (To pre-fill the form)
    // --- Check existance of ID in Database and Show Record ---
    $check_sql = "SELECT * FROM students where id = {$user_id}";
    $check_result = $db->mysqli->query($check_sql);

    //Show Error if id is not  found on Database 
    if (!$check_result || $check_result->num_rows == 0) {
        throw new Exception("🔍 **Record Not Found:** We could not find a student with ID **" . htmlspecialchars($user_id) . "** to delete. Please verify the ID.");
    }

    //Get the data of the Targeted User
    $rowData = $check_result->fetch_assoc();

    $student_id = $rowData['id'];
    $student_name = $rowData['student_name'];
    $age = $rowData['age'];
    $city = $rowData['city'];

} catch (Exception $e) {
    // ALL ERRORS (Connection, ID, Update, Selection) end up here
    $_SESSION['error'] = $e->getMessage();

    // If it's a connection or ID error, go back to main page. 
    // If it's an update error, stay on this page to show the error.
    if (
        strpos($e->getMessage(), 'Connection') !== false ||
        strpos($e->getMessage(), 'Record Not Found') !== false ||
        $user_id <= 0 || $user_id === false
    ) {
        header("Location: add-user.php");
    } else {
        header("Location: update-user.php?aid=" . $user_id);
    }
    exit();
}

include "header.php";
?>
<div id="admin-content">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="admin-heading">Modify User Details</h1>
            </div>
            <div class="col-md-offset-4 col-md-4">
                <!-- Form Start -->
                <?php
                //first check connection is established or not
                if (isset($_SESSION['error'])) {
                    echo "<div id='alert-box' class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
                    unset($_SESSION['error']);
                }
                ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?aid=" . $user_id); ?>" method="POST">

                    <div class="form-group">
                        <input type="hidden" name="user_id" class="form-control" value="<?php echo $student_id; ?>"
                            placeholder="">
                    </div>
                    <div class="form-group">
                        <label>Student Name</label>
                        <input type="text" name="name" class="form-control"
                            value="<?php echo htmlspecialchars($student_name); ?>" placeholder="" required>
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="text" name="age" class="form-control" value="<?php echo htmlspecialchars($age); ?>"
                            placeholder="" required>
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" class="form-control"
                            value="<?php echo htmlspecialchars($city); ?>" placeholder="" required>
                    </div>

                    <input type="submit" name="submit" class="btn btn-primary" value="Update" required />
                </form>
                <!-- /Form -->
            </div>
        </div>
    </div>
</div>
<?php include "footer.php"; ?>