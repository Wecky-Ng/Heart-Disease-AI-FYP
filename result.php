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
                                                        // Prepare data for display using the formatted data from getPredictionRecordById
                                                        $displayData = [
                                                            'riskLevel' => $record['result'], // Formatted 'High Risk' or 'Low Risk'
                                                            'probabilityPercent' => $record['probability'], // Formatted 'XX.XX%'
                                                            'riskClass' => ($record['prediction_result'] == 1) ? 'result-high' : 'result-low', // Use prediction_result from DB
                                                            'riskDescription' => ($record['prediction_result'] == 1) ? "This record indicates a high risk of heart disease. Please consult with a healthcare professional." : "This record indicates a low risk of heart disease. Maintain a healthy lifestyle.",
                                                            'parameters' => $record // Pass the whole record which includes all parameters
                                                        ];
                                                        // No need for array_merge if 'parameters' holds the full record
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

                                                        // Prepare data for display, mirroring the GET request structure
                                                        $displayData = [
                                                            'riskLevel' => ($prediction == 1) ? 'High Risk' : 'Low Risk',
                                                            'probabilityPercent' => round($confidence * 100, 2) . '%',
                                                            'riskClass' => ($prediction == 1) ? 'result-high' : 'result-low',
                                                            'riskDescription' => ($prediction == 1) ? "Based on the provided parameters, you have a high risk of heart disease. Please consult with a healthcare professional as soon as possible." : "Based on the provided parameters, you have a low risk of heart disease. Maintain a healthy lifestyle to keep it that way.",
                                                            'parameters' => $validatedData // Use the validated form data for display
                                                        ];

                                                        // 5. Save prediction record to database (if user is logged in)
                                                        if ($userId) {
                                                            $saveResult = savePredictionRecord(
                                                                $userId,
                                                                $validatedData, // Pass the original validated data
                                                                $prediction,    // Pass the raw prediction (0 or 1)
                                                                $confidence     // Pass the raw confidence/probability
                                                            );
                                                            if (!$saveResult['success']) {
                                                                // Log error, but don't necessarily block the user from seeing the result
                                                                error_log("Failed to save prediction record for user {$userId}: " . $saveResult['message']);
                                                                // Optionally set a non-critical error message for the user
                                                                // $displayError = "Could not save this prediction to your history. " . ($displayError ?? '');
                                                            }
                                                        } else {
                                                            // User not logged in, cannot save history
                                                            // Optionally inform the user
                                                            // $displayError = "Log in to save your prediction history. " . ($displayError ?? '');
                                                        }
                                                    } elseif ($apiError) {
                                                        $displayError = "Prediction failed: " . $apiError;
                                                    } else {
                                                        $displayError = "Prediction failed due to an unknown API error.";
                                                    }
                                                } else {
                                                    // Validation failed
                                                    $displayError = "Form validation failed: " . implode(", ", $validationResult['errors']);
                                                }
                                            } else {
                                                // Neither GET with ID nor POST - show message or redirect
                                                $displayError = "No prediction data to display. Please submit the form or view a specific record from your history.";
                                            }

                                            // --- Display Result or Error ---
                                            if ($displayError) {
                                                echo '<div class="alert alert-danger">' . htmlspecialchars($displayError) . '</div>';
                                            } elseif ($displayData) {
                                                // Extract variables for easier use in HTML
                                                $riskLevel = $displayData['riskLevel'];
                                                $probabilityPercent = $displayData['probabilityPercent'];
                                                $riskClass = $displayData['riskClass'];
                                                $riskDescription = $displayData['riskDescription'];
                                                $parameters = $displayData['parameters'];
                                            ?>
                                                <!-- Result Display Box -->
                                                <div class="card card-bordered">
                                                    <div class="card-inner">
                                                        <div class="result-box <?php echo htmlspecialchars($riskClass); ?>">
                                                            <h4 class="mb-2">Prediction Result: <span class="fw-bold"><?php echo htmlspecialchars($riskLevel); ?></span></h4>
                                                            <p class="lead">Probability of Heart Disease: <strong><?php echo htmlspecialchars($probabilityPercent); ?></strong></p>
                                                            <p><?php echo htmlspecialchars($riskDescription); ?></p>
                                                        </div>

                                                        <h5 class="mt-4">Parameters Used for Prediction:</h5>
                                                        <table class="table table-striped parameter-table">
                                                            <tbody>
                                                                <?php
                                                                // Define labels for parameters (use the keys from $validatedData or $record)
                                                                $parameterLabels = [
                                                                    'bmi' => 'BMI',
                                                                    'smoking' => 'Smoking',
                                                                    'alcohol_drinking' => 'Heavy Alcohol Consumption',
                                                                    'stroke' => 'Stroke History',
                                                                    'physical_health' => 'Poor Physical Health Days (last 30)',
                                                                    'mental_health' => 'Poor Mental Health Days (last 30)',
                                                                    'diff_walking' => 'Difficulty Walking',
                                                                    'sex' => 'Sex',
                                                                    'age' => 'Age',
                                                                    'race' => 'Race',
                                                                    'diabetic' => 'Diabetic Status',
                                                                    'physical_activity' => 'Physical Activity (last 30 days)',
                                                                    'gen_health' => 'General Health Perception',
                                                                    'sleep_time' => 'Average Sleep Time (hours)',
                                                                    'asthma' => 'Asthma History',
                                                                    'kidney_disease' => 'Kidney Disease History',
                                                                    'skin_cancer' => 'Skin Cancer History'
                                                                    // Add more labels as needed based on your form/DB fields
                                                                ];

                                                                foreach ($parameterLabels as $key => $label) {
                                                                    // Check if the key exists in the parameters array
                                                                    if (isset($parameters[$key])) {
                                                                        echo '<tr>';
                                                                        echo '<th>' . htmlspecialchars($label) . '</th>';
                                                                        // Handle boolean-like 'Yes'/'No' for better display
                                                                        $value = $parameters[$key];
                                                                        if (is_string($value) && in_array(strtolower($value), ['yes', 'no'])) {
                                                                            $displayValue = ucfirst(strtolower($value));
                                                                        } else {
                                                                            $displayValue = $value;
                                                                        }
                                                                        echo '<td>' . htmlspecialchars($displayValue) . '</td>';
                                                                        echo '</tr>';
                                                                    }
                                                                }
                                                                ?>
                                                            </tbody>
                                                        </table>

                                                        <?php
                                                        // Display contributing factors if available (only from POST/API result)
                                                        // Note: History view (GET) doesn't store/show factors currently.
                                                        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($apiResult['factors']) && is_array($apiResult['factors']) && !empty($apiResult['factors'])) {
                                                            echo '<h5 class="mt-4">Potential Contributing Factors:</h5>';
                                                            echo '<ul>';
                                                            foreach ($apiResult['factors'] as $factor) {
                                                                echo '<li>' . htmlspecialchars($factor) . '</li>';
                                                            }
                                                            echo '</ul>';
                                                        }
                                                        ?>

                                                        <div class="mt-4">
                                                            <a href="user_input_form.php" class="btn btn-primary">Make Another Prediction</a>
                                                            <a href="history.php" class="btn btn-outline-secondary">View History</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php
                                            } // End of if ($displayData)
                                            ?>
                                        </div><!-- .col -->

                                        <div class="col-lg-4">
                                            <div class="card card-bordered">
                                                <div class="card-inner">
                                                    <h5 class="card-title">Understanding Your Result</h5>
                                                    <p class="text-soft">This prediction is based on statistical patterns found in the dataset and the model used. It is not a substitute for a professional medical diagnosis.</p>
                                                    <ul>
                                                        <li><strong>Low Risk:</strong> Indicates a lower statistical probability compared to the average in the dataset. Continue healthy habits.</li>
                                                        <li><strong>High Risk:</strong> Indicates a higher statistical probability. It is strongly recommended to consult a doctor for a comprehensive evaluation and advice.</li>
                                                    </ul>
                                                    <p class="text-soft mt-2">Factors like age, BMI, smoking, and existing conditions (like diabetes, stroke history) often significantly influence the risk score.</p>
                                                    <a href="model_details.php" class="btn btn-link">Learn more about the model and data</a>
                                                </div>
                                            </div>
                                            <div class="card card-bordered mt-4">
                                                <div class="card-inner">
                                                    <h5 class="card-title">Next Steps</h5>
                                                    <p>Regardless of the result, consider these general health recommendations:</p>
                                                    <ul>
                                                        <li>Maintain a balanced diet.</li>
                                                        <li>Engage in regular physical activity.</li>
                                                        <li>Avoid smoking and limit alcohol intake.</li>
                                                        <li>Manage stress effectively.</li>
                                                        <li>Get regular check-ups with your doctor.</li>
                                                    </ul>
                                                    <a href="health_disease_facts.php" class="btn btn-link">More Health Facts</a>
                                                </div>
                                            </div>
                                        </div><!-- .col -->
                                    </div><!-- .row -->
                                </div><!-- .nk-block -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Include the footer component -->
            <?php include PROJECT_ROOT . '/footer.php'; ?>
        </div>
    </div>

    <!-- Include the scripts component -->
    <?php include PROJECT_ROOT . '/includes/scripts.php'; ?>
</body>

</html>
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
                                                // Use the same labels as the POST request for consistency
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
                                                    // Access data from the 'parameters' sub-array which holds the full record
                                                    if (isset($displayData['parameters'][$key])) {
                                                        $displayValue = htmlspecialchars($displayData['parameters'][$key]);
                                                        // Add mapping for boolean/numeric values to text if desired
                                                        echo "<tr>";
                                                        echo "<th>{$label}</th>";
                                                        echo "<td>{$displayValue}</td>";
                                                        echo "</tr>";
                                                    }
                                                }
                                                echo "</tbody>";
                                                echo "</table>";
                                                echo "</div>"; // end table-responsive
                                                echo "</div>"; // end mt-4

                                                echo "<div class='mt-4'>";
                                                echo "<a href='history.php' class='btn btn-outline-primary'>Back to History</a>";
                                                echo "</div>";

                                                echo "</div>"; // end card-inner
                                                echo "</div>"; // end card
                                            }
                                            elseif ($displayError) {
                                                // Display Error (covers both GET and POST errors if structured correctly)
                                                echo "<div class='card card-bordered'>";
                                                echo "<div class='card-inner'>";
                                                echo "<div class='card-head'>";
                                                echo "<h5 class='card-title'>Error</h5>";
                                                echo "</div>";
                                                echo "<div class='alert alert-danger'>";
                                                echo "<p>" . htmlspecialchars($displayError) . "</p>";
                                                echo "</div>";
                                                echo "<div class='mt-3'>";
                                                echo "<a href='history.php' class='btn btn-outline-primary'>Back to History</a>";
                                                echo "</div>";
                                                echo "</div>"; // card-inner
                                                echo "</div>"; // card
                                            }
                                            else {
                                                 // Default message if neither POST nor valid GET with ID
                                                 // This case should ideally only be hit if the page is accessed directly without POST data or GET ID
                                                 echo "<div class='alert alert-info'>Invalid request. Please submit the prediction form or view results from the history page.</div>";
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