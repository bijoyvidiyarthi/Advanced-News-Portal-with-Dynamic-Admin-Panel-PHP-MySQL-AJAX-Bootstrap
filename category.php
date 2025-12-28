<?php
session_start();
include 'header.php';
include 'config.php';
//-- get category id from url ---

if (isset($_GET['c_id'])) {
    $category_id = mysqli_real_escape_string($conn, $_GET['c_id']);
} else {
    //if no id found redirect to home page
    $_SESSION['error'] = "⚠️ **Category Not Found:** No category ID provided.";
    header("Location: index.php");
    exit();
}

//check if category id is valid number or not or empty
if (empty($category_id) || !is_numeric($category_id)) {
    $_SESSION['error'] = "⚠️ **Invalid Category ID:** The category ID provided is not valid.";
    //if there is any error redirect to current page
    header("Location: index.php");
    exit();
} else {
    $category_id = (int) $category_id; //cast to integer for safety

    //check valid category id or does it exist in database
    $cat_check_sql = "SELECT * from category where category_id = {$category_id}";
    $cat_check_result = mysqli_query($conn, $cat_check_sql);

    if (!$cat_check_result || mysqli_num_rows($cat_check_result) == 0) {
        $_SESSION['error'] = "⚠️ **Category Not Found:** The requested category does not exist.";
        //if there is any error redirect to current page
        header("Location: index.php");
        exit();
    } else {
        $cat_row = mysqli_fetch_assoc($cat_check_result);
        $category = $cat_row['category_name'];
    }
}


//--- Pagination Setup---
$limit = 5;

// Set the current page number, ensuring it's an integer and at least 1
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    //if anyone try to access page less than 1 then redirect to page 1 and show error
    $_SESSION['error'] = "⚠️ **Page Not Found:** You tried to access page " . ($page) . ", but it does not exist.";
    header("Location: $_SERVER[PHP_SELF]?c_id=" . $category_id . "&page=" . 1);
    exit(); // CRITICAL: Stop script so redirect happens immediately
}

//Calculate Offset
$offset = ($page - 1) * $limit;


//--- Calculate total records --

//check connection
if (!$conn) {
    $_SESSION['error'] = "⚠️ **Database Connection Error:** We are currently unable to connect to the database. Please try again later.";
    header("Location: error-page.php");
    exit();
} else {
    //count SQL
    // Count query matches the filter so pagination links are accurate
    $countSql = "SELECT COUNT(post_id) AS total_posts FROM post p
                 WHERE p.category = {$category_id}";
    $countResult = mysqli_query($conn, $countSql);


    //By default total records and pages will be 0
    $total_records = 0;
    $total_pages = 0;

    if ($countResult && mysqli_num_rows($countResult) > 0) {
        // Get total number of users
        $countrow = mysqli_fetch_assoc($countResult); //here row is an associative array that contains total_users as key
        $total_posts = $countrow['total_posts'];
        $total_pages = ceil($total_posts / $limit); //eg: 45/10 =4.5 =>5 page, 0.4= 1 page

        // Ensure the current page doesn't exceed the total pages 
        // (e.g., if a record was deleted)
        if ($page > $total_pages && $total_pages > 0) {
            //if anyone try to access page more than total pages then redirect to last page and show error
            $_SESSION['error'] = "⚠️ **Page Not Found:** You tried to access page " . ($page) . ", but it does not exist.";
            header("Location: $_SERVER[PHP_SELF]?c_id=" . $category_id . "&page=" . $total_pages);
            exit(); // CRITICAL: Stop script so redirect happens immediately
        }
    }
}
?>
<div id="main-content">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <?php
                //show error message if any
                if (isset($_SESSION['error'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                    unset($_SESSION['error']);
                }
                ?>
                <!-- post-container -->
                <div class="post-container">
                    <?php
                    //first check if connection is established or not
                    if ($conn):
                        // Fetch users from database and display here 
                        $sql = "SELECT p.post_id, p.title, p.description, p.post_date, p.post_img, 
                            u.username, p.author, c.category_name, p.category
                            FROM post p
                            LEFT JOIN category c ON p.category = c.category_id
                            LEFT JOIN user u ON p.author = u.user_id
                            WHERE p.category = {$category_id}
                            ORDER BY p.post_id DESC
                            LIMIT {$limit} OFFSET {$offset}";

                        $result = mysqli_query($conn, $sql);

                        if (!$result):
                            $_SESSION['error'] = "Error fetching posts: " . mysqli_error($conn);
                            header("Location: $_SERVER[PHP_SELF]");
                            mysqli_close($conn);
                        else:
                            ?>

                            <h2 class="page-heading"><?php echo $category; ?></h2>

                            <?php

                            if (mysqli_num_rows($result) > 0):
                                $id_no = $offset + 1;

                                while ($row = mysqli_fetch_assoc($result)):
                                    $id = $row['post_id'];
                                    $title = $row['title'];
                                    $description = $row['description'];
                                    $author = $row['username'];
                                    $user_id = $row['author'];
                                    $category_id = $row['category'];
                                    $category = $row['category_name'];
                                    $date = $row['post_date'];
                                    $post_img = $row['post_img'];
                                    ?>
                                    <div class="post-content">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <a class="post-img" href="single.php?id=<?php echo $id; ?>"><img
                                                        src="admin/upload/<?php echo $post_img; ?>" alt="<?php echo $title; ?>" /></a>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="inner-content clearfix">
                                                    <h3><a href='single.php?id=<?php echo $id; ?>'><?php echo $title; ?></a>
                                                    </h3>
                                                    <div class="post-information">
                                                        <span>
                                                            <i class="fa fa-tags" aria-hidden="true"></i>
                                                            <a
                                                                href='category.php?c_id=<?php echo $category_id; ?>'><?php echo $category; ?></a>
                                                        </span>
                                                        <span>
                                                            <i class="fa fa-user" aria-hidden="true"></i>
                                                            <a href='author.php?aid=<?php echo $user_id; ?>'><?php echo $author; ?></a>
                                                        </span>
                                                        <span>
                                                            <i class="fa fa-calendar" aria-hidden="true"></i>
                                                            <?php echo $date; ?>
                                                        </span>
                                                    </div>
                                                    <p class="description">
                                                        <?php echo nl2br(substr(htmlspecialchars($description), 0, 130)) . '...'; ?>
                                                    </p>
                                                    <a class='read-more pull-right' href='single.php?id=<?php echo $id; ?>'>read
                                                        more</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                endwhile;
                            else:
                                echo "<h2>No Record Found.</h2>";
                            endif;
                            //close connection
                            mysqli_close($conn);
                        endif;
                    endif;
                    ?>
                    <!-- Pagination -->

                    <ul class='pagination'>
                        <?php
                        // pagination links code
                        if ($total_pages > 1):
                            //Previous Page
                            if ($page > 1):
                                echo '<li class="prev"><a href=" ' . $_SERVER['PHP_SELF'] . '?c_id=' . $category_id . '&page=' . ($page - 1) . ' " >Prev</a></li>';
                            endif;

                            //Show Page numbers
                            $range = 2;
                            //eg. if current page is 3, start will 3-2 = 1 (pagination: ..12 3 45)
                            $start = max(1, $page - $range);
                            $end = min($total_pages, $page + $range);

                            // Start Ellipsis
                            if ($start > 1):
                                echo '<li><a href=" ' . $_SERVER['PHP_SELF'] . '?c_id=' . $category_id . '&page=1">1</a></li>';
                                if ($start > 2)
                                    echo '<li><span>...</span></li>';
                            endif;

                            // Truncation logic (first page and ellipsis)
                            for ($i = $start; $i <= $end; $i++):
                                $active = ($i == $page) ? "active" : "";
                                echo '<li class=" ' . $active . ' "><a href=" ' . $_SERVER['PHP_SELF'] . '?c_id=' . $category_id . '&page=' . $i . ' " >' . $i . '</a></li>';
                            endfor;


                            // End Ellipsis
                            if ($end < $total_pages):
                                if ($end < $total_pages - 1)
                                    echo '<li><span>...</span></li>';
                                echo '<li><a href=" ' . $_SERVER['PHP_SELF'] . '?c_id=' . $category_id . '&page=' . $total_pages . '">' . $total_pages . '</a></li>';
                            endif;

                            //next page
                            if (1 < $total_pages && $page < $total_pages):
                                echo '<li class="next"><a href=" ' . $_SERVER['PHP_SELF'] . '?c_id=' . $category_id . '&page=' . ($page + 1) . ' " >Next</a></li>';
                            endif;
                        else:
                            echo "<p> All Records Are Shown</p>";
                        endif;
                        ?>
                    </ul>
                </div><!-- /post-container -->
            </div>
            <?php include 'sidebar.php'; ?>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>