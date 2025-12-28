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
                
                if(!empty($search_value)){
                    echo ' <input type="text" name="search" class="form-control" placeholder="Search ....." value="' . $search_value . '">';
                } else {    
                echo '<input type="text" name="search" class="form-control" placeholder="Search .....">';
                }
                ?>
                
               
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-danger" >Search</button>
                </span>
            </div>
        </form>
    </div>
    <!-- /search box -->
    <!-- recent posts box -->
    <div class="recent-post-container">
        <h4>Recent Posts</h4>
        <?php
        include 'config.php';
        //first check if connection is established or not
        if ($conn):
            // Fetch posts from database according to recent date (today's posts first, then 1 day old, etc.)
            $sql = "SELECT p.post_id, p.title, p.post_date, p.post_img, 
                    c.category_name, p.category
                    FROM post p
                    LEFT JOIN category c ON p.category = c.category_id
                    ORDER BY p.post_date DESC 
                    LIMIT 5";

            $result = mysqli_query($conn, $sql);

            if (!$result):
                $_SESSION['error'] = "Error fetching posts: " . mysqli_error($conn);
                header("Location: $_SERVER[PHP_SELF]");
                mysqli_close($conn);
            else:
                if (mysqli_num_rows($result) > 0):
                    //show only 5 recent posts
                    $count = 0;
                    while ($row = mysqli_fetch_assoc($result)):
                        $id = $row['post_id'];
                        $title = $row['title'];
                        $category_id = $row['category'];
                        $category = $row['category_name'];
                        $date = $row['post_date'];
                        $post_img = $row['post_img'];
                        ?>
                        <div class="recent-post">
                            <a class="post-img" href="<?php echo "single.php?id=" . htmlspecialchars($id); ?>">
                                <img src="admin/upload/<?php echo htmlspecialchars($post_img); ?>"
                                    alt="<?php echo htmlspecialchars($title); ?>" />
                            </a>
                            <div class="post-content">
                                <h5><a href="single.php?id=<?php echo htmlspecialchars($id); ?>">
                                        <?php // truncate long titles
                                                        if (strlen($title) > 40) {
                                                            $title = substr($title, 0, 37) . '...';
                                                        }
                                                        echo htmlspecialchars($title);
                                                        ?>
                                    </a></h5>
                                <span>
                                    <i class="fa fa-tags" aria-hidden="true"></i>
                                    <a href='category.php?c_id=<?php echo htmlspecialchars($category_id); ?>'>
                                        <?php
                                        // truncate long category names, it should only contains 1 word
                                        $category = explode(' ', $category);
                                        echo htmlspecialchars($category[0]);
                                        ?>
                                    </a>
                                </span>
                                <span>
                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                    <?php echo htmlspecialchars($date); ?>
                                </span>
                                <a class="read-more" href="single.php?id=<?php echo htmlspecialchars($id); ?>">read more</a>
                            </div>
                        </div>
                        <?php
                        $count++;
                        if ($count >= 5) {
                            break;
                        }
                    endwhile;
                else:
                    echo "<h3>No Posts Found</h3>";
                endif;
            endif;
        else:
            echo "<h3>Connection Error</h3>";
        endif;
        mysqli_close($conn);
        ?>
    </div>
    <!-- /recent posts box -->
</div>