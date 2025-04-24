<?php
// Include session management
require_once 'session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: home.php');
    exit();
}

// Initialize variables
$error = '';
$email = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        // Include user functions
        require_once 'database/set_user.php';
        
        // Authenticate user
        $result = loginUser($email, $password);
        
        if ($result['status']) {
            // Set user session
            $user = $result['user'];
            setUserSession($user['id'], $user['username'], $user['email']);
            
            // Redirect to dashboard
            header('Location: home.php');
            exit();
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
    <meta name="description" content="Heart Disease Prediction using AI - Login">
    <title>Login - Heart Disease Prediction</title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/dashlite.css">
    <link rel="stylesheet" href="css/theme.css">
</head>
<body class="nk-body bg-white npc-default pg-auth">
    <div class="nk-app-root">
        <div class="nk-main">
            <div class="nk-wrap nk-wrap-nosidebar">
                <div class="nk-content">
                    <div class="nk-block nk-block-middle nk-auth-body wide-xs">
                        <div class="brand-logo pb-4 text-center">
                            <a href="home.php" class="logo-link">
                                <h2>Heart Disease AI</h2>
                            </a>
                        </div>
                        <div class="card">
                            <div class="card-inner card-inner-lg">
                                <div class="nk-block-head">
                                    <div class="nk-block-head-content">
                                        <h4 class="nk-block-title">Sign In</h4>
                                        <div class="nk-block-des">
                                            <p>Access the Heart Disease Prediction dashboard using your email and password.</p>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endif; ?>
                                <form action="login.php" method="post">
                                    <div class="form-group">
                                        <div class="form-label-group">
                                            <label class="form-label" for="email">Email</label>
                                        </div>
                                        <div class="form-control-wrap">
                                            <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="Enter your email address" value="<?php echo htmlspecialchars($email); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="form-label-group">
                                            <label class="form-label" for="password">Password</label>
                                            <!-- <a class="link link-primary link-sm" href="#">Forgot Password?</a> -->
                                        </div>
                                        <div class="form-control-wrap">
                                            <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Enter your password" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button class="btn btn-lg btn-primary btn-block">Sign in</button>
                                    </div>
                                </form>
                                <div class="form-note-s2 text-center pt-4"> New on our platform? <a href="register.php">Create an account</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="nk-footer nk-auth-footer-full">
                        <div class="container wide-lg">
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <div class="nk-block-content text-center text-lg-left">
                                        <p class="text-soft">&copy; 2023 Heart Disease AI. All Rights Reserved.</p>
                                    </div>
                                </div>
                            </div>
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