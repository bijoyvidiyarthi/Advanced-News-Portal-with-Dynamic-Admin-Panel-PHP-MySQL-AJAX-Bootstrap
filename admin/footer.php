<!-- Footer -->
<div id="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php
                include "config.php";

                $sql = "SELECT footerdesc from settings";
                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $desc = $row['footerdesc'];
                }
                ?>
                <span>
                    <?php echo $desc; ?>
                </span>
            </div>
        </div>
    </div>
</div>
<!-- /Footer -->
</body>

</html>