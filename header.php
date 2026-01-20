<?php
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ==========================
   Visitor Tracking
========================== */
$ip = $_SERVER['REMOTE_ADDR'];
$current_time = date("Y-m-d H:i:s");
$today_date = date("Y-m-d");

// IP tracking with improved query
$check_log = "SELECT id FROM visitor_logs WHERE ip_address = '$ip' AND DATE(visit_date) = '$today_date'";
$log_result = mysqli_query($conn, $check_log);

if ($log_result && mysqli_num_rows($log_result) === 0) {
    mysqli_query($conn, "INSERT INTO visitor_logs (ip_address, visit_date) VALUES ('$ip', '$current_time')");
    mysqli_query($conn, "INSERT INTO daily_visits (visit_date, views) VALUES ('$today_date', 1) ON DUPLICATE KEY UPDATE views = views + 1");
}

/* ==========================
   Fetch Website Settings
========================== */
$site_info_sql = "SELECT * FROM settings LIMIT 1";
$site_info_res = mysqli_query($conn, $site_info_sql);
$settings = mysqli_fetch_assoc($site_info_res);

$sitename = $settings['websitename'];
$site_desc = $settings['footerdesc']; // Use footerdesc or add a 'description' column in DB
$site_logo = $settings['logo'];

/* ==========================
   Dynamic SEO Title Logic
========================== */
$pagename = basename($_SERVER['PHP_SELF'], ".php");
$title = $sitename;

function getTitleName($conn, $table, $column, $whereCol, $id)
{
    $id = (int) $id;
    $sql = "SELECT $column AS value FROM $table WHERE $whereCol = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt)
        return null;
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['value'] ?? null;
}

switch ($pagename) {
    case 'single':
        if (isset($_GET['id'])) {
            $post_title = getTitleName($conn, 'post', 'title', 'post_id', $_GET['id']);
            $title = htmlspecialchars($post_title) . " | " . $sitename;
        }
        break;
    case 'category':
        if (isset($_GET['c_id'])) {
            $cat_title = getTitleName($conn, 'category', 'category_name', 'category_id', $_GET['c_id']);
            $title = htmlspecialchars($cat_title) . " News | " . $sitename;
        }
        break;
    case 'search':
        if (isset($_GET['search'])) {
            $title = 'Search: ' . htmlspecialchars($_GET['search']) . " | " . $sitename;
        }
        break;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>

    <meta name="description" content="<?php echo substr(strip_tags($site_desc), 0, 160); ?>">
    <meta name="robots" content="index, follow">

    <meta property="og:title" content="<?php echo $title; ?>">
    <meta property="og:description" content="Latest news and updates from <?php echo $sitename; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <div id="header">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <?php
                    if (empty($site_logo)) {
                        echo '<a href="index.php" id="logo"><h1>' . $sitename . '</h1></a>';
                    } else {
                        echo '<a href="index.php" id="logo"><img src="admin/images/' . $site_logo . '" alt="' . $sitename . ' Logo"></a>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ======== home-menu-bar ========= -->
    <div id="menu-bar">
        <div class="container">
            <div class="row">
                <div class="col-md-12" style="position: relative;">
                    <button class="menu-toggle" id="menu-toggle-btn">
                        <i class="fas fa-bars"></i>
                    </button>

                    <div class="menu-overlay" id="menu-overlay"></div>

                    <ul class='menu' id="main-menu">
                        <?php $home_active = ($pagename == 'index') ? "active" : ""; ?>
                        <li><a class="<?php echo $home_active; ?>" href="index.php">Home</a></li>

                        <?php
                        $active_cat_id = isset($_GET['c_id']) ? (int) $_GET['c_id'] : 0;
                        $sql_cat = "SELECT * 
                                    FROM category 
                                    WHERE post > 0 
                                    AND show_in_header = 1 
                                    ORDER BY category_id ASC";

                        $res_cat = mysqli_query($conn, $sql_cat);

                        if ($res_cat && mysqli_num_rows($res_cat) > 0) {
                            while ($row_cat = mysqli_fetch_assoc($res_cat)) {
                                $cat_active = ($row_cat['category_id'] == $active_cat_id) ? "active" : "";
                                echo "<li><a class='{$cat_active}' href='category.php?c_id={$row_cat['category_id']}'>{$row_cat['category_name']}</a></li>";
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
   
    </script>