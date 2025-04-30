<?php
// Define PROJECT_ROOT if it hasn't been defined
// Ensure there are NO blank lines or whitespace BEFORE this opening <?php tag.
if (!defined('PROJECT_ROOT')) {
    // Assuming save_prediction.php is in the root
    // Adjust dirname(__DIR__) if save_prediction.php is in a subdirectory
    define('PROJECT_ROOT', dirname(__DIR__));
}

require_once PROJECT_ROOT . '/session.php';
require_once PROJECT_ROOT . '/database/connection.php'; // Ensure this file has no leading/trailing whitespace
require_once PROJECT_ROOT . '/database/set_user_prediction_record.php'; // Ensure this file has no leading/trailing whitespace

// Set the content type header for JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit(); // Stop execution after sending response
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit(); // Stop execution after sending response
}

// Get the raw POST data
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

// Basic validation of received data
if (!$data || !isset($data['inputs']) || !isset($data['prediction']) || !isset($data['confidence'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing data.']);
    exit(); // Stop execution after sending response
}

$userId = $_SESSION['user_id'] ?? null;
$inputs = $data['inputs'];
$prediction = (int)$data['prediction'];
$confidence = (float)$data['confidence'];

if (!$userId) {
    // This should technically be caught by isLoggedIn(), but double-check
    echo json_encode(['success' => false, 'message' => 'User session error.']);
    exit(); // Stop execution after sending response
}

// Get database connection
// Corrected function call from connectToDatabase() to getDbConnection()
$conn = getDbConnection();
if (!$conn) {
    error_log("Database connection failed in save_prediction.php");
    echo json_encode(['success' => false, 'message' => 'Database connection error.']);
    exit(); // Stop execution after sending response
}

// Prepare data for saving: Extract only the columns expected by user_prediction_history
// This prevents errors if $inputs contains extra fields like 'save_record'
$expected_columns = [
    'bmi', 'smoking', 'alcohol_drinking', 'stroke', 'physical_health',
    'mental_health', 'diff_walking', 'sex', 'age', 'race', 'diabetic',
    'physical_activity', 'gen_health', 'sleep_time', 'asthma', 'kidney_disease',
    'skin_cancer'
];

$dbData = [];
foreach ($expected_columns as $column) {
    if (isset($inputs[$column])) {
        // Basic type casting based on schema (adjust if needed)
        if (in_array($column, ['bmi', 'physical_health', 'mental_health', 'sleep_time'])) {
            $dbData[$column] = (float)$inputs[$column];
        } elseif (in_array($column, ['age'])) {
            $dbData[$column] = (int)$inputs[$column];
        } else {
            // Assume tinyint/int for others based on schema
            $dbData[$column] = (int)$inputs[$column];
        }
    } else {
        // Handle missing expected data - log error and potentially stop
        error_log("Missing expected input column '{$column}' in save_prediction.php for user {$userId}.");
        echo json_encode(['success' => false, 'message' => "Missing required data: {$column}."]);
        $conn->close();
        exit();
    }
}

// Call function to save the history record
$historyId = savePredictionHistory($conn, $userId, $dbData, $prediction, $confidence);

if ($historyId) {
    // The last test record is now derived directly from history, no separate update needed.
    echo json_encode(['success' => true, 'message' => 'Prediction saved successfully.']);
} else {
    // Log the specific database error if possible from savePredictionHistory
    error_log("Failed to save prediction history for user {$userId}.");
    echo json_encode(['success' => false, 'message' => 'Failed to save prediction history.']);
}

// Close the database connection
$conn->close();

// Ensure there are NO blank lines or whitespace AFTER this closing ?> tag (or omit the tag).
?>
