<?php
/**
 * User Management Functions
 *
 * This file contains functions for user registration, authentication,
 * and profile management, including soft deletion.
 */

// Include database connection
require_once __DIR__ . '/connection.php';

/**
 * Register a new user
 *
 * @param string $username Username
 * @param string $email Email address
 * @param string $password Password (will be hashed)
 * @param string $full_name Full name (optional)
 * @param string $date_of_birth Date of birth (optional)
 * @param string $gender Gender (optional)
 * @return array Result with status (bool) and message (string), plus user_id on success
 */
function registerUser($username, $email, $password, $full_name = null, $date_of_birth = null, $gender = null) {
    $db = getDbConnection();

    // Check if username or email already exists
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $stmt->close();
        // Connection is managed globally, do not close here
        // $db->close(); 
        if ($user['username'] === $username) {
            return ['status' => false, 'message' => 'Username already exists'];
        } else {
            return ['status' => false, 'message' => 'Email already exists'];
        }
    }
    $stmt->close();

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    try {
        $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, date_of_birth, gender, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssss", $username, $email, $hashedPassword, $full_name, $date_of_birth, $gender);
        $result = $stmt->execute();

        if ($result) {
             $lastInsertId = $db->insert_id;
             $stmt->close();
             // Connection is managed globally, do not close here
             // $db->close();
             return [
                'status' => true,
                'message' => 'Registration successful',
                'user_id' => $lastInsertId
            ];
        } else {
             $stmt->close();
             // Connection is managed globally, do not close here
             // $db->close();
             error_log("Registration failed: " . $db->error);
             return ['status' => false, 'message' => 'Registration failed'];
        }
    } catch (mysqli_sql_exception $e) {
         error_log("Database error during registration: " . $e->getMessage());
         // Connection is managed globally, do not close here
         // if (isset($db) && $db) $db->close();
         return ['status' => false, 'message' => 'Database error during registration.'];
    }
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
        return ['status' => false, 'message' => 'Email not found or password incorrect.”'];
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
                return ['status' => false, 'message' => 'Account does not exist.'];
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
        return ['status' => false, 'message' => 'Email not found or password incorrect.”'];
    }
}

/**
 * Update user profile
 *
 * @param int $userId User ID
 * @param array $data Data to update (keys: full_name, date_of_birth, gender, password)
 * @return array Result with status (bool) and message (string)
 */
function updateUserProfile($userId, $data) {
    $db = getDbConnection();

    // Build update query
    $fields = [];
    $values = [];
    $paramTypes = '';

    // Define allowed fields to update
    $allowedFields = ['full_name', 'date_of_birth', 'gender'];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $fields[] = "`$field` = ?";
            $values[] = $data[$field];
            $paramTypes .= 's'; // Assuming these are strings (or null)
        }
    }

    // Handle password update if provided
    if (isset($data['password']) && !empty($data['password'])) {
        $fields[] = "`password` = ?";
        $values[] = $data['password']; // Hashed password is expected here
        $paramTypes .= 's';
    }

    // If no fields to update, return success (or an appropriate message)
    if (empty($fields)) {
         // Connection is managed globally, do not close here
         // $db->close();
        return ['status' => true, 'message' => 'No data provided for update.'];
    }

    // Add user ID to values array for the WHERE clause
    $values[] = $userId;
    $paramTypes .= 'i'; // User ID is an integer

    // Construct the full query
    $query = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";

    // Execute update query
    try {
        $stmt = $db->prepare($query);

        // Bind parameters dynamically
        $bindParams = [$paramTypes];
        foreach ($values as &$value) {
            $bindParams[] = &$value;
        }

        // Use call_user_func_array to call bind_param with dynamic arguments
        call_user_func_array([$stmt, 'bind_param'], $bindParams);

        $result = $stmt->execute();

        if ($result) {
            $stmt->close();
            // Connection is managed globally, do not close here
            // $db->close();
            return ['status' => true, 'message' => 'Profile updated successfully'];
        } else {
            $stmt->close();
            // Connection is managed globally, do not close here
            // $db->close();
            error_log("Profile update failed: " . $db->error);
            return ['status' => false, 'message' => 'Profile update failed'];
        }
    } catch (mysqli_sql_exception $e) {
        error_log("Database error during profile update: " . $e->getMessage());
         // Connection is managed globally, do not close here
        // if (isset($db) && $db) $db->close();
        return ['status' => false, 'message' => 'Database error during profile update.'];
    }
}

/**
 * Soft deletes a user account by updating the status to '2' (deleted).
 *
 * @param int $userId The ID of the user to delete.
 * @return array Result with status (bool) and message (string).
 */
function deleteUserAccount($userId) {
    $db = getDbConnection();

    // Prepare the SQL query to update the user's status to 2 (deleted)
    $query = "UPDATE users SET status = 2, updated_at = NOW() WHERE id = ?";

    try {
        $stmt = $db->prepare($query);

        // Check if prepare was successful
        if ($stmt === false) {
            error_log("MySQL prepare error in deleteUserAccount: " . $db->error);
            // Close DB connection before returning
            $db->close();
            return ['status' => false, 'message' => 'Failed to prepare delete statement.'];
        }

        // Bind the user ID parameter
        $stmt->bind_param("i", $userId);

        // Execute the update query
        $result = $stmt->execute();

        if ($result) {
            // Check if any rows were affected (user with this ID existed)
            if ($stmt->affected_rows > 0) {
                 $stmt->close();
                 // Close DB connection before returning
                 $db->close();
                 return ['status' => true, 'message' => 'Account marked as deleted successfully.'];
            } else {
                 $stmt->close();
                 // Close DB connection before returning
                 $db->close();
                 return ['status' => false, 'message' => 'User not found or already deleted.'];
            }
        } else {
            $stmt->close();
            // Close DB connection before returning
            $db->close();
            error_log("Account deletion failed for user ID {$userId}: " . $db->error);
            return ['status' => false, 'message' => 'Failed to delete account. Please try again.'];
        }
    } catch (mysqli_sql_exception $e) {
        error_log("Database error during account deletion for user ID {$userId}: " . $e->getMessage());
         // Close DB connection on exception
        if (isset($db) && $db) $db->close();
        return ['status' => false, 'message' => 'Database error during account deletion.'];
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
            status tinyint(1) NOT NULL DEFAULT '1' COMMENT '0: suspended user; 1: active user;2: deleted user',
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
