<?php
// Start the session
session_start();

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
}

// Function to get current user information
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'user_id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? 'Guest User',
        'email' => $_SESSION['email'] ?? 'guest@example.com',
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

// For demonstration purposes - if no user is logged in, set as guest
if (!isLoggedIn()) {
    // This is just a placeholder for guest users
    // In a real application, you would redirect to login page or show limited content
    $_SESSION['username'] = 'Guest User';
    $_SESSION['email'] = 'guest@example.com';
    $_SESSION['user_role'] = 'Guest';
}
?>