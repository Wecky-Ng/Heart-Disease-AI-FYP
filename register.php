<?php
// Define PROJECT_ROOT if it hasn't been defined (for local development when not routed through api/index.php)
if (!defined('PROJECT_ROOT')) {
    // Assuming register.php is at the project root
    define('PROJECT_ROOT', __DIR__);
    // If register.php is in a subdirectory, adjust __DIR__ accordingly, e.g., dirname(__DIR__)
}
// Include session management and user functions
require_once PROJECT_ROOT . '/session.php';
require_once PROJECT_ROOT . '/database/set_user.php'; // Assuming this contains registerUser and updateUserProfile
require_once PROJECT_ROOT . '/database/get_user.php'; // Assuming this contains usernameExists, emailExists, and getUserById

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: home.php');
    exit();
}

// Initialize variables
$errors = []; // Changed from single $error string to an array of errors
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

    // --- Validation Checks (using independent if statements to collect all errors) ---

    // Check for empty required fields
    if (empty($username)) {
        $errors[] = 'Username is required.';
    }
    if (empty($email)) {
        $errors[] = 'Email is required.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }
    if (empty($confirmPassword)) {
        $errors[] = 'Confirm Password is required.';
    }

    // Check if passwords match (only if both are provided)
    if (!empty($password) && !empty($confirmPassword) && $password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    // Check password length (only if password is provided)
    if (!empty($password) && strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    // Email format validation (This will now run even if other errors exist if email is not empty)
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    // Username length validation (only if username is provided)
    if (!empty($username) && (strlen($username) < 3 || strlen($username) > 50)) {
        $errors[] = 'Username must be between 3 and 50 characters.';
    }

    // Check if username or email exists (only if basic format/presence validation passes to avoid unnecessary DB calls)
    // Check if the errors array *so far* only contains required field errors before checking existence
    $basic_errors_only = true;
    foreach ($errors as $err) {
        if (strpos($err, 'required') === false) {
            $basic_errors_only = false;
            break;
        }
    }

    if ($basic_errors_only) {
        if (!empty($username) && usernameExists($username)) {
            $errors[] = 'Username already exists. Please choose a different username.';
        }
        if (!empty($email) && emailExists($email)) {
            $errors[] = 'Email already exists. Please use a different email address.';
        }
    } else {
        // If there are other validation errors (like format or length),
        // we skip the existence checks until those are fixed by the user.
        // This prevents checking existence for, say, an invalid email format.
    }


    // --- Process Registration if no errors ---
    if (empty($errors)) {
        // Register the user
        // Assuming registerUser($username, $email, $password) returns ['status' => bool, 'user_id' => int, 'message' => string]
        $result = registerUser($username, $email, $password);

        if ($result['status']) {
            // If additional profile data was provided, update the user profile
            // Ensure $result['user_id'] is set by registerUser on success
            if (isset($result['user_id']) && (!empty($full_name) || !empty($date_of_birth) || !empty($gender))) {
                $userData = [
                    'full_name' => $full_name,
                    'date_of_birth' => !empty($date_of_birth) ? $date_of_birth : null, // Ensure empty string is stored as null
                    'gender' => !empty($gender) ? $gender : null // Ensure empty string is stored as null
                ];

                // Assuming updateUserProfile($user_id, $data) exists
                updateUserProfile($result['user_id'], $userData);
            }

            $success = 'Registration successful!';
            // Store user data for auto-login (prepare for redirect)
            $user_id = $result['user_id'] ?? null; // Get user ID from result if available

            // IMPORTANT: Set session variables *before* outputting HTML and JS if auto-login is desired
            if (isset($user_id) && $user = getUserById($user_id)) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                // The redirect will happen in the SweetAlert then() clause
            }


            // Clear form data on success only after potentially setting session vars
            $username = '';
            $email = '';
            $full_name = '';
            $date_of_birth = '';
            $gender = '';
        } else {
            // Handle specific registration function errors and add to errors array
            $errors[] = $result['message'] ?? 'An unknown registration error occurred.';
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
                                <form action="register.php" method="post" class="form-validate is-alter"> <?php /* Added form-validate class for potential client-side validation */ ?>
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
                                    </div>
                                    <div class="form-group">
                                        <button class="btn btn-lg btn-primary btn-block">Register</button>
                                    </div>
                                </form>
                                <div class="form-note-s2 text-center pt-4">Already have an account? <a href="login.php">Sign in</a>
                                </div>
                            </div>
                        </div>
                        <?php /* Placed outside the form but within the main content area */ ?>
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
                    <?php include PROJECT_ROOT . '/footer.php'; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include PROJECT_ROOT . '/includes/scripts.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Terms & Conditions Popup
            const termsLink = document.getElementById('terms-link');
            const termsContent = document.getElementById('project-purpose-content'); // Corrected ID reference

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
                        // SweetAlert text is usually left-aligned by default, remove explicit classes unless needed
                    });
                });
            }

            // SweetAlert2 for displaying errors (now handles an array)
            <?php if (!empty($errors)): ?>
                <?php
                // Prepare messages for JavaScript.
                // 1. htmlspecialchars: prevents XSS if messages contain user input like <script>
                // 2. implode("<br>", ...): joins messages with <br> for multi-line display in HTML
                // 3. addslashes: escapes quotes and backslashes for the JavaScript string literal
                $js_error_messages = addslashes(implode("<br>", array_map("htmlspecialchars", $errors)));
                ?>
                Swal.fire({
                    title: 'Registration Error',
                    html: '<?php echo $js_error_messages; ?>', // Use html property to render <br>
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#6576ff'
                });
            <?php endif; ?>

            // SweetAlert2 for displaying success
            <?php if (!empty($success)): ?>
                Swal.fire({
                    title: 'Success!',
                    text: '<?php echo addslashes($success); ?>',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#6576ff'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirect after success message
                        // The PHP code *must* set the session variables for auto-login BEFORE this script runs.
                        // The presence of the user_id variable in the PHP scope indicates successful registration and potential session setting.
                        <?php if (isset($user_id) && $user_id !== null): ?>
                            // Assuming session was set successfully in PHP after registration
                            window.location.href = 'home.php';
                        <?php else: ?>
                            // Fallback redirect if for some reason user_id wasn't set or auto-login logic needs review
                            window.location.href = 'login.php'; // Or home.php depending on desired flow without auto-login
                        <?php endif; ?>
                    }
                });
            <?php endif; ?>
        });
    </script>
</body>

</html>