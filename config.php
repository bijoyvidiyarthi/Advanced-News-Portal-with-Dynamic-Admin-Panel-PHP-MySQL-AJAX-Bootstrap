<?php
//if user is not admin restrict access
//restrict access to admin users only
// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
//     // If the user is not an admin, redirect to post.php or another appropriate page
//     header("Location: post.php");
// }
//if admin proceed to connect to database
$hostname = "localhost";
$username = "root";
$password = "";
$database = "news-site";

$conn = mysqli_connect($hostname, $username, $password, $database) or die("Connection Failed");

?>