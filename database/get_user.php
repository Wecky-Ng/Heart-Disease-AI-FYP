<?php
/**
 * User Retrieval Functions
 * 
 * This file contains functions for retrieving user information from the database.
 */

// Include database connection
require_once __DIR__ . '/connection.php';

/**
 * Get user by ID
 * 
 * @param int $userId User ID
 * @return array|false User data or false if not found
 */
function getUserById($userId) {
    $db = getDbConnection();
    
    $stmt = $db->prepare("SELECT id, username, email, full_name, date_of_birth, gender, created_at, updated_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    
    if ($stmt->rowCount() === 0) {
        return false;
    }
    
    return $stmt->fetch();
}

/**
 * Get user by username
 * 
 * @param string $username Username
 * @return array|false User data or false if not found
 */
function getUserByUsername($username) {
    $db = getDbConnection();
    
    $stmt = $db->prepare("SELECT id, username, email, full_name, date_of_birth, gender, created_at, updated_at FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->rowCount() === 0) {
        return false;
    }
    
    return $stmt->fetch();
}

/**
 * Get user by email
 * 
 * @param string $email Email address
 * @return array|false User data or false if not found
 */
function getUserByEmail($email) {
    $db = getDbConnection();
    
    $stmt = $db->prepare("SELECT id, username, email, full_name, date_of_birth, gender, created_at, updated_at FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() === 0) {
        return false;
    }
    
    return $stmt->fetch();
}

/**
 * Check if username exists
 * 
 * @param string $username Username to check
 * @return bool True if exists, false otherwise
 */
function usernameExists($username) {
    $db = getDbConnection();
    
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    return $stmt->rowCount() > 0;
}

/**
 * Check if email exists
 * 
 * @param string $email Email to check
 * @return bool True if exists, false otherwise
 */
function emailExists($email) {
    $db = getDbConnection();
    
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    return $stmt->rowCount() > 0;
}