<?php
/**
 * Get user prediction history and last test record
 *
 * This file retrieves the prediction history and the last test record for a specific user from the database
 */

// Include database connection - Assuming connection.php is in the same directory
require_once __DIR__ . '/connection.php';

/**
 * Fetches prediction history for a specific user
 *
 * @param int $userId The ID of the user whose prediction history to retrieve
 * @return array|false An array of prediction history records on success, or false on error
 */
function getUserPredictionHistory($userId) {
    $db = getDbConnection(); // Assuming getDbConnection() is defined in connection.php

    // Prepare the SQL query
    // Using prepared statement to prevent SQL injection
    $sql = "SELECT * FROM user_prediction_history WHERE user_id = ? ORDER BY created_at DESC";

    // Prepare and execute the statement
    $stmt = $db->prepare($sql);
    // Check if prepare was successful
    if ($stmt === false) {
        error_log("MySQL prepare error in getUserPredictionHistory: " . $db->error);
        // Close the database connection before returning false on error
        $db->close();
        return false;
    }

    $stmt->bind_param("i", $userId);
    $execute_success = $stmt->execute();

    // Check if execution was successful
    if ($execute_success === false) {
         error_log("MySQL execute error in getUserPredictionHistory: " . $stmt->error);
         $stmt->close();
         $db->close();
         return false;
    }


    $result = $stmt->get_result();

    // Initialize an empty array to store the prediction history
    $predictionHistory = [];

    // Fetch all records and add them to the array
    while ($row = $result->fetch_assoc()) {
        // Format the data for display (adjust based on your needs)
        $details = sprintf(
            "Age: %d, BMI: %.1f, Sleep: %.1f hrs",
            $row['age'],
            $row['bmi'],
            $row['sleep_time']
        );

        // Determine risk level based on prediction result and confidence
        $riskLevel = 'Low Risk';
        $badge_class = 'badge-success'; // Default badge class
        if (isset($row['prediction_result'])) {
            if ($row['prediction_result'] == 1) {
                $riskLevel = 'High Risk';
                $badge_class = 'badge-danger';
            }
            // If you have a 'Medium Risk' category based on confidence, implement that logic here
            // Example:
            /*
            if (isset($row['prediction_confidence']) && $row['prediction_result'] == 1 && $row['prediction_confidence'] < 0.7) {
                 $riskLevel = 'Medium Risk';
                 $badge_class = 'badge-warning';
            }
            */
        }


        // Format the prediction confidence as a percentage
        $probability = 'N/A';
        if (isset($row['prediction_confidence'])) {
             $probability = htmlspecialchars(number_format($row['prediction_confidence'] * 100, 2) . '%');
        }


        // Add the formatted record to the array
        $predictionHistory[] = [
            'id' => $row['id'],
            'date' => date('Y-m-d H:i:s', strtotime($row['created_at'])), // Include time for more detail
            'result' => $riskLevel,
            'probability' => $probability,
            'details' => $details,
            // Add all raw data for detailed view if needed
            'raw_data' => $row
        ];
    }

    // Close the statement and database connection
    $stmt->close();
    $db->close();

    return $predictionHistory;
}


/**
 * Fetches the user's last test record from the database.
 *
 * @param int $userId The ID of the user.
 * @return array|false The last test record as an associative array, or false if not found or on error.
 */
function getLastTestRecord($userId) {
    $db = getDbConnection(); // Assuming getDbConnection() is defined in connection.php

    // Using prepared statement to prevent SQL injection
    // Join user_last_test_record with user_prediction_history to get the full record
    $query = "SELECT ph.* FROM user_last_test_record ultr
              JOIN user_prediction_history ph ON ultr.prediction_history_id = ph.id
              WHERE ultr.user_id = ?";

    $stmt = $db->prepare($query);
    // Check if prepare was successful
    if ($stmt === false) {
        error_log("MySQL prepare error in getLastTestRecord: " . $db->error);
        // Close the database connection before returning false on error
        $db->close();
        return false;
    }

    // Bind parameter and execute
    $stmt->bind_param("i", $userId);
    $execute_success = $stmt->execute();

     // Check if execution was successful
    if ($execute_success === false) {
         error_log("MySQL execute error in getLastTestRecord: " . $stmt->error);
         $stmt->close();
         $db->close();
         return false;
    }


    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        $db->close(); // Close the database connection
        return false; // No record found
    }

    $lastRecord = $result->fetch_assoc();
    $stmt->close();
    $db->close(); // Close the database connection

    return $lastRecord;
}
