<?php
/**
 * Database Connection File
 *
 * This file establishes a secure connection to the MySQL database
 * using mysqli with proper error handling.
 */

// Define the project root directory if it hasn't been defined already
// This is a fallback in case connection.php is accessed in a way
// that doesn't go through api/index.php where PROJECT_ROOT is defined.
// However, with the vercel.json routing, it should be defined.
if (!defined('PROJECT_ROOT')) {
    // Assuming connection.php is in database/ and project root is one level up
    define('PROJECT_ROOT', dirname(__DIR__));
}


// Load environment variables from .env file ONLY if not in a production-like environment
// where variables are set by the hosting platform (like Vercel).
// We check for a Vercel-specific environment variable (VERCEL) or check if
// a key variable like DB_HOST is already set in the environment.
if (!isset($_ENV['VERCEL']) && !isset($_SERVER['VERCEL']) && !isset($_ENV['DB_HOST'])) {
    // Include the Composer autoloader using the defined PROJECT_ROOT.
    // This is needed for Dotenv to be available.
    // This require_once is now inside the conditional check.
    require_once PROJECT_ROOT . '/vendor/autoload.php';

    // Load environment variables from the .env file located at the project root.
    // The path is relative to the location of connection.php (database/).
    $dotenv = Dotenv\Dotenv::createImmutable(PROJECT_ROOT); // Use PROJECT_ROOT directly
    $dotenv->load();
}


// Database configuration from environment variables
// Use $_ENV or getenv() to access variables set by Vercel or loaded from .env
define('DB_HOST', $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'heart_disease_db');
define('DB_USER', $_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? getenv('DB_CHARSET') ?? 'utf8mb4');

// Create a mysqli connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check for connection errors
if ($mysqli->connect_error) {
    // Log the error for debugging on the server
    error_log("Database Connection Error: " . $mysqli->connect_error);
    // Die with a generic message to avoid exposing sensitive details
    die('Database Connection Error.');
}

// Set charset
$mysqli->set_charset(DB_CHARSET);

// Store the connection in a global variable (optional, but matches your original code)
$GLOBALS['db'] = $mysqli;

/**
 * Get database connection
 *
 * @return mysqli Database connection object
 */
function getDbConnection() {
    // Ensure the connection is initialized if this function is called directly
    // without the main script flow that initializes $mysqli.
    // This might require moving the $mysqli initialization outside the function
    // or adding initialization logic here if $GLOBALS['db'] is not set.
    // Based on your original code, $mysqli is initialized globally, so this is fine.
    if (!isset($GLOBALS['db'])) {
         // This case should ideally not happen with the current structure
         // where connection.php is required early.
         // You might add error handling or re-initialization here if needed.
         error_log("Database connection not initialized before getDbConnection call.");
         die('Database connection not available.');
    }
    return $GLOBALS['db'];
}
