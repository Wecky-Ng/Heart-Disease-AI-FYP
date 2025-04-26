<?php
/**
 * User Management Functions
 *
 * This file contains functions for user registration, authentication,
 * and profile management.
 */

// Include database connection
require_once __DIR__ . '/connection.php';

/**
 * Register a new user
 *
 * @param string $username Username
 * @param string $email Email address
 * @param string $password Password (will be hashed)
 * @param string $role User role (default: 'user')
 * @return array Result with status and message
 */
function registerUser($username, $email, $password, $full_name = null, $date_of_birth = null, $gender = null) {
    $db = getDbConnection();

    // Check if username or email already exists
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email); // Bind parameters
    $stmt->execute();

    $result = $stmt->get_result(); // Get the result object

    // Correctly check the number of rows using num_rows
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc(); // Fetch the user data
        $stmt->close(); // Close the statement
        if ($user['username'] === $username) {
            return ['status' => false, 'message' => 'Username already exists'];
        } else {
            return ['status' => false, 'message' => 'Email already exists'];
        }
    }
    $stmt->close(); // Close the statement if no rows found

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    try {
        $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, date_of_birth, gender, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        // Use bind_param for insert statement
        $stmt->bind_param("ssssss", $username, $email, $hashedPassword, $full_name, $date_of_birth, $gender);
        $result = $stmt->execute();

        if ($result) {
             $lastInsertId = $db->insert_id; // Get the last inserted ID using mysqli
             $stmt->close(); // Close the statement
             return [
                'status' => true,
                'message' => 'Registration successful',
                'user_id' => $lastInsertId
            ];
        } else {
             $stmt->close(); // Close the statement
             // Log the error for debugging
             error_log("Registration failed: " . $db->error);
             return ['status' => false, 'message' => 'Registration failed'];
        }
    } catch (mysqli_sql_exception $e) { // Catch mysqli_sql_exception for mysqli errors
         // Log the database error
         error_log("Database error during registration: " . $e->getMessage());
         // Return a generic error message to the user
         return ['status' => false, 'message' => 'Database error during registration.'];
    }
}

/**
 * Authenticate a user
 *
 * @param string $username Username or email
 * @param string $password Password
 * @return array Result with status, message and user data if successful
 */
function loginUser($username, $password) {
    $db = getDbConnection();

    // Check if input is email or username
    $field = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    // Get user from database
    $stmt = $db->prepare("SELECT * FROM users WHERE $field = ?");
    $stmt->bind_param("s", $username); // Bind parameter
    $stmt->execute();

    $result = $stmt->get_result(); // Get the result object

    // Correctly check the number of rows using num_rows
    if ($result->num_rows === 0) {
        $stmt->close(); // Close the statement
        return ['status' => false, 'message' => 'User not found'];
    }

    $user = $result->fetch_assoc(); // Fetch the user data
    $stmt->close(); // Close the statement

    // Verify password
    if (password_verify($password, $user['password'])) {
        // Check user status
        if (isset($user['status']) && $user['status'] == 0) { // Added isset check for status
            return ['status' => false, 'message' => 'Your account has been suspended. Please contact support.'];
        } elseif (isset($user['status']) && $user['status'] == 2) { // Added isset check for status
            return ['status' => false, 'message' => 'This account has been deleted.'];
        }

        // Update last login time by updating the updated_at timestamp
        $updateStmt = $db->prepare("UPDATE users SET updated_at = NOW() WHERE id = ?");
        $updateStmt->bind_param("i", $user['id']); // Bind parameter
        $updateStmt->execute();
        $updateStmt->close(); // Close the update statement

        // Remove password from user data before returning
        unset($user['password']);

        return [
            'status' => true,
            'message' => 'Login successful',
            'user' => $user
        ];
    } else {
        return ['status' => false, 'message' => 'Invalid password'];
    }
}

// Note: The getUserById function is duplicated in get_user.php.
// It's generally better to have user retrieval functions in one file (get_user.php)
// and user setting/management functions in another (set_user.php).
// I will remove the duplicate getUserById function from this file
// and ensure the one in get_user.php is correct.

/**
 * Update user profile
 *
 * @param int $userId User ID
 * @param array $data Data to update (keys: username, email, etc.)
 * @return array Result with status and message
 */
function updateUserProfile($userId, $data) {
    $db = getDbConnection();

    // Build update query
    $fields = [];
    $values = [];

    foreach ($data as $key => $value) {
        // Skip password as it needs special handling
        if ($key === 'password') continue;

        // Add field to update list
        $fields[] = "`$key` = ?"; // Enclose field name in backticks
        $values[] = $value;
    }

    // Handle password update if provided
    if (isset($data['password']) && !empty($data['password'])) {
        $fields[] = "`password` = ?";
        $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    // If no fields to update, return success (or an appropriate message)
    if (empty($fields)) {
        return ['status' => true, 'message' => 'No data provided for update.'];
    }

    // Add user ID to values array for the WHERE clause
    $values[] = $userId;

    // Construct the full query
    $query = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";

    // Execute update query
    try {
        $stmt = $db->prepare($query);

        // Determine parameter types for bind_param
        $paramTypes = str_repeat('s', count($values) - 1) . 'i'; // Assuming most are strings, last is integer ID

        // Bind parameters dynamically
        $bindParams = [$paramTypes];
        foreach ($values as &$value) {
            $bindParams[] = &$value;
        }

        // Use call_user_func_array to call bind_param with dynamic arguments
        call_user_func_array([$stmt, 'bind_param'], $bindParams);

        $result = $stmt->execute();

        if ($result) {
            $stmt->close(); // Close the statement
            return ['status' => true, 'message' => 'Profile updated successfully'];
        } else {
            $stmt->close(); // Close the statement
            error_log("Profile update failed: " . $db->error);
            return ['status' => false, 'message' => 'Profile update failed'];
        }
    } catch (mysqli_sql_exception $e) {
        error_log("Database error during profile update: " . $e->getMessage());
        return ['status' => false, 'message' => 'Database error during profile update.'];
    }
}

/**
 * Create users table if it doesn't exist
 *
 * @return bool True if successful, false otherwise
 */
function createUsersTable() {
    $db = getDbConnection();

    // Note: Using mysqli_query or mysqli::query for DDL statements is simpler
    // than prepare/execute for this case.
    $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INT NOT NULL AUTO_INCREMENT,
            username VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) DEFAULT NULL,
            date_of_birth DATE DEFAULT NULL,
            gender ENUM('Male','Female','Other') DEFAULT NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY username (username),
            UNIQUE KEY email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
    ";

    try {
        // Use mysqli::query for CREATE TABLE
        if ($db->query($sql)) {
             return true;
        } else {
             error_log("Error creating users table: " . $db->error);
             return false;
        }
    } catch (mysqli_sql_exception $e) {
         error_log("Database error creating users table: " . $e->getMessage());
         return false;
    }
}

// Removed the duplicate getUserById function from here.
// User retrieval functions should be in get_user.php.

?>
