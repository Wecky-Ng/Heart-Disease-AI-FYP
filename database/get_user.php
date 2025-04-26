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
    $stmt->bind_param("i", $userId); // Bind parameter
    $stmt->execute();

    $result = $stmt->get_result(); // Get the result object

    // Correctly check the number of rows using num_rows
    if ($result->num_rows === 0) {
        $stmt->close(); // Close the statement
        return false;
    }

    $user = $result->fetch_assoc(); // Fetch the user data as an associative array
    $stmt->close(); // Close the statement

    return $user;
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
    $stmt->bind_param("s", $username); // Bind parameter
    $stmt->execute();

    $result = $stmt->get_result(); // Get the result object

    // Correctly check the number of rows using num_rows
    if ($result->num_rows === 0) {
        $stmt->close(); // Close the statement
        return false;
    }

    $user = $result->fetch_assoc(); // Fetch the user data as an associative array
    $stmt->close(); // Close the statement

    return $user;
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
    $stmt->bind_param("s", $email); // Bind parameter
    $stmt->execute();

    $result = $stmt->get_result(); // Get the result object

    // Correctly check the number of rows using num_rows
    if ($result->num_rows === 0) {
        $stmt->close(); // Close the statement
        return false;
    }

    $user = $result->fetch_assoc(); // Fetch the user data as an associative array
    $stmt->close(); // Close the statement

    return $user;
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
    $stmt->bind_param("s", $username); // Bind parameter
    $stmt->execute();

    $result = $stmt->get_result(); // Get the result object

    // Correctly check the number of rows using num_rows
    $exists = $result->num_rows > 0;
    $stmt->close(); // Close the statement

    return $exists;
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
    $stmt->bind_param("s", $email); // Bind parameter
    $stmt->execute();

    $result = $stmt->get_result(); // Get the result object

    // Correctly check the number of rows using num_rows
    $exists = $result->num_rows > 0;
    $stmt->close(); // Close the statement

    return $exists;
}
