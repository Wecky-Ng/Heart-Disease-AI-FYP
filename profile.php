<?php
// Include session management
require_once 'session.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get current user data
$userData = getCurrentUser();

// Initialize variables
$success = '';
$error = '';

// Process form submission for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real application, you would update the user's profile in the database
    // For demonstration purposes, we'll just show a success message
    $success = 'Profile updated successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Heart Disease Prediction using AI - User Profile">
    <title>User Profile - Heart Disease Prediction</title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/dashlite.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="nk-body bg-lighter">
    <div class="nk-app-root">
        <!-- Include the side menu component -->
        <?php include 'sidemenu.php'; ?>
        
        <div class="nk-main">
            <!-- Include the header component -->
            <?php include 'header.php'; ?>
            
            <!-- Main content -->
            <div class="nk-content">
                <div class="container-fluid">
                    <div class="nk-content-inner">
                        <div class="nk-content-body">
                            <div class="nk-block-head nk-block-head-sm">
                                <div class="nk-block-between">
                                    <div class="nk-block-head-content">
                                        <h3 class="nk-block-title page-title">User Profile</h3>
                                        <div class="nk-block-des text-soft">
                                            <p>View and update your profile information.</p>
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
    
    <!-- JavaScript -->
    <script src="js/bundle.js"></script>
    <script src="js/scripts.js"></script>
</body>
</html>