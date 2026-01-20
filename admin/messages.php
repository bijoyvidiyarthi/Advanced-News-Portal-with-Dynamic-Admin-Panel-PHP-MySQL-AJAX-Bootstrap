<?php
include_once __DIR__ . "/config.php";
include_once __DIR__ . "/includes/auth.php";
include_once __DIR__ . "/includes/header.php";
include_once __DIR__ . "/includes/sidebar.php";

// Delete Message
if (isset($_GET['del_id'])) {
    $del_id = (int) $_GET['del_id'];
    mysqli_query($conn, "DELETE FROM contact_messages WHERE id = $del_id");
    echo "<script>window.location='messages.php';</script>";
}
?>

<div class="container-fluid py-4">
    <div class="card shadow border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-inbox me-2"></i> User Messages</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Sender</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM contact_messages ORDER BY id DESC";
                        $result = mysqli_query($conn, $sql);
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                ?>
                                <tr>
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td>
                                        <strong><?php echo $row['name']; ?></strong><br>
                                        <small class="text-muted"><?php echo $row['email']; ?></small>
                                    </td>
                                    <td><span class="badge bg-info text-dark"><?php echo $row['subject']; ?></span></td>
                                    <td style="max-width: 300px;"><?php echo substr($row['message'], 0, 100); ?>...</td>
                                    <td><?php echo date('d M, Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <a href="view-message.php?id=<?php echo $row['id']; ?>"
                                            class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>
                                        <a href="messages.php?del_id=<?php echo $row['id']; ?>"
                                            onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger"><i
                                                class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>No messages found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . "/includes/footer.php"; ?>