<?php
// Main entry point for Vercel serverless function

// Set content type for PHP files
header('Content-Type: text/html; charset=UTF-8');

// Get the requested URI
$uri = $_SERVER['REQUEST_URI'];

// Remove query string if present
$uri = strtok($uri, '?');

// If the URI ends with a slash, append index.php
if (substr($uri, -1) === '/') {
    $uri .= 'index.php';
}

// If no extension, assume it's a PHP file
if (!pathinfo($uri, PATHINFO_EXTENSION)) {
    $uri .= '.php';
}

// Check if the file exists
$file_path = __DIR__ . $uri;
if (file_exists($file_path)) {
    // Include the file
    include $file_path;
} else {
    // Check if we need to route to API
    if (strpos($uri, '/api/') === 0) {
        include __DIR__ . '/api/index.php';
    } else {
        // Fallback to home.php if it exists, otherwise show index
        if (file_exists(__DIR__ . '/home.php')) {
            include __DIR__ . '/home.php';
        } else if (file_exists(__DIR__ . '/index.html')) {
            include __DIR__ . '/index.html';
        } else {
            echo "<h1>Welcome to Heart Disease AI</h1>";
            echo "<p>Please check the configuration if you're seeing this page.</p>";
        }
    }
}