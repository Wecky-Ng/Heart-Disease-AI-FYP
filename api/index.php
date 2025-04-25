<?php
// This file serves as the API entry point for Vercel
// Forward all requests to the appropriate PHP file

// Get the requested path
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove '/api' prefix if present
if (strpos($path, '/api') === 0) {
    $path = substr($path, 4);
}

// Handle static files (CSS, JS, images, fonts)
$static_extensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'ttf', 'woff', 'woff2'];
$path_info = pathinfo($path);
$extension = isset($path_info['extension']) ? strtolower($path_info['extension']) : '';

if (in_array($extension, $static_extensions)) {
    $file_path = __DIR__ . '/..' . $path;
    if (file_exists($file_path)) {
        // Set appropriate content type
        switch ($extension) {
            case 'css':
                header('Content-Type: text/css');
                break;
            case 'js':
                header('Content-Type: application/javascript');
                break;
            case 'jpg':
            case 'jpeg':
                header('Content-Type: image/jpeg');
                break;
            case 'png':
                header('Content-Type: image/png');
                break;
            case 'gif':
                header('Content-Type: image/gif');
                break;
            case 'svg':
                header('Content-Type: image/svg+xml');
                break;
            case 'ttf':
                header('Content-Type: font/ttf');
                break;
            case 'woff':
                header('Content-Type: font/woff');
                break;
            case 'woff2':
                header('Content-Type: font/woff2');
                break;
        }
        readfile($file_path);
        exit();
    }
}

// If path is empty or just '/', redirect to home
if (empty($path) || $path === '/') {
    require __DIR__ . '/../home.php';
    exit();
}

// Otherwise, include the requested PHP file
$file_path = __DIR__ . '/..' . $path;
if (file_exists($file_path)) {
    require $file_path;
} else {
    // Fallback to home if file doesn't exist
    require __DIR__ . '/../home.php';
}