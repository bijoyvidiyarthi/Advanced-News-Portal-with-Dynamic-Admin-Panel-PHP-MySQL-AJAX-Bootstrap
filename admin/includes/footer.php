</main>
<footer class="app-footer bg-dark text-light py-5 mt-5">
  <div class="container">
    <div class="row g-4">
      <?php
      // Fetch website settings from the database
      $sql = "SELECT * FROM settings LIMIT 1";
      $result = mysqli_query($conn, $sql);

      // Initialize default values for fallback
      $site_name = "Global News";
      $footer_desc = "Your trusted source for the latest news and breaking updates.";
      $copyright = "© 2026 All Rights Reserved.";
      $fb = $yt = $email = $phone = $logo = "";

      if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $site_name = $row['websitename'];
        $footer_desc = $row['footerdesc'];
        $copyright = $row['copyright_text'];
        $fb = $row['facebook_page'];
        $yt = $row['youtube_url'];
        $email = $row['contact_email'];
        $phone = $row['contact_phone'];
        $logo = $row['logo'];
      }
      ?>

      <!-- Section: About Website -->
      <div class="col-lg-4 col-md-6">
        <h5 class="fw-bold mb-3 text-white">About <?php echo $site_name; ?></h5>
        <p class="text-secondary small line-height-lg">
          <?php echo $footer_desc; ?>
        </p>
        <!-- Social Media Links -->
        <div class="mt-3">
          <?php if (!empty($fb)): ?>
            <a href="<?php echo $fb; ?>" class="btn btn-sm btn-outline-light border-0 me-2" target="_blank">
              <i class="fab fa-facebook-f"></i>
            </a>
          <?php endif; ?>
          <?php if (!empty($yt)): ?>
            <a href="<?php echo $yt; ?>" class="btn btn-sm btn-outline-light border-0 me-2" target="_blank">
              <i class="fab fa-youtube"></i>
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Section: Quick Navigation -->
      <div class="col-lg-4 col-md-6 ps-lg-5">
        <h5 class="fw-bold mb-3 text-white">Quick Links</h5>
        <ul class="list-unstyled small">
          <li class="mb-2"><a href="index.php" class="text-secondary text-decoration-none hover-white">Home</a></li>
          <li class="mb-2"><a href="#" class="text-secondary text-decoration-none hover-white">All Categories</a></li>
          <li class="mb-2"><a href="settings.php" class="text-secondary text-decoration-none hover-white">Website
              Settings</a></li>
          <li class="mb-2"><a href="change-password.php"
              class="text-secondary text-decoration-none hover-white">Security & Privacy</a></li>
        </ul>
      </div>

      <!-- Section: Contact Details -->
      <div class="col-lg-4 col-md-12">
        <h5 class="fw-bold mb-3 text-white">Contact Info</h5>
        <div class="text-secondary small">
          <?php if (!empty($email)): ?>
            <p class="mb-2"><i class="fas fa-envelope me-2 text-primary"></i> <?php echo $email; ?></p>
          <?php endif; ?>
          <?php if (!empty($phone)): ?>
            <p class="mb-2"><i class="fas fa-phone-alt me-2 text-primary"></i> <?php echo $phone; ?></p>
          <?php endif; ?>
          <p class="mb-2"><i class="fas fa-map-marker-alt me-2 text-primary"></i> Dhaka, Bangladesh</p>
        </div>
      </div>
    </div>

    <hr class="mt-4 border-secondary opacity-25">

    <!-- Section: Copyright and Credits -->
    <div class="row align-items-center mt-3">
      <div class="col-md-6 text-center text-md-start">
        <span class="text-secondary small"><?php echo $copyright; ?></span>
      </div>
      <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
        <small class="text-secondary">Powered by <span class="text-white fw-bold">Aryaan Dhar</span></small>
      </div>
    </div>
  </div>
</footer>
</div>

<!-- Custom Footer Styles -->

<?php
// Close database connection if it exists
if (isset($conn)) {
  mysqli_close($conn);
}
?>

</div>
<!-- Closing div for main wrapper if applicable -->

<!-- ==========================================
     JAVASCRIPT LIBRARIES
     ========================================== -->

<!-- OverlayScrollbars: Custom scrollbar styling library -->
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"
  integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ=" crossorigin="anonymous"></script>

<!-- Popper.js: Required positioning engine for Bootstrap tooltips and popovers -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
  xintegrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>

<!-- Bootstrap 5: Core framework for layout and interactive components -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
  xintegrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>

<!-- ApexCharts: Interactive chart library for data visualization -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
  integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8=" crossorigin="anonymous"></script>

<!-- SortableJS: Library for reorderable drag-and-drop lists -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"
  integrity="sha256-ipiJrswvAR4VAx/th+6zWsdeYmVae0iJuiR+6OqHJHQ=" crossorigin="anonymous"></script>

<!-- CKEditor 5: Rich text editor for admin post areas -->
<script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>

<!-- ==========================================
     CUSTOM THEME SCRIPTS
     ========================================== -->
<script src="../script.js"></script>
<script src="assets/js/adminlte.js"></script>
<script src="assets/js/dashboard.js"></script>

</body>

</html>