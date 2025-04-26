<?php
// Include session management and user functions
require_once PROJECT_ROOT . '/session.php';
require_once PROJECT_ROOT . '/database/set_user.php';
require_once PROJECT_ROOT . '/database/get_user.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get current user session data
$sessionData = getCurrentUser();

// Get complete user data from database
$userData = getUserById($sessionData['user_id']);
if (!$userData) {
    // If user not found in database, log them out
    endUserSession();
    header('Location: login.php');
    exit();
}

// Initialize variables
$success = '';
$error = '';

// Process form submission for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    if (!empty($current_password) && !empty($new_password)) {
        // Verify current password
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userData['id']]);
        $user = $stmt->fetch();
        
        if (!password_verify($current_password, $user['password'])) {
            $error = 'Current password is incorrect';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters long';
        } else {
            $updateData['password'] = $new_password;
        }
    }
    
    // Update profile if no errors
    if (empty($error)) {
        $result = updateUserProfile($userData['id'], $updateData);
        
        if ($result['status']) {
            $success = 'Profile updated successfully!';
            // Refresh user data
            $userData = getUserById($userData['id']);
        } else {
            $error = $result['message'];
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
    <!-- Include common stylesheets -->
    <?php include PROJECT_ROOT . '/includes/styles.php'; ?>
</head>
<body class="nk-body bg-lighter">
    <div class="nk-app-root">
        <!-- Include the side menu component -->
        <?php include PROJECT_ROOT . '/sidemenu.php'; ?>
        
        <div class="nk-main">
            <!-- Include the header component -->
            <?php include PROJECT_ROOT . '/header.php'; ?>
            
            <!-- Main content -->
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
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            
                            <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                            <?php endif; ?>
                            
                            <div class="nk-block">
                                <div class="card">
                                    <div class="card-inner">
                                        <form action="profile.php" method="post" class="form-validate">
                                            <div class="row g-gs">
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
                                                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($userData['full_name'] ?? ''); ?>">
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
                                                                                <input type="password" class="form-control" id="new_password" name="new_password">
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
                                                    <div class="form-group">
                                                        <button type="submit" class="btn btn-primary">Update Profile</button>
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
                            
                            <div class="nk-block">
                                <?php if (!empty($success)): ?>
                                <div class="alert alert-success"><?php echo $success; ?></div>
                                <?php endif; ?>
                                <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endif; ?>
                                
                                <div class="card">
                                    <div class="card-aside-wrap">
                                        <div class="card-inner card-inner-lg">
                                            <div class="nk-block-head">
                                                <div class="nk-block-head-content">
                                                    <h4 class="nk-block-title">Personal Information</h4>
                                                    <div class="nk-block-des">
                                                        <p>Basic information about your account.</p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="nk-block">
                                                <form action="profile.php" method="post" class="form-validate">
                                                    <div class="row g-gs">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="form-label" for="username">Username</label>
                                                                <div class="form-control-wrap">
                                                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($userData['username']); ?>" required>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="form-label" for="email">Email</label>
                                                                <div class="form-control-wrap">
                                                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="form-label" for="phone">Phone Number</label>
                                                                <div class="form-control-wrap">
                                                                    <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter your phone number">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="form-label" for="dob">Date of Birth</label>
                                                                <div class="form-control-wrap">
                                                                    <input type="date" class="form-control" id="dob" name="dob">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="form-group">
                                                                <button type="submit" class="btn btn-primary">Update Profile</button>
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
            </div>
            
            <!-- Footer -->
            <div class="nk-footer">
                <div class="container-fluid">
                    <div class="nk-footer-wrap">
                        <div class="nk-footer-copyright"> &copy; 2023 Heart Disease AI. All Rights Reserved.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Include common JavaScript -->
    <?php include PROJECT_ROOT . '/includes/scripts.php'; ?>
</body>
</html>