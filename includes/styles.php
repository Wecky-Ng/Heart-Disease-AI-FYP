<?php
/**
 * Common stylesheet includes for Heart Disease AI FYP
 * This file centralizes all CSS imports for easier global management
 */
// Define PROJECT_ROOT if it hasn't been defined (for local development when not routed through api/index.php)
if (!defined('PROJECT_ROOT')) {
    // Assuming home.php is at the project root
    define('PROJECT_ROOT', __DIR__);
    // If home.php is in a subdirectory, adjust __DIR__ accordingly, e.g., dirname(__DIR__)
}
require_once PROJECT_ROOT . '/session.php';
?>
<!-- Stylesheets -->
<link rel="stylesheet" href="css/dashlite.css">
<link rel="stylesheet" href="css/theme.css">
<link rel="stylesheet" href="css/dashboard.css">
<link rel="stylesheet" href="css/sidemenu-fix.css">
<link rel="stylesheet" href="css/overlay-fix.css">
<link rel="stylesheet" href="css/customcss.css">