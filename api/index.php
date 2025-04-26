<?php
// This file serves as the single entry point for all PHP requests
// routed by the vercel.json configuration.

// Define the project root directory.
// __DIR__ is the directory of the current file (api/index.php).
// dirname(__DIR__) goes up one directory level from the current file's directory.
// If api/index.php is located in an 'api' subdirectory at the project root,
// dirname(__DIR__) correctly defines PROJECT_ROOT as the absolute path to the project root.
define('PROJECT_ROOT', dirname(__DIR__)); // Corrected definition of PROJECT_ROOT

// Include the Composer autoloader using the defined PROJECT_ROOT.
// This assumes your vendor directory is directly under the project root.
require_once PROJECT_ROOT . '/vendor/autoload.php'; // This line is now correct

// Basic routing logic based on the request URI.
// This determines which main application file to execute.
$request_uri = $_SERVER['REQUEST_URI'];

// Remove query string for routing purposes
$request_path = strtok($request_uri, '?');

// Simple routing: require the corresponding file based on the request path.
// You can expand this logic for more complex routing needs or integrate a framework router.
$target_file = '';
switch ($request_path) {
    case '/':
        // Route the root path to home.php
        $target_file = 'home.php';
        break;
    case '/home.php':
    case '/user_input_form.php':
    case '/result.php':
    case '/session.php':
        // Route direct requests for these top-level PHP files
        $target_file = ltrim($request_path, '/'); // Remove leading slash
        break;
    // Add cases for other top-level PHP files if you have them (e.g., /login.php, /register.php)

    // Note: Requests for files in subdirectories like /database/connection.php
    // are also routed here by vercel.json. However, these files are typically
    // included by other PHP files and not accessed directly via URL.
    // The requires within those files (using PROJECT_ROOT) will work correctly
    // once they are included by a file that was routed through api/index.php.
    // If you need to handle direct access to certain subdirectory PHP files,
    // you would add specific cases here.

    default:
        // If no specific route matches, return a 404 Not Found response.
        http_response_code(404);
        echo "404 Not Found";
        exit; // Stop execution
}

// Construct the full path to the target file
$target_file_path = PROJECT_ROOT . '/' . $target_file;

// Check if the target file exists before requiring it
if (file_exists($target_file_path)) {
    // Require the target file.
    // This is where the code from home.php (or other files) will be executed.
    // Since PROJECT_ROOT is defined here, it will be available in the required file.
    require $target_file_path;
} else {
    // If the target file doesn't exist, return a 404.
    http_response_code(404);
    echo "404 Not Found: " . htmlspecialchars($request_path); // Show requested path
}

?>
