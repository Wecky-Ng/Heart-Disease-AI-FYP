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
                                        <div id="project-purpose-content" style="display: none;">
                                            <h4>About This Project</h4>
                                            <p>This application, <strong>Heart Disease AI</strong>, is part of a Final Year Project developed by <strong>Wecky Ng Wei Chie</strong>, a student at <strong>Asia Pacific University of Technology and Innovation (APU)</strong>, pursuing a <strong>Bachelor’s Degree in Computer Science with a specialism in Artificial Intelligence</strong>.</p>

                                            <p>The application aims to assist users—especially those who may be unaware of their heart disease risk—by offering a quick and accessible predictive assessment using Artificial Intelligence. It is designed to promote early awareness and is not intended to replace professional medical evaluation or diagnosis.</p>

                                            <p><strong>Important Notice:</strong></p>
                                            <ul>
                                                <li>This tool provides AI-generated predictions based on user input and is for informational purposes only.</li>
                                                <li>It is not a certified medical device and should not be relied upon for any medical decision-making.</li>
                                                <li>The developer is not responsible for any inaccurate predictions or for any medical, legal, financial, or personal consequences resulting from the use of this application.</li>
                                                <li>Users are strongly advised to consult a qualified healthcare professional for any medical concerns or symptoms.</li>
                                            </ul>

                                            <p><strong>Data Privacy:</strong></p>
                                            <ul>
                                                <li>Providing health-related data in this app is entirely voluntary.</li>
                                                <li>You may choose not to submit any personal or health information at any point.</li>
                                                <li>All information entered will be processed securely and confidentially for the sole purpose of generating predictions.</li>
                                                <li>No personally identifiable information will be shared or stored without your clear permission.</li>
                                            </ul>

                                            <p>If you have questions about the project or app, you may contact the developer:</p>
                                            <ul>
                                                <li><strong>Name:</strong> Wecky Ng Wei Chie</li>
                                                <li><strong>TP Number:</strong> TP051083</li>
                                                <li><strong>Email:</strong> TP051083@mail.apu.edu.my (Preferred: MS Teams)</li>
                                                <li><strong>Phone:</strong> +60182322119 (Preferred: WhatsApp Message)</li>
                                            </ul>

                                            <p>Thank you for using Heart Disease AI and supporting this educational research project.</p>
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