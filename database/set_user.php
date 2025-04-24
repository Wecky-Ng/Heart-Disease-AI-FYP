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
    $stmt->execute([$username, $email]);
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch();
        if ($user['username'] === $username) {
            return ['status' => false, 'message' => 'Username already exists'];
        } else {
            return ['status' => false, 'message' => 'Email already exists'];
        }
    }
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    try {
        $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, date_of_birth, gender, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$username, $email, $hashedPassword, $full_name, $date_of_birth, $gender]);
        
        if ($result) {
            return [
                'status' => true, 
                'message' => 'Registration successful',
                'user_id' => $db->lastInsertId()
            ];
        } else {
            return ['status' => false, 'message' => 'Registration failed'];
        }
    } catch (PDOException $e) {
        return ['status' => false, 'message' => 'Database error: ' . $e->getMessage()];
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
    $stmt->execute([$username]);
    
    if ($stmt->rowCount() === 0) {
        return ['status' => false, 'message' => 'User not found'];
    }
    
    $user = $stmt->fetch();
    
    // Verify password
    if (password_verify($password, $user['password'])) {
        // Update last login time by updating the updated_at timestamp
        $updateStmt = $db->prepare("UPDATE users SET updated_at = NOW() WHERE id = ?");
        $updateStmt->execute([$user['id']]);
        
        // Remove password from user data
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

/**
 * Get user by ID
 * 
 * @param int $userId User ID
 * @return array|false User data or false if not found
 */
function getUserById($userId) {
    $db = getDbConnection();
    
    $stmt = $db->prepare("SELECT id, username, email, role, created_at, last_login FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    
    if ($stmt->rowCount() === 0) {
        return false;
    }
    
    return $stmt->fetch();
}

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
        
        $fields[] = "$key = ?";
        $values[] = $value;
    }
    
    // Handle password update if provided
    if (isset($data['password']) && !empty($data['password'])) {
        $fields[] = "password = ?";
        $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    
    // Add user ID to values array
    $values[] = $userId;
    
    // Execute update query
    try {
        $stmt = $db->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?");
        $result = $stmt->execute($values);
        
        if ($result) {
            return ['status' => true, 'message' => 'Profile updated successfully'];
        } else {
            return ['status' => false, 'message' => 'Profile update failed'];
        }
    } catch (PDOException $e) {
        return ['status' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Create users table if it doesn't exist
 * 
 * @return bool True if successful, false otherwise
 */
function createUsersTable() {
    $db = getDbConnection();
    
    try {
        $db->exec("
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
            )
        ");
        return true;
    } catch (PDOException $e) {
        return false;
    }
}