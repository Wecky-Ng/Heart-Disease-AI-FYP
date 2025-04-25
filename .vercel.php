<?php
// Vercel PHP configuration file
// This file helps ensure proper PHP execution on Vercel

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
    // Fallback to home.php
    include __DIR__ . '/home.php';
}