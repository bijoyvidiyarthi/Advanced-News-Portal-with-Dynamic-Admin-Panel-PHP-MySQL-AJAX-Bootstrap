<?php
// ==== Start session and Database ======
include_once __DIR__ . "/config.php";
include __DIR__ . "/includes/auth.php";

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include "includes/header.php";
include "includes/sidebar.php";
?>

<div class="app-content">
    <div class="container-fluid">
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
        <div id="ajax-alert" style=""></div>

<form id="layoutForm">
    <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

    <div class="row">
        <div class="col-md-7">
            <div class="card card-primary card-outline mb-4 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-list-check me-2"></i>Menu & Footer Categories</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Category Name</th>
                                <th class="text-center">Show in Header</th>
                                <th class="text-center">Show in Footer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_cat = "SELECT * FROM category";
                            $res_cat = mysqli_query($conn, $sql_cat);
                            while ($row_cat = mysqli_fetch_assoc($res_cat)) {
                            ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($row_cat['category_name']); ?></td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input layout-switch" type="checkbox" 
                                               data-type="category" 
                                               data-id="<?php echo $row_cat['category_id']; ?>" 
                                               data-column="show_in_header"
                                               <?php echo ($row_cat['show_in_header'] == 1) ? 'checked' : ''; ?>>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input layout-switch" type="checkbox" 
                                               data-type="category" 
                                               data-id="<?php echo $row_cat['category_id']; ?>" 
                                               data-column="show_in_footer"
                                               <?php echo ($row_cat['show_in_footer'] == 1) ? 'checked' : ''; ?>>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card card-info card-outline mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="bi bi-share me-2"></i>Social Media Status</h3>
                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#addSocialModal">
                        <i class="bi bi-plus-lg"></i> Add New
                    </button>
                </div>
                <div class="card-body">
                    <?php
                    $sql_social = "SELECT * FROM social_links ORDER BY id DESC";
                    $res_social = mysqli_query($conn, $sql_social);
                    if(mysqli_num_rows($res_social) > 0) {
                        while($social = mysqli_fetch_assoc($res_social)) {
                    ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <span>
                            <i class="<?php echo $social['icon_class']; ?> me-2"></i> 
                            <?php echo htmlspecialchars($social['platform_name']); ?>
                        </span>
                        <div class="form-check form-switch">
                            <input class="form-check-input swith-button layout-switch" type="checkbox" 
                                   data-type="social" 
                                   data-id="<?php echo $social['id']; ?>" 
                                   data-column="status"
                                   <?php echo ($social['status'] == 1) ? 'checked' : ''; ?>>
                        </div>
                    </div>
                    <?php 
                        }
                    } else {
                        echo "<p class='text-muted text-center'>No social links added yet.</p>";
                    }
                    ?>
                </div>
            </div>

            <div class="alert alert-info py-2 shadow-sm">
                <i class="bi bi-info-circle me-2"></i> Changes are saved automatically.
            </div>
        </div>
    </div>
</form>
    </div>
</div>

<div class="modal fade" id="addSocialModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="socialMediaForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Social Platform</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="responseMessage"></div>
                    
                    <input type="hidden" id="ajax_csrf" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Platform Name</label>
                        <input type="text" id="platform_name" class="form-control" placeholder="e.g. WhatsApp" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon Class (FontAwesome)</label>
                        <input type="text" id="icon_class" class="form-control" placeholder="e.g. fab fa-facebook-f" required>
                        <small class="text-muted">Use <a href="https://fontawesome.com/v5/search?m=free" target="_blank">FontAwesome 5</a> classes.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Profile Link</label>
                        <input type="url" id="platform_url" class="form-control" placeholder="https://..." required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.layout-switch').on('change', function() {
        const el = $(this);
        const data = {
            action: 'toggle_switch',
            type: el.data('type'),     // category / social
            id: el.data('id'),         // ID
            column: el.data('column'), // show_in_header / show_in_footer / status
            value: el.is(':checked') ? 1 : 0,
            csrf_token: $('#csrf_token').val()
        };

        el.closest('.form-switch').css('opacity', '0.5');

        $.ajax({
            url: 'ajax-update-layout.php',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                el.closest('.form-switch').css('opacity', '1');
                if (response.status === 'success') {
                    showToast('Saved Successfully!', 'success');
                } else {
                    showToast('Error: ' + response.message, 'danger');
                    el.prop('checked', !el.is(':checked')); // ভুল হলে আগের অবস্থায় ফেরত
                }
            },
            error: function() {
                el.closest('.form-switch').css('opacity', '1');
                showToast('Server Error!', 'danger');
                el.prop('checked', !el.is(':checked'));
            }
        });
    });

     //Alart-Message
    function showToast(msg, type) {
        const alertBox = $('#ajax-alert');
        alertBox.html(`<div class="alert alert-${type} shadow-lg py-2 px-3">${msg}</div>`).fadeIn();
        setTimeout(() => alertBox.fadeOut(), 2000);
    }
});
</script>
<script>
$(document).ready(function() {
    $('#socialMediaForm').on('submit', function(e) {
        e.preventDefault(); 

        const saveBtn = $('#saveBtn');
        saveBtn.prop('disabled', true).text('Saving...');

        
        const formData = {
            platform_name: $('#platform_name').val(),
            icon_class: $('#icon_class').val(),
            platform_url: $('#platform_url').val(),
            csrf_token: $('#ajax_csrf').val()
        };

        $.ajax({
            url: 'ajax-add-social.php', 
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#responseMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                    
                   
                    setTimeout(function() {
                        location.reload(); 
                    }, 1500);
                } else {
                    $('#responseMessage').html('<div class="alert alert-danger">' + response.message + '</div>');
                    saveBtn.prop('disabled', false).text('Save Changes');
                }
            },
            error: function() {
                alert('Something went wrong. Please try again.');
                saveBtn.prop('disabled', false).text('Save Changes');
            }
        });
    });
});
</script>
<?php include "includes/footer.php"; ?>