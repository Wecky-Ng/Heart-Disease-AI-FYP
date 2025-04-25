<?php
// Include session management and user functions
require_once 'session.php';
require_once 'database/get_user.php';

// Initialize variables for form prefill
$gender = '';
$userData = null;
$lastTestData = null;

// Function to get user's last test record
function getLastTestRecord($userId) {
    $db = getDbConnection();
    
    $query = "SELECT ph.* FROM user_last_test_record ultr 
              JOIN user_prediction_history ph ON ultr.prediction_history_id = ph.id 
              WHERE ultr.user_id = ?";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$userId]);
    
    if ($stmt->rowCount() === 0) {
        return false;
    }
    
    return $stmt->fetch();
}

// Check if user is logged in
if (isUserLoggedIn()) {
    // Get current user session data
    $sessionData = getCurrentUser();
    
    // Get user data from database
    if (isset($sessionData['user_id'])) {
        $userData = getUserById($sessionData['user_id']);
        
        // Get gender if available
        if ($userData && !empty($userData['gender'])) {
            $gender = $userData['gender'];
        }
        
        // Get user's last test record if available
        require_once 'database/connection.php';
        $lastTestData = getLastTestRecord($sessionData['user_id']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Heart Disease Prediction using AI">
    <title>Heart Disease Prediction</title>
    <!-- Include common stylesheets -->
    <?php include 'includes/styles.php'; ?>
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
        <!-- Include the side menu component -->
        <?php include 'sidemenu.php'; ?>
        
        <div class="nk-main">
            <!-- Include the header component -->
            <?php include 'header.php'; ?>
            
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
                                                    <?php if (isUserLoggedIn() && ($gender || $lastTestData)): ?>
                                                    <div class="alert alert-info">
                                                        <div class="alert-icon"><em class="icon ni ni-info-fill"></em></div>
                                                        <div class="alert-text">Some fields have been prefilled with your profile data and previous test information.</div>
                                                    </div>
                                                    <?php endif; ?>
                                                    <form id="prediction-form" action="result.php" method="post" class="form-validate">
                                                        <div class="row g-4">
                                                            <!-- Basic Information -->
                                                            <div class="col-12">
                                                                <h6 class="overline-title text-primary">Basic Information</h6>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="sex">Gender</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="sex" name="sex" required>
                                                                            <option value="">Select</option>
                                                                            <option value="Male" <?php echo ($gender === 'Male') ? 'selected' : ''; ?>>Male</option>
                                                                            <option value="Female" <?php echo ($gender === 'Female') ? 'selected' : ''; ?>>Female</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="age_category">Age Category</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="age_category" name="age_category" required>
                                                                            <option value="">Select</option>
                                                                            <option value="18-24">18-24</option>
                                                                            <option value="25-29">25-29</option>
                                                                            <option value="30-34">30-34</option>
                                                                            <option value="35-39">35-39</option>
                                                                            <option value="40-44">40-44</option>
                                                                            <option value="45-49">45-49</option>
                                                                            <option value="50-54">50-54</option>
                                                                            <option value="55-59">55-59</option>
                                                                            <option value="60-64">60-64</option>
                                                                            <option value="65-69">65-69</option>
                                                                            <option value="70-74">70-74</option>
                                                                            <option value="75-79">75-79</option>
                                                                            <option value="80 or older">80 or older</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="race">Race/Ethnicity</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="race" name="race" required>
                                                                            <option value="">Select</option>
                                                                            <option value="White">White</option>
                                                                            <option value="Black">Black</option>
                                                                            <option value="Asian">Asian</option>
                                                                            <option value="Hispanic">Hispanic</option>
                                                                            <option value="American Indian/Alaskan Native">American Indian/Alaskan Native</option>
                                                                            <option value="Other">Other</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="bmi">BMI</label>
                                                                    <div class="form-control-wrap">
                                                                        <input type="number" step="0.01" class="form-control" id="bmi" name="bmi" min="10" max="60" value="<?php echo ($lastTestData && isset($lastTestData['bmi'])) ? htmlspecialchars($lastTestData['bmi']) : ''; ?>" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Health Indicators -->
                                                            <div class="col-12">
                                                                <h6 class="overline-title text-primary mt-3">Health Indicators</h6>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="smoking">Smoking</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="smoking" name="smoking" required>
                                                                            <option value="">Select</option>
                                                                            <option value="Yes">Yes</option>
                                                                            <option value="No">No</option>
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
                                                                            <option value="Yes">Yes</option>
                                                                            <option value="No">No</option>
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
                                                                            <option value="Yes">Yes</option>
                                                                            <option value="No">No</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="physical_health">Physical Health (days)</label>
                                                                    <div class="form-control-wrap">
                                                                        <input type="number" step="0.1" class="form-control" id="physical_health" name="physical_health" min="0" max="30" required>
                                                                        <small class="form-text text-muted">Number of days physical health not good (0-30)</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="mental_health">Mental Health (days)</label>
                                                                    <div class="form-control-wrap">
                                                                        <input type="number" step="0.1" class="form-control" id="mental_health" name="mental_health" min="0" max="30" required>
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
                                                                            <option value="Yes">Yes</option>
                                                                            <option value="No">No</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Medical Conditions -->
                                                            <div class="col-12">
                                                                <h6 class="overline-title text-primary mt-3">Medical Conditions</h6>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="diabetic">Diabetic</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="diabetic" name="diabetic" required>
                                                                            <option value="">Select</option>
                                                                            <option value="Yes">Yes</option>
                                                                            <option value="No">No</option>
                                                                            <option value="No, borderline diabetes">No, borderline diabetes</option>
                                                                            <option value="Yes (during pregnancy)">Yes (during pregnancy)</option>
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
                                                                            <option value="Yes">Yes</option>
                                                                            <option value="No">No</option>
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
                                                                            <option value="Excellent">Excellent</option>
                                                                            <option value="Very good">Very good</option>
                                                                            <option value="Good">Good</option>
                                                                            <option value="Fair">Fair</option>
                                                                            <option value="Poor">Poor</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="sleep_time">Sleep Time (hours)</label>
                                                                    <div class="form-control-wrap">
                                                                        <input type="number" step="0.1" class="form-control" id="sleep_time" name="sleep_time" min="0" max="24" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="asthma">Asthma</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="asthma" name="asthma" required>
                                                                            <option value="">Select</option>
                                                                            <option value="Yes">Yes</option>
                                                                            <option value="No">No</option>
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
                                                                            <option value="Yes">Yes</option>
                                                                            <option value="No">No</option>
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
                                                                            <option value="Yes">Yes</option>
                                                                            <option value="No">No</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Privacy Option -->
                                                            <div class="col-12 mt-3">
                                                                <div class="form-group">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input" id="save_record" name="save_record" value="1">
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
                
                <!-- Include the footer component -->
                <?php include 'footer.php'; ?>
            </div>
        </div>
    </div>
    
    <!-- Include common scripts -->
    <?php include 'includes/scripts.php'; ?>
    
    <script>
        $(document).ready(function() {
            // Form validation
            $('#prediction-form').submit(function(e) {
                var isValid = true;
                
                // Check all required fields
                $(this).find('[required]').each(function() {
                    if ($(this).val() === '') {
                        isValid = false;
                        $(this).addClass('error').parent().append('<div class="error-message">This field is required</div>');
                    } else {
                        $(this).removeClass('error');
                        $(this).parent().find('.error-message').remove();
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    toastr.error('Please fill in all required fields');
                }
            });
            
            // Clear error on input change
            $(document).on('change', '.form-control, .form-select', function() {
                $(this).removeClass('error');
                $(this).parent().find('.error-message').remove();
            });
        });
    </script>
</body>
</html>