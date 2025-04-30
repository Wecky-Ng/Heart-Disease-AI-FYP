<?php
// Ensure there are NO blank lines or whitespace BEFORE this opening <?php tag.

// Set the maximum lifetime of session data on the server
// This should generally be equal to or longer than the cookie lifetime
$session_gc_maxlifetime = 1800; // Set to 30 minutes

// Set session cookie parameters before starting the session
// Lifetime of the session cookie in seconds (30 minutes = 1800 seconds)
$session_cookie_lifetime = 1800;

// Set the session cookie parameters
// This must be called BEFORE session_start()
session_set_cookie_params([
    'lifetime' => $session_cookie_lifetime,
    'path' => '/', // Make the cookie available across the entire domain
    'domain' => '', // Use default domain (current domain)
    'secure' => true, // Only send cookie over HTTPS (important for Vercel)
    'httponly' => true, // Make cookie accessible only through HTTP headers
    'samesite' => 'Lax' // Recommended for modern browsers
]);

// Set the session garbage collection max lifetime
// This must be called BEFORE session_start()
ini_set('session.gc_maxlifetime', $session_gc_maxlifetime);

// Start the session if one hasn't been started already
// This should be called after session_set_cookie_params and ini_set
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    // Check if user_id is set and not empty in the session.
    // This relies on the session being active and not expired by PHP's GC or cookie lifetime.
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to set user session after successful login
function setUserSession($userId, $username, $email, $userRole = 'User') {
    // Ensure session is started before setting variables
     if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['user_role'] = $userRole;
    $_SESSION['login_time'] = time(); // Store login time (optional, but can be useful)
}

// Function to get current user information
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    // Return user data from session
    return [
        'user_id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? 'Guest User', // Provide defaults if session variables are missing
        'email' => $_SESSION['email'] ?? 'Guest Mode',
        'user_role' => $_SESSION['user_role'] ?? 'Guest',
        'login_time' => $_SESSION['login_time'] ?? null
    ];
}

// Function to end user session (logout)
function endUserSession() {
    // Ensure session is started before destroying
     if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Unset all session variables
    $_SESSION = [];

    // If it's desired to kill the session, also delete the session cookie.
    // This is the standard way to destroy the session cookie by setting its expiration in the past.
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, // Set expiration to a time in the past
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Finally, destroy the session data on the server.
    session_destroy();
}

// Note: The commented-out guest user logic below is generally not needed
// if you are handling authentication by checking isLoggedIn().
// Uncommenting this might overwrite session data unexpectedly if not careful.
/*
// For demonstration purposes - if no user is logged in, set as guest
if (!isLoggedIn()) {
    // This is just a placeholder for guest users
    // In a real application, you would redirect to login page or show limited content
    $_SESSION['username'] = 'Guest User';
    $_SESSION['email'] = 'guest@example.com';
    $_SESSION['user_role'] = 'Guest';
}
*/
?>
