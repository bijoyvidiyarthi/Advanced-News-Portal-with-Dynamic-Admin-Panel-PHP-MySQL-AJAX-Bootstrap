<?php
require_once 'pageMap.php';

$breadcrumbs = [];
$pageUrl = $currentPageUrl;

while ($pageUrl && isset($pagesUrl[$pageUrl])) {
    array_unshift($breadcrumbs, [
        'title' => $pagesUrl[$pageUrl]['title'],
        'url'   => $pageUrl
    ]);
    $pageUrl = $pagesUrl[$pageUrl]['parent'];
}

// Default pageTitle fallback
$pageTitle = !empty($breadcrumbs) ? end($breadcrumbs)['title'] : 'Dashboard';
