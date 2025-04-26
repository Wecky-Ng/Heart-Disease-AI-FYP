// Vercel Serverless Function Handler (api/index.php)
// This script routes requests within the Vercel function environment.

// Set default content type
header('Content-Type: text/html; charset=UTF-8');

// Get the requested path relative to the domain root
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Determine the target PHP file based on the path
// Note: Static assets should be handled by Vercel's routing (`filesystem` handle)

// Default to home.php if path is empty or root
if (empty($path) || $path === '/') {
    $target_file = 'home.php';
} else {
    // Remove leading slash
    $target_file = ltrim($path, '/');

    // If the path doesn't have an extension, assume .php
    if (!pathinfo($target_file, PATHINFO_EXTENSION)) {
        $target_file .= '.php';
    }
}

// Construct the full path to the target file within the function's directory
// Assumes the build script copies necessary files (like home.php, login.php, etc.)
// into the function's root directory alongside this handler (index.php).
$file_path = __DIR__ . '/' . $target_file;

// Include the target file if it exists, otherwise fallback to home.php
if (file_exists($file_path)) {
    require $file_path;
} else {
    // Log missing file for debugging (optional)
    // error_log("Target file not found: " . $file_path . " | Falling back to home.php");
    
    // Fallback to home.php if the specific file doesn't exist
    $fallback_path = __DIR__ . '/home.php';
    if (file_exists($fallback_path)) {
        require $fallback_path;
    } else {
        // Critical error: home.php is missing
        http_response_code(500);
        echo "Error: Application files not found.";
        error_log("Critical Error: home.php not found at " . $fallback_path);
    }
}