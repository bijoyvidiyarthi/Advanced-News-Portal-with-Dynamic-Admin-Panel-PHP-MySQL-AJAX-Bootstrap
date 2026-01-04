<?php 

include "config.php";

$user = 'sukhidey123';
$pass = 'admin123';
$hash = password_hash($pass, PASSWORD_BCRYPT);

// Force update the DB
$sql = "UPDATE `user` SET password = '$hash' WHERE username = '$user'";
if(mysqli_query($conn, $sql)) {
    echo "Database Updated! Now try logging in with: <br>User: admin <br>Pass: admin123";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>