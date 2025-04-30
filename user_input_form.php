<?php
// Define PROJECT_ROOT if it hasn't been defined (for local development when not routed through api/index.php)
if (!defined('PROJECT_ROOT')) {
    // Assuming user_input_form.php is at the project root
    define('PROJECT_ROOT', __DIR__);
    // If user_input_form.php is in a subdirectory, adjust __DIR__ accordingly, e.g., dirname(__DIR__)
}
// Include session management and user functions
require_once PROJECT_ROOT . '/session.php';
require_once PROJECT_ROOT . '/database/get_user.php';
// Include the file containing database functions, including getLastTestRecord
require_once PROJECT_ROOT . '/database/get_user_prediction_history.php'; // Now includes getLastTestRecord

// Check for session error messages
$session_error = '';
if (isset($_SESSION['error'])) {
    $session_error = $_SESSION['error'];
    unset($_SESSION['error']); // Clear the error message after retrieving it
}

// Initialize variables for form prefill
$genderPrefillValue = '';
$agePrefillValue = ''; // Initialize age prefill value
$userData = null;
$lastTestData = null;

// Removed the local getLastTestRecord function definition from here


// Check if user is logged in
if (isLoggedIn()) {
    // Get current user session data
    $sessionData = getCurrentUser();

    // Get user data from database
    if (isset($sessionData['user_id'])) {
        $userId = $sessionData['user_id'];
        $userData = getUserById($userId); // Assuming getUserById is in database/get_user.php

        // Get user's last test record if available
        // Call the function from the included database file
        $lastTestData = getLastTestRecord($userId); // Now calling the function defined in get_user_prediction_history.php

        // --- Prefill Logic ---

        // Prefill Gender: Prioritize last test data, then user profile
        // Note: The last test record stores 'sex' (0=Female, 1=Male), while user profile stores 'gender' (Male/Female/Other string)
        if ($lastTestData && isset($lastTestData['sex']) && $lastTestData['sex'] !== null) {
            // Map tinyint sex from last test record to string for comparison if needed,
            // or directly use the tinyint value if your form select options match (0/1)
            $genderPrefillValue = htmlspecialchars($lastTestData['sex']); // Use the tinyint value directly
        } elseif ($userData && !empty($userData['gender'])) {
            // Map gender string from user profile to the tinyint value expected by the form (0 for Female, 1 for Male)
            // Assuming 'Male' -> 1, 'Female' -> 0 based on your DB schema comment for user_prediction_history.sex
            $genderPrefillValue = ($userData['gender'] === 'Male') ? '1' : '0';
             // Handle 'Other' if necessary, perhaps default to empty or a specific value
             if ($userData['gender'] === 'Other') {
                 $genderPrefillValue = ''; // Or handle 'Other' as needed
             }
        } else {
             $genderPrefillValue = ''; // No gender data available
        }


        // Prefill Age: Prioritize last test data, then calculate from date of birth
        if ($lastTestData && isset($lastTestData['age']) && $lastTestData['age'] !== null) {
            $agePrefillValue = htmlspecialchars($lastTestData['age']);
        } elseif ($userData && !empty($userData['date_of_birth'])) {
            // Calculate age from date of birth
            try {
                $birthDate = new DateTime($userData['date_of_birth']);
                $today = new DateTime('today');
                $age = $birthDate->diff($today)->y;
                $agePrefillValue = $age;
            } catch (Exception $e) {
                error_log("Error calculating age from date of birth for user ID {$userId}: " . $e->getMessage());
                $agePrefillValue = ''; // Fallback if date calculation fails
            }
        } else {
             $agePrefillValue = ''; // No age data available
        }
    }
} else {
    // If not logged in, no prefill data is available
    $genderPrefillValue = '';
    $agePrefillValue = '';
    $lastTestData = null; // Ensure this is null if not logged in
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Heart Disease Prediction using AI">
    <title>Heart Disease Prediction</title>
    <?php include PROJECT_ROOT . '/includes/styles.php'; ?>
    <style>
        .card-prediction {
            transition: all 0.3s ease;
        }
        .card-prediction:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(43, 84, 180, 0.15);
        }
        .result-box {
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
            display: none;
        }
        .result-high {
            background-color: rgba(255, 91, 91, 0.1);
            border-left: 4px solid #ff5b5b;
        }
        .result-low {
            background-color: rgba(46, 204, 113, 0.1);
            border-left: 4px solid #2ecc71;
        }
        .result-medium {
            background-color: rgba(255, 165, 0, 0.1);
            border-left: 4px solid #ffa500;
        }
        @media (max-width: 768px) {
            .form-group {
                margin-bottom: 1rem;
            }
            .card-inner {
                padding: 1.25rem;
            }
        }
    </style>
</head>
<body class="nk-body bg-lighter">
    <div class="nk-app-root">
        <?php include PROJECT_ROOT . '/sidemenu.php'; ?>

        <div class="nk-sidebar-overlay" data-target="sidebarMenu"></div>

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
                                            <h3 class="nk-block-title page-title">Heart Disease Prediction</h3>
                                            <div class="nk-block-des text-soft">
                                                <p>Enter your health parameters to predict heart disease risk.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Display Session Error using a hidden input for JS -->
                                <input type="hidden" id="session-error-message" value="<?php echo htmlspecialchars($session_error); ?>">

                                <div class="nk-block">
                                    <div class="row g-gs">
                                        <div class="col-lg-8">
                                            <div class="card card-bordered h-100 card-prediction">
                                                <div class="card-inner">
                                                    <div class="card-head">
                                                        <h5 class="card-title">Health Parameters</h5>
                                                    </div>
                                                    <?php if (isLoggedIn() && ($genderPrefillValue !== '' || $agePrefillValue !== '' || $lastTestData)): ?>
                                                    <div class="alert alert-info">
                                                        <div class="alert-icon"><em class="icon ni ni-info-fill"></em></div>
                                                        <div class="alert-text">Some fields have been prefilled with your profile data and previous test information.</div>
                                                    </div>
                                                    <?php endif; ?>
                                                    <form id="prediction-form" action="result.php" method="post" class="form-validate">
                                                        <div class="row g-4">
                                                            <div class="col-12">
                                                                <h6 class="overline-title text-primary">Basic Information</h6>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="sex">Gender</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="sex" name="sex" required>
                                                                            <option value="">Select</option>
                                                                            <option value="0" <?php echo ($genderPrefillValue === '0') ? 'selected' : ''; ?>>Female</option>
                                                                            <option value="1" <?php echo ($genderPrefillValue === '1') ? 'selected' : ''; ?>>Male</option>
                                                                            </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="age">Age</label>
                                                                    <div class="form-control-wrap">
                                                                        <input type="number" class="form-control" id="age" name="age" min="18" max="120" value="<?php echo htmlspecialchars($agePrefillValue); ?>" required>
                                                                        <small class="form-text text-muted">Enter your age in years (e.g., 45)</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="race">Race/Ethnicity</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="race" name="race" required>
                                                                            <option value="">Select</option>
                                                                            <option value="0" <?php echo ($lastTestData && isset($lastTestData['raw_data']['race']) && $lastTestData['raw_data']['race'] === 0) ? 'selected' : ''; ?>>White</option>
                                                                            <option value="1" <?php echo ($lastTestData && isset($lastTestData['raw_data']['race']) && $lastTestData['raw_data']['race'] === 1) ? 'selected' : ''; ?>>Black</option>
                                                                            <option value="2" <?php echo ($lastTestData && isset($lastTestData['raw_data']['race']) && $lastTestData['raw_data']['race'] === 2) ? 'selected' : ''; ?>>Asian</option>
                                                                            <option value="3" <?php echo ($lastTestData && isset($lastTestData['raw_data']['race']) && $lastTestData['raw_data']['race'] === 3) ? 'selected' : ''; ?>>Hispanic</option>
                                                                            <option value="4" <?php echo ($lastTestData && isset($lastTestData['raw_data']['race']) && $lastTestData['raw_data']['race'] === 4) ? 'selected' : ''; ?>>American Indian/Alaskan Native</option>
                                                                            <option value="5" <?php echo ($lastTestData && isset($lastTestData['raw_data']['race']) && $lastTestData['raw_data']['race'] === 5) ? 'selected' : ''; ?>>Other</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="bmi">BMI</label>
                                                                    <div class="form-control-wrap">
                                                                        <input type="number" step="0.01" class="form-control" id="bmi" name="bmi" min="10" max="60" value="<?php echo ($lastTestData && isset($lastTestData['raw_data']['bmi'])) ? htmlspecialchars($lastTestData['raw_data']['bmi']) : ''; ?>" required maxlength="50">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-12">
                                                                <h6 class="overline-title text-primary mt-3">Health Indicators</h6>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="smoking">Smoking</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="smoking" name="smoking" required>
                                                                            <option value="">Select</option>
                                                                            <option value="1" <?php echo ($lastTestData && isset($lastTestData['raw_data']['smoking']) && $lastTestData['raw_data']['smoking'] === 1) ? 'selected' : ''; ?>>Yes</option>
                                                                            <option value="0" <?php echo ($lastTestData && isset($lastTestData['raw_data']['smoking']) && $lastTestData['raw_data']['smoking'] === 0) ? 'selected' : ''; ?>>No</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="alcohol_drinking">Alcohol Drinking</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="alcohol_drinking" name="alcohol_drinking" required>
                                                                            <option value="">Select</option>
                                                                            <option value="1" <?php echo ($lastTestData && isset($lastTestData['raw_data']['alcohol_drinking']) && $lastTestData['raw_data']['alcohol_drinking'] === 1) ? 'selected' : ''; ?>>Yes</option>
                                                                            <option value="0" <?php echo ($lastTestData && isset($lastTestData['raw_data']['alcohol_drinking']) && $lastTestData['raw_data']['alcohol_drinking'] === 0) ? 'selected' : ''; ?>>No</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="stroke">Had Stroke</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="stroke" name="stroke" required>
                                                                            <option value="">Select</option>
                                                                            <option value="1" <?php echo ($lastTestData && isset($lastTestData['raw_data']['stroke']) && $lastTestData['raw_data']['stroke'] === 1) ? 'selected' : ''; ?>>Yes</option>
                                                                            <option value="0" <?php echo ($lastTestData && isset($lastTestData['raw_data']['stroke']) && $lastTestData['raw_data']['stroke'] === 0) ? 'selected' : ''; ?>>No</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="physical_health">Physical Health (days)</label>
                                                                    <div class="form-control-wrap">
                                                                        <input type="number" step="0.1" class="form-control" id="physical_health" name="physical_health" min="0" max="30" value="<?php echo ($lastTestData && isset($lastTestData['raw_data']['physical_health'])) ? htmlspecialchars($lastTestData['raw_data']['physical_health']) : ''; ?>" required maxlength="50">
                                                                        <small class="form-text text-muted">Number of days physical health not good (0-30)</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="mental_health">Mental Health (days)</label>
                                                                    <div class="form-control-wrap">
                                                                        <input type="number" step="0.1" class="form-control" id="mental_health" name="mental_health" min="0" max="30" value="<?php echo ($lastTestData && isset($lastTestData['raw_data']['mental_health'])) ? htmlspecialchars($lastTestData['raw_data']['mental_health']) : ''; ?>" required maxlength="50">
                                                                        <small class="form-text text-muted">Number of days mental health not good (0-30)</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="diff_walking">Difficulty Walking</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="diff_walking" name="diff_walking" required>
                                                                            <option value="">Select</option>
                                                                            <option value="1" <?php echo ($lastTestData && isset($lastTestData['raw_data']['diff_walking']) && $lastTestData['raw_data']['diff_walking'] === 1) ? 'selected' : ''; ?>>Yes</option>
                                                                            <option value="0" <?php echo ($lastTestData && isset($lastTestData['raw_data']['diff_walking']) && $lastTestData['raw_data']['diff_walking'] === 0) ? 'selected' : ''; ?>>No</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-12">
                                                                <h6 class="overline-title text-primary mt-3">Medical Conditions</h6>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="diabetic">Diabetic</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="diabetic" name="diabetic" required>
                                                                            <option value="">Select</option>
                                                                            <option value="1" <?php echo ($lastTestData && isset($lastTestData['raw_data']['diabetic']) && $lastTestData['raw_data']['diabetic'] === 1) ? 'selected' : ''; ?>>Yes</option>
                                                                            <option value="0" <?php echo ($lastTestData && isset($lastTestData['raw_data']['diabetic']) && $lastTestData['raw_data']['diabetic'] === 0) ? 'selected' : ''; ?>>No</option>
                                                                            <option value="2" <?php echo ($lastTestData && isset($lastTestData['raw_data']['diabetic']) && $lastTestData['raw_data']['diabetic'] === 2) ? 'selected' : ''; ?>>No, borderline diabetes</option>
                                                                            <option value="3" <?php echo ($lastTestData && isset($lastTestData['raw_data']['diabetic']) && $lastTestData['raw_data']['diabetic'] === 3) ? 'selected' : ''; ?>>Yes (during pregnancy)</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="physical_activity">Physical Activity</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="physical_activity" name="physical_activity" required>
                                                                            <option value="">Select</option>
                                                                            <option value="1" <?php echo ($lastTestData && isset($lastTestData['raw_data']['physical_activity']) && $lastTestData['raw_data']['physical_activity'] === 1) ? 'selected' : ''; ?>>Yes</option>
                                                                            <option value="0" <?php echo ($lastTestData && isset($lastTestData['raw_data']['physical_activity']) && $lastTestData['raw_data']['physical_activity'] === 0) ? 'selected' : ''; ?>>No</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="gen_health">General Health</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="gen_health" name="gen_health" required>
                                                                            <option value="">Select</option>
                                                                            <option value="0" <?php echo ($lastTestData && isset($lastTestData['raw_data']['gen_health']) && $lastTestData['raw_data']['gen_health'] === 0) ? 'selected' : ''; ?>>Excellent</option>
                                                                            <option value="1" <?php echo ($lastTestData && isset($lastTestData['raw_data']['gen_health']) && $lastTestData['raw_data']['gen_health'] === 1) ? 'selected' : ''; ?>>Very good</option>
                                                                            <option value="2" <?php echo ($lastTestData && isset($lastTestData['raw_data']['gen_health']) && $lastTestData['raw_data']['gen_health'] === 2) ? 'selected' : ''; ?>>Good</option>
                                                                            <option value="3" <?php echo ($lastTestData && isset($lastTestData['raw_data']['gen_health']) && $lastTestData['raw_data']['gen_health'] === 3) ? 'selected' : ''; ?>>Fair</option>
                                                                            <option value="4" <?php echo ($lastTestData && isset($lastTestData['raw_data']['gen_health']) && $lastTestData['raw_data']['gen_health'] === 4) ? 'selected' : ''; ?>>Poor</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="sleep_time">Sleep Time (hours)</label>
                                                                    <div class="form-control-wrap">
                                                                        <input type="number" step="0.1" class="form-control" id="sleep_time" name="sleep_time" min="0" max="24" value="<?php echo ($lastTestData && isset($lastTestData['raw_data']['sleep_time'])) ? htmlspecialchars($lastTestData['raw_data']['sleep_time']) : ''; ?>" required maxlength="50">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="asthma">Asthma</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="asthma" name="asthma" required>
                                                                            <option value="">Select</option>
                                                                            <option value="1" <?php echo ($lastTestData && isset($lastTestData['raw_data']['asthma']) && $lastTestData['raw_data']['asthma'] === 1) ? 'selected' : ''; ?>>Yes</option>
                                                                            <option value="0" <?php echo ($lastTestData && isset($lastTestData['raw_data']['asthma']) && $lastTestData['raw_data']['asthma'] === 0) ? 'selected' : ''; ?>>No</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="kidney_disease">Kidney Disease</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="kidney_disease" name="kidney_disease" required>
                                                                            <option value="">Select</option>
                                                                            <option value="1" <?php echo ($lastTestData && isset($lastTestData['raw_data']['kidney_disease']) && $lastTestData['raw_data']['kidney_disease'] === 1) ? 'selected' : ''; ?>>Yes</option>
                                                                            <option value="0" <?php echo ($lastTestData && isset($lastTestData['raw_data']['kidney_disease']) && $lastTestData['raw_data']['kidney_disease'] === 0) ? 'selected' : ''; ?>>No</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="skin_cancer">Skin Cancer</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="skin_cancer" name="skin_cancer" required>
                                                                            <option value="">Select</option>
                                                                            <option value="1" <?php echo ($lastTestData && isset($lastTestData['raw_data']['skin_cancer']) && $lastTestData['raw_data']['skin_cancer'] === 1) ? 'selected' : ''; ?>>Yes</option>
                                                                            <option value="0" <?php echo ($lastTestData && isset($lastTestData['raw_data']['skin_cancer']) && $lastTestData['raw_data']['skin_cancer'] === 0) ? 'selected' : ''; ?>>No</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-12 mt-3">
                                                                <?php if (isLoggedIn()): ?>
                                                                <div class="form-group">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input" id="save_record" name="save_record" value="1" <?php echo ($lastTestData) ? 'checked' : ''; // Check only based on last test data if logged in ?>>
                                                                        <label class="custom-control-label" for="save_record">Save this prediction to my history</label>
                                                                        <br>
                                                                        <p class="text-muted small mt-1">If unchecked, this prediction will not be stored in our database for privacy reasons.</p>
                                                                    </div>
                                                                </div>
                                                                <?php else: ?>
                                                                <p class="text-muted small mt-1">For your privacy concerns, your record will not be collected.</p>
                                                                <?php endif; ?>
                                                            </div>

                                                            <div class="col-12">
                                                                <div class="form-group">
                                                                    <button type="submit" class="btn btn-lg btn-primary">Predict Heart Disease Risk</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="card card-bordered h-100">
                                                <div class="card-inner">
                                                    <div class="card-head">
                                                        <h5 class="card-title">Prediction Result</h5>
                                                    </div>
                                                    <!-- Prediction result will now be shown via SweetAlert -->
                                                    <div class="card-head">
                                                        <h5 class="card-title">About This Prediction</h5>
                                                    </div>
                                                    <p>This heart disease prediction tool uses a machine learning model trained on the CDC's 2020 heart disease dataset. The model analyzes various health parameters to estimate your risk of heart disease.</p>

                                                    <h6 class="overline-title text-primary mt-4">Key Risk Factors</h6>
                                                    <ul class="list list-sm list-checked">
                                                        <li>BMI (Body Mass Index)</li>
                                                        <li>Smoking and alcohol consumption</li>
                                                        <li>Previous stroke history</li>
                                                        <li>Physical and mental health</li>
                                                        <li>Diabetes status</li>
                                                        <li>Physical activity level</li>
                                                        <li>Sleep patterns</li>
                                                        <li>Other medical conditions</li>
                                                    </ul>
                                                    <div class="alert alert-warning mt-3">
                                                        <div class="alert-icon"><em class="icon ni ni-alert-circle-fill"></em></div>
                                                        <strong>Disclaimer:</strong> This tool provides an estimate only and should not replace professional medical advice. Always consult with a healthcare provider for proper diagnosis and treatment.
                                                    </div>
                                                </div>
                                            </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('prediction-form');
            // const resultDisplay = document.getElementById('prediction-result-display'); // Removed as we use SweetAlert
            const submitButton = form.querySelector('button[type="submit"]');
            // Assuming the button structure might change, let's select elements robustly
            // const spinner = submitButton.querySelector('.spinner-border');
            // const buttonText = submitButton.querySelector('.button-text');
            const originalButtonHTML = submitButton.innerHTML; // Store original button content
            const formElements = form.elements;
            const sessionErrorMessage = document.getElementById('session-error-message').value;

            // Display session error if present
            if (sessionErrorMessage) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: sessionErrorMessage,
                    confirmButtonColor: '#5a62c8'
                });
            }

            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent default form submission

                // Disable form elements and button
                for (let i = 0; i < formElements.length; i++) {
                    formElements[i].disabled = true;
                }
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';

                // Show loading alert
                Swal.fire({
                    title: 'Processing...',
                    text: 'Analyzing your data, please wait.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const formData = new FormData(form);
                const jsonData = {};
                formData.forEach((value, key) => { jsonData[key] = value; });

                // Send data to the API endpoint
                fetch('https://heart-disease-prediction-api-84fu.onrender.com/predict', { // Ensure this endpoint is correct
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(jsonData)
                })
                .then(response => {
                    if (!response.ok) {
                        // Try to parse error message from response body
                        return response.json().then(err => {
                            throw new Error(err.error || `Server error: ${response.status}`);
                        }).catch(() => {
                            // Fallback if response body is not JSON or empty
                            throw new Error(`Network error: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    Swal.close(); // Close loading alert

                    // Re-enable form elements and restore button
                    for (let i = 0; i < formElements.length; i++) {
                        formElements[i].disabled = false;
                    }
                    submitButton.innerHTML = originalButtonHTML;

                    // Display result using SweetAlert
                    let iconType = 'info';
                    let titleText = 'Prediction Result';
                    // Adjust based on your actual API response structure
                    let resultText = `Prediction: ${data.prediction_text || 'N/A'} (Probability: ${data.probability_percentage || 'N/A'}%)`;

                    if (data.prediction === 1) { // Assuming 1 means high risk
                        iconType = 'warning';
                        titleText = 'High Risk Detected';
                    } else if (data.prediction === 0) { // Assuming 0 means low risk
                        iconType = 'success';
                        titleText = 'Low Risk Detected';
                    }

                    Swal.fire({
                        icon: iconType,
                        title: titleText,
                        html: resultText + '<br><br><a href="/history.php" class="btn btn-primary btn-sm">View Details in History</a>',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#5a62c8'
                    });

                    // Optionally clear the form after successful prediction
                    // form.reset();

                })
                .catch(error => {
                    Swal.close(); // Close loading alert

                    // Re-enable form elements and restore button
                    for (let i = 0; i < formElements.length; i++) {
                        formElements[i].disabled = false;
                    }
                    submitButton.innerHTML = originalButtonHTML;

                    console.error('Prediction Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Prediction Failed',
                        text: error.message || 'An unexpected error occurred. Please check the console or try again.',
                        confirmButtonColor: '#e85347'
                    });
                });
            });
        });
    </script>
                event.preventDefault(); // Prevent default form submission

                // Show spinner and disable button
                spinner.style.display = 'inline-block';
                buttonText.textContent = 'Predicting...';
                submitButton.disabled = true;
                resultDisplay.style.display = 'none'; // Hide previous results
                resultDisplay.innerHTML = ''; // Clear previous results

                const formData = new FormData(form);
                const formObject = {};
                formData.forEach((value, key) => {
                    // Convert numeric string fields to numbers where appropriate
                    // Adjust this list based on your actual numeric fields
                    if (['bmi', 'physical_health', 'mental_health', 'age', 'sleep_time'].includes(key)) {
                        formObject[key] = parseFloat(value) || 0; // Use parseFloat, handle NaN with 0 or null
                    } else if (['smoking', 'alcohol_drinking', 'stroke', 'diff_walking', 'sex', 'race', 'diabetic', 'physical_activity', 'gen_health', 'asthma', 'kidney_disease', 'skin_cancer'].includes(key)) {
                        formObject[key] = parseInt(value, 10); // Ensure integer for categorical/binary
                    } else {
                        formObject[key] = value;
                    }
                });

                // Call the Vercel prediction API
                fetch('/api/predict', { // Use relative path for Vercel deployment
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formObject)
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw new Error(err.error || 'Network response was not ok'); });
                    }
                    return response.json();
                })
                .then(data => {
                    // --- NEW LOGIC: Save prediction then show SweetAlert & Redirect ---
                    const predictionResult = data.prediction; // 0 or 1
                    const confidenceScore = data.confidence;

                    // Prepare data for saving
                    const saveData = {
                        inputs: formObject, // Send the processed form data
                        prediction: predictionResult,
                        confidence: confidenceScore
                    };

                    // 1. Asynchronously save the prediction
                    fetch('/database/save_prediction.php', { // Call the new PHP endpoint
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(saveData)
                    })
                    .then(saveResponse => saveResponse.json())
                    .then(saveResult => {
                        if (saveResult.success) {
                            console.log('Prediction saved successfully.');
                            // 2. Show SweetAlert with result
                            const riskLevel = predictionResult === 1 ? 'High Risk' : 'Low Risk';
                            const confidencePercent = (confidenceScore * 100).toFixed(2);
                            const alertIcon = predictionResult === 1 ? 'warning' : 'success';
                            const alertTitle = `Prediction: ${riskLevel}`;
                            const alertText = `Confidence: ${confidencePercent}%. Click OK to view details.`;

                            Swal.fire({
                                icon: alertIcon,
                                title: alertTitle,
                                text: alertText,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#5a62c8'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // 3. Redirect to result.php via POST
                                    const postForm = document.createElement('form');
                                    postForm.method = 'POST';
                                    postForm.action = 'result.php';
                                    postForm.style.display = 'none'; // Hide the form

                                    // Add original form data as hidden inputs
                                    for (const key in formObject) {
                                        if (formObject.hasOwnProperty(key)) {
                                            const input = document.createElement('input');
                                            input.type = 'hidden';
                                            input.name = key;
                                            input.value = formObject[key];
                                            postForm.appendChild(input);
                                        }
                                    }

                                    // Add prediction results as hidden inputs
                                    const predictionInput = document.createElement('input');
                                    predictionInput.type = 'hidden';
                                    predictionInput.name = 'prediction_result';
                                    predictionInput.value = predictionResult;
                                    postForm.appendChild(predictionInput);

                                    const confidenceInput = document.createElement('input');
                                    confidenceInput.type = 'hidden';
                                    confidenceInput.name = 'prediction_confidence';
                                    confidenceInput.value = confidenceScore;
                                    postForm.appendChild(confidenceInput);

                                    document.body.appendChild(postForm);
                                    postForm.submit();
                                }
                            });
                        } else {
                            // Saving failed
                            console.error('Failed to save prediction:', saveResult.message);
                            Swal.fire({
                                icon: 'error',
                                title: 'Save Error',
                                text: 'Could not save the prediction result. Please try again. ' + (saveResult.message || ''),
                                confirmButtonColor: '#e74c3c'
                            });
                        }
                    })
                    .catch(saveError => {
                        console.error('Error saving prediction:', saveError);
                        Swal.fire({
                            icon: 'error',
                            title: 'Save Error',
                            text: 'An error occurred while trying to save the prediction result: ' + saveError.message,
                            confirmButtonColor: '#e74c3c'
                        });
                    })
                    .finally(() => {
                         // Re-enable button regardless of save outcome, but only after save attempt
                         resetButton();
                    });
                    // --- END NEW LOGIC ---

                    // --- OLD LOGIC (Remove/Comment Out) ---
                    /*
                    const riskLevel = data.prediction === 1 ? 'High Risk' : 'Low Risk';
                    const confidencePercent = (data.confidence * 100).toFixed(2);
                    const resultClass = data.prediction === 1 ? 'result-high' : 'result-low';

                    resultDisplay.innerHTML = `
                        <div class="result-box ${resultClass}">
                            <h6 class="title">Risk Level: ${riskLevel}</h6>
                            <p>Confidence: ${confidencePercent}%</p>
                        </div>
                    `;
                    resultDisplay.style.display = 'block';
                    */
                    // --- END OLD LOGIC ---
                })
                .catch(error => {
                    console.error('Error during prediction fetch:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Prediction Error',
                        text: 'Failed to get prediction: ' + error.message,
                        confirmButtonColor: '#e74c3c'
                    });
                    resetButton(); // Reset button on fetch error
                });
                // Note: resetButton() is now called within the save promise chain's finally block or catch block
            });

            function resetButton() {
                spinner.style.display = 'none';
                buttonText.textContent = 'Predict';
                submitButton.disabled = false;
            }
        });
    </script>
</body>
</html>