<?php
//code for dynamic title showing according page
include 'config.php';

$pagename = basename($_SERVER['PHP_SELF'], ".php");

switch ($pagename) {
    case 'single':
        //get the post title from database
        if (isset($_GET['id'])) {
            $post_id = mysqli_real_escape_string($conn, $_GET['id']);
            $sql = "SELECT title FROM post WHERE post_id = {$post_id}";
            $result = mysqli_query($conn, $sql);
            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $title = htmlspecialchars($row['title']);
            } else {
                $title = "Post Not Found";
            }
        } else {
            $title = "Post Not Found";
        }
        break;

    case 'category':
        //get the category name from database
        if (isset($_GET['c_id'])) {
            $cat_id = mysqli_real_escape_string($conn, $_GET['c_id']);
            $sql = "SELECT category_name FROM category WHERE category_id = {$cat_id}";
            $result = mysqli_query($conn, $sql);
            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $title = htmlspecialchars($row['category_name']) . " News";
            } else {
                $title = "Category Not Found";
            }
        } else {
            $title = "Category Not Found";
        }
        break;

    case 'author':
        //get the author name from database
        if (isset($_GET['aid'])) {
            $author_id = mysqli_real_escape_string($conn, $_GET['aid']);
            $sql = "SELECT username FROM user WHERE user_id = {$author_id}";
            $result = mysqli_query($conn, $sql);
            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $title = "Posts by " . htmlspecialchars($row['username']);
            } else {
                $title = "Author Not Found";
            }
        } else {
            $title = "Author Not Found";
        }
        break;

    case 'search':
        //get the search keyword
        if (isset($_GET['search'])) {
            $search_keyword = htmlspecialchars($_GET['search']);
            $title = "Search results for '" . $search_keyword . "'";
        } else {
            $title = "Search";
        }
        break;

    default:
        $title = "News Site";
        break;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title><?php echo $title; ?></title>
    <!-- Bootstrap -->
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="css/font-awesome.css">
    <!-- Custom stlylesheet -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <!-- HEADER -->
    <div id="header">
        <!-- container -->
        <div class="container">
            <!-- row -->
            <div class="row">
                <!-- LOGO -->
                <div class=" col-md-offset-4 col-md-4">
                    <?php

                    if ($conn) {
                        $logosql = "SELECT logo from settings";
                        $result = mysqli_query($conn, $logosql);

                        if (mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);
                            $logo = $row['logo'];
                        }
                        mysqli_close($conn);
                    }
                    
                    ?>
                    <a href="index.php" id="logo"><img class="logo"
                            src="images/<?php echo htmlspecialchars($logo); ?>"></a>
                </div>
                <!-- /LOGO -->
            </div>
        </div>
    </div>
    <!-- /HEADER -->
    <!-- Menu Bar -->
    <div id="menu-bar">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <ul class='menu'>
                        <li><a href='index.php'>Home</a></li>
                        <?php
                        include 'config.php';
                        //first check if connection is established or not
                        if ($conn) {
                            // Fetch users from database and display here
                            $sql = "SELECT * 
                            FROM category
                            ORDER BY category_id ASC";

                            $result = mysqli_query($conn, $sql);

                            if (!$result) {
                                die("Query Failed: " . mysqli_error($connection));
                            } else {
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $cat_id = $row['category_id'];
                                        $category_name = $row['category_name'];
                                        $post = $row['post'];
                                        ?>
                                        <li><a href='category.php?c_id=<?php echo $cat_id; ?>'>
                                                <?php echo $category_name; ?></a></li>
                                        <?php
                                    }
                                }
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- /Menu Bar -->