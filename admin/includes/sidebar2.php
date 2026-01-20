<?php
$currentPage = basename($_SERVER['PHP_SELF']);

// ১. সাধারণ লিংকের জন্য Active চেক
function isActive($page)
{
    global $currentPage;
    return $currentPage === $page ? 'active' : '';
}

// ২. প্যারেন্ট মেনু (LI) খোলা রাখার জন্য
function isMenuOpen(array $pages)
{
    global $currentPage;
    return in_array($currentPage, $pages, true) ? 'menu-open' : '';
}

// ৩. প্যারেন্ট লিংক (A) কালার ও এরো ঠিক রাখার জন্য
function isParentActive(array $pages)
{
    global $currentPage;
    return in_array($currentPage, $pages, true) ? 'active' : '';
}
?>

<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="post.php" class="brand-link">
            <span class="brand-text fw-light">News Admin Panel</span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?= isActive('dashboard.php') ?>">
                        <i class="nav-icon bi bi-speedometer"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <?php 
                    $newsPages = ['post.php', 'add-post.php', 'pending-post.php', 'draft-post.php', 'update-post.php']; 
                ?>
                <li class="nav-item <?= isMenuOpen($newsPages) ?>">
                    <a href="#" class="nav-link <?= isParentActive($newsPages) ?>">
                        <i class="nav-icon bi bi-newspaper"></i>
                        <p>News Management <i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>

                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="post.php" class="nav-link <?= isActive('post.php') ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>All News</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="add-post.php" class="nav-link <?= isActive('add-post.php') ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Add New Post</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="pending-post.php" class="nav-link <?= isActive('pending-post.php') ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Pending Approval</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="draft-post.php" class="nav-link <?= isActive('draft-post.php') ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Drafts</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1): ?>
                    
                    <?php $taxPages = ['category.php', 'tags.php', 'update-category.php']; ?>
                    <li class="nav-item <?= isMenuOpen($taxPages) ?>">
                        <a href="#" class="nav-link <?= isParentActive($taxPages) ?>">
                            <i class="nav-icon bi bi-tags"></i>
                            <p>Taxonomy <i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>

                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="category.php" class="nav-link <?= isActive('category.php') || isActive('update-category.php') ?>">
                                    <i class="nav-icon bi bi-circle"></i>
                                    <p>Categories</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="tags.php" class="nav-link <?= isActive('tags.php') ?>">
                                    <i class="nav-icon bi bi-circle"></i>
                                    <p>Tags</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <?php $userPages = ['users.php', 'add-user.php', 'update-user.php']; ?>
                    <li class="nav-item <?= isMenuOpen($userPages) ?>">
                        <a href="#" class="nav-link <?= isParentActive($userPages) ?>">
                            <i class="nav-icon bi bi-people"></i>
                            <p>User Management <i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>

                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="users.php" class="nav-link <?= isActive('users.php') ?>">
                                    <i class="nav-icon bi bi-circle"></i>
                                    <p>All Staffs</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="add-user.php" class="nav-link <?= isActive('add-user.php') ?>">
                                    <i class="nav-icon bi bi-circle"></i>
                                    <p>Add New Staff</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="comments.php" class="nav-link <?= isActive('comments.php') ?>">
                            <i class="nav-icon bi bi-chat-dots"></i>
                            <p>Comments</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="settings.php" class="nav-link <?= isActive('settings.php') ?>">
                            <i class="nav-icon bi bi-gear"></i>
                            <p>Settings</p>
                        </a>
                    </li>

                <?php endif; ?>

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