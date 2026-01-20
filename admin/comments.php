<?php
//  ============== Start session and Database ===============
include_once __DIR__ . "/config.php";
include __DIR__ . "/includes/auth.php";

// ======== Database Connection Check ==========
if (!$conn) {
    $_SESSION['error'] = "⚠️ Database Connection Error!";
    header("Location: dashboard.php");
    exit();
}

// --- AJAX request handler ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    // clean past output
    ob_clean();
    header('Content-Type: application/json');

    if ($_POST['action'] == 'update_status') {
        $id = (int) $_POST['id'];
        $current_status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($current_status == 'approved') ? 'pending' : 'approved';
        $sql = "UPDATE comments SET status = '{$new_status}' WHERE comment_id = {$id}";
        $res = mysqli_query($conn, $sql);
        echo json_encode(['success' => $res]);
        exit;
    }

    if ($_POST['action'] == 'delete_comment') {
        $id = (int) $_POST['id'];
        $sql = "DELETE FROM comments WHERE comment_id = {$id}";
        $res = mysqli_query($conn, $sql);
        echo json_encode(['success' => $res]);
        exit;
    }
}


/**
 * PAGINATION SETUP
 */
$limit = 5;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Total comments count for pagination
$countSql = "SELECT COUNT(comment_id) AS total_comments FROM comments";
$countResult = mysqli_query($conn, $countSql);
$total_comments = mysqli_fetch_assoc($countResult)['total_comments'];
$total_pages = ceil($total_comments / $limit);

/**
 * FETCH COMMENTS WITH POST TITLE
 * Join with post table to show which post the comment belongs to
 */
$sql = "SELECT c.*, p.title AS post_title 
        FROM comments c 
        LEFT JOIN post p ON c.post_id = p.post_id 
        ORDER BY 
            (CASE WHEN c.status = 'pending' THEN 0 ELSE 1 END) ASC, 
            c.created_at DESC
        LIMIT {$offset}, {$limit}";

$result = mysqli_query($conn, $sql);

include "includes/header.php";
include "includes/sidebar.php";
?>

<div class="app-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-md-12">

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $_SESSION['success'];
                        unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title"><i class="bi bi-chat-left-text me-2"></i> Manage User Comments</h3>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px">ID</th>
                                        <th>User Info</th>
                                        <th>Comment</th>
                                        <th>Related Post</th>
                                        <th>Date</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($result) > 0):
                                        $sl_no = $offset + 1;
                                        while ($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td>#<?= $sl_no ?></td>
                                                <td>
                                                    <div class="fw-bold"><?= htmlspecialchars($row['name']); ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($row['email']); ?></small>
                                                </td>
                                                <td style="max-width: 250px;">
                                                    <p class="small mb-0 text-wrap"><?= htmlspecialchars($row['comment']); ?>
                                                    </p>
                                                </td>
                                                <td>
                                                    <small class="text-primary fw-medium">
                                                        <?= $row['post_title'] ? htmlspecialchars(mb_strimwidth($row['post_title'], 0, 30, "...")) : 'Post Deleted'; ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold text-dark js-timeago"
                                                            data-time="<?= $row['created_at']; ?>">
                                                            <?= $row['created_at']; ?>
                                                        </span>
                                                        <small class="text-muted" style="font-size: 0.75rem;">
                                                            <?= date('d M, Y', strtotime($row['created_at'])); ?>
                                                        </small>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($row['status'] == 'approved'): ?>
                                                        <span
                                                            class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                                    <?php else: ?>
                                                        <span
                                                            class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button"
                                                            class="btn btn-outline-secondary btn-update-status"
                                                            data-id="<?= $row['comment_id']; ?>"
                                                            data-status="<?= $row['status']; ?>">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </button>

                                                        <button type="button" class="btn btn-outline-danger btn-delete-comment"
                                                            data-id="<?= $row['comment_id']; ?>" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            $sl_no++;
                                        endwhile;
                                    else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">No comments found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer clearfix">
                        <ul class="pagination pagination-sm m-0 float-end">
                            <?php if ($page > 1): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?= $page - 1; ?>">&laquo;</a></li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?= $page + 1; ?>">&raquo;</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    //time ago function
    // dashboard.js

    /**
     *  Time Ago Function
     */
    function timeAgo(dateParam) {
        if (!dateParam) return null;
        const date = new Date(dateParam.replace(/-/g, "/"));
        const today = new Date();
        const seconds = Math.round((today - date) / 1000);

        if (isNaN(seconds)) return dateParam;
        if (seconds < 5) return 'Just Now';

        const intervals = {
            'y': 31536000, 'mo': 2592000, 'd': 86400, 'h': 3600, 'm': 60
        };

        for (let key in intervals) {
            const interval = Math.floor(seconds / intervals[key]);
            if (interval >= 1) return `${interval}${key} ago`;
        }
        return `${seconds}s ago`;
    }

    function updateAllTimes() {
        document.querySelectorAll('.js-timeago').forEach(el => {
            const timestamp = el.getAttribute('data-time');
            if (timestamp) el.innerText = timeAgo(timestamp);
        });
    }

    /**
     *  AJAX Action Handler (Status & Delete)
     */
    document.addEventListener('click', function (e) {
        // --- update status ---
        const updateBtn = e.target.closest('.btn-update-status');
        if (updateBtn) {
            const id = updateBtn.getAttribute('data-id');
            const status = updateBtn.getAttribute('data-status');

            const formData = new URLSearchParams();
            formData.append('action', 'update_status');
            formData.append('id', id);
            formData.append('status', status);

            fetch('comments.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
                .then(response => response.json())
                .then(res => {
                    if (res.success) {
                        location.reload();
                    } else {
                        alert('Update failed!');
                    }
                });
        }

        // --- delete logic ---
        const deleteBtn = e.target.closest('.btn-delete-comment');
        if (deleteBtn) {
            if (confirm('Are you sure you want to delete this comment?')) {
                const id = deleteBtn.getAttribute('data-id');

                const formData = new URLSearchParams();
                formData.append('action', 'delete_comment');
                formData.append('id', id);

                fetch('comments.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData
                })
                    .then(response => response.json())
                    .then(res => {
                        if (res.success) {
                            // table row delete
                            deleteBtn.closest('tr').remove();
                        } else {
                            alert('Delete failed!');
                        }
                    });
            }
        }
    });

    /**
     *  Initialization
     */
    document.addEventListener('DOMContentLoaded', function () {
        updateAllTimes();
        setInterval(updateAllTimes, 60000); // update every minute
    });



</script>
<?php include_once __DIR__ . "/includes/footer.php"; ?>