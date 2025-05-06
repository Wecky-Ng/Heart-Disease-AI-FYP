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
function savePredictionHistory($userId, $data, $prediction, $confidence)
{
    $db = getDbConnection();
    // Map form data keys to database columns - Adjust keys if they differ in $data
    // Ensure data types are correct before binding
    $sql = "INSERT INTO user_prediction_history (
        user_id, bmi, smoking, alcohol_drinking, stroke, physical_health,
        mental_health, diff_walking, sex, age, race, diabetic,
        physical_activity, gen_health, sleep_time, asthma, kidney_disease,
        skin_cancer, prediction_result, prediction_confidence, created_at
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"; // Added created_at and its placeholder

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        error_log("Error preparing statement for saving history: " . $db->error);
        // Log the specific MySQL error for better debugging
        error_log("MySQL Error: " . $db->error);
        return false;
    }

    // Bind parameters - Adjust types (i=int, d=double/float, s=string) as per your table schema
    // The order and types in the string MUST exactly match the order of columns in the SQL query
    // and the types of the variables being bound.
    // Corrected type string based on the column order in the INSERT query and data types from fypheartdiseasedatabasestructure (2).txt:
    // user_id (i), bmi (d), smoking (i), alcohol_drinking (i), stroke (i),
    // physical_health (d), mental_health (d), diff_walking (i), sex (i), age (i),
    // race (i), diabetic (i), physical_activity (i), gen_health (i), sleep_time (d),
    // asthma (i), kidney_disease (i), skin_cancer (i), prediction_result (i), prediction_confidence (d)
    $types = 'idiiiddiiiiiiidiiid'; // Corrected type string (20 characters for 20 parameters)

    // Bind the parameters
    $stmt->bind_param(
        $types,
        $userId,
        $data['bmi'],
        $data['smoking'],
        $data['alcohol_drinking'],
        $data['stroke'],
        $data['physical_health'],
        $data['mental_health'],
        $data['diff_walking'],
        $data['sex'],
        $data['age'],
        $data['race'],
        $data['diabetic'],
        $data['physical_activity'],
        $data['gen_health'],
        $data['sleep_time'],
        $data['asthma'],
        $data['kidney_disease'],
        $data['skin_cancer'],
        $prediction,
        $confidence
    );

    if ($stmt->execute()) {
        $lastId = $db->insert_id;
        $stmt->close();
        return $lastId;
    } else {
        error_log("Error executing statement for saving history: " . $stmt->error);
        $stmt->close();
        return false;
    }
}
