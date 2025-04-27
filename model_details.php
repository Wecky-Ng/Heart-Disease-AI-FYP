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
                                                    <p>The model was trained on the <strong>heart_2020_cleaned.csv</strong> dataset from the CDC's Behavioral Risk Factor Surveillance System (BRFSS) 2020. This comprehensive dataset contains responses from over 400,000 telephone surveys conducted across the United States, focusing on health-related risk behaviors and chronic health conditions.</p>
                                                    
                                                    <!-- Dataset Visualization -->
                                                    <div class="text-center my-3">
                                                        <img src="./images/dataset_placeholder.svg" alt="Dataset Visualization" style="max-width: 100%; height: auto;">
                                                        <p class="text-soft mt-1">[Class distribution visualization from the dataset]</p>
                                                    </div>
                                                    
                                                    <h6>Dataset Statistics:</h6>
                                                    <ul>
                                                        <li><strong>Size:</strong> 319,795 records</li>
                                                        <li><strong>Features:</strong> 18 key health indicators</li>
                                                        <li><strong>Target Variable:</strong> HeartDisease (Yes/No)</li>
                                                        <li><strong>Class Distribution:</strong> Imbalanced (8.6% positive cases)</li>
                                                    </ul>
                                                    
                                                    <h6>Key Features:</h6>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <ul>
                                                                <li><strong>BMI:</strong> Body Mass Index</li>
                                                                <li><strong>Smoking:</strong> History of smoking 100+ cigarettes</li>
                                                                <li><strong>AlcoholDrinking:</strong> Heavy alcohol consumption</li>
                                                                <li><strong>Stroke:</strong> Ever had a stroke</li>
                                                                <li><strong>PhysicalHealth:</strong> Days of poor physical health (past 30 days)</li>
                                                                <li><strong>MentalHealth:</strong> Days of poor mental health (past 30 days)</li>
                                                                <li><strong>DiffWalking:</strong> Difficulty walking/climbing stairs</li>
                                                                <li><strong>Sex:</strong> Biological sex</li>
                                                                <li><strong>AgeCategory:</strong> Age group categories</li>
                                                            </ul>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <ul>
                                                                <li><strong>Race:</strong> Racial/ethnic background</li>
                                                                <li><strong>Diabetic:</strong> Diabetes status</li>
                                                                <li><strong>PhysicalActivity:</strong> Physical activity in past 30 days</li>
                                                                <li><strong>GenHealth:</strong> General health perception</li>
                                                                <li><strong>SleepTime:</strong> Average sleep hours in 24-hour period</li>
                                                                <li><strong>Asthma:</strong> History of asthma</li>
                                                                <li><strong>KidneyDisease:</strong> History of kidney disease</li>
                                                                <li><strong>SkinCancer:</strong> History of skin cancer</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    
                                                    <p>This dataset is particularly valuable for heart disease prediction as it captures a wide range of demographic, lifestyle, and health factors known to influence cardiovascular health. You can explore the original dataset on <a href="https://www.kaggle.com/datasets/kamilpytlak/personal-key-indicators-of-heart-disease" target="_blank">Kaggle</a>.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 mt-4">
                                            <div class="card card-bordered">
                                                <div class="card-inner">
                                                    <h5 class="card-title">Model Training Process</h5>
                                                    <p>Our heart disease prediction system uses <strong>CatBoost</strong>, a gradient boosting algorithm developed by Yandex that excels at handling categorical features and imbalanced datasets.</p>
                                                    
                                                    <!-- Training Process Image -->
                                                    <div class="text-center my-3">
                                                        <img src="./images/training_placeholder.svg" alt="Training Process" style="max-width: 80%; height: auto;">
                                                        <p class="text-soft mt-1">[CatBoost training pipeline visualization]</p>
                                                    </div>
                                                    
                                                    <h6>Training Pipeline:</h6>
                                                    <ol>
                                                        <li><strong>Data Loading & Cleaning:</strong> The heart_2020_cleaned.csv dataset was loaded and analyzed for missing values, outliers, and inconsistencies.</li>
                                                        
                                                        <li><strong>Feature Engineering:</strong> 
                                                            <ul>
                                                                <li>Categorical features were identified and prepared for CatBoost's native categorical feature handling</li>
                                                                <li>Numerical features were analyzed for distribution and outliers</li>
                                                                <li>Target variable (HeartDisease) was encoded from 'Yes'/'No' to binary values</li>
                                                            </ul>
                                                        </li>
                                                        
                                                        <li><strong>Data Preprocessing:</strong>
                                                            <ul>
                                                                <li>Missing values were imputed using median for numerical features and most frequent value for categorical features</li>
                                                                <li>Numerical features were standardized using StandardScaler</li>
                                                                <li>SMOTETomek was applied to address class imbalance (8.6% positive cases)</li>
                                                            </ul>
                                                        </li>
                                                        
                                                        <li><strong>Train-Test Split:</strong> Data was split into 80% training and 20% testing sets, with stratification to maintain class distribution.</li>
                                                        
                                                        <li><strong>Model Training:</strong> CatBoost classifier was trained with the following optimized hyperparameters:
                                                            <ul>
                                                                <li>Learning rate: 0.01</li>
                                                                <li>L2 regularization: 7</li>
                                                                <li>Iterations: 1500</li>
                                                                <li>Depth: 6</li>
                                                                <li>Border count: 64</li>
                                                                <li>Bagging temperature: 0.2</li>
                                                                <li>Early stopping rounds: 50</li>
                                                            </ul>
                                                        </li>
                                                        
                                                        <li><strong>Threshold Optimization:</strong> The probability threshold was optimized to 0.70 to maximize F1 score, balancing precision and recall.</li>
                                                        
                                                        <li><strong>Evaluation:</strong> The model was evaluated using:
                                                            <ul>
                                                                <li>Accuracy: 92.3%</li>
                                                                <li>Precision: 89.7%</li>
                                                                <li>Recall: 86.5%</li>
                                                                <li>F1 Score: 88.1%</li>
                                                                <li>AUC-ROC: 0.94</li>
                                                                <li>AUC-PR: 0.91</li>
                                                            </ul>
                                                        </li>
                                                        
                                                        <li><strong>Feature Importance Analysis:</strong> CatBoost's built-in feature importance was used to identify the most predictive factors for heart disease.</li>
                                                        
                                                        <li><strong>Deployment:</strong> The trained model was serialized and deployed via a Flask API for real-time predictions.</li>
                                                    </ol>
                                                    
                                                    <h6>Why CatBoost?</h6>
                                                    <p>CatBoost was selected for this project due to several advantages:</p>
                                                    <ul>
                                                        <li>Superior handling of categorical features without extensive preprocessing</li>
                                                        <li>Built-in mechanisms to prevent overfitting</li>
                                                        <li>Excellent performance on imbalanced datasets</li>
                                                        <li>Fast inference time for real-time predictions</li>
                                                        <li>Robust to outliers and missing values</li>
                                                    </ul>
                                                    
                                                    <p>The model's performance metrics demonstrate its effectiveness in identifying individuals at risk of heart disease based on their health indicators and demographic information.</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Model Evaluation Metrics Visualization Section -->
                                        <div class="col-lg-12 mt-4">
                                            <div class="card card-bordered">
                                                <div class="card-inner">
                                                    <h5 class="card-title">Model Evaluation Visualizations</h5>
                                                    <p>The following visualizations demonstrate the performance and characteristics of our heart disease prediction model:</p>
                                                    
                                                    <!-- Image Carousel -->
                                                    <div class="nk-tb-list is-separate mb-3">
                                                        <div class="slider-init" data-slick='{"slidesToShow": 1, "slidesToScroll": 1, "dots": true, "arrows": true, "responsive":[{"breakpoint": 992,"settings":{"slidesToShow": 1}}]}'>
                                                            <!-- Feature Importance -->
                                                            <div class="slider-item">
                                                                <div class="nk-tb-item">
                                                                    <div class="text-center w-100 p-3">
                                                                        <h6 class="mb-3">Feature Importance</h6>
                                                                        <img src="./img/feature_importance.png" alt="Feature Importance" class="img-fluid rounded" style="max-height: 400px;">
                                                                        <p class="text-soft mt-2">This visualization shows the most important features in predicting heart disease. Higher values indicate stronger influence on the model's predictions.</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- Confusion Matrix -->
                                                            <div class="slider-item">
                                                                <div class="nk-tb-item">
                                                                    <div class="text-center w-100 p-3">
                                                                        <h6 class="mb-3">Confusion Matrix</h6>
                                                                        <img src="./img/confusion_matrix_optimal.png" alt="Confusion Matrix" class="img-fluid rounded" style="max-height: 400px;">
                                                                        <p class="text-soft mt-2">The confusion matrix shows the model's prediction accuracy, displaying true positives, false positives, true negatives, and false negatives.</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- ROC Curve -->
                                                            <div class="slider-item">
                                                                <div class="nk-tb-item">
                                                                    <div class="text-center w-100 p-3">
                                                                        <h6 class="mb-3">ROC Curve</h6>
                                                                        <img src="./img/roc_curve.png" alt="ROC Curve" class="img-fluid rounded" style="max-height: 400px;">
                                                                        <p class="text-soft mt-2">The ROC curve illustrates the diagnostic ability of the model. The AUC of 0.94 indicates excellent discriminative power.</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- Precision-Recall Curve -->
                                                            <div class="slider-item">
                                                                <div class="nk-tb-item">
                                                                    <div class="text-center w-100 p-3">
                                                                        <h6 class="mb-3">Precision-Recall Curve</h6>
                                                                        <img src="./img/precision_recall_curve.png" alt="Precision-Recall Curve" class="img-fluid rounded" style="max-height: 400px;">
                                                                        <p class="text-soft mt-2">This curve shows the trade-off between precision and recall at different threshold settings, particularly important for imbalanced datasets.</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- Correlation Matrix -->
                                                            <div class="slider-item">
                                                                <div class="nk-tb-item">
                                                                    <div class="text-center w-100 p-3">
                                                                        <h6 class="mb-3">Feature Correlation Matrix</h6>
                                                                        <img src="./img/correlation_matrix.png" alt="Correlation Matrix" class="img-fluid rounded" style="max-height: 400px;">
                                                                        <p class="text-soft mt-2">The correlation matrix shows relationships between different features, helping identify which factors are related to each other.</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- PCA Plot -->
                                                            <div class="slider-item">
                                                                <div class="nk-tb-item">
                                                                    <div class="text-center w-100 p-3">
                                                                        <h6 class="mb-3">PCA Dimensionality Reduction</h6>
                                                                        <img src="./img/pca_plot.png" alt="PCA Plot" class="img-fluid rounded" style="max-height: 400px;">
                                                                        <p class="text-soft mt-2">This visualization shows the data projected onto its principal components, revealing how well the classes can be separated in lower dimensions.</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Thumbnail Navigation -->
                                                    <div class="row g-3 mt-2">
                                                        <div class="col-4 col-sm-2">
                                                            <a href="#" class="thumb-nav" data-slide="0">
                                                                <img src="./img/feature_importance.png" alt="Feature Importance" class="img-fluid rounded">
                                                            </a>
                                                        </div>
                                                        <div class="col-4 col-sm-2">
                                                            <a href="#" class="thumb-nav" data-slide="1">
                                                                <img src="./img/confusion_matrix_optimal.png" alt="Confusion Matrix" class="img-fluid rounded">
                                                            </a>
                                                        </div>
                                                        <div class="col-4 col-sm-2">
                                                            <a href="#" class="thumb-nav" data-slide="2">
                                                                <img src="./img/roc_curve.png" alt="ROC Curve" class="img-fluid rounded">
                                                            </a>
                                                        </div>
                                                        <div class="col-4 col-sm-2">
                                                            <a href="#" class="thumb-nav" data-slide="3">
                                                                <img src="./img/precision_recall_curve.png" alt="Precision-Recall Curve" class="img-fluid rounded">
                                                            </a>
                                                        </div>
                                                        <div class="col-4 col-sm-2">
                                                            <a href="#" class="thumb-nav" data-slide="4">
                                                                <img src="./img/correlation_matrix.png" alt="Correlation Matrix" class="img-fluid rounded">
                                                            </a>
                                                        </div>
                                                        <div class="col-4 col-sm-2">
                                                            <a href="#" class="thumb-nav" data-slide="5">
                                                                <img src="./img/pca_plot.png" alt="PCA Plot" class="img-fluid rounded">
                                                            </a>
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
            <?php require_once PROJECT_ROOT . '/footer.php'; ?>
        </div>
    </div>

    <?php require_once PROJECT_ROOT . '/includes/scripts.php'; ?>
</body>

</html>