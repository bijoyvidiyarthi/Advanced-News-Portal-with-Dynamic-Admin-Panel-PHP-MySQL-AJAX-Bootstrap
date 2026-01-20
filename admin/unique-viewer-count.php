<?php
session_start();
$post_id = $_GET['id'];

// চেক করা হচ্ছে এই সেশনে এই নিউজটি আগে পড়া হয়েছে কি না
if (!isset($_SESSION['viewed_post_' . $post_id])) {
    // যদি আগে না পড়ে থাকে, তবেই ভিউ ১ বাড়বে
    $update_view = "UPDATE posts SET viewCount = viewCount + 1 WHERE id = $post_id";
    mysqli_query($conn, $update_view);

    // সেশনে মার্ক করে রাখা হলো যে সে এই নিউজটি দেখে ফেলেছে
    $_SESSION['viewed_post_' . $post_id] = true;
}