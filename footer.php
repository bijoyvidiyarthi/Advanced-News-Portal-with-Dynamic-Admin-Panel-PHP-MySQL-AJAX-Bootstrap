<div id="footer">
    <div class="container">
        <div class="row footer-links-container">
            <div class="col-md-4 footer-widget">

                <h3>About Us</h3>
                <?php
                include 'config.php';
                $sql_set = "SELECT * FROM settings LIMIT 1";
                $result_set = mysqli_query($conn, $sql_set);
                $row_set = mysqli_fetch_assoc($result_set);
                ?>
                <p><?php echo substr($row_set['footerdesc'], 0, 180); ?>...</p>
                <div class="social-links">
                    <div class="social-links">
                        <?php
                        $social_query = "SELECT * FROM social_links WHERE status = 1";
                        $social_res = mysqli_query($conn, $social_query);
                        while ($social = mysqli_fetch_assoc($social_res)) { ?>
                            <a href="<?php echo $social['platform_url']; ?>" target="_blank"
                                title="<?php echo $social['platform_name']; ?>">
                                <i class="<?php echo htmlspecialchars($social['icon_class']); ?>"></i>
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <!-- Categories -->
            <div class="col-md-4 footer-widget">
                <h3>Categories</h3>
                <ul class="footer-links">
                    <?php
                    $cat_footer_query = "SELECT * FROM category WHERE show_in_footer = 1";
                    $cat_footer_res = mysqli_query($conn, $cat_footer_query);
                    if (mysqli_num_rows($cat_footer_res) > 0) {
                        while ($cat_row = mysqli_fetch_assoc($cat_footer_res)) { ?>
                            <li>
                                <a href="category.php?cid=<?php echo $cat_row['category_id']; ?>">
                                    <i class="fas fa-chevron-right"></i> <?php echo $cat_row['category_name']; ?>
                                </a>
                            </li>
                        <?php }
                    } else {
                        echo "<li><small class='text-muted'>No categories selected.</small></li>";
                    } ?>
                </ul>
            </div>
            <!-- </ Categories  -->

            <!-- Quick Links -->
            <div class="col-md-4 footer-widget">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="index.php" target="_blank"><i class="fas fa-chevron-right"></i> Home</a></li>
                    <li><a href="contact.php" target="_blank"><i class="fas fa-chevron-right"></i> Contact Us</a></li>
                    <li><a href="about.php" target="_blank"><i class="fas fa-chevron-right"></i> About Us</a></li>
                    <li><a href="privacy.php" target="_blank"><i class="fas fa-chevron-right"></i> Privacy Policy</a>
                    </li>
                </ul>
            </div>
            <!-- </ Quick Links -->


            <!-- Quick Links -->
            <div class="col-md-4 footer-widget">
                <h3>Contact Info</h3>
                <ul class="contact-list">
                    <?php if (!empty($row_set['address'])) { ?>
                        <li><i class="fas fa-map-marker-alt"></i>
                            <?php echo $row_set['address']; ?>
                        </li>
                    <?php } ?>

                    <?php if (!empty($row_set['contact_phone'])) { ?>
                        <li><i class="fas fa-phone"></i>
                            <?php echo $row_set['contact_phone']; ?>
                        </li>
                    <?php } ?>

                    <?php if (!empty($row_set['contact_email'])) { ?>
                        <li><i class="fas fa-envelope"></i>
                            <?php echo $row_set['contact_email']; ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <!-- </ Contacts Info -->
        </div>

        <div class="row">
            <div class="col-md-12 copyright-section">
                <span class="text-secondary small"><?php echo $row_set['copyright_text']; ?></span>
            </div>
        </div>
    </div>
</div>

<?php mysqli_close($conn); ?>

<script src="script.js"></script>
</body>

</html>