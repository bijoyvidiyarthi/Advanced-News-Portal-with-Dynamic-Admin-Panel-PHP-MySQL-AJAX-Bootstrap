<?php
include_once __DIR__ . "/config.php";
include_once __DIR__ . "/includes/auth.php";
include_once __DIR__ . "/includes/header.php";
include_once __DIR__ . "/includes/sidebar.php";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div id="alert-container" style="display:none;">
                <div id="alert-msg" class="alert"></div>
            </div>

            <div class="card shadow border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-key me-2 text-primary"></i>Change Password</h5>
                </div>
                <div class="card-body p-4">
                    <form id="passwordUpdateForm">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <div class="mb-3">
                            <label class="small fw-bold">Current Password</label>
                            <input type="password" name="current_pass" class="form-control" required
                                placeholder="Enter current password">
                        </div>

                        <div class="mb-3">
                            <label class="small fw-bold">New Password</label>
                            <input type="password" name="new_pass" id="new_pass" class="form-control" required
                                placeholder="Min 6 characters">
                        </div>

                        <div class="mb-3">
                            <label class="small fw-bold">Confirm New Password</label>
                            <input type="password" name="confirm_pass" class="form-control" required
                                placeholder="Repeat new password">
                        </div>

                        <div class="d-grid">
                            <button type="submit" id="passSaveBtn" class="btn btn-primary shadow-sm">
                                <span id="btnText">Update Password</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#passwordUpdateForm').on('submit', function (e) {
            e.preventDefault();

            let newPass = $('#new_pass').val();
            let confirmPass = $('input[name="confirm_pass"]').val();
            let alertContainer = $('#alert-container');
            let alertMsg = $('#alert-msg');

            //password requirement checking
            let passRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

            if (!passRegex.test(newPass)) {
                alertContainer.fadeIn();
                alertMsg.removeClass('alert-success').addClass('alert-danger')
                    .html("<b>Password must follow:</b><br>• Minimum 8 characters<br>• At least one uppercase & one lowercase letter<br>• At least one number<br>• At least one special character (@$!%*?&)");
                return false; 
            }

            if (newPass !== confirmPass) {
                alertContainer.fadeIn();
                alertMsg.removeClass('alert-success').addClass('alert-danger').text("Confirm password does not match!");
                return false;
            }

            let saveBtn = $("#passSaveBtn");
            saveBtn.prop('disabled', true).find('#btnText').text('Processing...');

            $.ajax({
                url: 'update-password.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (response) {
                    alertContainer.fadeIn();
                    if (response.status === 'success') {
                        alertMsg.removeClass('alert-danger').addClass('alert-success').text(response.message);
                        $('#passwordUpdateForm')[0].reset();
                    } else {
                        alertMsg.removeClass('alert-success').addClass('alert-danger').text(response.message);
                    }
                    saveBtn.prop('disabled', false).find('#btnText').text('Update Password');
                    setTimeout(() => { alertContainer.fadeOut(); }, 6000);
                }
            });
        });
    });
   
</script>

<?php include_once __DIR__ . "/includes/footer.php"; ?>