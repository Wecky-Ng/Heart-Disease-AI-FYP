<?php
/**
 * Get Health Information Recommendations
 *
 * This file retrieves health information from the database based on category
 */

// Include database connection
require_once 'connection.php';

/**
 * Get health information by category
 *
 * @param int $category Category ID (1: Heart Disease Facts, 2: Prevention Tips, 3: Treatment Options)
 * @return array Array of health information records
 */
function getHealthInformationByCategory($category) {
    global $mysqli;
    
    // Validate category input
    $category = filter_var($category, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 3]]);
    if ($category === false) {
        return [];
    }
    
    // Prepare statement to prevent SQL injection
    $stmt = $mysqli->prepare("SELECT id, title, detail FROM health_information WHERE category = ? ORDER BY `index` ASC");
    $stmt->bind_param("i", $category);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $healthInfo = [];
    
    while ($row = $result->fetch_assoc()) {
        $healthInfo[] = $row;
    }
    
    $stmt->close();
    return $healthInfo;
}

/**
 * Get all health information categories
 *
 * @return array Array of all health information records grouped by category
 */
function getAllHealthInformation() {
    global $mysqli;
    
    $query = "SELECT id, title, detail, category FROM health_information ORDER BY category, `index` ASC";
    $result = $mysqli->query($query);
    
    $healthInfo = [
        1 => [], // Heart Disease Facts
        2 => [], // Prevention Tips
        3 => []  // Treatment Options
    ];
    
    while ($row = $result->fetch_assoc()) {
        $category = $row['category'];
        $healthInfo[$category][] = $row;
    }
    
    return $healthInfo;
}