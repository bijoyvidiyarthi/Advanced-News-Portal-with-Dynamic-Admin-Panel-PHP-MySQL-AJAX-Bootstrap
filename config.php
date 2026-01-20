<?php
//if user is not admin restrict access
//restrict access to admin users only
// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
//     // If the user is not an admin, redirect to post.php or another appropriate page
//     header("Location: post.php");
// }
//if admin proceed to connect to database
$hostname = "http://localhost/PHP%20Basic%202025/news-template/news-template/";
$servername = "localhost";
$username = "root";
$password = "";
$database = "news-site";

$conn = mysqli_connect($servername, $username, $password, $database) or die("Connection Failed");

// FORCE UTF-8 (Bangla support)
mysqli_set_charset($conn, "utf8mb4");

?>