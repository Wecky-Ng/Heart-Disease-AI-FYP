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

/**
 * Authenticate a user
 *
 * @param string $username Username or email
 * @param string $password Password
 * @return array Result with status (bool), message (string) and user data (array) if successful
 */
function loginUser($username, $password) {
    $db = getDbConnection();

    // Check if input is email or username
    $field = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    // Get user from database
    $stmt = $db->prepare("SELECT * FROM users WHERE $field = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        // Connection is managed globally, do not close here
        // $db->close();
        return ['status' => false, 'message' => 'Email not found or password incorrect."'];
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Verify password
    if (password_verify($password, $user['password'])) {
        // Check user status
        if (isset($user['status'])) { // Check if status column exists
            if ($user['status'] == 0) {
                // Connection is managed globally, do not close here
                // $db->close();
                return ['status' => false, 'message' => 'Your account has been suspended. Please contact support.'];
            } elseif ($user['status'] == 2) {
                 // Connection is managed globally, do not close here
                 // $db->close();
                return ['status' => false, 'message' => 'This account has been deleted.'];
            }
            // If status is 1 (active), proceed with login
        } else {
            // If status column is missing, assume active for backward compatibility or log an error
            error_log("User status column missing for user ID: " . $user['id']);
            // Continue with login as if status is 1
        }


        // Update last login time by updating the updated_at timestamp
        $updateStmt = $db->prepare("UPDATE users SET updated_at = NOW() WHERE id = ?");
        $updateStmt->bind_param("i", $user['id']);
        $updateStmt->execute();
        $updateStmt->close();

        // Connection is managed globally, do not close here
        // $db->close();

        // Remove password from user data before returning
        unset($user['password']);

        return [
            'status' => true,
            'message' => 'Login successful',
            'user' => $user
        ];
    } else {
         // Connection is managed globally, do not close here
        // $db->close();
        return ['status' => false, 'message' => 'Email not found or password incorrect."'];
    }
}
