<?php
//first check if connection is established or not
if (!$conn) {
    echo "<h3>Connection Error</h3>";
}
function fetchPosts($conn, $orderby, $limit = 5)
{
    $data = [];

    // Fetch posts from database
    $sql = "SELECT p.post_id, p.title, p.published_at, p.post_img, 
                c.category_name, p.category
                FROM post p
                LEFT JOIN category c ON p.category = c.category_id
                WHERE p.status = 'approved'
                ORDER BY $orderby
                LIMIT $limit";

    $result = mysqli_query($conn, $sql);

    if (!$result || mysqli_num_rows($result) == 0) {
        return [
            'error' => 'No posts found.'
        ];
    }

    while ($row = mysqli_fetch_assoc($result)) {

        $data[] = [
            'post_id' => $row['post_id'],
            'title' => $row['title'],
            'category_id' => $row['category'],
            'category' => $row['category_name'],
            'date' => $row['published_at'],
            'image' => $row['post_img']
        ];
    }

    return [
        'data' => $data
    ];
}

$recentPosts = fetchPosts($conn, "p.published_at DESC");
$popularPosts = fetchPosts($conn, "p.viewCount DESC");

// sidebar containers

$sidebarContainers = [
    [
        'title' => 'Recent Posts',
        'result' => $recentPosts
    ],
    [
        'title' => 'Popular News',
        'result' => $popularPosts
    ]
];
?>
<div id="sidebar" class="col-md-4">
    <!-- search box -->
    <div class="search-box-container">
        <h4>Search</h4>
        <form class="search-post" action="search.php" method="GET">
            <div class="input-group">
                <?php
                if (isset($_GET['search'])) {
                    $search_value = htmlspecialchars($_GET['search']);
                } else {
                    $search_value = "";
                }

                if (!empty($search_value)) {
                    echo ' <input type="text" name="search" class="form-control" placeholder="Search ....." value="' . $search_value . '">';
                } else {
                    echo '<input type="text" name="search" class="form-control" placeholder="Search .....">';
                }
                ?>
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-danger">Search</button>
                </span>
            </div>
        </form>
    </div>
    <!-- /search box -->

    <!-- recent post/popular post box container-->
    <?php foreach ($sidebarContainers as $box): ?>

        <div class="recent-post-container mt-4">
            <h4><?php echo htmlspecialchars($box['title']); ?></h4>
            <?php
            // Fetch posts from database according to recent date 
            if (isset($box['result']['error'])) {
                echo "<div class='alert alert-warning'>{$box['result']['error']}</div>";
            } else {
                //show only 5 recent posts
                $count = 0;
                foreach ($box['result']['data'] as $post):
                    ?>
                    <div class="recent-post">
                        <a class="post-img" href="<?php echo "single.php?id=" . htmlspecialchars($post['post_id']); ?>">
                            <img src="admin/upload/<?php echo htmlspecialchars($post['image']); ?>"
                                alt="<?php echo htmlspecialchars($post['title']); ?>" />
                        </a>
                        <div class="post-content">
                            <h5>
                                <a href="single.php?id=<?php echo htmlspecialchars($post['post_id']); ?>">
                                    <?php
                                    // truncate long titles
                                    if (strlen($post['post_id']) > 30) {
                                        $p_title = substr($post['title'], 0, 27) . '...';
                                    } else {
                                        $p_title = $post['title'];
                                    }
                                    echo htmlspecialchars($p_title);
                                    ?>
                                </a>
                            </h5>
                            <span>
                                <i class="fa fa-tags" aria-hidden="true"></i>
                                <a href='category.php?c_id=<?php echo htmlspecialchars($post['category_id']); ?>'>
                                    <?php
                                    // truncate long category names, it should only contains 1 word
                                    $category = explode(' ', $post['category']);
                                    echo htmlspecialchars($category[0]);
                                    ?>
                                </a>
                            </span>
                            <span>
                                <i class="fa fa-calendar" aria-hidden="true"></i>
                                <?php echo htmlspecialchars(date("d M, Y", strtotime($post['date']))); ?>
                            </span>
                            <a class="read-more" href="single.php?id=<?php echo htmlspecialchars($post['post_id']); ?>">read
                                more</a>
                        </div>
                    </div>
                    <?php
                    $count++;
                    if ($count >= 5) {
                        break;
                    }
                endforeach;
            }
            ?>
        </div>
        <!-- /recent posts box -->
    <?php endforeach; ?>

    <!-- /recent posts box && popular posts box -->

    <div class="sidebar_container mt-4">
        <h4>Categories</h4>
        <ul class="d-flex flex-wrap gap-2">
            <?php
            $sql_cat = "SELECT * FROM category 
                            WHERE post > 0 
                            ORDER BY category_name ASC";

            $res_cat = mysqli_query($conn, $sql_cat);
            if (mysqli_num_rows($res_cat) > 0) {
                while ($cat_row = mysqli_fetch_assoc($res_cat)) {
                    echo "<li class='btn btn-outline-danger btn-sm mb-2 me-1 tag_link' style='border-radius: 20px;'>
                                <a href='category.php?c_id={$cat_row['category_id']}' style='text-decoration:none;'>{$cat_row['category_name']}</a>
                                <span class='badge bg-danger rounded-pill'>{$cat_row['post']}</span>
                          </li>";
                }
            }
            ?>
        </ul>
    </div>
    <div class="sidebar_container mt-4">
        <h4>Tags</h4>
        <div class="tag-cloud p-3 bg-white border">
            <?php
            if ($conn):
                /* Fetching all tags from post table and separating them */
                $sql_tags = "SELECT tags FROM post WHERE tags != ''";
                if (isset($_GET['id'])) {
                    $sql_tags .= " && post_id = " . $_GET['id'];
                }
                $res_tags = mysqli_query($conn, $sql_tags);
                $all_tags = [];

                if (mysqli_num_rows($res_tags) > 0) {
                    while ($tag_row = mysqli_fetch_assoc($res_tags)) {
                        $tags_arr = explode(',', $tag_row['tags']);
                        foreach ($tags_arr as $tag) {
                            $all_tags[] = trim($tag);
                        }
                    }
                    /* Getting unique tags only */
                    $unique_tags = array_unique($all_tags);

                    foreach ($unique_tags as $final_tag) {
                        if (!empty($final_tag)) {
                            echo "<a href='tag-post.php?tag=" . urlencode($final_tag) . "' class='btn btn-outline-dark btn-xs mb-2 me-1' style='font-size:11px; text-transform:uppercase; margin-right:5px; margin-bottom:5px; display:inline-block;'>#{$final_tag}</a>";
                        }
                    }
                } else {
                    echo "<span>No tags found.</span>";
                }
            endif;
            ?>
        </div>
    </div>

</div>