<?php
/* ==============================
   Current Page
================================ */
$currentPage_url = basename($_SERVER['PHP_SELF']);

/* ==============================
   Helper Functions
================================ */
function isActive($page_url)
{
    global $currentPage_url;
    return $currentPage_url === $page_url ? 'active' : '';
}

function isMenuOpen(array $pages_url)
{
    global $currentPage_url;
    return in_array($currentPage_url, $pages_url, true) ? 'menu-open' : '';
}

function isParentActive(array $pages_url)
{
    global $currentPage_url;
    return in_array($currentPage_url, $pages_url, true) ? 'active' : '';
}

/* ==============================
   MENU CONFIGURATION
================================ */

/* --- News Management --- */
$newsMenu = [
    ['file' => 'post.php', 'label' => 'All News', 'icon' => 'bi bi-list-ul'],
    ['file' => 'add-post.php', 'label' => 'Add New Post', 'icon' => 'bi bi-plus-circle'],
    ['file' => 'pending-post.php', 'label' => 'Pending Approval', 'icon' => 'bi bi-hourglass-split'],
    ['file' => 'draft-post.php', 'label' => 'Drafts', 'icon' => 'bi bi-file-earmark-text'],
    ['file' => 'update-post.php', 'label' => 'Update Post', 'icon' => 'bi bi-pencil-square'],
    ['file' => 'media_gallery.php', 'label' => 'Media Gallery', 'icon' => 'bi bi-images']
];
$newsPages = array_column($newsMenu, 'file');

/* --- Taxonomy --- */
$taxonomyMenu = [
    ['file' => 'category.php', 'label' => 'Categories', 'icon' => 'bi bi-circle'],
    ['file' => 'update-category.php', 'label' => 'Update Category', 'icon' => 'bi bi-pencil-square'],
    ['file' => 'tags.php', 'label' => 'Tags', 'icon' => 'bi bi-circle'],
];
$taxPages = array_column($taxonomyMenu, 'file');

/* --- Users --- */
$userMenu = [
    ['file' => 'users.php', 'label' => 'All Staffs', 'icon' => 'bi bi-circle'],
    ['file' => 'add-user.php', 'label' => 'Add New Staff', 'icon' => 'bi bi-circle'],
    ['file' => 'update-user.php', 'label' => 'Update User', 'icon' => 'bi bi-pencil-square'],
];
$userPages = array_column($userMenu, 'file');

/* --- Personal Account --- */
$accountMenu = [
    ['file' => 'profile.php', 'label' => 'My Profile', 'icon' => 'bi bi-person-circle'],
    ['file' => 'change-password.php', 'label' => 'Change Password', 'icon' => 'bi bi-shield-lock'],
];
$accountPages = array_column($accountMenu, 'file');

/* --- Messages --- */
$messageMenu = [
    ['file' => 'messages.php', 'label' => 'Contact Messages', 'icon' => 'bi bi-envelope-paper'],
    ['file' => 'view-message.php', 'label' => 'View Message', 'icon' => 'bi bi-eye'],
];
$messagePages = array_column($messageMenu, 'file');


/* --- System Settings --- */
$systemSettingsMenu = [
    ['file' => 'settings.php', 'label' => 'General Settings', 'icon' => 'bi bi-gear'],
    ['file' => 'layout-settings.php', 'label' => 'Layout Settings', 'icon' => 'bi bi-layout-sidebar-inset'],
];
$settingsPages = array_column($systemSettingsMenu, 'file');
?>


<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">

    <!-- Brand -->
    <div class="sidebar-brand">
        <!--begin::Brand Image-->
        <img src="" alt="News Site Logo" class="brand-image opacity-75 shadow" />
        <!--end::Brand Image-->
        <a href="post.php" class="brand-link">
            <span class="brand-text fw-light">News Admin Panel</span>
        </a>
    </div>
    <!--end::Brand -->


    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">

                <!-- ===  Dash-Board === -->
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?= isActive('dashboard.php') ?>">
                        <i class="nav-icon bi bi-speedometer"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <!-- ===  </ Dash-Board === -->

                <li class="nav-header text-uppercase small text-secondary px-3 mt-4 mb-1"
                    style="list-style: none; font-size: 0.75rem; font-weight: 700;">
                    News Management
                </li>
                <!-- ===   News-Management === -->
                <li class="nav-item <?= isMenuOpen($newsPages) ?>">
                    <a href="#" class="nav-link <?= isParentActive($newsPages) ?>">
                        <i class="nav-icon bi bi-newspaper"></i>
                        <p>
                            News Management
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        <?php foreach ($newsMenu as $item): ?>
                            <li class="nav-item">
                                <a href="<?= $item['file'] ?>" class="nav-link <?= isActive($item['file']) ?>">
                                    <i class="nav-icon <?= $item['icon'] ?>"></i>
                                    <p><?= $item['label'] ?></p>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <!-- ===   </ News-Management === -->


                <!-- === ================================= -->
                <!-- ===  Category-Tags-UserManagement === -->
                <!-- === ================================= -->


                <!-- -- < Category and Tags Management ---- -->
                <li class="nav-header text-uppercase small text-secondary px-3 mt-4 mb-1"
                    style="list-style: none; font-size: 0.75rem; font-weight: 700;">
                    Category and Tags Management
                </li>

                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1): ?>

                    <li class="nav-item <?= isMenuOpen($taxPages) ?>">
                        <a href="#" class="nav-link <?= isParentActive($taxPages) ?>">
                            <i class="nav-icon bi bi-tags"></i>
                            <p>Taxonomy <i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>

                        <ul class="nav nav-treeview">
                            <?php foreach ($taxonomyMenu as $item): ?>
                                <li class="nav-item">
                                    <a href="<?= $item['file'] ?>" class="nav-link <?= isActive($item['file']) ?>">
                                        <i class="nav-icon <?= $item['icon'] ?>"></i>
                                        <p>
                                            <?= $item['label'] ?>
                                        </p>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <!-- -----  </ Category and Tags Management ------ -->


                    <!-- -----   Users Management ------ -->
                    <li class="nav-header text-uppercase small text-secondary px-3 mt-4 mb-1"
                        style="list-style: none; font-size: 0.75rem; font-weight: 700;">
                        Users Management
                    </li>
                    <li class="nav-item <?= isMenuOpen($userPages) ?>">
                        <a href="#" class="nav-link <?= isParentActive($userPages) ?>">
                            <i class="nav-icon bi bi-people"></i>
                            <p>
                                User Management
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            <?php foreach ($userMenu as $item): ?>
                                <li class="nav-item">
                                    <a href="<?= $item['file'] ?>" class="nav-link <?= isActive($item['file']) ?>"> <i
                                            class="nav-icon bi bi-circle"></i>
                                        <p>
                                            <?= $item['label'] ?>
                                        </p>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>

                    <!-- -----  </ Users Management ------ -->

                    <!-- -----   Comment and Settings ------ -->
                    <!-- Comments -->
                    <li class="nav-header text-uppercase small text-secondary px-3 mt-4 mb-1"
                        style="list-style: none; font-size: 0.75rem; font-weight: 700;">
                        Interaction & Settings
                    </li>
                    <li class="nav-item">
                        <a href="comments.php" class="nav-link <?= isActive('comments.php') ?>">
                            <i class="nav-icon bi bi-chat-dots"></i>
                            <p>Comments</p>
                        </a>
                    </li>


                    <li class="nav-item">
                        <a href="messages.php" class="nav-link <?= isParentActive($messagePages) ?>">
                            <i class="nav-icon bi bi-envelope-at"></i>
                            <p>
                                Contact Messages
                                <?php
                                //count new messages
                                $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM contact_messages");
                                $msg_data = mysqli_fetch_assoc($res);
                                if ($msg_data['total'] > 0) {
                                    echo '<span class="badge bg-danger float-end">' . $msg_data['total'] . '</span>';
                                }
                                ?>
                            </p>
                        </a>
                    </li>


                    <!-- Settings -->
                    <li class="nav-item <?= isMenuOpen($settingsPages) ?>">
                        <a href="#" class="nav-link <?= isParentActive($settingsPages) ?>">
                            <i class="nav-icon bi bi-sliders"></i>
                            <p>
                                System Settings
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            <?php foreach ($systemSettingsMenu as $item): ?>
                                <li class="nav-item">
                                    <a href="<?= $item['file'] ?>" class="nav-link <?= isActive($item['file']) ?>">
                                        <i class="nav-icon <?= $item['icon'] ?>"></i>
                                        <p><?= $item['label'] ?></p>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>

                <?php endif; ?>
                <!-- -----   </Comment & Settings ------ -->

                <!-- === ================================================= -->
                <!-- ===  </Category-Tags-Users-Comments and Settings === -->
                <!-- === ================================================ -->


                <!-- -----   User Account ------ -->
                <li class="nav-header text-uppercase small text-secondary px-3 mt-4 mb-1"
                    style="list-style: none; font-size: 0.75rem; font-weight: 700;">
                    USER ACCOUNT
                </li>

                <li class="nav-item <?= isMenuOpen($accountPages) ?>">
                    <a href="#" class="nav-link <?= isParentActive($accountPages) ?>">
                        <i class="nav-icon bi bi-person-gear"></i>
                        <p>
                            Account Settings
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php foreach ($accountMenu as $item): ?>
                            <li class="nav-item">
                                <a href="<?= $item['file'] ?>" class="nav-link <?= isActive($item['file']) ?>">
                                    <i class="nav-icon <?= $item['icon'] ?>"></i>
                                    <p>
                                        <?= $item['label'] ?>
                                    </p>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <!-- -----   </ User Account ------ -->

                <!-- Logout -->
                <li class="nav-item border-top mt-2 pt-2">
                    <a href="logout.php" class="nav-link text-danger">
                        <i class="nav-icon bi bi-box-arrow-right"></i>
                        <p>Logout</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0"><?php echo $pageTitle; ?></h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <?php
                        $count = count($breadcrumbs);
                        foreach ($breadcrumbs as $index => $crumb):
                            if ($index === $count - 1): ?>
                                <li class="breadcrumb-item active">
                                    <?php echo $crumb['title']; ?>
                                </li>
                            <?php else: ?>
                                <li class="breadcrumb-item">
                                    <a href="<?php echo $crumb['url']; ?>">
                                        <?php echo $crumb['title']; ?>
                                    </a>
                                </li>
                            <?php endif;
                        endforeach; ?>
                    </ol>
                </div>
            </div>
        </div>
    </div>