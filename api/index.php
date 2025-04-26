<?php
// api/index.php - Acts as a router for root PHP files

// Get the requested path from the original request URI
$requestUri = $_SERVER['REQUEST_URI'];

// Remove leading/trailing slashes and query string
$path = trim(parse_url($requestUri, PHP_URL_PATH), '/');

// Default file if no path is specified or if it's just '/'
if (empty($path)) {
    $path = 'home.php'; // Default to home.php (or your actual homepage file)
}

// Construct the target file path relative to the directory where this script runs
// The build script copies root PHP files here.
$targetFile = __DIR__ . '/' . $path;

// Security: Basic check to prevent directory traversal.
// Real applications might need more robust checks.
if (strpos($path, '..') !== false || strpos($path, './') !== false) {
    http_response_code(400); // Bad Request
    echo 'Invalid path requested.';
    exit;
}

// Check if the target file exists (e.g., /about -> about.php)
if (is_file($targetFile) && pathinfo($targetFile, PATHINFO_EXTENSION) === 'php') {
    require $targetFile;
} 
// Check if a file with .php extension exists (e.g. /about -> about.php)
else if (is_file($targetFile . '.php')) {
    require $targetFile . '.php';
}
else {
    // File not found
    http_response_code(404);
    // Check if a custom 404 page exists
    if (is_file(__DIR__ . '/404.php')) {
        require __DIR__ . '/404.php';
    } else {
        echo 'Page not found.';
    }
}

?>