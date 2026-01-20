</main>
<footer class="app-footer bg-white border-top py-4 mt-5">
  <div class="container">
    <div class="row align-items-center">
      <?php
      // Fetch all settings from the database
      // Assuming config file is already included in the calling page
      $sql = "SELECT * FROM settings LIMIT 1";
      $result = mysqli_query($conn, $sql);

      // Set default values to prevent errors if the query fails
      $footer_desc = "";
      $copyright = "© 2026 All Rights Reserved.";
      $fb = $yt = $email = "";

      if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $footer_desc = $row['footerdesc'];
        $copyright = $row['copyright_text'];
        $fb = $row['facebook_page'];
        $yt = $row['youtube_url'];
        $email = $row['contact_email'];
      }
      ?>

      <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
        <p class="text-muted small mb-1"><?php echo $footer_desc; ?></p>
        <div class="text-muted small">
          <strong><?php echo $copyright; ?></strong>
        </div>
      </div>

      <div class="col-md-6 text-center text-md-end">
        <div class="social-links mb-2">
          <?php if (!empty($fb)): ?>
            <a href="<?php echo $fb; ?>" class="text-primary me-3 text-decoration-none" target="_blank">
              <i class="fab fa-facebook fa-lg"></i>
            </a>
          <?php endif; ?>

          <?php if (!empty($yt)): ?>
            <a href="<?php echo $yt; ?>" class="text-danger me-3 text-decoration-none" target="_blank">
              <i class="fab fa-youtube fa-lg"></i>
            </a>
          <?php endif; ?>

          <?php if (!empty($email)): ?>
            <a href="mailto:<?php echo $email; ?>" class="text-secondary text-decoration-none">
              <i class="fas fa-envelope fa-lg"></i>
            </a>
          <?php endif; ?>
        </div>
        <small class="text-muted">Powered by <a href="#" class="text-decoration-none text-dark fw-bold">Global News
            Admin</a></small>
      </div>
    </div>
  </div>
</footer>

<?php
// Closing the database connection is a good practice after queries are finished
if (isset($conn)) {
  mysqli_close($conn);
}
?>
</div> <!-- Closing div for main wrapper if applicable -->

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