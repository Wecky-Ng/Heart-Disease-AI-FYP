<?php
// Include this file in all pages that need session management
// It should be included after session.php is included

// Only output the session manager script for logged-in users
if (function_exists('isLoggedIn') && isLoggedIn()) {
    // Check if the js directory exists, create it if not
    $jsDir = __DIR__ . '/../js';
    if (!file_exists($jsDir)) {
        mkdir($jsDir, 0755, true);
    }
    
    // Output the session timeout configuration for JavaScript
    echo "<script>
    // Session configuration
    const SESSION_TIMEOUT = " . SESSION_TIMEOUT . "; // in seconds
    const isLoggedIn = true;
    </script>";
    
    // Include the session manager JavaScript
    echo "<script src='js/session_manager.js'></script>";
}
?>