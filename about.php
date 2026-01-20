<?php 
include 'header.php'; 
include 'config.php'; 
?>

<div id="main-content" class="about-page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="about-header text-center animate__animated animate__fadeIn">
                    <h2 class="section-title">About Our Portal</h2>
                    <?php 
                    // Fetching Dynamic Description from Settings
                    $sql_set = "SELECT footerdesc, websitename FROM settings LIMIT 1";
                    $res_set = mysqli_query($conn, $sql_set);
                    $row_set = mysqli_fetch_assoc($res_set);
                    ?>
                    <p class="about-lead">
                        Welcome to <strong><?php echo $row_set['websitename']; ?></strong>. 
                        <?php echo $row_set['footerdesc']; ?>
                    </p>
                </div>

                <hr class="section-divider">

                <div class="team-section">
                    <h3 class="section-subtitle text-center">Meet Our Editorial Team</h3>
                    <div class="row">
                        <?php
                        // Fetching Users from Database
                        $sql_auth = "SELECT * FROM user ORDER BY role DESC, user_id ASC";
                        $res_auth = mysqli_query($conn, $sql_auth);

                        if(mysqli_num_rows($res_auth) > 0){
                            while($row_auth = mysqli_fetch_assoc($res_auth)){
                        ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="author-card">
                                <div class="author-img-wrapper">
                                    <img src="images/default-avatar.png" alt="Author Profile">
                                </div>
                                <div class="author-content">
                                    <h4><?php echo $row_auth['first_name'] . " " . $row_auth['last_name']; ?></h4>
                                    <p class="author-role">
                                        <?php echo ($row_auth['role'] == 1) ? "Chief Editor" : "Staff Reporter"; ?>
                                    </p>
                                    <div class="author-meta">
                                        <span><i class="fas fa-user"></i> @<?php echo $row_auth['username']; ?></span>
                                    </div>
                                    <a href="author.php?aid=<?php echo $row_auth['user_id']; ?>" class="view-posts-btn">
                                        View All Posts
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php 
                            }
                        } 
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* About Page General */
.about-page {
    padding: 80px 0;
    background-color: #fcfcfc;
}

.section-title {
    font-size: 38px;
    font-weight: 800;
    color: #222;
    margin-bottom: 20px;
}

.about-lead {
    font-size: 18px;
    line-height: 1.8;
    color: #555;
    max-width: 900px;
    margin: 0 auto 40px;
}

.section-divider {
    width: 60px;
    border-top: 3px solid #1e90ff;
    margin: 40px auto;
}

.section-subtitle {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 50px;
    color: #333;
}

/* Author Card Design */
.author-card {
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    margin-bottom: 30px;
    transition: all 0.3s ease;
    border: 1px solid #f0f0f0;
    text-align: center;
    padding: 30px 20px;
}

.author-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.12);
}

.author-img-wrapper img {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    border: 4px solid #f0f7ff;
    object-fit: cover;
    margin-bottom: 20px;
}

.author-content h4 {
    font-size: 20px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
}

.author-role {
    color: #1e90ff;
    font-weight: 600;
    font-size: 14px;
    text-transform: uppercase;
    margin-bottom: 15px;
    letter-spacing: 0.5px;
}

.author-meta {
    font-size: 13px;
    color: #888;
    margin-bottom: 20px;
}

.view-posts-btn {
    display: inline-block;
    padding: 8px 20px;
    background-color: #f0f0f0;
    color: #333;
    border-radius: 25px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: 0.3s;
}

.view-posts-btn:hover {
    background-color: #1e90ff;
    color: #fff;
    text-decoration: none;
}
</style>

<?php include 'footer.php'; ?>