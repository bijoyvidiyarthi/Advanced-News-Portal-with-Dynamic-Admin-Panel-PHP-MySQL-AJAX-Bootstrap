<?php
/* =========================
   Session & Authentication
========================= */
include_once __DIR__ . "/config.php";
include_once __DIR__ . "/includes/auth.php";


// ================= CSRF TOKEN =================
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ================= AUTH DATA =================
$user_role = $_SESSION['user_role'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 0;

// ================= VALIDATE ID =================
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    $_SESSION['error'] = "Invalid post ID.";
    header("Location: post.php");
    exit;
}

$post_id = (int) $_GET['id'];

// ================= FETCH POST =================
$stmt = $conn->prepare(
    "SELECT p.*, c.category_name, u.username
     FROM post p
     LEFT JOIN category c ON p.category = c.category_id
     LEFT JOIN user u ON p.author = u.user_id
     WHERE p.post_id = ?"
);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Post not found.";
    header("Location: post.php");
    exit;
}

$rowData = $result->fetch_assoc();
$stmt->close();

// ================= AUTHORIZATION =================
if ($user_role != 1 && $rowData['author'] != $user_id) {
    $_SESSION['error'] = "Access denied.";
    header("Location: post.php");
    exit;
}

include_once __DIR__ . "/includes/header.php";
include_once __DIR__ . "/includes/sidebar.php";
?>

<div class="app-content">
    <div class="container-fluid">

        <form action="save-update.php" method="POST" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="post_id" value="<?php echo $rowData['post_id']; ?>">
            <input type="hidden" name="old_category" value="<?php echo $rowData['category']; ?>">
            <input type="hidden" name="old-image" value="<?php echo htmlspecialchars($rowData['post_img']); ?>">

            <div class="row">
                <div class="col-md-8">

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php
                            $errors_array = explode("|||", $_SESSION['error']);
                            foreach ($errors_array as $error) {
                                echo "<div>• " . htmlspecialchars(trim($error)) . "</div>";
                            }
                            unset($_SESSION['error']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card card-primary card-outline mb-4">
                        <div class="card-body">
                            <!-- Post Title -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Post Title</label>
                                <input type="text" name="post_title" class="form-control form-control-lg"
                                    value="<?php echo htmlspecialchars($rowData['title']); ?>" required>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Post Description</label>
                                <textarea name="postdesc" id="editor" class="form-control" rows="15" required>
                                            <?php echo htmlspecialchars($rowData['description']); ?>
                                </textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tags</label>
                                <input type="text" name="tags" class="form-control"
                                    value="<?php echo htmlspecialchars($rowData['tags']); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Publish Options -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="card-title">Publish Settings</div>
                        </div>

                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Category</label>
                                <select class="form-select" name="category">
                                    <?php
                                    $cat_stmt = $conn->prepare("SELECT category_id, category_name FROM category");
                                    $cat_stmt->execute();
                                    $cat_result = $cat_stmt->get_result();
                                    while ($cat = $cat_result->fetch_assoc()) {
                                        $selected = ($cat['category_id'] == $rowData['category']) ? 'selected' : '';
                                        echo "<option value='{$cat['category_id']}' {$selected}>" . htmlspecialchars($cat['category_name']) . "</option>";
                                    }
                                    $cat_stmt->close();
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Post Status</label>
                                <select name="status" class="form-select">
                                    <?php
                                    $statuses = ['draft' => 'Draft', 'approved' => 'Approved (Live)', 'pending' => 'Pending Review'];
                                    foreach ($statuses as $value => $label) {
                                        if ($user_role != 1 && $value === 'approved')
                                            continue;
                                        if ($user_role == 1 && $value === 'pending')
                                            continue;
                                        $selected = ($rowData['status'] === $value) ? 'selected' : '';
                                        echo "<option value='{$value}' {$selected}>{$label}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Display Flags -->
                            <div class="mb-3 border rounded p-2 bg-light">
                                <label class="form-label d-block fw-bold mb-1">Visibility Flags</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_breaking" value="1"
                                        id="breaking" <?php echo ($rowData['is_breaking']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="breaking">Breaking News</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_featured" value="1"
                                        id="featured" <?php echo ($rowData['is_featured']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="featured">Featured News</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Featured Image -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="card-title">Featured Image</div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 text-center border p-2 bg-light rounded">
                                <img src="upload/<?php echo htmlspecialchars($rowData['post_img']); ?>"
                                    class="img-fluid rounded mb-2" style="max-height: 150px;">
                                <div class="small text-muted">Current Image</div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label small text-muted">Upload New:</label>
                                <input type="file" name="fileToUpload" class="form-control mb-2" id="imageInput"
                                    onchange="previewFile()">

                                <div class="text-center fw-bold text-muted my-2">- OR -</div>

                                <button type="button" class="btn btn-outline-primary w-100 mb-3" data-bs-toggle="modal"
                                    data-bs-target="#mediaModal">
                                    <i class="bi bi-images me-1"></i> Choose from Library
                                </button>

                                <input type="hidden" name="selected_image" id="selected_image_input">

                                <div class="mt-2 text-center border rounded p-2 bg-white position-relative d-flex align-items-center justify-content-center"
                                    style="min-height: 150px;">
                                    <img id="preview" src="#" alt="Preview"
                                        style="max-width: 100%; max-height: 200px; display: none;"
                                        class="rounded shadow-sm" />
                                    <span id="previewText" class="text-muted small">No image selected</span>

                                    <button type="button" id="removeBtn"
                                        class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 shadow"
                                        style="display: none;" onclick="clearImageSelection()">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                <small class="text-info">Leave empty to keep current image.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Submit / Cancel -->
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" name="submit" class="btn btn-primary w-100 mb-2">
                                <i class="bi bi-save me-1"></i> Update Post
                            </button>
                            <a href="post.php" class="btn btn-outline-secondary w-100">Cancel</a>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>

<!-- Media Library Modal -->
<div class="modal fade" id="mediaModal" tabindex="-1" aria-labelledby="mediaModalLabel" aria-hidden="true"
    style="z-index: 9999;">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaModalLabel">Select Image from Library</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="row g-3">
                    <?php
                    $sql_media = "SELECT * FROM media ORDER BY id DESC LIMIT 40";
                    $res_media = mysqli_query($conn, $sql_media);

                    if (mysqli_num_rows($res_media) > 0) {
                        while ($media = mysqli_fetch_assoc($res_media)) {
                            $img_src = file_exists($media['image_path']) ? $media['image_path'] : 'assets/img/no-image.jpg';
                            ?>
                            <div class="col-6 col-md-3 col-lg-2">
                                <div class="card h-100 media-card shadow-sm"
                                    onclick="selectMedia('<?php echo $media['image_path']; ?>')">
                                    <div class="ratio ratio-1x1 bg-white border-bottom">
                                        <img src="<?php echo $img_src; ?>" class="card-img-top object-fit-cover p-1" alt="Img"
                                            loading="lazy">
                                    </div>
                                    <div class="card-footer p-1 text-center bg-white border-0">
                                        <small class="text-truncate d-block text-muted" style="max-width: 100%;"
                                            title="<?php echo $media['image_name']; ?>">
                                            <?php echo $media['image_name']; ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div class="col-12 text-center py-5 text-muted">
                                <i class="bi bi-images display-1"></i>
                                <p class="mt-3">No images found in the library.</p>
                              </div>';
                    }
                    ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
include_once __DIR__ . "/includes/footer.php";
?>