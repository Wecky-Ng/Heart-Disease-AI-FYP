<?php
/**
 * Database Connection File
 * 
 * This file establishes a secure connection to the MySQL database
 * using PDO with proper error handling.
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

// Connection options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// DSN (Data Source Name)
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

// Create a PDO instance (connect to the database)
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    // Set the connection as a global variable
    $GLOBALS['db'] = $pdo;
} catch (PDOException $e) {
    // Handle connection error
    die('Database Connection Error: ' . $e->getMessage());
}

/**
 * Get database connection
 * 
 * @return PDO Database connection object
 */
function getDbConnection() {
    return $GLOBALS['db'];
}