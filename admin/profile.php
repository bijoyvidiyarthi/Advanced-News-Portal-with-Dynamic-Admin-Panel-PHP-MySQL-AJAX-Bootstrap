<?php
include_once __DIR__ . "/config.php";
include_once __DIR__ . "/includes/auth.php";
include_once __DIR__ . "/includes/header.php";
include_once __DIR__ . "/includes/sidebar.php";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user_id = $_SESSION['user_id'];
$row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM user WHERE user_id = {$user_id}"));
?>

<div class="container-fluid py-4">
    <div id="alert-container" class="row justify-content-center" style="display:none;">
        <div class="col-md-10">
            <div id="alert-msg" class="alert"></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow border-0 text-center p-3 profile_card">
                <div class="card-body">
                    <?php $img = (!empty($row['user_img'])) ? "upload/users/" . $row['user_img'] : "images/default-user.png"; ?>
                    <img id="display_img" src="<?= $img ?>" class="rounded-circle img-thumbnail mb-3 shadow-sm"
                        style="width: 150px; height: 150px; object-fit: cover;">
                    <h4 id="display_full_name" class="fw-bold mb-0">
                        <?= $row['first_name'] . " " . $row['last_name'] ?: $row['username'] ?>
                    </h4>
                    <p class="text-muted text-uppercase small">
                        <?= ($row['role'] == 1) ? "Administrator" : "Staff" ?>
                    </p>
                    <p id="display_bio" class="text-muted small italic mb-3" style="font-style: italic;">
                        <?= $row['bio'] ?: 'No bio added yet.' ?>
                    </p>

                    <hr>
                    <div class="text-start small">
                        <p class="mb-1"><strong>Email:</strong> <span id="display_email"><?= $row['email'] ?></span></p>
                        <p class="mb-1"><strong>Username:</strong> <?= $row['username'] ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Profile Settings</h5>
                </div>
                <div class="card-body">
                    <form id="profileUpdateForm" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="small fw-bold">First Name</label>
                                <input type="text" name="f_name" id="f_name" class="form-control"
                                    value="<?= $row['first_name'] ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="small fw-bold">Last Name</label>
                                <input type="text" name="l_name" id="l_name" class="form-control"
                                    value="<?= $row['last_name'] ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="small fw-bold">Full Name</label>
                                <input type="text" name="full_name" id="full_name" class="form-control bg-light"
                                    value="<?= $row['first_name'] . " " . $row['last_name'] ?>" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="small fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= $row['email'] ?>"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold">Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?= $row['phone'] ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="small fw-bold d-block">Change Profile Picture</label>
                                <label for="new_image" class="upload-label small">
                                    <i class="fas fa-upload me-2"></i> Choose Image
                                </label>
                                <input type="file" id="new_image" accept="image/*">
                            </div>

                            <div class="modal fade" id="cropperModal" tabindex="-1" aria-hidden="true"
                                data-bs-backdrop="static">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Position & Crop Your Photo</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="img-container">
                                                <img id="imageToCrop" src="">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn btn-primary" id="cropAndSave">Apply
                                                Crop</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold">Facebook URL</label>
                                <input type="url" name="facebook" class="form-control"
                                    value="<?= trim($row['facebook']) ?>" placeholder="https://facebook.com/...">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="small fw-bold">Twitter URL</label>
                                <input type="url" name="twitter" class="form-control"
                                    value="<?= isset($row['twitter']) ? trim($row['twitter']) : '' ?>"
                                    placeholder="https://twitter.com/...">
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold">Instagram URL</label>
                                <input type="url" name="instagram" class="form-control"
                                    value="<?= isset($row['instagram']) ? trim($row['instagram']) : '' ?>"
                                    placeholder="https://instagram.com/...">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="small fw-bold">Bio</label>
                            <textarea name="bio" class="form-control" rows="3"><?= $row['bio'] ?></textarea>
                        </div>

                        <button type="submit" id="saveBtn" class="btn btn-primary px-5 shadow-sm">
                            <span id="btnText">Save Changes</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Ensure the image doesn't break out of the container */
    .img-container {
        max-height: 400px;
        width: 100%;
    }

    /* Round the cropping area to match the profile card circle */
    .cropper-view-box,
    .cropper-face {
        border-radius: 50%;
    }

    /* Hide the native file input and style the custom UI */
    #new_image {
        display: none;
    }

    .upload-label {
        cursor: pointer;
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        padding: 10px;
        display: block;
        text-align: center;
        border-radius: 8px;
        transition: 0.3s;
    }

    .upload-label:hover {
        background: #e9ecef;
    }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        let cropper;
        let croppedBlob;
        const imageToCrop = document.getElementById('imageToCrop');
        const cropperModal = new bootstrap.Modal(document.getElementById('cropperModal'));

        /**
         * 1. Initialize Cropper when image is selected
         */
        $("#new_image").change(function (e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    imageToCrop.src = event.target.result;
                    cropperModal.show();
                };
                reader.readAsDataURL(files[0]);
            }
        });

        // Start Cropper when modal opens
        document.getElementById('cropperModal').addEventListener('shown.bs.modal', function () {
            cropper = new Cropper(imageToCrop, {
                aspectRatio: 1,
                viewMode: 1, // Restrict crop box to container
                dragMode: 'move',
                guides: false,
                autoCropArea: 1,
                cropBoxResizable: true
            });
        });

        // Clean up when modal closes
        document.getElementById('cropperModal').addEventListener('hidden.bs.modal', function () {
            cropper.destroy();
        });

        /**
         * 2. Capture the Crop
         */
        $("#cropAndSave").click(function () {
            const canvas = cropper.getCroppedCanvas({
                width: 500, // High quality resolution
                height: 500,
                imageSmoothingQuality: 'high'
            });

            canvas.toBlob((blob) => {
                croppedBlob = blob;
                // Update the profile card preview instantly
                $("#display_img").attr("src", canvas.toDataURL('image/jpeg'));
                cropperModal.hide();
            }, 'image/jpeg', 0.9); // 90% quality
        });

        /**
         * 3. Live Text Sync
         */
        $('#f_name, #l_name').on('input', function () {
            let combinedName = ($('#f_name').val().trim() + ' ' + $('#l_name').val().trim()).trim();
            $('#full_name').val(combinedName);
            $('#display_full_name').text(combinedName || "User Name");
        });

        $('input[name="email"]').on('input', function () {
            $('#display_email').text($(this).val() || "email@example.com");
        });

        $('textarea[name="bio"]').on('input', function () {
            $('#display_bio').text($(this).val() || "No bio added yet.");
        });

        /**
         * 4. AJAX Profile Update (Including Cropped Image)
         */
        $('#profileUpdateForm').on('submit', function (e) {
            e.preventDefault();

            let formData = new FormData(this);

            // Append cropped image if it exists
            if (croppedBlob) {
                formData.set('new_image', croppedBlob, 'profile.jpg');
            }

            let saveBtn = $("#saveBtn");
            saveBtn.prop('disabled', true).find('#btnText').text('Updating...');

            $.ajax({
                url: 'update-profile.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (response) {
                    $('#alert-container').fadeIn();
                    if (response.status === 'success') {
                        $('#alert-msg').removeClass('alert-danger').addClass('alert-success').text(response.message);
                        if (response.new_img) {
                            $("#display_img").attr("src", response.new_img + '?' + new Date().getTime());
                        }
                    } else {
                        $('#alert-msg').removeClass('alert-success').addClass('alert-danger').html(response.message);
                    }
                    saveBtn.prop('disabled', false).find('#btnText').text('Save Changes');
                    setTimeout(() => { $('#alert-container').fadeOut(); }, 5000);
                }
            });
        });
    });
</script>
<?php include_once __DIR__ . "/includes/footer.php"; ?>