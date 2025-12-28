<?php

// 1. Start session globally
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. The Autoloader: Automatically loads classes when you call 'new ClassName()'
spl_autoload_register(function ($class_name) {
    // Path to your classes folder
    $path = __DIR__ . "/classes/" . $class_name . ".php";

    if (file_exists($path)) {
        require_once $path;
    } else {
        // Log error if file is missing
        error_log("Class file not found: " . $path);
    }

    
});

/**
 * Global Helper Shortcut
 * This can access Database::db() because it's defined here 
 * AFTER the autoloader is registered.
 */
/**
 * @return QueryBuilder
 */
function db() {
    return \Database::db();
}

// 3. Global Constants (Optional but professional)
define('BASE_URL', 'http://localhost/PHP%20Basic%202025/news-template/news-template/');
?>