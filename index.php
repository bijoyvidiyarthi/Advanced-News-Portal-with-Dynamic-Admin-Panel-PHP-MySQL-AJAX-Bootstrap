<?php
session_start();
include 'config.php';
include 'header.php';

//--- Pagination Setup---
$limit = 5;

// Set the current page number
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $_SESSION['error'] = "⚠️ **Page Not Found:** You tried to access page " . ($page) . ", but it does not exist.";
    header("Location: $_SERVER[PHP_SELF]?page=1");
    exit();
}

// Calculate Offset
$offset = ($page - 1) * $limit;

// Check DB connection
if (!$conn) {
    $_SESSION['error'] = "⚠️ **Database Connection Error:** We are currently unable to connect to the database. Please try again later.";
    header("Location: error-page.php");
    exit();
}

// Count total approved records
$countSql = "SELECT COUNT(post_id) AS total_posts FROM post WHERE status = 'approved'";
$countResult = mysqli_query($conn, $countSql);

$total_posts = 0;
$total_pages = 0;

if ($countResult && mysqli_num_rows($countResult) > 0) {
    $countrow = mysqli_fetch_assoc($countResult);
    $total_posts = $countrow['total_posts'];
    $total_pages = ceil($total_posts / $limit);

    if ($page > $total_pages && $total_pages > 0) {
        $_SESSION['error'] = "⚠️ **Page Not Found:** You tried to access page " . ($page) . ", but it does not exist.";
        header("Location: index.php?page=" . $total_pages);
        exit();
    }
}

?>

<!--==== Breaking News Panel ===== -->
<div id="breaking-news" class="bg-danger text-white py-2">
    <div class="container">
        <div class="row breaking_news_container">
            <div class="col-md-2"><strong>Breaking News:</strong></div>
            <div class="col-md-10 breaking_lines">
                <marquee behavior="scroll" direction="left" onmouseover="this.stop();" onmouseout="this.start();">
                    <?php
                    $breaking_sql = "SELECT post_id, title FROM post WHERE is_breaking = 1 AND status = 'approved' ORDER BY published_at DESC LIMIT 5";
                    $breaking_result = mysqli_query($conn, $breaking_sql);

                    while ($b_row = mysqli_fetch_assoc($breaking_result)) {
                        echo "<a href='single.php?id={$b_row['post_id']}' class='text-white breaking_text me-4' style='text-decoration:none;'>● {$b_row['title']}</a>";
                    }
                    ?>
                </marquee>
            </div>
        </div>
    </div>
</div>

<div id="main-content" class="mt-4">
    <div class="container">

        <!-- Featured Section -->
        <div class="featured-section mb-5 pb-4 border-bottom">
            <div class="row mb-4">
                <div class="col-md-12">
                    <h2 class="page-heading home-page-heading">Featured Stories</h2>
                </div>
            </div>

            <div class="row g-4">
                <?php
                $featured_sql = "SELECT p.post_id, p.title, p.post_img, c.category_name 
                                  FROM post p 
                                  LEFT JOIN category c ON p.category = c.category_id 
                                  WHERE p.is_featured = 1 AND p.status = 'approved'
                                  ORDER BY p.published_at DESC LIMIT 6";

                $featured_res = mysqli_query($conn, $featured_sql);
                while ($f_row = mysqli_fetch_assoc($featured_res)) {
                    ?>
                    <div class="col-md-4">
                        <div class="featured-post-box shadow-sm border rounded overflow-hidden mb-3">
                            <a href="single.php?id=<?php echo $f_row['post_id']; ?>" class="featured-link">
                                <div class="featured-img-wrapper">
                                    <img src="admin/upload/<?php echo $f_row['post_img']; ?>" class="featured-img"
                                        alt="<?php echo htmlspecialchars($f_row['title']); ?>">
                                    <div class="overlay">
                                        <span class="overlay-text">View Post</span>
                                    </div>
                                </div>
                                <div class="p-3 bg-white featured-content">
                                    <span class="label label-danger">
                                        <?php echo $f_row['category_name']; ?>
                                    </span>
                                    <h4 class="mt-2 featured-title">
                                        <?php echo $f_row['title']; ?>
                                    </h4>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <!-- /Featured Section -->

        <div class="row">
            <div class="col-md-12">
                <h2 class="page-heading home-page-heading">Latest News</h2>
            </div>
            <div class="col-md-8">
                <?php
                if (isset($_SESSION['error'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                    unset($_SESSION['error']);
                }
                ?>

                <div class="post-container">
                    <?php
                    if ($conn):
                        $sql = "SELECT p.post_id, p.title, p.description, p.published_at, p.post_img, 
                                    u.username, p.author, c.category_name, p.category
                                FROM post p
                                LEFT JOIN category c ON p.category = c.category_id
                                LEFT JOIN user u ON p.author = u.user_id
                                WHERE p.status = 'approved'
                                ORDER BY p.published_at DESC
                                LIMIT {$limit} OFFSET {$offset}";

                        $result = mysqli_query($conn, $sql);

                        if (!$result):
                            echo '<div class="alert alert-danger">Error fetching posts.</div>';
                        else:
                            if (mysqli_num_rows($result) > 0):
                                while ($row = mysqli_fetch_assoc($result)):
                                    ?>
                                    <div class="latest-news-box">
                                        <div class="row g-3 align-items-center">
                                            <div class="col-md-4">
                                                <a class="latest-img" href="single.php?id=<?php echo $row['post_id']; ?>">
                                                    <img src="admin/upload/<?php echo $row['post_img']; ?>"
                                                        alt="<?php echo htmlspecialchars($row['title']); ?>">
                                                </a>
                                            </div>

                                            <div class="col-md-8">
                                                <div class="latest-content">
                                                    <h3 class="latest-title">
                                                        <a href="single.php?id=<?php echo $row['post_id']; ?>">
                                                            <?php echo htmlspecialchars($row['title']); ?>
                                                        </a>
                                                    </h3>

                                                    <div class="latest-meta">
                                                        <span>
                                                            <i class="fa fa-tags"></i>
                                                            <a href="category.php?c_id=<?php echo $row['category']; ?>">
                                                                <?php echo htmlspecialchars($row['category_name']); ?>
                                                            </a>
                                                        </span>
                                                        <span>
                                                            <i class="fa fa-user"></i>
                                                            <a href="author.php?aid=<?php echo $row['author']; ?>">
                                                                <?php echo htmlspecialchars($row['username']); ?>
                                                            </a>
                                                        </span>
                                                        <span>
                                                            <i class="fa fa-calendar"></i>
                                                            <?php echo date("d M, Y", strtotime($row['published_at'])); ?>
                                                        </span>
                                                    </div>

                                                    <div class="latest-desc posts_container_desc">
                                                        <?php echo substr(strip_tags($row['description']), 0, 160) . '...'; ?>
                                                    </div>

                                                    <a class="latest-read-more" href="single.php?id=<?php echo $row['post_id']; ?>">
                                                        Read Full Story →
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                endwhile;
                            else:
                                echo "<p class='alert alert-warning'>No records found.</p>";
                            endif;
                        endif;
                    endif;
                    ?>

                    <!-- Pagination -->
                    <nav aria-label="Page navigation" class="mt-5">
                        <ul class='pagination justify-content-center'>
                            <?php
                            if ($total_pages > 1):
                                if ($page > 1):
                                    echo '<li class="page-item"><a class="page-link text-dark" href="' . $_SERVER['PHP_SELF'] . '?page=' . ($page - 1) . '">Previous</a></li>';
                                endif;

                                $range = 2;
                                $start = max(1, $page - $range);
                                $end = min($total_pages, $page + $range);

                                for ($i = $start; $i <= $end; $i++):
                                    $active = ($i == $page) ? "active bg-danger border-danger" : "";
                                    $text_color = ($i == $page) ? "text-white" : "text-dark";
                                    echo '<li class="page-item ' . $active . '"><a class="page-link ' . $text_color . '" href="' . $_SERVER['PHP_SELF'] . '?page=' . $i . '">' . $i . '</a></li>';
                                endfor;

                                if ($page < $total_pages):
                                    echo '<li class="page-item"><a class="page-link text-dark" href="' . $_SERVER['PHP_SELF'] . '?page=' . ($page + 1) . '">Next</a></li>';
                                endif;
                            endif;
                            ?>
                        </ul>
                    </nav>
                </div>

            </div>

            <style>
               
            </style>

            <?php include 'sidebar.php'; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>