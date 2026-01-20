<?php
$hostname = "localhost";
$username = "root";
$password = "";
$database = "news-site";

// --- 30 days Session Logic ---
$session_lifetime = 30 * 24 * 60 * 60;

// session path
$session_save_path = __DIR__ . '/sessions';

// if not find, than create a folder
if (!is_dir($session_save_path)) {
    mkdir($session_save_path, 0777, true);
    // security
    file_put_contents($session_save_path . '/.htaccess', "Deny from all");
}

// settings befor session start
ini_set('session.gc_maxlifetime', $session_lifetime);
ini_set('session.cookie_lifetime', $session_lifetime);
session_save_path($session_save_path);

// cookie parametere
session_set_cookie_params([
    'lifetime' => $session_lifetime,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = mysqli_connect($hostname, $username, $password, $database) or die("Connection Failed");

define('BASE_URL', 'http://localhost/PHP%20Basic%202025/news-template/news-template/admin/');
date_default_timezone_set('Asia/Dhaka');
?>