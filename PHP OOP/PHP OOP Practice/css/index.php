<?php 
//to restrict direct access to this upload folder
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//redirect to an Error Page 
 header("Location: ../error-page.php");
exit();

?>