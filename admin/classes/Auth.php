<?php 

// --- 5. Security Guard (Auth Layer) ---
class Auth
{
    /**
     * Shortcut to restrict access to Admins only.
     * Usage: Auth::adminOnly();
     */

    public static function adminAccess(string $redirect = "post.php")
    {
        // Check if session is started (safety check)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
            errors::showError("🚫 **Access Denied:** Admin permission required.", $redirect);
        }
    }
    /**
     * Optional: Shortcut to check if any user is logged in
     */
    public static function checkLogin(string $redirect = "index.php")
    {
        if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
            errors::showError("Please login to access this page.", $redirect);
        }
    }
}