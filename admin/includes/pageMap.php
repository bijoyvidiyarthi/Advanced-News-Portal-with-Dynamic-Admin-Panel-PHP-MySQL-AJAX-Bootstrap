<?php
$currentPageUrl = basename($_SERVER['PHP_SELF']);

$pagesUrl = [
    'dashboard.php' => ['title' => 'Dashboard', 'parent' => null],

    // News
    'post.php' => ['title' => 'All News', 'parent' => 'dashboard.php'],
    'add-post.php' => ['title' => 'Create New Post', 'parent' => 'post.php'],
    'pending-post.php' => ['title' => 'Pending Approval', 'parent' => 'post.php'],
    'draft-post.php' => ['title' => 'Drafts', 'parent' => 'post.php'],
    'update-post.php' => ['title' => 'Update Post', 'parent' => 'post.php'],

    // Taxonomy
    'category.php' => ['title' => 'Categories', 'parent' => 'dashboard.php'],
    'update-category.php' => ['title' => 'Update Category', 'parent' => 'category.php'],
    'tags.php' => ['title' => 'Tags', 'parent' => 'category.php'],

    // Users
    'users.php' => ['title' => 'All Staffs', 'parent' => 'dashboard.php'],
    'add-user.php' => ['title' => 'Add New Staff', 'parent' => 'users.php'],
    'update-user.php' => ['title' => 'Update User', 'parent' => 'users.php'],

    // Others
    'media_gallery.php' => ['title' => 'Media Library', 'parent' => 'dashboard.php'],
    'comments.php' => ['title' => 'Comments', 'parent' => 'dashboard.php'],
    'settings.php' => ['title' => 'Settings', 'parent' => 'dashboard.php'],

    //user Profile
    'profile.php' => ['title' => 'My Profile', 'parent' => 'dashboard.php'],
    'change-password.php' => ['title' => 'Change Password', 'parent' => 'profile.php']

];
