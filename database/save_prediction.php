<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and object files
include_once __DIR__ . '/database/db_connection.php';
include_once __DIR__ . '/database/set_user_prediction_record.php';

// Get database connection
$database = new Database();
$conn = $database->getConnection();

// Get posted data
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Check if data is not empty and contains required keys
if (!empty($data) && isset($data['inputs']) && isset($data['prediction']) && isset($data['confidence'])) {
    // Extract data
    $inputs = $data['inputs'];
    $prediction_result = $data['prediction'];
    $prediction_confidence = $data['confidence']; // Extract confidence

    // Call the function to set the user prediction record
    // Pass the confidence value to the function
    if (set_user_prediction_record($conn, $inputs, $prediction_result, $prediction_confidence)) {
        // Set response code - 201 Created
        http_response_code(201);
        // Tell the user
        echo json_encode(array("message" => "Prediction record was saved."));
    } else {
        // If unable to set the record
        // Set response code - 503 Service Unavailable
        http_response_code(503);
        // Tell the user
        echo json_encode(array("message" => "Unable to save prediction record."));
    }
} else {
    // Tell the user data is incomplete
    // Set response code - 400 Bad Request
    http_response_code(400);
    echo json_encode(array("message" => "Unable to save prediction record. Data is incomplete or invalid."));
}

// Close database connection
$conn = null;
?>
