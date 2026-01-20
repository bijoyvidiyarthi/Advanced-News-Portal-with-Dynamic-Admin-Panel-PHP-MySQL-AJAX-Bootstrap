<?php
require_once __DIR__ . '/../config.php';

// Redirect if Not logged in
if (!isset($_SESSION["is_logged_in"]) || $_SESSION["is_logged_in"] !== true) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// session-timeout check
$expire_after = 30 * 24 * 60 * 60;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] >= $expire_after)) {

    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$_SESSION['last_activity'] = time();
?>