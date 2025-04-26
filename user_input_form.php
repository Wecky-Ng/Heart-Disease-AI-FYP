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
// require_once PROJECT_ROOT . '/database/connection.php'; // connection.php is now included by get_user.php and get_user_prediction_history.php

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
                                                                        <input type="number" class="form-control" id="age" name="age" min="1" max="120" value="<?php echo htmlspecialchars($agePrefillValue); ?>" required>
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
                                                                        <input type="number" step="0.01" class="form-control" id="bmi" name="bmi" min="10" max="60" value="<?php echo ($lastTestData && isset($lastTestData['raw_data']['bmi'])) ? htmlspecialchars($lastTestData['raw_data']['bmi']) : ''; ?>" required>
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
                                                                        <input type="number" step="0.1" class="form-control" id="physical_health" name="physical_health" min="0" max="30" value="<?php echo ($lastTestData && isset($lastTestData['raw_data']['physical_health'])) ? htmlspecialchars($lastTestData['raw_data']['physical_health']) : ''; ?>" required>
                                                                        <small class="form-text text-muted">Number of days physical health not good (0-30)</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="mental_health">Mental Health (days)</label>
                                                                    <div class="form-control-wrap">
                                                                        <input type="number" step="0.1" class="form-control" id="mental_health" name="mental_health" min="0" max="30" value="<?php echo ($lastTestData && isset($lastTestData['raw_data']['mental_health'])) ? htmlspecialchars($lastTestData['raw_data']['mental_health']) : ''; ?>" required>
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
                                                                        <input type="number" step="0.1" class="form-control" id="sleep_time" name="sleep_time" min="0" max="24" value="<?php echo ($lastTestData && isset($lastTestData['raw_data']['sleep_time'])) ? htmlspecialchars($lastTestData['raw_data']['sleep_time']) : ''; ?>" required>
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
                                                                <div class="form-group">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input" id="save_record" name="save_record" value="1" <?php echo (isLoggedIn() && $lastTestData) ? 'checked' : ''; ?>>
                                                                        <label class="custom-control-label" for="save_record">Save this prediction to my history</label>
                                                                        <p class="text-muted small mt-1">If unchecked, this prediction will not be stored in our database for privacy reasons.</p>
                                                                    </div>
                                                                </div>
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

                                                    <div class="alert alert-warning mt-4">
                                                        <div class="alert-icon"><em class="icon ni ni-alert-circle"></em></div>
                                                        <div class="alert-text">This tool provides an estimate only and should not replace professional medical advice. Always consult with a healthcare provider for proper diagnosis and treatment.</div>
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
    </div>

    <?php include PROJECT_ROOT . '/includes/scripts.php'; ?>

    <script>
        $(document).ready(function() {
            // Form validation
            $('#prediction-form').submit(function(e) {
                var isValid = true;

                // Check all required fields
                $(this).find('[required]').each(function() {
                    if ($(this).val() === '' || ($(this).attr('type') === 'number' && $(this).val() < $(this).attr('min'))) {
                         isValid = false;
                         // Add error class and message if not already present
                         if (!$(this).hasClass('error')) {
                            $(this).addClass('error').parent().append('<div class="error-message">This field is required and must be valid</div>');
                         }
                    } else {
                        $(this).removeClass('error');
                        $(this).parent().find('.error-message').remove();
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    toastr.error('Please fill in all required fields correctly');
                }
            });

            // Clear error on input change
            $(document).on('change keyup', '.form-control, .form-select', function() {
                // Only remove error if the field is no longer empty and meets min requirements for numbers
                if ($(this).val() !== '' && (!$(this).attr('type') === 'number' || $(this).val() >= $(this).attr('min'))) {
                    $(this).removeClass('error');
                    $(this).parent().find('.error-message').remove();
                }
            });

            // Prefill logic for dropdowns based on lastTestData
            // This part remains mostly the same, using the PHP variables set above
            <?php if ($lastTestData && isset($lastTestData['raw_data'])): ?> // Check if raw_data exists
                $('#race').val('<?php echo htmlspecialchars($lastTestData['raw_data']['race'] ?? ''); ?>');
                $('#smoking').val('<?php echo htmlspecialchars($lastTestData['raw_data']['smoking'] ?? ''); ?>');
                $('#alcohol_drinking').val('<?php echo htmlspecialchars($lastTestData['raw_data']['alcohol_drinking'] ?? ''); ?>');
                $('#stroke').val('<?php echo htmlspecialchars($lastTestData['raw_data']['stroke'] ?? ''); ?>');
                $('#diff_walking').val('<?php echo htmlspecialchars($lastTestData['raw_data']['diff_walking'] ?? ''); ?>');
                $('#diabetic').val('<?php echo htmlspecialchars($lastTestData['raw_data']['diabetic'] ?? ''); ?>');
                $('#physical_activity').val('<?php echo htmlspecialchars($lastTestData['raw_data']['physical_activity'] ?? ''); ?>');
                $('#gen_health').val('<?php echo htmlspecialchars($lastTestData['raw_data']['gen_health'] ?? ''); ?>');
                $('#sleep_time').val('<?php echo htmlspecialchars($lastTestData['raw_data']['sleep_time'] ?? ''); ?>'); // Added sleep_time prefill
                $('#asthma').val('<?php echo htmlspecialchars($lastTestData['raw_data']['asthma'] ?? ''); ?>');
                $('#kidney_disease').val('<?php echo htmlspecialchars($lastTestData['raw_data']['kidney_disease'] ?? ''); ?>');
                $('#skin_cancer').val('<?php echo htmlspecialchars($lastTestData['raw_data']['skin_cancer'] ?? ''); ?>');
            <?php endif; ?>

             // Prefill logic for gender from user profile if no last test data
            <?php if ($genderPrefillValue !== '' && (!isset($lastTestData['raw_data']['sex']) || $lastTestData['raw_data']['sex'] === null)): ?> // Check raw_data for sex
                 $('#sex').val('<?php echo htmlspecialchars($genderPrefillValue); ?>');
            <?php endif; ?>

             // Prefill logic for age from user profile if no last test data age
            <?php if ($agePrefillValue !== '' && (!isset($lastTestData['raw_data']['age']) || $lastTestData['raw_data']['age'] === null)): ?> // Check raw_data for age
                 $('#age').val('<?php echo htmlspecialchars($agePrefillValue); ?>');
            <?php endif; ?>

        });
    </script>
    <?php require_once PROJECT_ROOT . '/footer.php'; ?>
    <?php include PROJECT_ROOT . '/includes/scripts.php'; ?>
</body>
</html>
