<?php
// Define PROJECT_ROOT if it hasn't been defined (for local development when not routed through api/index.php)
if (!defined('PROJECT_ROOT')) {
    // Assuming profile.php is at the project root
    define('PROJECT_ROOT', __DIR__);
    // If profile.php is in a subdirectory, adjust __DIR__ accordingly, e.g., dirname(__DIR__)
}
// Include session management and user functions
require_once PROJECT_ROOT . '/session.php';
require_once PROJECT_ROOT . '/database/set_user.php'; // This file now contains deleteUserAccount
require_once PROJECT_ROOT . '/database/get_user.php';
require_once PROJECT_ROOT . '/database/connection.php'; // Include connection.php if needed by set_user.php or get_user.php

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get current user session data
$sessionData = getCurrentUser();

// Get complete user data from database
// Ensure getUserById is secure and uses prepared statements
$userData = getUserById($sessionData['user_id']);
if (!$userData) {
    // If user not found in database (e.g., already deleted or a database issue), log them out
    endUserSession();
    header('Location: login.php');
    exit();
}

// Initialize variables for messages
$success = '';
$error = '';

// Process form submission for profile update or deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the update profile form was submitted
    if (isset($_POST['update_profile'])) {
        // Get form data
        $full_name = trim($_POST['full_name'] ?? '');
        $date_of_birth = trim($_POST['date_of_birth'] ?? '');
        $gender = $_POST['gender'] ?? '';
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Prepare data for update
        $updateData = [
            'full_name' => $full_name,
            'date_of_birth' => $date_of_birth ?: null,
            'gender' => $gender ?: null
        ];

        // Handle password change if requested
        if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
             // Only proceed with password change if all fields are provided
             if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                 $error = 'All password fields must be filled to change password.';
             } else {
                // Verify current password
                $db = getDbConnection(); // Ensure this function is available and returns a valid DB connection
                // Using prepared statement for password verification
                $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->bind_param("i", $userData['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();
                // Note: Closing the DB connection here might be premature if updateUserProfile uses it.
                // It's better to pass the connection or ensure getDbConnection() handles connections properly (e.g., persistent).
                // For now, assuming getDbConnection() provides a new connection or manages it.
                // $db->close(); // Removed premature close

                if (!$user || !password_verify($current_password, $user['password'])) {
                    $error = 'Current password is incorrect.';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'New passwords do not match.';
                } elseif (strlen($new_password) < 8) {
                    $error = 'New password must be at least 8 characters long.';
                } else {
                    // Add hashed new password to update data
                    $updateData['password'] = password_hash($new_password, PASSWORD_DEFAULT);
                }
             }
        }

        // Update profile if no errors
        if (empty($error)) {
            // Ensure updateUserProfile is secure and uses prepared statements
            $result = updateUserProfile($userData['id'], $updateData);

            if ($result['status']) {
                $success = 'Profile updated successfully!';
                // Refresh user data after successful update
                // Re-fetch user data to reflect any changes (e.g., gender, dob)
                $userData = getUserById($userData['id']);
            } else {
                $error = $result['message'];
            }
        }
    } elseif (isset($_POST['delete_account'])) {
        // --- Handle Account Deletion ---
        // Call the deleteUserAccount function
        $delete_result = deleteUserAccount($userData['id']);

        if ($delete_result['status']) {
            // Account successfully marked as deleted
            // End the user's session and redirect to logout.php
            endUserSession(); // Assuming this function is defined in session.php
            header('Location: logout.php?status=deleted'); // Redirect to logout page with status
            exit();
        } else {
            // Failed to mark account as deleted
            $error = $delete_result['message'];
            // Optionally, log the error server-side as well
            error_log("Account deletion failed for user ID {$userData['id']}: " . $delete_result['message']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Heart Disease Prediction using AI - User Profile">
    <title>User Profile - Heart Disease Prediction</title>
    <?php include PROJECT_ROOT . '/includes/styles.php'; ?>
    <style>
        /* Basic styling to keep buttons on the same line */
        .form-actions {
            display: flex;
            justify-content: space-between; /* Pushes buttons to ends */
            align-items: center;
            gap: 10px; /* Space between buttons */
            flex-wrap: wrap; /* Allow wrapping on smaller screens */
        }
        .form-actions .btn {
            flex-grow: 0; /* Prevent buttons from growing */
            flex-shrink: 0; /* Prevent buttons from shrinking */
        }
        /* If you want the delete button specifically on the right */
        .form-actions .delete-button-container {
             margin-left: auto; /* Pushes this container to the right */
        }
    </style>
</head>
<body class="nk-body bg-lighter">
    <div class="nk-app-root">
        <?php include PROJECT_ROOT . '/sidemenu.php'; ?>

        <div class="nk-main">
            <?php include PROJECT_ROOT . '/header.php'; ?>

            <div class="nk-wrap">
                <div class="nk-content">
                <div class="container-fluid">
                    <div class="nk-content-inner">
                        <div class="nk-content-body">
                            <div class="nk-block-head nk-block-head-sm">
                                <div class="nk-block-between">
                                    <div class="nk-block-head-content">
                                        <h3 class="nk-block-title page-title">My Profile</h3>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                            <?php endif; ?>

                            <div class="nk-block">
                                <div class="card">
                                    <div class="card-inner">
                                        <form action="profile.php" method="post" class="form-validate">
                                            <input type="hidden" name="update_profile" value="1"> <div class="row g-gs">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label" for="username">Username</label>
                                                        <div class="form-control-wrap">
                                                            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($userData['username']); ?>" readonly>
                                                            <small class="text-muted">Username cannot be changed</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label" for="email">Email</label>
                                                        <div class="form-control-wrap">
                                                            <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($userData['email']); ?>" readonly>
                                                            <small class="text-muted">Email cannot be changed</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label" for="full_name">Full Name</label>
                                                        <div class="form-control-wrap">
                                                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($userData['full_name'] ?? ''); ?>" maxlength="50">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label" for="date_of_birth">Date of Birth</label>
                                                        <div class="form-control-wrap">
                                                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($userData['date_of_birth'] ?? ''); ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label" for="gender">Gender</label>
                                                        <div class="form-control-wrap">
                                                            <select class="form-control" id="gender" name="gender">
                                                                <option value="" <?php echo empty($userData['gender']) ? 'selected' : ''; ?>>Select Gender</option>
                                                                <option value="Male" <?php echo ($userData['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                                                <option value="Female" <?php echo ($userData['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                                                <option value="Other" <?php echo ($userData['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="form-label">Change Password</label>
                                                        <div class="card card-bordered">
                                                            <div class="card-inner">
                                                                <div class="row g-3">
                                                                    <div class="col-12">
                                                                        <div class="form-group">
                                                                            <label class="form-label" for="current_password">Current Password</label>
                                                                            <div class="form-control-wrap">
                                                                                <input type="password" class="form-control" id="current_password" name="current_password">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label class="form-label" for="new_password">New Password</label>
                                                                            <div class="form-control-wrap">
                                                                                <input type="password" class="form-control" id="new_password" name="new_password" minlength="8">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label class="form-label" for="confirm_password">Confirm New Password</label>
                                                                            <div class="form-control-wrap">
                                                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group form-actions">
                                                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                                                        <div class="delete-button-container">
                                                             <button type="submit" name="delete_account" class="btn btn-danger">Delete Account</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>

            <?php include PROJECT_ROOT . '/footer.php'; ?>
        </div>
    </div>

    <?php include PROJECT_ROOT . '/includes/scripts.php'; ?>
    <script>
        // JavaScript to confirm the deletion
        $(document).ready(function() {
            $('button[name="delete_account"]').on('click', function(e) {
                if (!confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
                    e.preventDefault(); // Prevent form submission if user cancels
                }
            });
        });
    </script>
</body>
</html>
