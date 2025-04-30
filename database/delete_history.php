<?php
/**
 * Delete user prediction history
 *
 * This file contains functions for deleting prediction history records from the database
 */

// Include database connection
require_once __DIR__ . '/connection.php';

/**
 * Delete a specific prediction history record
 *
 * @param int $recordId The ID of the prediction history record to delete
 * @param mysqli $db The database connection object
 * @param int $recordId The ID of the prediction history record to delete
 * @param int $userId The ID of the user who owns the record (for security verification)
 * @return bool True on successful deletion, false otherwise
 */
function deletePredictionRecord($recordId, $userId) {
    $db = getDbConnection(); // Connection is now passed as a parameter
    
    

    // Now, delete the main prediction history record
    // Prepare the SQL query with user_id check for security
    // This ensures users can only delete their own records
    $sql = "DELETE FROM user_prediction_history WHERE id = ? AND user_id = ?";
    
    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        error_log("MySQL prepare error in deletePredictionRecord: " . $db->error);
        // Connection is managed globally, do not close here
        // $db->close();
        return false;
    }
    
    $stmt->bind_param("ii", $recordId, $userId);
    $execute_success = $stmt->execute();
    
    if ($execute_success === false) {
        error_log("MySQL execute error in deletePredictionRecord: " . $stmt->error);
        $stmt->close();
        $db->close();
        return false;
    }
    
    // Check if any rows were affected (record was deleted)
    $affected_rows = $stmt->affected_rows;
    
    $stmt->close();
    // $db->close(); // Do not close the connection here; let the caller manage it
    
    return ($affected_rows > 0);
}

/**
 * Delete all prediction history records for a specific user
 *
 * @param mysqli $db The database connection object
 * @param int $userId The ID of the user whose prediction history to delete
 * @return bool True on successful deletion, false otherwise
 */
function deleteAllPredictionRecords($userId) {
    $db = getDbConnection(); // Connection is now passed as a parameter
    
    // Prepare the SQL query
    $sql = "DELETE FROM user_prediction_history WHERE user_id = ?";
    
    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        error_log("MySQL prepare error in deleteAllPredictionRecords: " . $db->error);
        // Connection is managed globally, do not close here
        // $db->close();
        return false;
    }
    
    $stmt->bind_param("i", $userId);
    $execute_success = $stmt->execute();
    
    if ($execute_success === false) {
        error_log("MySQL execute error in deleteAllPredictionRecords: " . $stmt->error);
        $stmt->close();
        // Connection is managed globally, do not close here
        // $db->close();
        return false;
    }
    
    $stmt->close();
    // $db->close(); // Do not close the connection here; let the caller manage it
    
    return true;
}