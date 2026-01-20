<?php
/* =========================
   Session & Authentication
========================= */
include_once __DIR__ . "/config.php";
include __DIR__ . "/includes/auth.php";

/* =========================
   Helper: Safe Fetch One
========================= */
function fetchOne($conn, $sql, $key = null)
{
  $res = mysqli_query($conn, $sql);
  if ($res && $row = mysqli_fetch_assoc($res)) {
    return $key ? $row[$key] : $row;
  }
  return $key ? 0 : null;
}

/* =========================
   Chart 1: Last 7 Days Visits
========================= */
$dates = [];
$views = [];

for ($i = 6; $i >= 0; $i--) {
  $date = date('Y-m-d', strtotime("-$i days"));
  $dayName = date('D', strtotime($date));

  $sql_daily_views = "SELECT views 
                      FROM daily_visits 
                      WHERE visit_date = '$date'";

  $row = fetchOne($conn, $sql_daily_views, 'views');

  $dates[] = $dayName;
  $views[] = (int) $row;
}

/* ---------- Prepare Combined JSON Structure ---------- */
$weekly_view_data = [
  'dates' => $dates,
  'views' => $views
];

/* ---------- Save to JSON File for JS ---------- */
file_put_contents(
  'assets/json/weekly_view.json',
  json_encode($weekly_view_data, JSON_PRETTY_PRINT)
);



/* =========================
   Chart 2: Category-wise Views (Using Category Name)
========================= */

$sql_category_views = "SELECT c.category_name AS category_name,
                        SUM(p.viewCount) AS total_views
                    FROM post p
                    INNER JOIN category c ON p.category = c.category_id
                    WHERE p.status = 'approved'
                    AND p.published_at IS NOT NULL
                     AND DATE(p.published_at) = CURDATE() 
                    GROUP BY c.category_name
                    ORDER BY total_views DESC";

$res_category_views = mysqli_query($conn, $sql_category_views);

$categoryData = [];
$total_views = 0;

if ($res_category_views) {
  while ($row = mysqli_fetch_assoc($res_category_views)) {
    $categoryData[] = [
      'category' => $row['category_name'], // ✅ category_name
      'views' => (int) $row['total_views']
    ];
    $total_views += (int) $row['total_views'];
  }
}

/* Save JSON for JS */
file_put_contents(
  'assets/json/category_view.json',
  json_encode($categoryData, JSON_PRETTY_PRINT)
);


/* =========================
   Chart 3: Author-wise View Rating (Monthly & Yearly)
========================= */

// Current month & year
$current_month = date('m');
$current_year = date('Y');

/* ---- Monthly Top Authors- ---- */
$sql_author_monthly = "SELECT 
                        u.username AS author_name,
                        SUM(p.viewCount) AS total_views
                        FROM post p
                        INNER JOIN user u ON p.author = u.user_id
                        WHERE MONTH(p.created_date) = '$current_month'
                        AND YEAR(p.created_date)  = '$current_year'
                        AND p.status = 'approved'
                        AND p.published_at IS NOT NULL
                        GROUP BY p.author
                        ORDER BY total_views DESC
                        LIMIT 5";

$res_monthly = mysqli_query($conn, $sql_author_monthly);

$monthly_data = [];

if ($res_monthly && mysqli_num_rows($res_monthly) > 0) {
  while ($row = mysqli_fetch_assoc($res_monthly)) {
    $monthly_data[] = [
      'author' => $row['author_name'],
      'views' => (int) $row['total_views']
    ];
  }
}

/* --- Yearly Top Authors- --- */
$sql_author_yearly = "SELECT 
                      u.username AS author_name,
                      SUM(p.viewCount) AS total_views
                      FROM post p
                      INNER JOIN user u ON p.author = u.user_id
                      WHERE YEAR(p.created_date) = '$current_year'
                      AND p.status = 'approved'
                      AND p.published_at IS NOT NULL
                      GROUP BY p.author
                      ORDER BY total_views DESC
                      LIMIT 5";

$res_yearly = mysqli_query($conn, $sql_author_yearly);

$yearly_data = [];

if ($res_yearly && mysqli_num_rows($res_yearly) > 0) {
  while ($row = mysqli_fetch_assoc($res_yearly)) {
    $yearly_data[] = [
      'author' => $row['author_name'],
      'views' => (int) $row['total_views']
    ];
  }
}

/* ---  Final JSON Structure --- */
$author_view_data = [
  'monthly' => $monthly_data,
  'yearly' => $yearly_data
];

/* --- Save JSON for JavaScript --- */
file_put_contents(
  'assets/json/author_view.json',
  json_encode($author_view_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);


/* =========================
   Chart 4: Hourly Post Reach Analysis
   Save data to hourly_view.json
========================= */

$hourly_labels = [];
$hourly_posts_data = []; // store per post hourly views

// Last 24 hours
for ($i = 23; $i >= 0; $i--) {
  $hour = date('H', strtotime("-$i hours"));
  $date = date('Y-m-d', strtotime("-$i hours"));
  $hourLabel = $hour . ":00";
  $hourly_labels[] = $hourLabel;

  // Fetch all posts with views for this hour
  $sql_hourly_posts = "SELECT 
                        p.post_id, 
                        p.title, 
                        SUM(h.views_count) AS hourly_views
                       FROM post p
                       LEFT JOIN hourly_views h 
                       ON h.post_id = p.post_id 
                       AND h.view_hour = '$hour' 
                       AND h.view_date = '$date'
                       WHERE p.status='approved' 
                       AND p.published_at IS NOT NULL
                       GROUP BY p.post_id
                       ORDER BY p.published_at ASC"; // keep all posts

  $res_h = mysqli_query($conn, $sql_hourly_posts);

  while ($row = mysqli_fetch_assoc($res_h)) {

    $post_id = $row['post_id'];

    $title = mb_substr($row['title'], 0, 20);
    if (mb_strlen($row['title']) > 20) {
      $title .= '...';
    }

    $views = (int) ($row['hourly_views'] ?? 0);

    if (!isset($hourly_posts_data[$post_id])) {
      $hourly_posts_data[$post_id] = [
        'title' => $title,
        'hourly_views' => array_fill(0, 24, 0),
        'total_views' => 0
      ];
    }

    // set hourly view
    $hourly_posts_data[$post_id]['hourly_views'][23 - $i] = $views;
    $hourly_posts_data[$post_id]['total_views'] += $views;
  }
}

// Sequential array for JSON
$hourly_posts = array_values($hourly_posts_data);

// sort posts by total views (Top Posts)
usort($hourly_posts, fn($a, $b) => $b['total_views'] <=> $a['total_views']);

// Save JSON for JS
$hourly_json = [
  'labels' => $hourly_labels,
  'posts' => $hourly_posts
];

file_put_contents(
  'assets/json/hourly_view.json',
  json_encode($hourly_json, JSON_PRETTY_PRINT)
);

// For sidebar summary
$total_hourly_views = array_sum(array_map(fn($p) => $p['total_views'], $hourly_posts));



/* =========================
   Widgets Data
========================= */
// A) Total Approved News
$sql_total_news = "SELECT COUNT(post_id) 
                   AS total FROM post 
                   WHERE status = 'approved'";

$total_news = fetchOne($conn, $sql_total_news, 'total');

// B) Active Authors (Last 3 Days)
$sql_total_users = "SELECT COUNT(user_id) AS total FROM user";

$total_users = fetchOne($conn, $sql_total_users, 'total');

$sql_active_users = "SELECT COUNT(DISTINCT author) AS active
                      FROM post
                      WHERE created_date >= DATE_SUB(NOW(), INTERVAL 3 DAY)";

$active_users = fetchOne($conn, $sql_active_users, 'active');

$active_percentage = ($total_users > 0)
  ? round(($active_users / $total_users) * 100, 2)
  : 0;

// C) Popular Posts Count (Latest Publish Date)
$sql_last_publish_date = "SELECT MAX(DATE(published_at)) AS last_date FROM post";

$last_publish_date = fetchOne($conn, $sql_last_publish_date, 'last_date');

$total_hits = 0;

if ($last_publish_date) {

  $sql_total_hits = "SELECT COUNT(*) AS total
                      FROM post
                      WHERE DATE(published_at) = '$last_publish_date'
                      AND viewCount >= 0";

  $total_hits = fetchOne($conn, $sql_total_hits, 'total');
}

// D) Pending Posts
$sql_pending_posts = "SELECT COUNT(post_id) AS total 
                      FROM post 
                      WHERE status = 'pending'";

$total_pending = fetchOne($conn, $sql_pending_posts, 'total');

// E) Activity Feed
$sql_activity = "SELECT p.title, p.created_date, u.username
                  FROM post p
                  JOIN user u ON p.author = u.user_id
                  ORDER BY p.post_id DESC
                  LIMIT 5";

$res_activity = mysqli_query($conn, $sql_activity);

include "includes/header.php";
include "includes/sidebar.php";
?>

<!--begin::App Content-->
<div class="app-content">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">


      <!--begin::Widget 1 Col-->
      <div class="col-lg-3 col-6">
        <!--begin::Small Box Widget 1-->
        <div class="small-box text-bg-primary">
          <div class="inner">
            <h3><?php echo $total_news; ?></h3>
            <p>Live News</p>
          </div>
          <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true">
            <path
              d="M2.25 2.25a.75.75 0 000 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a3.752 3.752 0 00-2.806 3.63c0 .414.336.75.75.75h15.75a.75.75 0 000-1.5H5.378A2.25 2.25 0 017.5 15h11.218a.75.75 0 00.674-.421 60.358 60.358 0 002.96-7.228.75.75 0 00-.525-.965A60.864 60.864 0 005.68 4.509l-.232-.867A1.875 1.875 0 003.636 2.25H2.25zM3.75 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM16.5 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z">
            </path>
          </svg>
          <a href="post.php"
            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
            More info <i class="bi bi-link-45deg"></i>
          </a>
        </div>
        <!--end::Small Box Widget 1-->
      </div>
      <!--end:: Widget 1 Col-->

      <!--Start::Col 2-->

      <!-- ======== Start:: Active Authors Rating =========== -->
      <div class="col-lg-3 col-6">
        <div class="small-box text-bg-success">
          <div class="inner">
            <h3><?php echo $active_percentage; ?><sup class="fs-5">%</sup></h3>
            <p>Active Authors (Last 3 Days)</p>
          </div>
          <div class="small-box-icon">
            <i class="bi bi-graph-up-arrow"></i>
          </div>
          <?php if ($_SESSION['user_role'] == 1): ?>
            <a href="users.php"
              class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
              Manage Staffs <i class="bi bi-link-45deg"></i>
            </a>
          <?php else: ?>
            <div class="small-box-footer" style="height: 30px;"></div>
          <?php endif; ?>
        </div>
      </div>
      <!-- ======== end:: Active Authors Rating =========== -->

      <!--end::Col-->


      <div class="col-lg-3 col-6">
        <!--begin::Small Box Widget 3-->
        <div class="small-box text-bg-warning">
          <div class="inner">
            <h3><?php echo number_format($total_hits); ?></h3>
            <p>Today's Hits</p>
          </div>
          <div class="small-box-icon">
            <i class="bi bi-eye-fill"></i>
          </div>
          <a href="#" class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
            View Analytics <i class="bi bi-link-45deg"></i>
          </a>
        </div>
        <!--end::Small Box Widget 3-->
      </div>
      <!--end::Col-->


      <div class="col-lg-3 col-6">
        <!--begin::Small Box Widget 4-->
        <div class="small-box text-bg-danger">
          <div class="inner">
            <h3><?php echo $total_pending; ?></h3>
            <p>Pending Posts</p>
          </div>
          <div class="small-box-icon">
            <i class="bi bi-hourglass-split"></i>
          </div>
          <a href="pending-post.php"
            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
            Review Now <i class="bi bi-link-45deg"></i>
          </a>
        </div>
        <!--end::Small Box Widget 4-->
      </div>
      <!--end::Col-->

    </div>
    <!--end::Row-->


    <!--begin:: Data Chart Row-->
    <div class="row">

      <!-- Start col -->
      <div class="col-lg-7 connectedSortable">
        <div class="card mb-4">
          <div class="card-header">
            <h3 class="card-title">Weekly New Visitors Rate</h3>
          </div>
          <div class="card-body">
            <div id="revenue-chart"></div>
          </div>
        </div>
      </div>
      <!-- /.card -->


      <!-- /.Start col -->
      <!-- Start: Activity Feed Container -->
      <div class="col-lg-5 connectedSortable">
        <!-- Activity Feed -->
        <div class="card">
          <div class="card-header bg-body-secondary">
            <h3 class="card-title">Recent Activity</h3>
          </div>
          <div class="card-body p-0">
            <ul class="list-group list-group-flush">
              <?php if (mysqli_num_rows($res_activity) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($res_activity)): ?>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                      <i class="bi bi-pencil-square text-primary me-2"></i>
                      <strong>
                        <?php echo htmlspecialchars($row['username']); ?>
                      </strong>
                      posted:
                      <em>
                        <?php echo substr($row['title'], 0, 30) . '...'; ?>
                      </em>
                    </div>
                    <span class="badge bg-secondary rounded-pill" style="font-size: 0.7em;">
                      <?php echo date('d M', strtotime($row['created_date'])); ?>
                    </span>
                  </li>
                <?php endwhile; ?>
              <?php else: ?>
                <li class="list-group-item text-center">No recent activity.</li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
      <!-- /.Start col -->
    </div>

    <!--begin:: Data Chart Row 2-->
    <div class="row">

      <!--begin::  Category wise view chat- col-->
      <div class="col-lg-5">
        <!--begin:: Pie Chart with Category-->
        <div class="card mb-4 unique-card">
          <!-- Card Header -->
          <div class="card-header unique-card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title unique-card-title">Category-wise Post Views</h3>
            <!-- Card Tools -->
            <div class="card-tools unique-card-tools">
              <button type="button" class="btn btn-tool unique-btn-tool" data-lte-toggle="card-collapse">
                <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
              </button>
              <button type="button" class="btn btn-tool unique-btn-tool" data-lte-toggle="card-remove">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
          </div>
          <!-- Card Body -->
          <div class="card-body unique-card-body">
            <div class="row unique-row">
              <div class="col-12 unique-col-12">
                <!-- Pie Chart Container -->
                <div id="category-view-pie-chart" class="unique-pie-chart" style="min-height: 232.7px;"></div>
              </div>
            </div>
          </div>
          <!-- Card Footer -->
          <div class="card-footer unique-card-footer p-0">
            <ul class="nav nav-pills flex-column unique-footer-list">

              <?php if ($total_views > 0): ?>
                <?php foreach ($categoryData as $item):
                  $percentage = round(($item['views'] / $total_views) * 100);
                  ?>
                  <li class="nav-item">
                    <a href="#" class="nav-link unique-footer-link">
                      <?= htmlspecialchars($item['category']); ?>
                      <span class="float-end text-success">
                        <i class="bi bi-arrow-up fs-7"></i>
                        <?= $percentage; ?>%
                      </span>
                    </a>
                  </li>
                <?php endforeach; ?>
              <?php else: ?>
                <li class="nav-item">
                  <span class="nav-link">No data available</span>
                </li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
        <!--end:: Pie Chart with Category-->
      </div>
      <!-- end:: Pie Chart container -->


      <!--begin:: Author-Wise Viewer Chart -->
      <div class="col-lg-7">
        <div class="card mb-4">
          <div class="card-header border-0">
            <div class="d-flex justify-content-between align-items-center">
              <h3 class="card-title">Top Authors Performance</h3>

              <div class="d-flex gap-2">
                <!-- Toggle Buttons -->
                <div class="btn-group btn-group-sm" role="group">
                  <button id="authorMonthlyBtn" type="button" class="btn btn-primary">
                    Monthly
                  </button>
                  <button id="authorYearlyBtn" type="button" class="btn btn-outline-primary">
                    Yearly
                  </button>
                </div>

                <a href="users.php"
                  class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover ms-3">
                  View All Staffs
                </a>
              </div>
            </div>
          </div>

          <div class="card-body">
            <div class="d-flex mb-2">
              <p class="d-flex flex-column mb-0">
                <span class="text-bold text-lg">Author View Rating</span>
                <span class="text-muted">Percentage contribution based on total views</span>
              </p>
            </div>

            <div class="position-relative mb-4">
              <!-- Chart Container -->
              <div id="author-performance-chart" style="min-height: 300px;"></div>
            </div>

            <div class="d-flex flex-row justify-content-end small text-muted">
              <span class="me-3">
                <i class="bi bi-square-fill text-primary"></i> View Rating (%)
              </span>
            </div>
          </div>
        </div>
      </div>
      <!-- end:: Author-Viewe container-->

    </div>
    <!--end::Container Row 2-->


    <!--Begin::Container row 3-->

    <!-- =========================
     Real-time Post Reach Card
     ========================= -->
    <div class="row">
      <div class="col-12">
        <div class="card mb-4 shadow-sm">

          <!-- Card Header : Title + Toggle Buttons -->
          <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">

            <!-- Card Title -->
            <h3 class="card-title fw-bold m-0">
              <i class="bi bi-graph-up text-primary me-2"></i>
              Real-time Post Reach (Last 24 Hours)
            </h3>

            <!-- Toggle Buttons : Top Posts / All Posts -->
            <div class="btn-group btn-group-sm" role="group">
              <button id="hourlyTopPostsBtn" class="btn btn-outline-primary active">
                Top Posts
              </button>
              <button id="hourlyAllPostsBtn" class="btn btn-outline-primary">
                All Posts
              </button>
            </div>
          </div>
          <!-- /Card Header -->

          <!-- Card Body -->
          <div class="card-body p-0">
            <div class="row g-0">

              <!-- =========================
               Chart Section (Left)
               ========================= -->
              <div class="col-lg-9 p-3">
                <!-- ApexCharts container (hourly line chart) -->
                <div id="hourly-reach-chart" class="hourly-chart-container"></div>
              </div>
              <!-- /Chart Section -->

              <!-- =========================
               Post Distribution Sidebar
               ========================= -->
              <div class="col-lg-3 border-start bg-light-subtle">
                <div class="d-flex flex-column h-100">

                  <!-- Sidebar Header -->
                  <div class="p-3 border-bottom bg-light">
                    <h6 class="fw-bold mb-0 text-uppercase small text-muted">
                      Post Distribution
                    </h6>
                  </div>
                  <!-- /Sidebar Header -->

                  <!-- =========================
                   Dynamic Post List
                   (Injected by JavaScript)
                   ========================= -->
                  <div class="flex-grow-1 hourly-post-list-wrapper">
                    <ul class="list-group hourly-title list-group-flush" id="hourly-summary-list">
                      <!--
                    JS will dynamically inject:
                    - Post title (truncated)
                    - Total views badge
                    - Handles Top / All toggle
                  -->
                      <li class="list-group-item text-center text-muted small py-4">
                        Loading data...
                      </li>
                    </ul>
                  </div>
                  <!-- /Dynamic Post List -->

                  <!-- =========================
                   Total Reach Summary
                   (Updated by JavaScript)
                   ========================= -->
                  <div class="p-4 mt-auto border-top bg-white text-center">
                    <div class="mb-1">
                      <!-- Total hourly views (last 24h) -->
                      <span id="hourly-total" class="display-6 fw-black text-primary d-block">
                        <?= number_format($total_hourly_views) ?>
                      </span>

                      <small class="text-muted fw-bold text-uppercase tracking-wider" style="font-size: 0.7rem;">
                        Total Reach Today
                      </small>
                    </div>

                    <!-- Status Badge -->
                    <div class="mt-2">
                      <span
                        class="badge text_bg_white text-bg-success-subtle text-success border border-success-subtle px-3">
                        <i class="bi bi-arrow-up-right-circle-fill me-1"></i> Live Tracking
                      </span>
                    </div>
                  </div>
                  <!-- /Total Reach Summary -->

                </div>
              </div>
              <!-- /Post Distribution Sidebar -->

            </div>
          </div>
          <!-- /Card Body -->

        </div>
      </div>
    </div>
    <!-- /Real-time Post Reach Card -->

  </div>

  <!--end::App Content-->
  <?php include_once __DIR__ . "/includes/footer.php";
  ?>