<?php
// Include database connection
require_once __DIR__ . '/../database/connection.php';

/**
 * Fetch all FAQs from database
 * 
 * @return array Array of FAQ items or empty array on error
 */
function getFaqs() {
    try {
        $db = getDbConnection();
        $result = $db->query("SELECT id, faq_title, detail FROM faq WHERE status = 1 ORDER BY faq_index ASC");
        
        if (!$result) {
            throw new Exception("Query failed: " . $db->error);
        }
        
        $faqs = [];
        while ($row = $result->fetch_assoc()) {
            $faqs[] = $row;
        }
        
        $result->free();
        return $faqs;
    } catch (Exception $e) {
        // Log error and return empty array
        error_log('Database Error: ' . $e->getMessage());
        return [];
    }
}