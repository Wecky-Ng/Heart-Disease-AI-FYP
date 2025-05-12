<?php
// Define PROJECT_ROOT if it hasn't been defined
// Ensure there are NO blank lines or whitespace BEFORE this opening <?php tag.
if (!defined('PROJECT_ROOT')) {
    // Assuming save_prediction.php is in the root
    // Adjust dirname(__DIR__) if save_prediction.php is in a subdirectory
    define('PROJECT_ROOT', dirname(__DIR__));
}

// Turn on error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Keep errors hidden from response but log them

// Function to log to console (client-side) for debugging
function console_log($message) {
    // Add a header that can be read in the browser console
    header('X-Debug-Info: ' . $message);
    // Also log to server error log
    error_log($message);
}

// Function to safely convert values to proper types
function safeConvert($value, $type) {
    try {
        switch ($type) {
            case 'int':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'bool':
                return (bool)$value;
            default:
                return $value;
        }
    } catch (Exception $e) {
        error_log("Conversion error for value {$value} to {$type}: " . $e->getMessage());
        return null;
    }
}

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

// Safely convert prediction and confidence values
$prediction = safeConvert($data['prediction'], 'int');
$confidence = safeConvert($data['confidence'], 'float');

// Log the processed values
console_log('User ID: ' . $userId);
console_log('Prediction after casting: ' . $prediction . ' (type: ' . gettype($prediction) . ', original: ' . $data['prediction'] . ')');
console_log('Confidence after casting: ' . $confidence . ' (type: ' . gettype($confidence) . ', original: ' . $data['confidence'] . ')');

// Verify critical values
if ($prediction === null || $confidence === null) {
    console_log('ERROR: Critical values could not be converted properly');
    returnJsonError('Data conversion error. Please check your input values.', 400);
}

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

console_log('Processing input fields. Available keys: ' . json_encode(array_keys($inputs)));

$dbData = [];
foreach ($expected_columns as $column) {
    if (isset($inputs[$column])) {
        // Log the raw value before casting
        console_log("Field '{$column}' raw value: " . $inputs[$column] . " (type: " . gettype($inputs[$column]) . ")");
        
        // Basic type casting based on schema (adjust if needed)
        if (in_array($column, ['bmi', 'physical_health', 'mental_health', 'sleep_time'])) {
            $dbData[$column] = safeConvert($inputs[$column], 'float');
            console_log("Field '{$column}' cast to float: " . $dbData[$column] . " (original: {$inputs[$column]})");
        } elseif (in_array($column, ['age'])) {
            $dbData[$column] = safeConvert($inputs[$column], 'int');
            console_log("Field '{$column}' cast to int: " . $dbData[$column] . " (original: {$inputs[$column]})");
        } else {
            // Assume tinyint/int for others based on schema
            $dbData[$column] = safeConvert($inputs[$column], 'int');
            console_log("Field '{$column}' cast to int: " . $dbData[$column] . " (original: {$inputs[$column]})");
        }
        
        // Verify the value was converted properly
        if ($dbData[$column] === null) {
            console_log("WARNING: Field '{$column}' could not be properly converted. Using default value.");
            // Provide safe defaults based on column type
            if (in_array($column, ['bmi', 'physical_health', 'mental_health', 'sleep_time'])) {
                $dbData[$column] = 0.0; // Default for float fields
            } else {
                $dbData[$column] = 0; // Default for int fields
            }
        }
    } else {
        // Handle missing expected data - log error and potentially stop
        console_log("Missing expected input column '{$column}' in save_prediction.php for user {$userId}.");
        returnJsonError("Missing required data: {$column}.");
    }
}

// Verify database connection before attempting to save
try {
    require_once PROJECT_ROOT . '/database/connection.php';
    $db = getDbConnection();
    if (!$db) {
        console_log("Database connection failed in save_prediction.php");
        returnJsonError('Database connection failed. Please try again later.', 500);
    }
    
    // Test the connection with a simple query
    $testResult = $db->query("SELECT 1");
    if (!$testResult) {
        console_log("Database connection test failed: " . $db->error);
        returnJsonError('Database connection test failed: ' . $db->error, 500);
    }
    console_log("Database connection successful and verified");
} catch (Exception $e) {
    console_log("Exception connecting to database: " . $e->getMessage());
    returnJsonError('Database connection exception: ' . $e->getMessage(), 500);
}

// Log the final processed data before saving
console_log('Final processed data: ' . json_encode($dbData));
console_log('About to call savePredictionHistory with userId=' . $userId . ', prediction=' . $prediction . ', confidence=' . $confidence);

// Call function to save the history record
try {
    console_log('Calling savePredictionHistory with properly typed data');
    
    // Ensure we have a valid database connection before proceeding
    if (!isset($db) || !$db) {
        console_log('Database connection is not valid before calling savePredictionHistory');
        returnJsonError('Database connection error. Please try again later.', 500);
    }
    
    // Log data types for debugging
    console_log('userId type: ' . gettype($userId) . ', value: ' . $userId);
    console_log('prediction type: ' . gettype($prediction) . ', value: ' . $prediction);
    console_log('confidence type: ' . gettype($confidence) . ', value: ' . $confidence);
    
    $historyId = savePredictionHistory($userId, $dbData, $prediction, $confidence);
    
    console_log('savePredictionHistory result: ' . ($historyId ? 'Success, ID: ' . $historyId : 'Failed'));
    
    if (!$historyId) {
        // If savePredictionHistory returned false, there was an error
        console_log('savePredictionHistory returned false - database error occurred');
        returnJsonError('Failed to save prediction history. Database error occurred.', 500);
    }
} catch (Throwable $e) {
    // Catch both Exception and Error with Throwable
    console_log('Exception in savePredictionHistory call: ' . $e->getMessage());
    console_log('Exception trace: ' . $e->getTraceAsString());
    returnJsonError('Exception saving prediction: ' . $e->getMessage(), 500);
}

if ($historyId) {
    // The last test record is now derived directly from history, no separate update needed.
    $response = [
        'success' => true,
        'message' => 'Prediction saved successfully.',
        'history_id' => $historyId
    ];
    
    console_log('Sending success response: ' . json_encode($response));
    echo json_encode($response);
} else {
    // This should not be reached due to the check in the try block above
    // But keep as a fallback
    console_log('Reached fallback error handler - historyId is false');
    returnJsonError('Failed to save prediction history. Please try again later.', 500);
}

// Ensure we always exit properly
exit();
?>
