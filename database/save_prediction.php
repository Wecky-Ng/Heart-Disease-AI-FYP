<?php
// Define PROJECT_ROOT if it hasn't been defined
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(__DIR__)); // Assuming save_prediction.php is in the root
}

require_once PROJECT_ROOT . '/session.php';
require_once PROJECT_ROOT . '/database/connection.php';
require_once PROJECT_ROOT . '/database/set_user_prediction_record.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

// Get the raw POST data
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

// Basic validation of received data
if (!$data || !isset($data['inputs']) || !isset($data['prediction']) || !isset($data['confidence'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing data.']);
    exit();
}

$userId = $_SESSION['user_id'] ?? null;
$inputs = $data['inputs'];
$prediction = (int)$data['prediction'];
$confidence = (float)$data['confidence'];

if (!$userId) {
    // This should technically be caught by isLoggedIn(), but double-check
    echo json_encode(['success' => false, 'message' => 'User session error.']);
    exit();
}

// Get database connection
$conn = connectToDatabase();
if (!$conn) {
    error_log("Database connection failed in save_prediction.php");
    echo json_encode(['success' => false, 'message' => 'Database connection error.']);
    exit();
}

// Prepare data for saving (ensure keys match savePredictionHistory expectations)
$dbData = $inputs; // Assuming $inputs already has the correct keys

// Call function to save the history record
$historyId = savePredictionHistory($conn, $userId, $dbData, $prediction, $confidence);

if ($historyId) {
    // The last test record is now derived directly from history, no separate update needed.
    echo json_encode(['success' => true, 'message' => 'Prediction saved successfully.']);
} else {
    error_log("Failed to save prediction history for user {$userId}.");
    echo json_encode(['success' => false, 'message' => 'Failed to save prediction history.']);
}

$conn->close();
?>