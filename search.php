<?php
session_start();
include 'header.php';
include 'config.php';


//check connection
if (!$conn) {
    $_SESSION['error'] = "⚠️ **Database Connection Error:** We are currently unable to connect to the database. Please try again later.";
    header("Location: error-page.php");
    exit();

}

//-- get search input from url ---
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $clean_search = mysqli_real_escape_string($conn, $_GET['search']);
} else {
    //if no id found redirect to home page
    $_SESSION['error'] = "⚠️ **Search Error:** No search term provided.";
    header("Location: index.php");
    exit();
}

//--- Pagination Setup---
$limit = 5;
// Set the current page number, ensuring it's an integer and at least 1
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

if ($page < 1) {
    //if anyone try to access page less than 1 then redirect to page 1 and show error
    $_SESSION['error'] = "⚠️ **Page Not Found:** You tried to access page " . ($page) . ", but it does not exist.";
    header("Location: $_SERVER[PHP_SELF]?search=" . $clean_search . "&page=" . 1);
    exit(); // CRITICAL: Stop script so redirect happens immediately
}

//Calculate Offset
$offset = ($page - 1) * $limit;



//--- Calculate total records --

//count SQL
// Count query matches the filter so pagination links are accurate
//left join to include posts according to search username also if needed in future
$countSql = "SELECT COUNT(post_id) AS total_posts FROM post p
            LEFT JOIN category c ON p.category = c.category_id
            LEFT JOIN user u ON p.author = u.user_id
            WHERE p.title LIKE '%{$clean_search}%' 
            OR p.description LIKE '%{$clean_search}%' 
            OR u.username LIKE '%{$clean_search}%' 
            OR c.category_name LIKE '%{$clean_search}%'";

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
        header("Location: search.php?search=" . $clean_search);
        exit(); // CRITICAL: Stop script so redirect happens immediately
    }
}



//--- Validate Search Term Existence ---

if ($total_posts > 0) {
    $check_sql = "SELECT p.post_id, p.title, p.description, p.post_date, p.post_img, 
            u.username, p.author, c.category_name, p.category
            FROM post p
            LEFT JOIN category c ON p.category = c.category_id
            LEFT JOIN user u ON p.author = u.user_id
            WHERE p.title LIKE '%{$clean_search}%' 
            OR p.description LIKE '%{$clean_search}%' 
            OR u.username LIKE '%{$clean_search}%' 
            OR c.category_name LIKE '%{$clean_search}%'
            ORDER BY p.post_id DESC
            LIMIT {$limit} OFFSET {$offset}";
    $search_result = mysqli_query($conn, $check_sql);
}

?>
<div id="main-content">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <!-- post-container -->
                <div class="post-container">
                    <h2 class="page-heading">Search : <?php echo htmlspecialchars($clean_search); ?></h2>
                    <?php
                    //show error message if any
                    if (isset($_SESSION['error'])) {
                        echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                        unset($_SESSION['error']);
                    }

                    if ($total_posts > 0):
                        // Display posts only if there are matching records
                        if ($search_result && mysqli_num_rows($search_result) > 0):
                            //Show how many results found
                            echo "<h4>" . $total_posts . " result(s) found for '<strong>" . htmlspecialchars($clean_search) . "</strong>'</h4><hr>";

                            while ($row = mysqli_fetch_assoc($search_result)):
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
                                                    src="admin/upload/<?php echo $post_img; ?>"
                                                    alt="<?php echo htmlspecialchars($title); ?>" /></a>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="inner-content clearfix">
                                                <h3><a
                                                        href='single.php?id=<?php echo $id; ?>'><?php
                                                           //highlight search term in title if found
                                                           $escaped_title = htmlspecialchars($title);
                                                           $escaped_search = htmlspecialchars($clean_search);
                                                           // Highlight while preserving original case
                                                           echo preg_replace("/(" . preg_quote($escaped_search, '/') . ")/i", "<span class='search_highlight'>$1</span>", $escaped_title); ?></a>
                                                </h3>
                                                <div class="post-information">
                                                    <span>
                                                        <i class="fa fa-tags" aria-hidden="true"></i>
                                                        <a
                                                            href='category.php?c_id=<?php echo $category_id; ?>'><?php
                                                               //highlight search term in category name if found
                                                               // Category example                                         
                                                               $escaped_cat = htmlspecialchars($category);
                                                               echo preg_replace("/(" . preg_quote(htmlspecialchars($clean_search), '/') . ")/i", "<span class='search_highlight'>$1</span>", $escaped_cat); ?></a>
                                                    </span>
                                                    <span>
                                                        <i class="fa fa-user" aria-hidden="true"></i>
                                                        <a href='author.php?id=<?php echo $user_id; ?>'><?php
                                                           //highlight search term in authorname if found
                                                           // Author example
                                                           $escaped_auth = htmlspecialchars($author);
                                                           echo preg_replace("/(" . preg_quote(htmlspecialchars($clean_search), '/') . ")/i", "<span class='search_highlight'>$1</span>", $escaped_auth);
                                                           ?></a>
                                                    </span>
                                                    <span>
                                                        <i class="fa fa-calendar" aria-hidden="true"></i>
                                                        <?php echo htmlspecialchars($date); ?>
                                                    </span>
                                                </div>
                                                <p class="description">
                                                    <?php
                                                    // 1. Get raw data and TRIM the search term to remove accidental trailing spaces
                                                    $raw_description = $row['description'];


                                                    // 2. Truncate to 130 characters
                                                    $short_desc = substr($raw_description, 0, 130);

                                                    // 3. Escape for security
                                                    $escaped_desc = htmlspecialchars($short_desc);
                                                    $escaped_search = htmlspecialchars($clean_search);

                                                    // 4. Highlight Logic
                                                    if (!empty($escaped_search)) {
                                                        // We define the tag in a single, tight string with NO spaces inside the class name
                                                        $start_tag = "<span class='search_highlight'>";
                                                        $end_tag = "</span>";

                                                        // Using $0 to ensure the exact casing from the database is preserved
                                                        $highlighted_text = preg_replace(
                                                            "/" . preg_quote($escaped_search, '/') . "/i",
                                                            $start_tag . "$0" . $end_tag,
                                                            $escaped_desc
                                                        );
                                                    } else {
                                                        $highlighted_text = $escaped_desc;
                                                    }

                                                    // 5. Output
                                                    echo nl2br($highlighted_text) . '...'; ?>
                                                </p>
                                                <a class='read-more pull-right' href='single.php?id=<?php echo $id; ?>'>read
                                                    more</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            endwhile;
                        endif;
                    else:
                        echo "<h3>No posts found matching your search criteria.</h3>";
                    endif;
                    mysqli_close($conn);
                    ?>

                    <ul class='pagination'>
                        <?php
                        // pagination links code
                        if ($total_pages > 1):
                            //Previous Page
                            if ($page > 1):
                                echo '<li class="prev"><a href=" ' . $_SERVER['PHP_SELF'] . '?search=' . $clean_search . '&page=' . ($page - 1) . ' " >Prev</a></li>';
                            endif;

                            //Show Page numbers
                            $range = 2;
                            //eg. if current page is 3, start will 3-2 = 1 (pagination: ..12 3 45)
                            $start = max(1, $page - $range);
                            $end = min($total_pages, $page + $range);

                            // Start Ellipsis
                            if ($start > 1):
                                echo '<li><a href="' . $_SERVER['PHP_SELF'] . '?search=' . $clean_search . '&page=1">1</a></li>';
                                if ($start > 2)
                                    echo '<li><span>...</span></li>';
                            endif;

                            // Truncation logic (first page and ellipsis)
                            for ($i = $start; $i <= $end; $i++):
                                $active = ($i == $page) ? "active" : "";
                                echo '<li class=" ' . $active . ' "><a href=" ' . $_SERVER['PHP_SELF'] . '?search=' . $clean_search . '&page=' . $i . ' " >' . $i . '</a></li>';
                            endfor;


                            // End Ellipsis
                            if ($end < $total_pages):
                                if ($end < $total_pages - 1)
                                    echo '<li><span>...</span></li>';
                                echo '<li><a href="' . $_SERVER['PHP_SELF'] . '?search=' . $clean_search . '&page=' . $total_pages . '">' . $total_pages . '</a></li>';
                            endif;

                            //next page
                            if (1 < $total_pages && $page < $total_pages):
                                echo '<li class="next"><a href=" ' . $_SERVER['PHP_SELF'] . '?search=' . $clean_search . '&page=' . ($page + 1) . ' " >Next</a></li>';
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