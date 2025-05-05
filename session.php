<?php
// Check if session is already active before configuring settings
if (session_status() == PHP_SESSION_NONE) {
    // Configure PHP session settings
    ini_set('session.gc_maxlifetime', 3600); // Set session timeout to 1 hour (in seconds)
    ini_set('session.cookie_lifetime', 0); // Session cookie expires when browser closes
    
    // Start the session after configuring settings
    session_start();
}

// Define session timeout (in seconds)
define('SESSION_TIMEOUT', 3600); // 1 hour

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to set user session after successful login
function setUserSession($userId, $username, $email, $userRole = 'User') {
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['user_role'] = $userRole;
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
}

// Function to get current user information
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'user_id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? 'Guest User',
        'email' => $_SESSION['email'] ?? 'Guest Mode',
        'user_role' => $_SESSION['user_role'] ?? 'Guest',
        'login_time' => $_SESSION['login_time'] ?? null
    ];
}

// Function to end user session (logout)
function endUserSession() {
    // Unset all session variables
    $_SESSION = [];
    
    // If it's desired to kill the session, also delete the session cookie.
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Finally, destroy the session.
    session_destroy();
}

// Function to check if session needs refresh and refresh it
function checkAndRefreshSession() {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Check if last_activity is set
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    // Calculate time since last activity
    $currentTime = time();
    $lastActivity = $_SESSION['last_activity'];
    $timeSinceLastActivity = $currentTime - $lastActivity;
    
    // If user has been active recently, update last_activity
    if ($timeSinceLastActivity < SESSION_TIMEOUT) {
        $_SESSION['last_activity'] = $currentTime;
        return true;
    }
    
    // Session has expired
    endUserSession();
    return false;
}

// Function to get remaining session time in seconds
function getRemainingSessionTime() {
    if (!isLoggedIn() || !isset($_SESSION['last_activity'])) {
        return 0;
    }
    
    $currentTime = time();
    $lastActivity = $_SESSION['last_activity'];
    $elapsedTime = $currentTime - $lastActivity;
    
    $remainingTime = SESSION_TIMEOUT - $elapsedTime;
    return ($remainingTime > 0) ? $remainingTime : 0;
}

// AJAX endpoint to refresh session
if (isset($_GET['refresh_session']) && $_GET['refresh_session'] === 'true') {
    // Only process AJAX requests
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        if (isLoggedIn()) {
            $_SESSION['last_activity'] = time();
            echo json_encode(['success' => true, 'remaining' => getRemainingSessionTime()]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
        }
        exit;
    }
}

// For demonstration purposes - if no user is logged in, set as guest
if (!isLoggedIn()) {
    // This is just a placeholder for guest users
    // In a real application, you would redirect to login page or show limited content
    $_SESSION['username'] = 'Guest User';
    $_SESSION['email'] = 'Guest Mode';
    $_SESSION['user_role'] = 'Guest';
}

// Check and refresh session for logged-in users
if (isLoggedIn() && !isset($_GET['refresh_session'])) {
    checkAndRefreshSession();
}
?>