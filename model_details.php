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
    <link rel="icon" href="favicon.ico" type="image/x-icon">
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
                                                    <p>The heart disease prediction is powered by an advanced <strong>Stacking Ensemble Machine Learning model</strong>. This ensemble combines the strengths of two powerful gradient boosting algorithms, <strong>CatBoost</strong> and <strong>LightGBM</strong>, with a <strong>Logistic Regression</strong> model acting as the meta-learner. This approach leverages multiple models to improve prediction accuracy and robustness.</p>
                                                    <!-- Model Performance Visualization -->
                                                    <div class="text-center my-3">
                                                        <img src="./img/roc_curve.png" alt="ROC Curve" style="max-width: 80%; height: auto;">
                                                        <p class="text-soft mt-1">[Receiver Operating Characteristic (ROC) Curve showing the trade-off between true positive rate and false positive rate for the ensemble model.]</p>
                                                    </div>
                                                    <p>The final trained ensemble model is used by the backend Flask API. When you submit your data through the prediction form, it is securely sent to this API, which processes the information using the ensemble model and returns the prediction result (risk level and probability).</p>
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
                                                    
                                                    <!-- Model Performance Visualization -->
                                                    <div class="text-center my-3">
                                                        <img src="./img/precision_recall_curve.png" alt="Precision-Recall Curve" style="max-width: 80%; height: auto;">
                                                        <p class="text-soft mt-1">[Precision-Recall Curve illustrating the trade-off between precision and recall for different thresholds, particularly useful for imbalanced datasets.]</p>
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
                                                    <br>
                                                    <p>This dataset is particularly valuable for heart disease prediction as it captures a wide range of demographic, lifestyle, and health factors known to influence cardiovascular health. You can explore the original dataset on <a href="https://www.kaggle.com/datasets/kamilpytlak/personal-key-indicators-of-heart-disease" target="_blank">Kaggle</a>.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 mt-4">
                                            <div class="card card-bordered">
                                                <div class="card-inner">
                                                    <h5 class="card-title">Model Training Process</h5>
                                                    <p>Our heart disease prediction system utilizes a sophisticated <strong>Stacking Ensemble</strong> approach, combining <strong>CatBoost</strong> and <strong>LightGBM</strong> as base learners, with <strong>Logistic Regression</strong> as the meta-learner. This method aims to capture complex patterns by leveraging the diverse strengths of each model.</p>
                                                    <div class="nk-tb-item">
                                                    <!-- Feature Importance Image -->
                                                    <div class="text-center my-3">
                                                        <img src="./img/feature_importance.png" alt="Feature Importance" style="max-width: 80%; height: auto;">
                                                        <p class="text-soft mt-1">[Feature Importance. This visualization likely shows the aggregated or individual importance of features from one of the base models (e.g., CatBoost), indicating key predictors.]</p>
                                                    </div>

                                                    <h6>Training Pipeline:</h6>
                                                    <ol>
                                                        <li><strong>Data Loading & Preparation:</strong> The <code>heart_2020_cleaned.csv</code> dataset was loaded. The target variable 'HeartDisease' was label encoded ('No': 0, 'Yes': 1).</li>
                                                        <li><strong>Train-Test Split:</strong> Data was split into 80% training and 20% testing sets using stratification to maintain the original class distribution.</li>
                                                        <li><strong>Preprocessing:</strong>
                                                            <ul>
                                                                <li>Numerical features: Missing values imputed with the median, then scaled using StandardScaler.</li>
                                                                <li>Categorical features: Missing values imputed with the most frequent value, then one-hot encoded (dropping the first category).</li>
                                                            </ul>
                                                        </li>
                                                        <li><strong>Base Model Hyperparameter Tuning:</strong>
                                                            <ul>
                                                                <li><strong>CatBoost & LightGBM:</strong> RandomizedSearchCV (with 50 iterations and 3-fold stratified cross-validation) was used to find the optimal hyperparameters for both base models independently, optimizing for the 'f1_weighted' score. Search spaces covered parameters like learning rate, tree depth/leaves, regularization, and bagging/subsampling.</li>
                                                            </ul>
                                                        </li>
                                                        <li><strong>Stacking Ensemble Construction:</strong>
                                                            <ul>
                                                                <li>The tuned CatBoost and LightGBM models were used as base estimators.</li>
                                                                <li>A Logistic Regression model (with balanced class weights and 'liblinear' solver) was chosen as the meta-learner.</li>
                                                            </ul>
                                                        </li>
                                                        <li><strong>Meta-Learner Tuning:</strong> RandomizedSearchCV (20 iterations) was used to tune the regularization parameter (C) of the final Logistic Regression meta-learner within the stacking framework, again optimizing for 'f1_weighted'.</li>
                                                        <li><strong>Threshold Optimization:</strong> After training the final ensemble, the prediction probability threshold was optimized on the test set to maximize the F1 score, balancing precision and recall effectively for the imbalanced dataset. The optimal threshold was found to be approximately <strong>0.2168</strong>.</li>
                                                        <li><strong>Evaluation (on Test Set @ Optimal Threshold):</strong> The final tuned ensemble model achieved the following performance:
                                                            <ul>
                                                                <li><strong>AUC-ROC:</strong> ~0.9046 (Overall discrimination ability)</li>
                                                                <li><strong>Accuracy:</strong> ~0.8514</li>
                                                                <li><strong>Precision (for 'Yes'):</strong> ~0.40</li>
                                                                <li><strong>Recall (for 'Yes'):</strong> ~0.88</li>
                                                                <li><strong>F1 Score (for 'Yes'):</strong> ~0.55</li>
                                                                <li><em>(Note: Specific metrics depend on the exact tuning results and the optimal threshold chosen)</em></li>
                                                            </ul>
                                                        </li>
                                                        <li><strong>Deployment:</strong> The fully trained and tuned stacking ensemble model was serialized using joblib (<code>tuned_ensemble_cat_lgbm_v4.pkl</code>) for deployment via the Flask API.</li>
                                                    </ol>

                                                    <h6>Why Stacking Ensemble?</h6>
                                                    <p>A stacking ensemble was chosen to:</p>
                                                    <ul>
                                                        <li>Combine the predictive power of different high-performing models (CatBoost's categorical handling, LightGBM's speed).</li>
                                                        <li>Improve generalization by having a meta-learner combine the base model predictions.</li>
                                                        <li>Potentially achieve better performance than any single model alone, especially on complex datasets.</li>
                                                    </ul>

                                                    <p>The rigorous tuning process for both base models and the meta-learner, along with threshold optimization, ensures the model is well-suited for identifying individuals at risk of heart disease based on the available health indicators.</p>
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
    <script>
        $(document).ready(function() {
            // Initialize the slider
            var $slickSlider = $('.slider-init');
            
            // Handle thumbnail navigation clicks
            $('.thumb-nav').on('click', function(e) {
                e.preventDefault(); // Prevent default anchor behavior
                var slideIndex = $(this).data('slide');
                $slickSlider.slick('slickGoTo', slideIndex);
                return false; // Prevent page scroll
            });
            
            // Removed custom height adjustment logic as fixed height is now applied via CSS
        });
    </script>
</body>

</html>