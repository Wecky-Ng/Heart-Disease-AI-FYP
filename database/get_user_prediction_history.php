<?php
/**
 * Get user prediction history
 * 
 * This file retrieves the prediction history for a specific user from the database
 */

/**
 * Fetches prediction history for a specific user
 * 
 * @param int $userId The ID of the user whose prediction history to retrieve
 * @return array An array of prediction history records
 */
function getUserPredictionHistory($userId) {
    global $conn;
    
    // Prepare the SQL query
    $sql = "SELECT * FROM user_prediction_history WHERE user_id = ? ORDER BY created_at DESC";
    
    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    // Get the result
    $result = $stmt->get_result();
    
    // Initialize an empty array to store the prediction history
    $predictionHistory = [];
    
    // Fetch all records and add them to the array
    while ($row = $result->fetch_assoc()) {
        // Format the data for display
        $details = sprintf(
            "Age: %d, BMI: %.1f, Sleep: %.1f hrs", 
            $row['age'], 
            $row['bmi'], 
            $row['sleep_time']
        );
        
        // Determine risk level based on prediction result and confidence
        $riskLevel = 'Low Risk';
        if ($row['prediction_result'] == 1) {
            if ($row['prediction_confidence'] >= 0.7) {
                $riskLevel = 'High Risk';
            } else {
                $riskLevel = 'Medium Risk';
            }
        }
        
        // Format the prediction confidence as a percentage
        $probability = round($row['prediction_confidence'] * 100) . '%';
        
        // Add the formatted record to the array
        $predictionHistory[] = [
            'id' => $row['id'],
            'date' => date('Y-m-d', strtotime($row['created_at'])),
            'result' => $riskLevel,
            'probability' => $probability,
            'details' => $details,
            // Add all raw data for detailed view if needed
            'raw_data' => $row
        ];
    }
    
    // Close the statement
    $stmt->close();
    
    return $predictionHistory;
}