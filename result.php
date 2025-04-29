<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Heart Disease Prediction Result">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <title>Heart Disease Prediction - Result</title>
    <!-- Stylesheets -->
    <?php include PROJECT_ROOT . '/includes/styles.php'; ?>
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
        if (!defined('PROJECT_ROOT')) {
            // Assuming result.php is at the project root
            define('PROJECT_ROOT', __DIR__);
        }

        // Include necessary files FIRST, especially session.php
        require_once PROJECT_ROOT . '/session.php'; // Start session and provide isLoggedIn()

        // --- Redirect if not logged in ---
        if (!isLoggedIn()) {
            header('Location: home.php'); // Redirect to home page
            exit(); // Stop script execution
        }
        // --- End Redirect ---

        require_once PROJECT_ROOT . '/database/form_validation_preprocessing.php';
        require_once PROJECT_ROOT . '/database/set_user_prediction_record.php'; // Includes connection.php
        require_once PROJECT_ROOT . '/database/get_user_prediction_history.php'; // Includes getPredictionRecordById
        require_once PROJECT_ROOT . '/database/connection.php'; // Ensure connection is available

        // Helper function to get text representation of parameters
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

        // Check if viewing a specific history record via GET request
        if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
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
                         displayError = "Prediction result data is missing for this record.";
                    }
                } else {
                    $displayError = "Could not find the specified prediction record or you do not have permission to view it.";
                }
            } else {
                    $displayError = "Invalid record ID specified."; // User must be logged in due to check at top
            }
        }
        // The actual SweetAlert JS is added near the end of the body.
        // Check if form was submitted via POST
        elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
            // 1. Validate form data
            $validationResult = validateAndPreprocessFormData($_POST);

            if ($validationResult['isValid']) {
                $validatedData = $validationResult['data'];

                // 2. Prepare data for Python API (ensure keys match API expectations)
                // Map PHP keys (lowercase_snake) to Python API keys (PascalCase)
                $keyMapping = [
                    'bmi' => 'BMI',
                    'smoking' => 'Smoking',
                    'alcohol_drinking' => 'AlcoholDrinking',
                    'stroke' => 'Stroke',
                    'physical_health' => 'PhysicalHealth',
                    'mental_health' => 'MentalHealth',
                    'diff_walking' => 'DiffWalking',
                    'sex' => 'Sex',
                    'age' => 'Age', // Sending Age, assuming API handles AgeCategory derivation if needed
                    'race' => 'Race',
                    'diabetic' => 'Diabetic',
                    'physical_activity' => 'PhysicalActivity',
                    'gen_health' => 'GenHealth',
                    'sleep_time' => 'SleepTime',
                    'asthma' => 'Asthma',
                    'kidney_disease' => 'KidneyDisease',
                    'skin_cancer' => 'SkinCancer'
                ];

                $apiData = [];
                foreach ($keyMapping as $phpKey => $apiKey) {
                    if (isset($validatedData[$phpKey])) {
                        $apiData[$apiKey] = $validatedData[$phpKey];
                    }
                    // Note: If a key is missing in $validatedData but required by API,
                    // the API should handle it (as it currently does with .get(feature, 0))
                }

                // 3. Call Python Prediction API
                $apiUrl = $_ENV['PREDICTION_API_URL'] ?? 'https://heart-disease-prediction-api-84fu.onrender.com/predict'; // Default to localhost if not set
                $ch = curl_init($apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($apiData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 seconds timeout

                $apiResponseJson = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                $apiResult = null;
                $apiError = null;

                if ($curlError) {
                    $apiError = "API request failed (cURL error): " . $curlError;
                    error_log($apiError);
                } elseif ($httpCode != 200) {
                    $apiError = "API request failed with HTTP status code: " . $httpCode . ". Response: " . $apiResponseJson;
                    error_log($apiError);
                } else {
                    $apiResult = json_decode($apiResponseJson, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $apiError = "Failed to decode API response: " . json_last_error_msg() . ". Response: " . $apiResponseJson;
                        error_log($apiError);
                        $apiResult = null; // Ensure result is null on decode error
                    }
                }

                // 4. Process API Response and Save to DB (if applicable)
                if ($apiResult && isset($apiResult['prediction']) && isset($apiResult['confidence'])) {
                    $prediction = (int)$apiResult['prediction']; // 0 or 1
                    $confidence = (float)$apiResult['confidence']; // Probability

                    // Prepare data for display, mirroring the GET request structure
                    $displayData = [
                        'riskLevel' => ($prediction == 1) ? 'High Risk' : 'Low Risk',
                        'probabilityPercent' => round($confidence * 100, 2) . '%',
                        'riskClass' => ($prediction == 1) ? 'result-high' : 'result-low',
                        'riskDescription' => ($prediction == 1) ? "Based on the provided parameters, you have a high risk of heart disease. Please consult with a healthcare professional as soon as possible." : "Based on the provided parameters, you have a low risk of heart disease. Maintain a healthy lifestyle to keep it that way.",
                        'parameters' => $validatedData // Use the validated form data for display
                    ];

                    // 5. Save prediction to database if user is logged in
                    if ($userId) {
                        // Prepare data for database insertion (ensure keys match DB columns)
                        $dbData = $validatedData; // Start with validated data
                        $dbData['user_id'] = $userId;
                        $dbData['prediction_result'] = $prediction;
                        $dbData['prediction_confidence'] = $confidence;

                        // Call function to save the record
                        $saveResult = setUserPredictionRecord($dbData);
                        if (!$saveResult['success']) {
                            // Log error, maybe inform user, but don't necessarily stop display
                            error_log("Failed to save prediction record for user {$userId}: " . $saveResult['message']);
                            // Optionally set a non-blocking message for the user
                            // $displayError = "Could not save this prediction to your history.";
                        }
                    } else {
                        // Handle case where user is not logged in (though the top check should prevent this)
                        // Maybe display a message encouraging login to save history
                    }
                } else {
                    // Handle API error (already logged above)
                    $displayError = "Failed to get prediction from the AI model. Please try again later or contact support.";
                    // Ensure $displayData is null if API failed
                    $displayData = null;
                }
            } else {
                // Handle validation errors
                // Store errors in session flash message to display on the form page after redirect
                $_SESSION['form_errors'] = $validationResult['errors'];
                $_SESSION['form_data'] = $_POST; // Preserve submitted data
                header('Location: user_input_form.php'); // Redirect back to form
                exit();
            }
        }

        // Display results or error message
        if ($displayData) :
        ?>
            <div class="card card-bordered">
                <div class="card-inner">
                    <div class="result-box <?php echo htmlspecialchars($displayData['riskClass']); ?>">
                        <h4 class="mb-2">Prediction Result: <?php echo htmlspecialchars($displayData['riskLevel']); ?></h4>
                        <?php if (isset($displayData['probabilityPercent'])) : ?>
                            <p class="lead">Confidence: <?php echo htmlspecialchars($displayData['probabilityPercent']); ?></p>
                        <?php endif; ?>
                        <p><?php echo htmlspecialchars($displayData['riskDescription']); ?></p>
                    </div>

                    <h5 class="mt-4">Parameters Used for Prediction:</h5>
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
                                'skin_cancer' => 'History of Skin Cancer'
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
                        <a href="history.php" class="btn btn-outline-secondary"><em class="icon ni ni-list"></em> View History</a>
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

<!-- Include the footer component -->
<?php include PROJECT_ROOT . '/footer.php'; ?>
</div>
</div>
</div>

<!-- Include the scripts -->
<?php include PROJECT_ROOT . '/includes/scripts.php'; ?>

<!-- SweetAlert for Errors -->
<?php if ($displayError): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Oops... An Error Occurred',
        text: '<?php echo htmlspecialchars(addslashes($displayError)); ?>',
        confirmButtonText: 'OK'
    });
</script>
<?php endif; ?>

</body>

</html>