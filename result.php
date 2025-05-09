<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Heart Disease Prediction Result">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <title>Heart Disease Prediction - Result</title>
    <!-- Stylesheets -->
     
    <?php
    if (!defined('PROJECT_ROOT')) {
        // Assuming result.php is at the project root
        define('PROJECT_ROOT', __DIR__);
    }
    include PROJECT_ROOT . '/includes/styles.php'; 
    ?>
    <style>
        .result-box {
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
        }

        .result-high {
            background-color: rgba(255, 91, 91, 0.1);
            border-left: 4px solid #ff5b5b;
        }

        .result-medium {
            background-color: rgba(255, 165, 0, 0.1);
            border-left: 4px solid #ffa500;
        }

        .result-low {
            background-color: rgba(46, 204, 113, 0.1);
            border-left: 4px solid #2ecc71;
        }

        .parameter-table th {
            width: 40%;
        }
    </style>
</head>

<body class="nk-body bg-lighter">
    <div class="nk-app-root">
        <!-- Include the side menu component -->
        <?php
        // Define PROJECT_ROOT if it hasn't been defined (for local development when not routed through api/index.php)
        
        // Include the side menu component
        include PROJECT_ROOT . '/sidemenu.php';

        // Include necessary files FIRST, especially session.php
        require_once PROJECT_ROOT . '/session.php'; // Start session and provide isLoggedIn()

        
        require_once PROJECT_ROOT . '/database/get_user_prediction_history.php'; // Includes getPredictionRecordById and connection.php
       
        function getParameterText($key, $value) {
            // Handle null or empty values gracefully
            if ($value === null || $value === '') {
                return 'N/A';
            }

            switch ($key) {
                case 'smoking':
                case 'alcohol_drinking':
                case 'stroke':
                case 'diff_walking':
                case 'physical_activity':
                case 'asthma':
                case 'kidney_disease':
                case 'skin_cancer':
                    return ($value == 1) ? 'Yes' : 'No';
                case 'sex':
                    return ($value == 1) ? 'Male' : 'Female';
                case 'race':
                    $races = ['White', 'Black', 'Asian', 'Hispanic', 'American Indian/Alaskan Native', 'Other'];
                    return $races[$value] ?? 'Unknown';
                case 'diabetic':
                    $diabeticStatus = ['No', 'Yes', 'No, borderline diabetes', 'Yes (during pregnancy)'];
                    return $diabeticStatus[$value] ?? 'Unknown';
                case 'gen_health':
                    $healthStatus = ['Excellent', 'Very good', 'Good', 'Fair', 'Poor'];
                    return $healthStatus[$value] ?? 'Unknown';
                case 'physical_health':
                case 'mental_health':
                    return $value . ' days';
                case 'sleep_time':
                    return $value . ' hours';
                case 'bmi':
                case 'age':
                    return $value; // Return numerical value directly
                case 'prediction_result': // Although not typically displayed in params table, handle just in case
                     return ($value == 1) ? 'Heart Disease' : 'No Heart Disease';
                case 'prediction_confidence':
                     return round($value * 100, 2) . '%';
                default:
                    return htmlspecialchars($value); // Default fallback
            }
        }

        // Get current user ID
        $userId = $_SESSION['user_id'] ?? null; // Already checked login status above

        // --- Display Logic ---
        $displayData = null;
        $displayError = null; // This will be used for SweetAlert2

        // Check if form was submitted via POST (from user_input_form.php)
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Data is coming directly from the form submission after prediction & save
            // We expect 'prediction_result', 'prediction_confidence', and all input parameters
            if (isset($_POST['prediction_result']) && isset($_POST['prediction_confidence'])) {
                $predictionResult = (int)$_POST['prediction_result'];
                $predictionConfidence = (float)$_POST['prediction_confidence'];

                // Prepare data for display using POST data
                $displayData = [
                    'riskLevel' => ($predictionResult == 1) ? 'High Risk' : 'Low Risk',
                    'probabilityPercent' => round($predictionConfidence * 100, 2) . '%',
                    'riskClass' => ($predictionResult == 1) ? 'result-high' : 'result-low',
                    'riskDescription' => ($predictionResult == 1) ? "The prediction indicates a high risk of heart disease based on the provided parameters. Please consult with a healthcare professional." : "The prediction indicates a low risk of heart disease based on the provided parameters. Maintain a healthy lifestyle.",
                    'parameters' => $_POST // Pass all POST data which includes the form inputs
                ];
            } else {
                $displayError = "Prediction result data is missing from the submitted form.";
            }
        }
        // Check if viewing a specific history record via GET request
        elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
            $recordId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

            if ($recordId && $userId) {
                $record = getPredictionRecordById($recordId, $userId); // Assume this function fetches all needed columns including prediction_result and prediction_confidence
                if ($record) {
                    // Prepare data for display using the fetched record
                    // Ensure prediction_result and prediction_confidence are present in $record
                    $predictionResult = $record['prediction_result'] ?? null;
                    $predictionConfidence = $record['prediction_confidence'] ?? null;

                    if ($predictionResult !== null) {
                        $displayData = [
                            'riskLevel' => ($predictionResult == 1) ? 'High Risk' : 'Low Risk',
                            'probabilityPercent' => ($predictionConfidence !== null) ? round($predictionConfidence * 100, 2) . '%' : 'N/A',
                            'riskClass' => ($predictionResult == 1) ? 'result-high' : 'result-low',
                            'riskDescription' => ($predictionResult == 1) ? "This record indicates a high risk of heart disease. Please consult with a healthcare professional." : "This record indicates a low risk of heart disease. Maintain a healthy lifestyle.",
                            'parameters' => $record // Pass the whole record which includes all parameters
                        ];
                    } else {
                         $displayError = "Prediction result data is missing for this record.";
                    }
                } else {
                    $displayError = "Could not find the specified prediction record or you do not have permission to view it.";
                }
            } else {
                    $displayError = "Invalid record ID specified."; // User must be logged in due to check at top
            }
        }
        
        else {
            // Neither POST nor GET with ID - redirect to home
            // If accessed directly without POST data or GET ID
            if (!$displayData && !$displayError) { // Only redirect if no data/error was set by POST/GET
                header('Location: home.php');
                exit();
            }
        }

        
        ?>
        
        <div class="nk-sidebar-overlay" data-target="sidebarMenu"></div>

        <div class="nk-main">
            <?php include PROJECT_ROOT . '/header.php'; ?>
            
            <div class="nk-wrap">
                <div class="nk-content">
                    <div class="container-fluid">
                        <div class="nk-content-inner">
                            <div class="nk-content-body">
                                <div class="card card-bordered">
                <div class="card-inner">
                    <div class="nk-block-head nk-block-head-sm">
                        <div class="nk-block-between">
                            <div class="nk-block-head-content">
                                <h3 class="nk-block-title page-title">Prediction Result</h3>
                                <div class="nk-block-des text-soft">
                                    <p>Review the heart disease risk prediction based on the provided parameters.</p>
                                </div>
                            </div>
                            <div class="nk-block-head-content">
                                <a href="user_input_form.php" class="btn btn-outline-light bg-white d-none d-sm-inline-flex"><em class="icon ni ni-arrow-left"></em><span>New Prediction</span></a>
                                <a href="user_input_form.php" class="btn btn-icon btn-outline-light bg-white d-inline-flex d-sm-none"><em class="icon ni ni-arrow-left"></em></a>
                            </div>
                        </div>
                    </div><!-- .nk-block-head -->

                    <div class="nk-block">
                        <?php if ($displayData): ?>
                            <div class="result-box <?php echo htmlspecialchars($displayData['riskClass']); ?> mb-4">
                                <h4 class="title mb-2">Risk Level: <?php echo htmlspecialchars($displayData['riskLevel']); ?></h4>
                                <p class="lead">Confidence: <?php echo htmlspecialchars($displayData['probabilityPercent']); ?></p>
                                <p><?php echo htmlspecialchars($displayData['riskDescription']); ?></p>
                            </div>

                            <div class="card card-bordered">
                                <div class="card-inner">
                                    <h5 class="card-title">Parameters Used for Prediction:</h5>
                                    <table class="table table-striped parameter-table mt-3">
                                        <tbody>
                                            <?php
                                            // Define the order and labels for parameters
                                            $parameterLabels = [
                                                'age' => 'Age',
                                                'sex' => 'Sex',
                                                'bmi' => 'BMI',
                                                'smoking' => 'Smoking Status',
                                                'alcohol_drinking' => 'Alcohol Drinking Status',
                                                'stroke' => 'History of Stroke',
                                                'physical_health' => 'Physical Health (days bad in last 30)',
                                                'mental_health' => 'Mental Health (days bad in last 30)',
                                                'diff_walking' => 'Difficulty Walking',
                                                'race' => 'Race/Ethnicity',
                                                'diabetic' => 'Diabetic Status',
                                                'physical_activity' => 'Physical Activity (in last 30 days)',
                                                'gen_health' => 'General Health Perception',
                                                'sleep_time' => 'Average Sleep Time (hours)',
                                                'asthma' => 'History of Asthma',
                                                'kidney_disease' => 'History of Kidney Disease',
                                                'skin_cancer' => 'History of Skin Cancer',
                                                'prediction_result' => 'Prediction Result' // Added Prediction Result display
                                            ];

                                            foreach ($parameterLabels as $key => $label) {
                                                // Check if the parameter exists in the display data
                                                if (isset($displayData['parameters'][$key])) {
                                                    $value = $displayData['parameters'][$key];
                                                    $displayText = getParameterText($key, $value);
                                                    echo "<tr><th>" . htmlspecialchars($label) . "</th><td>" . htmlspecialchars($displayText) . "</td></tr>";
                                                } else {
                                                    // Optionally display if a parameter was expected but missing
                                                    // echo "<tr><th>" . htmlspecialchars($label) . "</th><td>N/A</td></tr>";
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                    <div class="mt-4">
                                        <a href="user_input_form.php" class="btn btn-primary"><em class="icon ni ni-plus"></em> New Prediction</a>
                                        <?php if(isLoggedIn()): ?>
                                        <a href="history.php" class="btn btn-outline-secondary"><em class="icon ni ni-list"></em> View History</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php elseif ($displayError) :
                                // Display error using a card, SweetAlert will be triggered later
                            ?>
                                <div class="card card-bordered">
                                    <div class="card-inner">
                                        <div class="alert alert-danger" role="alert">
                                            <h4 class="alert-heading">Error</h4>
                                            <p><?php echo htmlspecialchars($displayError); ?></p>
                                            <hr>
                                            <p class="mb-0">Please <a href="user_input_form.php" class="alert-link">try again</a> or contact support if the problem persists.</p>
                                        </div>
                                        <div class="mt-3">
                                             <a href="home.php" class="btn btn-light"><em class="icon ni ni-arrow-left"></em> Back to Home</a>
                                        </div>
                                    </div>
                                </div>
                            <?php else :
                                // Fallback if neither POST nor GET with ID, or other unexpected state
                            ?>
                                <div class="card card-bordered">
                                    <div class="card-inner">
                                        <div class="alert alert-warning" role="alert">
                                            No prediction data available to display. Please make a new prediction.
                                        </div>
                                        <div class="mt-3">
                                             <a href="user_input_form.php" class="btn btn-primary"><em class="icon ni ni-plus"></em> New Prediction</a>
                                             <a href="home.php" class="btn btn-light"><em class="icon ni ni-arrow-left"></em> Back to Home</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-lg-4">
                            <div class="card card-bordered">
                                <div class="card-inner">
                                    <h5 class="card-title">Understanding Your Result</h5>
                                    <p class="text-soft">This prediction is based on statistical models and data analysis. It is not a substitute for professional medical advice.</p>
                                    <ul>
                                        <li><strong>High Risk:</strong> Indicates a higher statistical probability based on the provided factors. It is strongly recommended to consult a healthcare professional for a comprehensive evaluation.</li>
                                        <li><strong>Low Risk:</strong> Indicates a lower statistical probability. Continue maintaining a healthy lifestyle, including regular check-ups.</li>
                                    </ul>
                                    <h6 class="mt-3">Next Steps:</h6>
                                    <ul class="list list-sm list-checked">
                                        <li>Discuss this result with your doctor.</li>
                                        <li>Learn more about <a href="health_info.php">heart disease prevention</a>.</li>
                                        <li>Consider lifestyle changes if applicable (diet, exercise, smoking cessation).</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- footer @s -->
            <?php include PROJECT_ROOT . '/footer.php'; ?>
            <!-- footer @e -->
        </div>
        <!-- wrap @e -->
    </div>
    <!-- main @e -->
</div>
<!-- app-root @e -->
<!-- JavaScript -->
<?php include PROJECT_ROOT . '/includes/scripts.php'; ?>
<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Display error message using SweetAlert if $displayError is set
    const displayError = <?php echo json_encode($displayError); ?>;
    if (displayError) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: displayError,
            confirmButtonColor: '#e74c3c'
        });
    }
</script>
</body>

</html>