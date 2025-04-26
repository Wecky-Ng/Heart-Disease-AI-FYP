<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Heart Disease Prediction Result">
    <title>Heart Disease Prediction - Result</title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/dashlite.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/dashboard.css">
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
                                            // Include form preprocessing file
                                            require_once PROJECT_ROOT . '/form_preprocessing.php';

                                            // Check if form was submitted
                                            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                                                // Process form data and call prediction API
                                                $predictionResult = processAndPredict($_POST);

                                                if ($predictionResult['success']) {
                                                    // API call successful, get the result
                                                    $result = $predictionResult['result'];
                                                    $data = $_POST; // Original form data for display

                                                    // Get risk level and probability from API result
                                                    $riskLevel = ucfirst($result['risk_level']);
                                                    $probability = $result['probability'];
                                                    $factors = $result['factors'];

                                                    // Set risk class and description based on risk level
                                                    if ($riskLevel == "High") {
                                                        $riskClass = "result-high";
                                                        $riskDescription = "Based on the provided parameters, you have a high risk of heart disease. Please consult with a healthcare professional as soon as possible.";
                                                    } elseif ($riskLevel == "Medium") {
                                                        $riskClass = "result-medium";
                                                        $riskDescription = "Based on the provided parameters, you have a moderate risk of heart disease. Consider lifestyle changes and regular check-ups.";
                                                    } else {
                                                        $riskClass = "result-low";
                                                        $riskDescription = "Based on the provided parameters, you have a low risk of heart disease. Maintain a healthy lifestyle to keep it that way.";
                                                    }

                                                    // Display the result
                                                    echo "<div class='card card-bordered'>";
                                                    echo "<div class='card-inner'>";
                                                    echo "<div class='card-head'>";
                                                    echo "<h5 class='card-title'>Prediction Result</h5>";
                                                    echo "</div>";
                                                    echo "<div class='result-box {$riskClass}'>";
                                                    echo "<h4>Heart Disease Risk: {$riskLevel}</h4>";
                                                    echo "<p>Probability: {$probability}%</p>";
                                                    echo "<p>{$riskDescription}</p>";

                                                    // Display contributing factors
                                                    if (!empty($factors)) {
                                                        echo "<div class='mt-3'>";
                                                        echo "<h6>Contributing Factors:</h6>";
                                                        echo "<ul class='list'>";
                                                        foreach ($factors as $factor) {
                                                            echo "<li>{$factor}</li>";
                                                        }
                                                        echo "</ul>";
                                                        echo "</div>";
                                                    }

                                                    echo "</div>";
                                                    echo "<div class='mt-4'>";
                                                    echo "<h6>Your Health Parameters</h6>";
                                                    echo "<div class='table-responsive'>";
                                                    echo "<table class='table table-bordered parameter-table'>";
                                                    echo "<tbody>";

                                                    // Display all parameters
                                                    $parameterLabels = [
                                                        'age' => 'Age',
                                                        'gender' => 'Gender',
                                                        'bmi' => 'BMI',
                                                        'blood_pressure' => 'Blood Pressure',
                                                        'cholesterol_level' => 'Cholesterol Level',
                                                        'exercise_habits' => 'Exercise Habits',
                                                        'smoking' => 'Smoking',
                                                        'family_heart_disease' => 'Family Heart Disease',
                                                        'diabetes' => 'Diabetes',
                                                        'high_blood_pressure' => 'High Blood Pressure',
                                                        'low_hdl_cholesterol' => 'Low HDL Cholesterol',
                                                        'high_ldl_cholesterol' => 'High LDL Cholesterol',
                                                        'alcohol_consumption' => 'Alcohol Consumption',
                                                        'stress_level' => 'Stress Level',
                                                        'sleep_hours' => 'Sleep Hours',
                                                        'sugar_consumption' => 'Sugar Consumption',
                                                        'triglyceride_level' => 'Triglyceride Level',
                                                        'fasting_blood_sugar' => 'Fasting Blood Sugar',
                                                        'crp_level' => 'CRP Level',
                                                        'homocysteine_level' => 'Homocysteine Level'
                                                    ];

                                                    foreach ($parameterLabels as $key => $label) {
                                                        if (isset($data[$key])) {
                                                            echo "<tr>";
                                                            echo "<th>{$label}</th>";
                                                            echo "<td>{$data[$key]}</td>";
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
                                                    echo "</div>";
                                                    echo "</div>";
                                                } else {
                                                    // API call failed or validation errors
                                                    echo "<div class='card card-bordered'>";
                                                    echo "<div class='card-inner'>";
                                                    echo "<div class='card-head'>";

                                                    if (isset($predictionResult['errors']) && !empty($predictionResult['errors'])) {
                                                        // Display validation errors
                                                        echo "<h5 class='card-title'>Validation Error</h5>";
                                                        echo "</div>";
                                                        echo "<div class='alert alert-danger'>";
                                                        echo "<p>There were errors in your submission:</p>";
                                                        echo "<ul>";
                                                        foreach ($predictionResult['errors'] as $error) {
                                                            echo "<li>{$error}</li>";
                                                        }
                                                        echo "</ul>";
                                                    } else {
                                                        // Display API error
                                                        echo "<h5 class='card-title'>Prediction Error</h5>";
                                                        echo "</div>";
                                                        echo "<div class='alert alert-danger'>";
                                                        echo "<p>" . $predictionResult['error'] . "</p>";
                                                        echo "<p>The prediction service might be unavailable. Please try again later.</p>";
                                                    }
                                                    echo "</div>";
                                                    echo "<div class='mt-4'>";
                                                    echo "<a href='user_input_form.php' class='btn btn-outline-primary'>Go Back</a>";
                                                    echo "</div>";
                                                    echo "</div>";
                                                    echo "</div>";
                                                }
                                            } else {
                                                // If accessed directly without form submission
                                                echo "<div class='card card-bordered'>";
                                                echo "<div class='card-inner'>";
                                                echo "<div class='alert alert-info'>";
                                                echo "<p>Please submit the prediction form to see results.</p>";
                                                echo "</div>";
                                                echo "<div class='mt-4'>";
                                                echo "<a href='user_input_form.php' class='btn btn-primary'>Go to Prediction Form</a>";
                                                echo "</div>";
                                                echo "</div>";
                                                echo "</div>";
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
    <script src="js/bundle.js"></script>
    <script src="js/scripts.js"></script>
</body>

</html>