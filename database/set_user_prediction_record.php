<?php

/**
 * Set user prediction record
 *
 * This file contains functions for saving prediction history and updating the last test record
 * for a logged-in user.
 */

require_once __DIR__ . '/connection.php';

/**
 * Updates the prediction result and confidence for an existing prediction history record.
 *
 * This function is used as a fallback when the initial insert doesn't properly store
 * the prediction_result and prediction_confidence values.
 *
 * @param int $recordId The ID of the prediction history record to update.
 * @param int $prediction The prediction result (0 or 1).
 * @param float $confidence The prediction confidence score.
 * @return bool True on successful update, false on failure.
 */
function updatePredictionResultAndConfidence($recordId, $prediction, $confidence)
{
    // Ensure we have a valid database connection
    $db = getDbConnection();
    if (!$db) {
        echo "<script>console.error('Failed to get database connection in updatePredictionResultAndConfidence');</script>";
        error_log("Failed to get database connection in updatePredictionResultAndConfidence");
        return false;
    }
    
    // Validate input parameters
    $recordId = (int)$recordId; // Ensure recordId is an integer
    $prediction = (int)$prediction; // Ensure prediction is an integer (0 or 1)
    $confidence = round((float)$confidence, 2); // Round confidence to 2 decimal places
    
    // Log the values for debugging
    echo "<script>console.log('Updating record ID: {$recordId} with prediction: {$prediction}, confidence: {$confidence}');</script>";
    error_log("Updating record ID: {$recordId} with prediction: {$prediction}, confidence: {$confidence}");
    
    // Prepare the update SQL statement
    $sql = "UPDATE user_prediction_history SET prediction_result = ?, prediction_confidence = ? WHERE id = ?";
    
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        echo "<script>console.error('Error preparing statement for updating prediction: " . addslashes($db->error) . "');</script>";
        error_log("Error preparing statement for updating prediction: " . $db->error);
        return false;
    }
    
    // Bind parameters - 'idi' for double (confidence), integer (prediction), integer (recordId)
    $stmt->bind_param("idi", $prediction, $confidence, $recordId);
    
    try {
        if ($stmt->execute()) {
            // Log successful update
            echo "<script>console.log('Successfully updated prediction values for record ID: {$recordId}');</script>";
            error_log("Successfully updated prediction values for record ID: {$recordId}");
            $stmt->close();
            return true;
        } else {
            // Log SQL execution errors
            $errorMsg = "Error executing statement for updating prediction: " . $stmt->error;
            echo "<script>console.error('{$errorMsg}');</script>";
            error_log($errorMsg);
            $stmt->close();
            return false;
        }
    } catch (Throwable $e) {
        // Log any exceptions or errors
        $errorMessage = $e->getMessage();
        echo "<script>console.error('Exception in updatePredictionResultAndConfidence: " . addslashes($errorMessage) . "');</script>";
        error_log("Exception in updatePredictionResultAndConfidence: " . $errorMessage);
        
        if (isset($stmt) && $stmt) {
            $stmt->close();
        }
        return false;
    }
}

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
        echo "<script>console.error('Failed to get database connection in savePredictionHistory');</script>";
        error_log("Failed to get database connection in savePredictionHistory");
        return false;
    }
    
    // Validate input parameters
    $userId = (int)$userId; // Ensure userId is an integer
    $prediction = (int)$prediction; // Ensure prediction is an integer (0 or 1)
    
    // Ensure confidence is properly converted to float
    // Force conversion to string first to handle any potential formatting issues
    $confidence = (float)$confidence; 
    // Add console.log for browser debugging instead of error_log
    echo "<script>console.log('Confidence value after initial casting: " . addslashes(var_export($confidence, true)) . "');</script>";
    // Keep error_log as backup
    error_log("Confidence value after initial casting: " . var_export($confidence, true));
    
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
            echo "<script>console.error('Missing required field \'{$field}\\' in savePredictionHistory');</script>";
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
        echo "<script>console.error('Error preparing statement for saving history: " . addslashes($db->error) . "');</script>";
        error_log("Error preparing statement for saving history: " . $db->error);
        // Log the specific MySQL error for better debugging
        echo "<script>console.error('MySQL Error: " . addslashes($db->error) . "');</script>";
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
    // Ensure the confidence parameter is properly typed as 'd' (double/float)
    // The correct type string must have exactly 20 characters for 20 parameters
    // i=integer, d=double/float for: user_id(i), bmi(d), smoking(i), alcohol_drinking(i), stroke(i),
    // physical_health(d), mental_health(d), diff_walking(i), sex(i), age(i), race(i), diabetic(i),
    // physical_activity(i), gen_health(i), sleep_time(d), asthma(i), kidney_disease(i), skin_cancer(i),
    // prediction_result(i), prediction_confidence(d)
    $types = 'idiiiddiiiiiiidiiidi'; // Exactly 20 characters for 20 parameters
    
    // Verify the type string length matches the parameter count
    $typeLength = strlen($types);
    $questionMarkCount = substr_count($sql, '?');
    error_log("Type string: '{$types}' has length: {$typeLength}, Question marks in SQL: {$questionMarkCount}");
    
    if ($typeLength != $questionMarkCount) {
        error_log("ERROR: Type string length ({$typeLength}) doesn't match parameter count ({$questionMarkCount})");
        return false;
    }
    
    // Debug the type string length
    error_log("Type string length: " . strlen($types) . ", Expected: 20");
    
    // Count the question marks in the SQL query
    $questionMarkCount = substr_count($sql, '?');
    error_log("Question mark count in SQL: " . $questionMarkCount);
    
    // Ensure type string matches parameter count
    if (strlen($types) != $questionMarkCount) {
        error_log("Type string length mismatch: " . strlen($types) . " characters for " . $questionMarkCount . " parameters");
        return false;
    }
    
    // Count the number of characters in the type string to ensure it matches the number of parameters
    if (strlen($types) !== 20) {
        error_log("Type string length mismatch: " . strlen($types) . " characters for 20 parameters");
        return false;
    }
    
    // Explicitly cast all values to their proper types to avoid type conversion issues
    $userId = (int)$userId;
    $data['bmi'] = (float)$data['bmi'];
    $data['smoking'] = (int)$data['smoking'];
    $data['alcohol_drinking'] = (int)$data['alcohol_drinking'];
    $data['stroke'] = (int)$data['stroke'];
    $data['physical_health'] = (float)$data['physical_health'];
    $data['mental_health'] = (float)$data['mental_health'];
    $data['diff_walking'] = (int)$data['diff_walking'];
    $data['sex'] = (int)$data['sex'];
    $data['age'] = (int)$data['age'];
    $data['race'] = (int)$data['race'];
    $data['diabetic'] = (int)$data['diabetic'];
    $data['physical_activity'] = (int)$data['physical_activity'];
    $data['gen_health'] = (int)$data['gen_health'];
    $data['sleep_time'] = (float)$data['sleep_time'];
    $data['asthma'] = (int)$data['asthma'];
    $data['kidney_disease'] = (int)$data['kidney_disease'];
    $data['skin_cancer'] = (int)$data['skin_cancer'];
    $prediction = (int)$prediction;
    // Round the confidence value to 2 decimal places before storing it in the database
    // This ensures it will be stored as expected (e.g., 0.82 instead of 0.81537892036955)
    $confidence = round((float)$confidence, 2);
    // Add console.log for browser debugging
    echo "<script>console.log('Final confidence value before binding (rounded to 2 decimals): " . addslashes(var_export($confidence, true)) . "');</script>";
    // Keep error_log as backup
    error_log("Final confidence value before binding (rounded to 2 decimals): " . var_export($confidence, true));

    // Bind the parameters - ensure we have exactly 20 parameters to match our type string
    try {
        // Create references for bind_param (required by mysqli)
        $params = array();
        $params[] = &$types;
        $params[] = &$userId;
        $params[] = &$data['bmi'];
        $params[] = &$data['smoking'];
        $params[] = &$data['alcohol_drinking'];
        $params[] = &$data['stroke'];
        $params[] = &$data['physical_health'];
        $params[] = &$data['mental_health'];
        $params[] = &$data['diff_walking'];
        $params[] = &$data['sex'];
        $params[] = &$data['age'];
        $params[] = &$data['race'];
        $params[] = &$data['diabetic'];
        $params[] = &$data['physical_activity'];
        $params[] = &$data['gen_health'];
        $params[] = &$data['sleep_time'];
        $params[] = &$data['asthma'];
        $params[] = &$data['kidney_disease'];
        $params[] = &$data['skin_cancer'];
        $params[] = &$prediction;
        $params[] = &$confidence;
        
        // Use call_user_func_array to bind all parameters at once
        call_user_func_array(array($stmt, 'bind_param'), $params);
        
        // Add console.log for browser debugging
        echo "<script>console.log('Successfully bound all parameters');</script>";
        error_log("Successfully bound all parameters");
    } catch (Exception $e) {
        echo "<script>console.error('Error binding parameters: " . addslashes($e->getMessage()) . "');</script>";
        error_log("Error binding parameters: " . $e->getMessage());
        header('X-Debug-BindError: ' . substr($e->getMessage(), 0, 100));
        $stmt->close();
        return false; // Return false instead of re-throwing to prevent uncaught exceptions
    }

    // Log the SQL and parameters for debugging - use both error_log and headers for client-side debugging
    // Add console.log for browser debugging
    echo "<script>
        console.log('Executing SQL: {$sql}');
        console.log('User ID: {$userId}');
        console.log('Data values: " . addslashes(json_encode($data)) . "');
        console.log('Prediction: {$prediction}, Confidence: {$confidence}, Confidence Type: " . gettype($confidence) . ", Raw Value: " . addslashes(var_export($confidence, true)) . "');
    </script>";
    
    // Keep error_log as backup
    error_log("Executing SQL: {$sql}");
    error_log("User ID: {$userId}");
    error_log("Data values: " . json_encode($data));
    error_log("Prediction: {$prediction}, Confidence: {$confidence}, Confidence Type: " . gettype($confidence) . ", Raw Value: " . var_export($confidence, true));
    
    // Add headers that can be seen in browser network tab
    header('X-Debug-UserID: ' . $userId);
    header('X-Debug-Prediction: ' . $prediction);
    header('X-Debug-Confidence: ' . $confidence);
    header('X-Debug-ConfidenceType: ' . gettype($confidence));
    
    // Create a copy of the SQL with actual values for debugging
    $debugSql = "INSERT INTO user_prediction_history (
        user_id, bmi, smoking, alcohol_drinking, stroke, physical_health,
        mental_health, diff_walking, sex, age, race, diabetic,
        physical_activity, gen_health, sleep_time, asthma, kidney_disease,
        skin_cancer, prediction_result, prediction_confidence, created_at
    )
    VALUES (
        {$userId}, {$data['bmi']}, {$data['smoking']}, {$data['alcohol_drinking']}, {$data['stroke']},
        {$data['physical_health']}, {$data['mental_health']}, {$data['diff_walking']}, {$data['sex']}, {$data['age']},
        {$data['race']}, {$data['diabetic']}, {$data['physical_activity']}, {$data['gen_health']}, {$data['sleep_time']},
        {$data['asthma']}, {$data['kidney_disease']}, {$data['skin_cancer']}, {$prediction}, {$confidence}, NOW()
    )";
    
    // Log to both error log and browser console
    echo "<script>console.log('DEBUG SQL with values: " . addslashes($debugSql) . "');</script>";
    error_log("DEBUG SQL with values: " . $debugSql);
    
    try {
        // Log bind_param success/failure
        echo "<script>console.log('About to execute statement with types: {$types}');</script>";
        error_log("About to execute statement with types: {$types}");
        header('X-Debug-BindTypes: ' . $types);
        
        if ($stmt->execute()) {
            $lastId = $db->insert_id;
            // Log successful insertion
            echo "<script>console.log('Successfully inserted record with ID: {$lastId}');</script>";
            error_log("Successfully inserted record with ID: {$lastId}");
            header('X-Debug-Success: true');
            header('X-Debug-RecordID: ' . $lastId);
            $stmt->close();
            
            // Call the update function to ensure prediction_result and prediction_confidence are properly set
            // This is a fallback in case these fields weren't properly inserted in the initial query
            $updateResult = updatePredictionResultAndConfidence($lastId, $prediction, $confidence);
            if ($updateResult) {
                echo "<script>console.log('Successfully updated prediction values for record ID: {$lastId}');</script>";
                error_log("Successfully updated prediction values for record ID: {$lastId}");
            } else {
                echo "<script>console.error('Failed to update prediction values for record ID: {$lastId}');</script>";
                error_log("Failed to update prediction values for record ID: {$lastId}");
            }
            
            return $lastId;
        } else {
            // Log SQL execution errors
            $errorMsg = "Error executing statement for saving history: " . $stmt->error;
            $errorCode = "MySQL Error Code: " . $stmt->errno;
            echo "<script>console.error('{$errorMsg}');</script>";
            echo "<script>console.error('{$errorCode}');</script>";
            error_log($errorMsg);
            error_log($errorCode);
            header('X-Debug-Error: ' . substr($errorMsg, 0, 100));
            header('X-Debug-ErrorCode: ' . $stmt->errno);
            $stmt->close();
            return false;
        }
    } catch (Throwable $e) {
        // Log any exceptions or errors (Throwable catches both Exception and Error)
        $errorMessage = $e->getMessage();
        echo "<script>console.error('Exception in savePredictionHistory: " . addslashes($errorMessage) . "');</script>";
        error_log("Exception in savePredictionHistory: " . $errorMessage);
        header('X-Debug-Exception: ' . substr($errorMessage, 0, 100));
        
        // Log the stack trace for more detailed debugging
        echo "<script>console.error('Exception stack trace: " . addslashes($e->getTraceAsString()) . "');</script>";
        error_log("Exception stack trace: " . $e->getTraceAsString());
        
        if (isset($stmt) && $stmt) {
            $stmt->close();
        }
        return false;
    }
}
