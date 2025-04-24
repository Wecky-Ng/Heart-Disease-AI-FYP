<?php
/**
 * Database Connection File
 * 
 * This file establishes a secure connection to the MySQL database
 * using mysqli with proper error handling.
 */

// Load environment variables from .env file
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Database configuration from environment variables
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'heart_disease_db');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

// Create a mysqli connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check for connection errors
if ($mysqli->connect_error) {
    die('Database Connection Error: ' . $mysqli->connect_error);
}

// Set charset
$mysqli->set_charset(DB_CHARSET);

// Store the connection in a global variable
$GLOBALS['db'] = $mysqli;

/**
 * Get database connection
 * 
 * @return mysqli Database connection object
 */
function getDbConnection() {
    return $GLOBALS['db'];
}