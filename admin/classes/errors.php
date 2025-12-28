<?php 
class errors
{
    /**
     * Handles error storage in sessions and redirects.
     * Use: errors::show($myErrors, "page.php");
     */
    public static function showError($errs, string $location)
    {
        $_SESSION['error'] = is_array($errs) ? implode("|||", $errs) : $errs;
        header("Location: $location");
        exit();
    }
}

