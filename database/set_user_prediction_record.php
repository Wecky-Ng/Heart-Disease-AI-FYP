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
 * @param int $userId The ID of the user.
 * @param array $data The processed input data from the form.
 * @param int $prediction The prediction result (0 or 1).
 * @param float $confidence The prediction confidence score.
 * @return int|false The ID of the inserted history record on success, false on failure.
 */
function savePredictionHistory($userId, $data, $prediction, $confidence)
{
    // Ensure we have a valid database connection
    $db = getDbConnection();
    if (!$db) {
        error_log("Failed to get database connection in savePredictionHistory");
        return false;
    }
    
    // Validate input parameters
    $userId = (int)$userId; // Ensure userId is an integer
    $prediction = (int)$prediction; // Ensure prediction is an integer (0 or 1)
    $confidence = (float)$confidence; // Ensure confidence is a float
    
    // Validate data array - ensure all required fields exist and are properly typed
    $requiredFields = [
        'bmi', 'smoking', 'alcohol_drinking', 'stroke', 'physical_health',
        'mental_health', 'diff_walking', 'sex', 'age', 'race', 'diabetic',
        'physical_activity', 'gen_health', 'sleep_time', 'asthma', 'kidney_disease',
        'skin_cancer'
    ];
    
    // Check if all required fields exist
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            error_log("Missing required field '{$field}' in savePredictionHistory");
            return false;
        }
    }
    
    // Ensure proper data types for each field
    $floatFields = ['bmi', 'physical_health', 'mental_health', 'sleep_time'];
    $intFields = ['smoking', 'alcohol_drinking', 'stroke', 'diff_walking', 'sex', 'age', 
                 'race', 'diabetic', 'physical_activity', 'gen_health', 'asthma', 
                 'kidney_disease', 'skin_cancer'];
    
    foreach ($floatFields as $field) {
        $data[$field] = (float)$data[$field];
    }
    
    foreach ($intFields as $field) {
        $data[$field] = (int)$data[$field];
    }
    // Map form data keys to database columns - Adjust keys if they differ in $data
    // Ensure data types are correct before binding
    $sql = "INSERT INTO user_prediction_history (
        user_id, bmi, smoking, alcohol_drinking, stroke, physical_health,
        mental_health, diff_walking, sex, age, race, diabetic,
        physical_activity, gen_health, sleep_time, asthma, kidney_disease,
        skin_cancer, prediction_result, prediction_confidence, created_at
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"; // NOW() is a MySQL function, not a parameter

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
    // Count the parameters: 1(userId) + 17(data fields) + 1(prediction) + 1(confidence) = 20
    // Adding the missing 'd' for the confidence parameter
    $types = 'idiiiddiiiiiiidiiidi';

    // Bind the parameters - ensure we have exactly 20 parameters to match our type string
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

    // Log the SQL and parameters for debugging
    error_log("Executing SQL: {$sql}");
    error_log("User ID: {$userId}");
    error_log("Data values: " . json_encode($data));
    error_log("Prediction: {$prediction}, Confidence: {$confidence}");
    
    try {
        if ($stmt->execute()) {
            $lastId = $db->insert_id;
            $stmt->close();
            return $lastId;
        } else {
            error_log("Error executing statement for saving history: " . $stmt->error);
            error_log("MySQL Error Code: " . $stmt->errno);
            $stmt->close();
            return false;
        }
    } catch (Exception $e) {
        error_log("Exception in savePredictionHistory: " . $e->getMessage());
        $stmt->close();
        return false;
    }
}
