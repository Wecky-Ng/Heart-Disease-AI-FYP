<?php
/**
 * Set user prediction record
 *
 * This file contains functions for saving prediction history and updating the last test record
 * for a logged-in user.
 */

require_once __DIR__ . '/connection.php';

/**
 * Saves the prediction details to the user_prediction_history table.
 *
 * @param mysqli|PDO $conn The database connection object.
 * @param int $userId The ID of the user.
 * @param array $data The processed input data from the form.
 * @param int $prediction The prediction result (0 or 1).
 * @param float $confidence The prediction confidence score.
 * @return int|false The ID of the inserted history record on success, false on failure.
 */
function savePredictionHistory($conn, $userId, $data, $prediction, $confidence)
{
    // Map form data keys to database columns - Adjust keys if they differ in $data
    // Ensure data types are correct before binding
    $sql = "INSERT INTO user_prediction_history (
                user_id, bmi, smoking, alcohol_drinking, stroke, physical_health,
                mental_health, diff_walking, sex, age, race, diabetic,
                physical_activity, gen_health, sleep_time, asthma, kidney_disease,
                skin_cancer, prediction_result, prediction_confidence
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error preparing statement for saving history: " . $conn->error);
        return false;
    }

    // Bind parameters - Adjust types (i, d, s) as per your table schema
    // Keys MUST match the output of validateAndPreprocessFormData
    $stmt->bind_param(
        'idddiidiiisiiidiiiii', // i=int, d=double/float
        $userId,                    // i
        $data['bmi'],               // d
        $data['smoking'],           // i
        $data['alcohol_drinking'],  // i
        $data['stroke'],            // i
        $data['physical_health'],   // i
        $data['mental_health'],     // i
        $data['diff_walking'],      // i
        $data['sex'],               // i
        $data['age'],               // i
        $data['race'],              // i
        $data['diabetic'],          // i
        $data['physical_activity'], // i
        $data['gen_health'],        // i
        $data['sleep_time'],        // d
        $data['asthma'],            // i
        $data['kidney_disease'],    // i
        $data['skin_cancer'],       // i
        $prediction,                // i
        $confidence                 // d
    );

    if ($stmt->execute()) {
        $lastId = $conn->insert_id;
        $stmt->close();
        return $lastId;
    } else {
        error_log("Error executing statement for saving history: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

/**
 * Inserts or updates the user's last test record in user_last_test_record table.
 *
 * @param mysqli|PDO $conn The database connection object.
 * @param int $userId The ID of the user.
 * @param int $predictionHistoryId The ID of the prediction history record to link.
 * @return bool True on success, false on failure.
 */
function updateLastTestRecord($conn, $userId, $predictionHistoryId)
{
    $sql = "INSERT INTO user_last_test_record (user_id, prediction_history_id)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE prediction_history_id = VALUES(prediction_history_id)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error preparing statement for updating last test record: " . $conn->error);
        return false;
    }

    $stmt->bind_param('ii', $userId, $predictionHistoryId);

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        error_log("Error executing statement for updating last test record: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

?>