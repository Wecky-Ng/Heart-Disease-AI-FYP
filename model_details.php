<?php
// Define PROJECT_ROOT if it hasn't been defined
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', __DIR__);
}
require_once PROJECT_ROOT . '/session.php';
require_once PROJECT_ROOT . '/includes/styles.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Details about the Heart Disease Prediction Model">
    <title>Heart Disease Prediction - Model Details</title>

</head>

<body class="nk-body bg-lighter">
    <div class="nk-app-root">
        <?php require_once PROJECT_ROOT . '/sidemenu.php'; ?>

        <div class="nk-sidebar-overlay" data-target="sidebarMenu"></div>

        <div class="nk-main">
            <?php require_once PROJECT_ROOT . '/header.php'; ?>

            <div class="nk-wrap">
                <div class="nk-content">
                    <div class="container-fluid">
                        <div class="nk-content-inner">
                            <div class="nk-content-body">
                                <div class="nk-block-head nk-block-head-sm">
                                    <div class="nk-block-between">
                                        <div class="nk-block-head-content">
                                            <h3 class="nk-block-title page-title">Model & Dataset Details</h3>
                                            <div class="nk-block-des text-soft">
                                                <p>Information about the AI model and dataset used for prediction.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="nk-block">
                                    <div class="row g-gs">
                                        <div class="col-lg-6">
                                            <div class="card card-bordered h-100">
                                                <div class="card-inner">
                                                    <h5 class="card-title">Prediction Model</h5>
                                                    <p>The heart disease prediction is powered by a Machine Learning model. Specifically, a <strong>RandomForestClassifier</strong> algorithm from the scikit-learn library is utilized. This model is trained to identify patterns in patient data that correlate with the presence or absence of heart disease.</p>
                                                    <!-- Placeholder for Model Image -->
                                                    <div class="text-center my-3">
                                                        <img src="./images/model_placeholder.svg" alt="Model Diagram Placeholder" style="max-width: 100%; height: auto;">
                                                        <p class="text-soft mt-1">[Diagram/Image explaining the model architecture]</p>
                                                    </div>
                                                    <p>The model is hosted externally via a Flask API. When you submit your data through the prediction form, it is securely sent to this API, which processes the information using the trained model and returns the prediction result (risk level and probability).</p>
                                                    <h6 class="card-title mt-3">API Interaction</h6>
                                                    <p>The prediction functionality relies on an external API built with Flask. This separation allows for independent scaling and updating of the prediction model without affecting the main web application. The communication happens over HTTPS to ensure data security.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="card card-bordered h-100">
                                                <div class="card-inner">
                                                    <h5 class="card-title">Dataset Information</h5>
                                                    <p>The model was trained on the <strong>heart_2020_cleaned.csv</strong> dataset, a publicly available dataset from the CDC's Behavioral Risk Factor Surveillance System (BRFSS) 2020. It contains data from over 300,000 adults in the US.</p>
                                                    <!-- Placeholder for Dataset Image/Visualization -->
                                                    <div class="text-center my-3">
                                                        <img src="./images/dataset_placeholder.svg" alt="Dataset Visualization Placeholder" style="max-width: 100%; height: auto;">
                                                        <p class="text-soft mt-1">[Chart/Image illustrating dataset features or distribution]</p>
                                                    </div>
                                                    <p>Key features used in the training include:</p>
                                                    <ul>
                                                        <li>Physical Health (e.g., BMI, Physical Activity)</li>
                                                        <li>Mental Health</li>
                                                        <li>Lifestyle Factors (e.g., Smoking, Alcohol Drinking)</li>
                                                        <li>Existing Conditions (e.g., Diabetes, Asthma, Kidney Disease, Skin Cancer)</li>
                                                        <li>Demographics (e.g., Age Category, Race, Sex)</li>
                                                        <!-- Add more specific features if needed -->
                                                    </ul>
                                                    <p>You can find more information about the dataset <a href="https://www.kaggle.com/datasets/kamilpytlak/personal-key-indicators-of-heart-disease" target="_blank">here</a>.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 mt-4">
                                            <div class="card card-bordered">
                                                <div class="card-inner">
                                                    <h5 class="card-title">Model Training Process</h5>
                                                    <p>The training process involved several key steps:</p>
                                                    <!-- Placeholder for Training Process Image -->
                                                    <div class="text-center my-3">
                                                        <img src="./images/training_placeholder.svg" alt="Training Process Placeholder" style="max-width: 80%; height: auto;">
                                                        <p class="text-soft mt-1">[Flowchart/Diagram of the training pipeline]</p>
                                                    </div>
                                                    <ol>
                                                        <li><strong>Data Loading & Cleaning:</strong> Loading the `heart_2020_cleaned.csv` dataset and handling any missing values or inconsistencies.</li>
                                                        <li><strong>Feature Engineering/Selection:</strong> Identifying the most relevant features and potentially creating new ones. Categorical features were encoded.</li>
                                                        <li><strong>Data Preprocessing:</strong> Scaling numerical features using StandardScaler to ensure all features contribute equally to the model training.</li>
                                                        <li><strong>Train-Test Split:</strong> Dividing the dataset into training and testing sets to evaluate the model's performance on unseen data.</li>
                                                        <li><strong>Model Training:</strong> Training the RandomForestClassifier model on the training data. Hyperparameter tuning might have been performed to optimize performance.</li>
                                                        <li><strong>Evaluation:</strong> Assessing the model's accuracy, precision, recall, F1-score, and AUC on the test set.</li>
                                                        <li><strong>Deployment:</strong> Saving the trained model and deploying it via the Flask API.</li>
                                                    </ol>
                                                    <p>[Add more specific details about the training parameters, evaluation metrics, or challenges faced here.]</p>
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
            <?php require_once PROJECT_ROOT . '/footer.php'; ?>
        </div>
    </div>

    <?php require_once PROJECT_ROOT . '/includes/scripts.php'; ?>
</body>

</html>