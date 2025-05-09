<?php
// Define PROJECT_ROOT if it hasn't been defined
// Ensure there are NO blank lines or whitespace BEFORE this opening <?php tag.
if (!defined('PROJECT_ROOT')) {
    // Assuming save_prediction.php is in the root
    // Adjust dirname(__DIR__) if save_prediction.php is in a subdirectory
    define('PROJECT_ROOT', dirname(__DIR__));
}

// Turn off PHP error display - errors will be logged but not displayed
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once PROJECT_ROOT . '/session.php';
require_once PROJECT_ROOT . '/database/set_user_prediction_record.php'; // Ensure this file has no leading/trailing whitespace

// Set the content type header for JSON response
header('Content-Type: application/json');

// Function to return JSON error response
function returnJsonError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

// Check if user is logged in
if (!isLoggedIn()) {
    returnJsonError('User not logged in.', 401);
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    returnJsonError('Invalid request method.', 405);
}

// Get the raw POST data
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

// Basic validation of received data
if (!$data) {
    returnJsonError('Invalid JSON data received.');
}

if (!isset($data['inputs']) || !isset($data['prediction']) || !isset($data['confidence'])) {
    returnJsonError('Missing required data fields.');
}

$userId = $_SESSION['user_id'] ?? null;
$inputs = $data['inputs'];
$prediction = (int)$data['prediction'];
$confidence = (float)$data['confidence'];

if (!$userId) {
    // This should technically be caught by isLoggedIn(), but double-check
    returnJsonError('User session error.', 401);
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
        returnJsonError("Missing required data: {$column}.");
    }
}

// Call function to save the history record
$historyId = savePredictionHistory($userId, $dbData, $prediction, $confidence);

if ($historyId) {
    // The last test record is now derived directly from history, no separate update needed.
    echo json_encode(['success' => true, 'message' => 'Prediction saved successfully.']);
} else {
    // Log the specific database error if possible from savePredictionHistory
    error_log("Failed to save prediction history for user {$userId}.");
    // Include more detailed error information in the logs
    error_log("Payload data: " . json_encode($data));
    error_log("Processed DB data: " . json_encode($dbData));
    returnJsonError('Failed to save prediction history. Please check server logs for details.', 500);
}
?>
