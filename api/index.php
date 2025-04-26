<?php
// api/index.php - Router for Vercel deployment

// Get the requested URI
$uri = $_SERVER['REQUEST_URI'];

// Remove query string
$uri = strtok($uri, '?');

// Remove trailing slash if it exists
$uri = rtrim($uri, '/');

// Default to home.php if the URI is empty or root
if ($uri == '' || $uri == '/') {
    $uri = '/home.php';
}

// Check if the file exists in the public directory
$file_path = __DIR__ . '/../public' . $uri;

if (file_exists($file_path)) {
    // Include the file
    include $file_path;
} else {
    // Check if it's a PHP file without the .php extension
    $php_file_path = $file_path . '.php';
    
    if (file_exists($php_file_path)) {
        include $php_file_path;
    } else {
        // File not found, return 404
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        echo "<p>The requested file could not be found.</p>";
    }
}