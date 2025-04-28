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
            // Assuming home.php is at the project root
            define('PROJECT_ROOT', __DIR__);
            // If home.php is in a subdirectory, adjust __DIR__ accordingly, e.g., dirname(__DIR__)
        }
        include PROJECT_ROOT . '/sidemenu.php'; ?>

        <div class="nk-main">
            <!-- Include the header component -->
            <?php include PROJECT_ROOT . '/header.php'; ?>

            <div class="nk-wrap">
                <div class="nk-content">
                    <div class="container-fluid">
                        <div class="nk-content-inner">
                            <div class="nk-content-body">
                                <div class="nk-block-head nk-block-head-sm">
                                    <div class="nk-block-between">
                                        <div class="nk-block-head-content">
                                            <h3 class="nk-block-title page-title">Heart Disease Prediction Result</h3>
                                            <div class="nk-block-des text-soft">
                                                <p>Analysis of your heart disease risk based on provided parameters.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="nk-block">
                                    <div class="row g-gs">
                                        <div class="col-lg-8">
                                            <?php
                                            // Include necessary files
                                            require_once PROJECT_ROOT . '/session.php'; // Start session
                                            require_once PROJECT_ROOT . '/database/form_validation_preprocessing.php';
                                            require_once PROJECT_ROOT . '/database/set_user_prediction_record.php'; // Includes connection.php
                                            require_once PROJECT_ROOT . '/database/get_user_prediction_history.php'; // Includes getPredictionRecordById
                                            require_once PROJECT_ROOT . '/database/connection.php'; // Ensure connection is available

                                            // Get current user ID
                                            $userId = $_SESSION['user_id'] ?? null;

                                            // --- Display Logic ---
                                            $displayData = null;
                                            $displayError = null;

                                            // Check if viewing a specific history record via GET request
                                            if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
                                                $recordId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

                                                if ($recordId && $userId) {
                                                    $record = getPredictionRecordById($recordId, $userId);
                                                    if ($record) {
                                                        // Prepare data for display (similar structure to POST result)
                                                        $displayData = [
                                                            'riskLevel' => $record['result'], // Already formatted as 'High Risk' or 'Low Risk'
                                                            'probabilityPercent' => $record['probability'], // Already formatted as 'XX.XX%'
                                                            'riskClass' => ($record['prediction'] == 1) ? 'result-high' : 'result-low',
                                                            'riskDescription' => ($record['prediction'] == 1) ? "This record indicates a high risk of heart disease. Please consult with a healthcare professional." : "This record indicates a low risk of heart disease. Maintain a healthy lifestyle.",
                                                            'parameters' => $record // Pass the whole record for parameter display
                                                        ];
                                                    } else {
                                                        $displayError = "Could not find the specified prediction record or you do not have permission to view it.";
                                                    }
                                                } else {
                                                    $displayError = "Invalid record ID specified or you are not logged in.";
                                                }
                                            }
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
                                                    $apiUrl = $_ENV['PREDICTION_API_URL'] ?? 'http://127.0.0.1:5000/predict'; // Default to localhost if not set
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
                                                        $riskLevel = $prediction == 1 ? 'High' : 'Low'; // Simplified risk level
                                                        $probabilityPercent = round($confidence * 100, 2);

                                                        // Set risk class and description based on prediction
                                                        if ($prediction == 1) {
                                                            $riskClass = "result-high";
                                                            $riskDescription = "Based on the provided parameters, you have a high risk of heart disease. Please consult with a healthcare professional as soon as possible.";
                                                        } else {
                                                            $riskClass = "result-low";
                                                            $riskDescription = "Based on the provided parameters, you have a low risk of heart disease. Maintain a healthy lifestyle to keep it that way.";
                                                        }

                                                        // 5. Save to Database if user logged in and checkbox checked
                                                        $saveHistory = isset($_POST['save_history']) && $_POST['save_history'] == 'on';
                                                        $userId = $_SESSION['user_id'] ?? null;
                                                        $dbSaveSuccess = null;
                                                        $dbSaveError = null;

                                                        if ($userId && $saveHistory) {
                                                            $conn = getDbConnection();
                                                            if ($conn) {
                                                                $historyId = savePredictionHistory($conn, $userId, $validatedData, $prediction, $confidence);
                                                                if ($historyId) {
                                                                    $lastTestSuccess = updateLastTestRecord($conn, $userId, $historyId);
                                                                    if ($lastTestSuccess) {
                                                                        $dbSaveSuccess = "Prediction saved to your history.";
                                                                    } else {
                                                                        $dbSaveError = "Failed to update last test record. History entry was created (ID: {$historyId}) but may not be linked correctly.";
                                                                        error_log("DB Error: Failed to update last test record for user {$userId}, history ID {$historyId}");
                                                                    }
                                                                } else {
                                                                    $dbSaveError = "Failed to save prediction history to the database.";
                                                                    error_log("DB Error: Failed to save prediction history for user {$userId}");
                                                                }
                                                                $conn->close();
                                                            } else {
                                                                $dbSaveError = "Failed to connect to the database to save history.";
                                                                error_log("DB Error: Failed to get DB connection in result.php");
                                                            }
                                                        }

                                                        // 6. Display Result
                                                        echo "<div class='card card-bordered'>";
                                                        echo "<div class='card-inner'>";
                                                        echo "<div class='card-head'>";
                                                        echo "<h5 class='card-title'>Prediction Result</h5>";
                                                        echo "</div>";

                                                        // Display DB save status if attempted
                                                        if ($dbSaveSuccess) {
                                                            echo "<div class='alert alert-success'>{$dbSaveSuccess}</div>";
                                                        } elseif ($dbSaveError) {
                                                            echo "<div class='alert alert-danger'>{$dbSaveError}</div>";
                                                        }

                                                        echo "<div class='result-box {$riskClass}'>";
                                                        echo "<h4>Heart Disease Risk: {$riskLevel}</h4>";
                                                        echo "<p>Confidence: {$probabilityPercent}%</p>";
                                                        echo "<p>{$riskDescription}</p>";
                                                        // Note: 'factors' are not returned by the simplified Python API anymore
                                                        echo "</div>";

                                                        // Display Input Parameters
                                                        echo "<div class='mt-4'>";
                                                        echo "<h6>Your Health Parameters</h6>";
                                                        echo "<div class='table-responsive'>";
                                                        echo "<table class='table table-bordered parameter-table'>";
                                                        echo "<tbody>";

                                                        // Map validated data keys to user-friendly labels
                                                        $parameterLabels = [
                                                            'bmi' => 'BMI',
                                                            'smoking' => 'Smoking Status (0=No, 1=Yes)',
                                                            'alcohol_drinking' => 'Alcohol Drinking (0=No, 1=Yes)',
                                                            'stroke' => 'Stroke History (0=No, 1=Yes)',
                                                            'physical_health' => 'Physical Health (days bad/month)',
                                                            'mental_health' => 'Mental Health (days bad/month)',
                                                            'diff_walking' => 'Difficulty Walking (0=No, 1=Yes)',
                                                            'sex' => 'Sex (0=Female, 1=Male)',
                                                            'age' => 'Age',
                                                            'race' => 'Race (0:White, 1:Black, 2:Asian, 3:Hispanic, 4:AmInd/AlNat, 5:Other)',
                                                            'diabetic' => 'Diabetic Status (0:No, 1:Yes, 2:Borderline, 3:Yes/Pregnancy)',
                                                            'physical_activity' => 'Physical Activity (0=No, 1=Yes)',
                                                            'gen_health' => 'General Health (0:Excellent, 1:V.Good, 2:Good, 3:Fair, 4:Poor)',
                                                            'sleep_time' => 'Sleep Time (hours)',
                                                            'asthma' => 'Asthma (0=No, 1=Yes)',
                                                            'kidney_disease' => 'Kidney Disease (0=No, 1=Yes)',
                                                            'skin_cancer' => 'Skin Cancer (0=No, 1=Yes)'
                                                        ];

                                                        foreach ($parameterLabels as $key => $label) {
                                                            if (isset($validatedData[$key])) {
                                                                $displayValue = $validatedData[$key];
                                                                // You might want to map integer values back to text for display here if needed
                                                                // e.g., for 'sex', 'smoking', etc.
                                                                echo "<tr>";
                                                                echo "<th>{$label}</th>";
                                                                echo "<td>{$displayValue}</td>";
                                                                echo "</tr>";
                                                            }
                                                        }

                                                        echo "</tbody>";
                                                        echo "</table>";
                                                        echo "</div>";
                                                        echo "</div>";
                                                        echo "<div class='mt-4'>";
                                                        echo "<a href='user_input_form.php' class='btn btn-outline-primary'>Make Another Prediction</a>";
                                                        echo "</div>";
                                                        echo "</div>"; // card-inner
                                                        echo "</div>"; // card

                                                    } else {
                                                        // API call failed or returned invalid data
                                                        echo "<div class='card card-bordered'>";
                                                        echo "<div class='card-inner'>";
                                                        echo "<div class='card-head'>";
                                                        echo "<h5 class='card-title'>Prediction Error</h5>";
                                                        echo "</div>";
                                                        echo "<div class='alert alert-danger'>";
                                                        echo "<p>Could not get a prediction result from the analysis service.</p>";
                                                        if ($apiError) {
                                                            echo "<p>Error details: {$apiError}</p>"; // Display logged error
                                                        }
                                                        echo "</div>";
                                                        echo "<div class='mt-3'>";
                                                        echo "<a href='user_input_form.php' class='btn btn-outline-primary'>Go Back to Form</a>";
                                                        echo "</div>";
                                                        echo "</div>"; // card-inner
                                                        echo "</div>"; // card
                                                    }

                                                } else {
                                                    // Validation failed
                                                    echo "<div class='card card-bordered'>";
                                                    echo "<div class='card-inner'>";
                                                    echo "<div class='card-head'>";
                                                    echo "<h5 class='card-title'>Validation Error</h5>";
                                                    echo "</div>";
                                                    echo "<div class='alert alert-danger'>";
                                                    echo "<p>There were errors in your submission:</p>";
                                                    echo "<ul>";
                                                    foreach ($validationResult['errors'] as $error) {
                                                        echo "<li>{$error}</li>";
                                                    }
                                                    echo "</ul>";
                                                    echo "</div>"; // Close alert
                                                    echo "<div class='mt-3'>";
                                                    echo "<a href='user_input_form.php' class='btn btn-outline-primary'>Go Back to Form</a>";
                                                    echo "</div>";
                                                    echo "</div>"; // Close card-inner
                                                    echo "</div>"; // Close card
                                                }
                                            } else {
                                                // If not a POST request, redirect or show an error
                                                echo "<div class='alert alert-warning'>No data submitted. Please use the prediction form.</div>";
                                            }
                                            ?>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="card card-bordered h-100">
                                                <div class="card-inner">
                                                    <div class="card-head">
                                                        <h5 class="card-title">What's Next?</h5>
                                                    </div>
                                                    <div class="nk-block">
                                                        <div class="nk-block-content">
                                                            <p>This prediction is based on a machine learning model trained on heart disease data.</p>
                                                            <p>Remember that this is not a medical diagnosis. If you have concerns about your heart health, please consult with a healthcare professional.</p>
                                                            <h6 class="mt-4">Recommendations:</h6>
                                                            <ul class="list list-sm list-checked">
                                                                <li>Maintain a healthy diet low in saturated fats</li>
                                                                <li>Exercise regularly (at least 150 minutes per week)</li>
                                                                <li>Avoid smoking and limit alcohol consumption</li>
                                                                <li>Manage stress through relaxation techniques</li>
                                                                <li>Get regular check-ups with your doctor</li>
                                                            </ul>
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
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <?php include PROJECT_ROOT . '/includes/scripts.php'; ?>
</body>

</html>

                                            // --- Display GET Result or Error ---
                                            elseif ($displayData) {
                                                echo "<div class='card card-bordered'>";
                                                echo "<div class='card-inner'>";
                                                echo "<div class='card-head'>";
                                                echo "<h5 class='card-title'>Prediction Result from History</h5>";
                                                echo "</div>";

                                                echo "<div class='result-box {$displayData['riskClass']}'>";
                                                echo "<h4>Heart Disease Risk: {$displayData['riskLevel']}</h4>";
                                                echo "<p>Confidence: {$displayData['probabilityPercent']}</p>";
                                                echo "<p>{$displayData['riskDescription']}</p>";
                                                echo "</div>";

                                                // Display Input Parameters from History
                                                echo "<div class='mt-4'>";
                                                echo "<h6>Health Parameters Recorded</h6>";
                                                echo "<div class='table-responsive'>";
                                                echo "<table class='table table-bordered parameter-table'>";
                                                echo "<tbody>";
                                                $parameterLabels = [
                                                    'age' => 'Age',
                                                    'sex' => 'Sex (1=Male, 0=Female)',
                                                    'cp' => 'Chest Pain Type',
                                                    'trestbps' => 'Resting Blood Pressure (mm Hg)',
                                                    'chol' => 'Serum Cholesterol (mg/dl)',
                                                    'fbs' => 'Fasting Blood Sugar > 120 mg/dl (1=True, 0=False)',
                                                    'restecg' => 'Resting Electrocardiographic Results',
                                                    'thalach' => 'Maximum Heart Rate Achieved',
                                                    'exang' => 'Exercise Induced Angina (1=Yes, 0=No)',
                                                    'oldpeak' => 'ST Depression Induced by Exercise Relative to Rest',
                                                    'slope' => 'Slope of the Peak Exercise ST Segment',
                                                    'ca' => 'Number of Major Vessels Colored by Fluoroscopy',
                                                    'thal' => 'Thalassemia'
                                                ];
                                                foreach ($parameterLabels as $key => $label) {
                                                    // Check if the key exists in the fetched record data
                                                    if (isset($displayData['parameters'][$key])) {
                                                        echo "<tr><th>{$label}</th><td>" . htmlspecialchars($displayData['parameters'][$key]) . "</td></tr>";
                                                    }
                                                }
                                                echo "</tbody>";
                                                echo "</table>";
                                                echo "</div>"; // end table-responsive
                                                echo "</div>"; // end mt-4

                                                echo "</div>"; // end card-inner
                                                echo "</div>"; // end card
                                            }
                                            elseif ($displayError) {
                                                // Display Error for GET request
                                                echo "<div class='alert alert-danger'>";
                                                echo "<h4>Error</h4>";
                                                echo "<p>" . htmlspecialchars($displayError) . "</p>";
                                                echo "</div>";
                                            }
                                            else {
                                                 // Default message if neither POST nor valid GET with ID
                                                 if ($_SERVER["REQUEST_METHOD"] != "POST") { // Avoid showing this if POST failed validation
                                                     echo "<div class='alert alert-info'>No prediction data available. Please submit the form or provide a valid history ID via the history page.</div>";
                                                 }
                                            }
                                            ?>
                                        </div>