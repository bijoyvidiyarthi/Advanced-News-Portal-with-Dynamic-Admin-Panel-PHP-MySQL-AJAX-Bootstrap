<?php
include_once __DIR__ . "/config.php";
include_once __DIR__ . "/includes/auth.php";
include_once __DIR__ . "/includes/header.php";
include_once __DIR__ . "/includes/sidebar.php";

// only admin can change settings
if ($_SESSION['user_role'] == 0) {
    header("Location: post.php");
}
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success'];
                unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <div class="card shadow border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-cogs me-2"></i> Website Global Settings</h5>
                </div>
                <div class="card-body p-4">
                    <?php
                    $sql = "SELECT * FROM settings LIMIT 1";
                    $result = mysqli_query($conn, $sql);
                    if (mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_assoc($result);
                        ?>
                        <form action="save-settings.php" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="small fw-bold">Website Name</label>
                                    <input type="text" name="website_name" value="<?= $row['websitename']; ?>"
                                        class="form-control" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="small fw-bold">Website Logo</label>
                                    <input type="file" name="logo" class="form-control mb-2">
                                    <img src="images/<?= $row['logo']; ?>" style="height: 50px;" class="img-thumbnail">
                                    <input type="hidden" name="old_logo" value="<?= $row['logo']; ?>">
                                </div>

                                <hr class="my-4">

                                <div class="col-md-12 mb-3">
                                    <label class="small fw-bold">Site Description (SEO)</label>
                                    <textarea name="site_desc" class="form-control"
                                        rows="2"><?= $row['site_desc']; ?></textarea>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="small fw-bold">Contact Email</label>
                                    <input type="email" name="contact_email" value="<?= $row['contact_email']; ?>"
                                        class="form-control">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="small fw-bold">Contact Phone</label>
                                    <input type="text" name="contact_phone" value="<?= $row['contact_phone']; ?>"
                                        class="form-control">
                                </div>

                                <hr class="my-4">

                                <div class="col-md-6 mb-3">
                                    <label class="small fw-bold">Facebook Page URL</label>
                                    <input type="url" name="facebook" value="<?= $row['facebook_page']; ?>"
                                        class="form-control">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="small fw-bold">YouTube Channel URL</label>
                                    <input type="url" name="youtube" value="<?= $row['youtube_url']; ?>"
                                        class="form-control">
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="small fw-bold">Footer Description</label>
                                    <textarea name="footer_desc" class="form-control" rows="3"
                                        required><?= $row['footerdesc']; ?></textarea>
                                </div>

                                <div class="col-md-12 mb-4">
                                    <label class="small fw-bold">Copyright Text</label>
                                    <input type="text" name="copyright"
                                        value="<?php echo htmlspecialchars($row['copyright_text'], ENT_QUOTES); ?>"
                                        class="form-control">
                                </div>

                                <div class="text-end">
                                    <button type="submit" name="submit" class="btn btn-primary px-5 shadow-sm">Save All
                                        Settings</button>
                                </div>
                        </form>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . "/includes/footer.php"; ?>