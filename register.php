<?php
// Define PROJECT_ROOT if it hasn't been defined (for local development when not routed through api/index.php)
if (!defined('PROJECT_ROOT')) {
    // Assuming home.php is at the project root
    define('PROJECT_ROOT', __DIR__);
    // If home.php is in a subdirectory, adjust __DIR__ accordingly, e.g., dirname(__DIR__)
}
// Include session management and user functions
require_once PROJECT_ROOT . '/session.php';
require_once PROJECT_ROOT . '/database/set_user.php';
require_once PROJECT_ROOT . '/database/get_user.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: home.php');
    exit();
}

// Initialize variables
$error = '';
$success = '';
$username = '';
$email = '';
$full_name = '';
$date_of_birth = '';
$gender = '';

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $date_of_birth = trim($_POST['date_of_birth'] ?? '');
    $gender = $_POST['gender'] ?? '';
    
    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $error = 'Username must be between 3 and 50 characters';
    } elseif (usernameExists($username)) {
        $error = 'Username already exists. Please choose a different username.';
    } elseif (emailExists($email)) {
        $error = 'Email already exists. Please use a different email address.';
    } else {
        // Register the user
        $result = registerUser($username, $email, $password);
        
        if ($result['status']) {
            // If additional profile data was provided, update the user profile
            if (!empty($full_name) || !empty($date_of_birth) || !empty($gender)) {
                $userData = [
                    'full_name' => $full_name,
                    'date_of_birth' => $date_of_birth ?: null,
                    'gender' => $gender ?: null
                ];
                
                updateUserProfile($result['user_id'], $userData);
            }
            
            $success = 'Registration successful!';
            // Store user data for auto-login
            $user_id = $result['user_id'];
            
            // Clear form data
            $username = '';
            $email = '';
            $full_name = '';
            $date_of_birth = '';
            $gender = '';
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
    <meta name="description" content="Heart Disease Prediction using AI - Register">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <title>Register - Heart Disease Prediction</title>
    <!-- Stylesheets -->
    <?php include PROJECT_ROOT . '/includes/styles.php'; ?>
    
</head>
<body class="nk-body bg-white npc-default pg-auth">
    <div class="nk-app-root">
        <div class="nk-main">
            <div class="nk-wrap nk-wrap-nosidebar">
                <div class="nk-content">
                    <div class="nk-block nk-block-middle nk-auth-body wide-xs mx-auto">
                        <div class="brand-logo pb-4 text-center">
                            <a href="home.php" class="logo-link">
                                <h2>Heart Disease AI</h2>
                            </a>
                        </div>
                        <div class="card">
                            <div class="card-inner card-inner-lg">
                                <div class="nk-block-head">
                                    <div class="nk-block-head-content">
                                        <h4 class="nk-block-title">Register</h4>
                                        <div class="nk-block-des">
                                            <p>Create a new account to access Heart Disease Prediction tools.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- Alert messages will be shown via SweetAlert2 -->
                                <form action="register.php" method="post">
                                    <div class="form-group">
                                        <label class="form-label" for="username">Username</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control form-control-lg" id="username" name="username" placeholder="Enter your username" value="<?php echo htmlspecialchars($username); ?>" required maxlength="50">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="email">Email</label>
                                        <div class="form-control-wrap">
                                            <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="Enter your email address" value="<?php echo htmlspecialchars($email); ?>" required maxlength="50">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="full_name">Full Name</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control form-control-lg" id="full_name" name="full_name" placeholder="Enter your full name" value="<?php echo htmlspecialchars($full_name); ?>" maxlength="50">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="date_of_birth">Date of Birth</label>
                                        <div class="form-control-wrap">
                                            <input type="date" class="form-control form-control-lg" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($date_of_birth); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="gender">Gender</label>
                                        <div class="form-control-wrap">
                                            <select class="form-control form-control-lg" id="gender" name="gender">
                                                <option value="" <?php echo empty($gender) ? 'selected' : ''; ?>>Select Gender</option>
                                                <option value="Male" <?php echo $gender === 'Male' ? 'selected' : ''; ?>>Male</option>
                                                <option value="Female" <?php echo $gender === 'Female' ? 'selected' : ''; ?>>Female</option>
                                                <option value="Other" <?php echo $gender === 'Other' ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="password">Password</label>
                                        <div class="form-control-wrap">
                                            <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Enter your password" required minlength="8">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="confirm_password">Confirm Password</label>
                                        <div class="form-control-wrap">
                                            <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-control-xs custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="terms" name="terms" required>
                                            <label class="custom-control-label" for="terms">I agree to the <a href="javascript:void(0);" id="terms-link">Terms & Conditions</a></label>
                                        </div>
                                        <!-- Hidden div for Terms & Conditions content -->
                                        <div id="terms-content" style="display: none;">
                                            <h4>Terms and Conditions</h4>
                                            <p>Welcome to Heart Disease AI!</p>
                                            <p>These terms and conditions outline the rules and regulations for the use of Heart Disease AI's Website.</p>
                                            <p>By accessing this website we assume you accept these terms and conditions. Do not continue to use Heart Disease AI if you do not agree to take all of the terms and conditions stated on this page.</p>
                                            <p><strong>Cookies:</strong><br>We employ the use of cookies. By accessing Heart Disease AI, you agreed to use cookies in agreement with the Heart Disease AI's Privacy Policy.</p>
                                            <p><strong>License:</strong><br>Unless otherwise stated, Heart Disease AI and/or its licensors own the intellectual property rights for all material on Heart Disease AI. All intellectual property rights are reserved. You may access this from Heart Disease AI for your own personal use subjected to restrictions set in these terms and conditions.</p>
                                            <p>You must not:</p>
                                            <ul>
                                                <li>Republish material from Heart Disease AI</li>
                                                <li>Sell, rent or sub-license material from Heart Disease AI</li>
                                                <li>Reproduce, duplicate or copy material from Heart Disease AI</li>
                                                <li>Redistribute content from Heart Disease AI</li>
                                            </ul>
                                            <p>This Agreement shall begin on the date hereof.</p>
                                            <p><strong>Disclaimer:</strong><br>The predictions provided by this tool are for informational purposes only and should not be considered a substitute for professional medical advice, diagnosis, or treatment. Always seek the advice of your physician or other qualified health provider with any questions you may have regarding a medical condition.</p>
                                            <!-- Add more terms as needed -->
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button class="btn btn-lg btn-primary btn-block">Register</button>
                                    </div>
                                </form>
                                <div class="form-note-s2 text-center pt-4">Already have an account? <a href="login.php">Sign in</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php include PROJECT_ROOT . '/footer.php'; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- JavaScript -->
    <?php include PROJECT_ROOT . '/includes/scripts.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Terms & Conditions Popup
        const termsLink = document.getElementById('terms-link');
        const termsContent = document.getElementById('terms-content');

        if (termsLink && termsContent) {
            termsLink.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default link behavior
                Swal.fire({
                    title: 'Terms & Conditions',
                    html: termsContent.innerHTML, // Use the content from the hidden div
                    icon: 'info',
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#6576ff',
                    width: '80%', // Adjust width as needed
                    textAlign: 'left', // Explicitly set text alignment to left
                    customClass: {
                        htmlContainer: 'text-left' // Keep this for good measure
                    }
                });
            });
        }
    });
    </script>

    
    <?php if (!empty($error)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Registration Error',
                text: '<?php echo addslashes($error); ?>',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#6576ff'
            });
        });
    </script>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Success!',
                text: '<?php echo addslashes($success); ?>',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#6576ff'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Auto-login and redirect to home page
                    <?php if (isset($user_id)): ?>
                    // Set session variables for the user
                    <?php 
                        $user = getUserById($user_id);
                        if ($user) {
                            $_SESSION['user_id'] = $user_id;
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['email'] = $user['email'];
                        }
                    ?>
                    window.location.href = 'home.php';
                    <?php endif; ?>
                }
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>