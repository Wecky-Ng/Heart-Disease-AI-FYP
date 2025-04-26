<?php
// Define PROJECT_ROOT if it hasn't been defined (for local development when not routed through api/index.php)
if (!defined('PROJECT_ROOT')) {
    // Assuming home.php is at the project root
    define('PROJECT_ROOT', __DIR__);
    // If home.php is in a subdirectory, adjust __DIR__ accordingly, e.g., dirname(__DIR__)
}
// Include session management
require_once PROJECT_ROOT . '/session.php';

// Destroy the session
session_unset();
session_destroy();

// Redirect to login page
header('Location: login.php');
exit();
?>