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
        // Connection is managed globally, do not close here
        // $db->close();
        return false;
    }

    $stmt->bind_param("i", $userId);
    $execute_success = $stmt->execute();

    // Check if execution was successful
    if ($execute_success === false) {
         error_log("MySQL execute error in getUserPredictionHistory: " . $stmt->error);
         $stmt->close();
         // Connection is managed globally, do not close here
         // $db->close();
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
            'created_at' => $row['created_at'], // Pass the original created_at timestamp
            'prediction_result' => $row['prediction_result'], // Pass the original prediction result
            'prediction_confidence' => $row['prediction_confidence'], // Pass the original confidence value
            // Include all individual fields directly for easier access in the history.php
            'age' => $row['age'],
            'sex' => $row['sex'],
            'bmi' => $row['bmi'],
            'smoking' => $row['smoking'],
            'alcohol_drinking' => $row['alcohol_drinking'],
            'stroke' => $row['stroke'],
            'physical_health' => $row['physical_health'],
            'mental_health' => $row['mental_health'],
            'diff_walking' => $row['diff_walking'],
            'race' => $row['race'],
            'diabetic' => $row['diabetic'],
            'physical_activity' => $row['physical_activity'],
            'gen_health' => $row['gen_health'],
            'sleep_time' => $row['sleep_time'],
            'asthma' => $row['asthma'],
            'kidney_disease' => $row['kidney_disease'],
            'skin_cancer' => $row['skin_cancer']
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
    // Fetch the latest record directly from user_prediction_history
    $query = "SELECT ph.* FROM user_prediction_history ph
              WHERE ph.user_id = ?
              ORDER BY ph.created_at DESC
              LIMIT 1";

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

    // Return the record directly - no need for raw_data nesting
    // This allows direct access to fields in the form prefill code
    return $lastRecord;
}

/**
 * Fetch a specific prediction history record by its ID for a specific user.
 *
 * @param int $recordId The ID of the prediction history record to fetch.
 * @param int $userId The ID of the user who owns the record.
 * @return array|null An associative array containing the record details if found and owned by the user, null otherwise.
 */
function getPredictionRecordById($recordId, $userId) {
    $db = getDbConnection();
    if (!$db) {
        error_log("Database connection failed in getPredictionRecordById");
        return null;
    }

    // Prepare the SQL query to fetch the specific record, ensuring it belongs to the user
    $sql = "SELECT * FROM user_prediction_history WHERE id = ? AND user_id = ?";

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        error_log("MySQL prepare error in getPredictionRecordById: " . $db->error);
        $db->close();
        return null;
    }

    $stmt->bind_param("ii", $recordId, $userId);
    $execute_success = $stmt->execute();

    if ($execute_success === false) {
        error_log("MySQL execute error in getPredictionRecordById: " . $stmt->error);
        $stmt->close();
        $db->close();
        return null;
    }

    $result = $stmt->get_result();
    $record = $result->fetch_assoc(); // Fetch the single record

    $stmt->close();
    $db->close();

    if ($record) {
        // Format the result and probability for display consistency
        $record['result'] = ($record['prediction_result'] == 1) ? 'High Risk' : 'Low Risk';
        $record['probability'] = round($record['prediction_confidence'] * 100, 2) . '%';
        // You might want to format the date here as well if needed
        // $record['date'] = date("Y-m-d H:i:s", strtotime($record['prediction_time']));
    }

    return $record; // Returns the record array or null if not found/not owned
}

?>
