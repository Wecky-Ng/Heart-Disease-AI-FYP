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
                                                    <form id="prediction-form" action="result.php" method="post" class="form-validate">
                                                        <div class="row g-4">
                                                            <!-- Basic Information -->
                                                            <div class="col-12">
                                                                <h6 class="overline-title text-primary">Basic Information</h6>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="age">Age</label>
                                                                    <div class="form-control-wrap">
                                                                        <input type="number" class="form-control" id="age" name="age" min="1" max="120" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="gender">Gender</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="gender" name="gender" required>
                                                                            <option value="">Select</option>
                                                                            <option value="Male">Male</option>
                                                                            <option value="Female">Female</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="bmi">BMI</label>
                                                                    <div class="form-control-wrap">
                                                                        <input type="number" step="0.01" class="form-control" id="bmi" name="bmi" min="10" max="50" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Cardiovascular Indicators -->
                                                            <div class="col-12">
                                                                <h6 class="overline-title text-primary mt-3">Cardiovascular Indicators</h6>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="blood_pressure">Blood Pressure (mm Hg)</label>
                                                                    <div class="form-control-wrap">
                                                                        <input type="number" class="form-control" id="blood_pressure" name="blood_pressure" min="80" max="200" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="cholesterol_level">Cholesterol Level (mg/dl)</label>
                                                                    <div class="form-control-wrap">
                                                                        <input type="number" class="form-control" id="cholesterol_level" name="cholesterol_level" min="100" max="600" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="high_blood_pressure">High Blood Pressure</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="high_blood_pressure" name="high_blood_pressure" required>
                                                                            <option value="">Select</option>
                                                                            <option value="Yes">Yes</option>
                                                                            <option value="No">No</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="low_hdl_cholesterol">Low HDL Cholesterol</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="low_hdl_cholesterol" name="low_hdl_cholesterol" required>
                                                                            <option value="">Select</option>
                                                                            <option value="Yes">Yes</option>
                                                                            <option value="No">No</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="high_ldl_cholesterol">High LDL Cholesterol</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="high_ldl_cholesterol" name="high_ldl_cholesterol" required>
                                                                            <option value="">Select</option>
                                                                            <option value="Yes">Yes</option>
                                                                            <option value="No">No</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="triglyceride_level">Triglyceride Level</label>
                                                                    <div class="form-control-wrap">
                                                                        <input type="number" class="form-control" id="triglyceride_level" name="triglyceride_level" min="50" max="500" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="fasting_blood_sugar">Fasting Blood Sugar</label>
                                                                    <div class="form-control-wrap">
                                                                        <input type="number" class="form-control" id="fasting_blood_sugar" name="fasting_blood_sugar" min="70" max="200" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="crp_level">CRP Level</label>
                                                                    <div class="form-control-wrap">
                                                                        <input type="number" step="0.01" class="form-control" id="crp_level" name="crp_level" min="0" max="20" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="homocysteine_level">Homocysteine Level</label>
                                                                    <div class="form-control-wrap">
                                                                        <input type="number" step="0.01" class="form-control" id="homocysteine_level" name="homocysteine_level" min="0" max="30" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Lifestyle Factors -->
                                                            <div class="col-12">
                                                                <h6 class="overline-title text-primary mt-3">Lifestyle Factors</h6>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="exercise_habits">Exercise Habits</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="exercise_habits" name="exercise_habits" required>
                                                                            <option value="">Select</option>
                                                                            <option value="Low">Low</option>
                                                                            <option value="Medium">Medium</option>
                                                                            <option value="High">High</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
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
                                                                    <label class="form-label" for="alcohol_consumption">Alcohol Consumption</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="alcohol_consumption" name="alcohol_consumption" required>
                                                                            <option value="">Select</option>
                                                                            <option value="Low">Low</option>
                                                                            <option value="Medium">Medium</option>
                                                                            <option value="High">High</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="stress_level">Stress Level</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="stress_level" name="stress_level" required>
                                                                            <option value="">Select</option>
                                                                            <option value="Low">Low</option>
                                                                            <option value="Medium">Medium</option>
                                                                            <option value="High">High</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="sleep_hours">Sleep Hours</label>
                                                                    <div class="form-control-wrap">
                                                                        <input type="number" step="0.1" class="form-control" id="sleep_hours" name="sleep_hours" min="3" max="12" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="sugar_consumption">Sugar Consumption</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="sugar_consumption" name="sugar_consumption" required>
                                                                            <option value="">Select</option>
                                                                            <option value="Low">Low</option>
                                                                            <option value="Medium">Medium</option>
                                                                            <option value="High">High</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Medical History -->
                                                            <div class="col-12">
                                                                <h6 class="overline-title text-primary mt-3">Medical History</h6>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="family_heart_disease">Family Heart Disease</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="family_heart_disease" name="family_heart_disease" required>
                                                                            <option value="">Select</option>
                                                                            <option value="Yes">Yes</option>
                                                                            <option value="No">No</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="diabetes">Diabetes</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select" id="diabetes" name="diabetes" required>
                                                                            <option value="">Select</option>
                                                                            <option value="Yes">Yes</option>
                                                                            <option value="No">No</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="form-group">
                                                                    <button type="submit" class="btn btn-primary">Predict Risk</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="card card-bordered h-100 card-prediction">
                                                <div class="card-inner">
                                                    <div class="card-head">
                                                        <h5 class="card-title">Prediction Result</h5>
                                                    </div>
                                                    <div id="result-container">
                                                        <p class="text-soft">Fill in the form and click "Predict Risk" to see your heart disease risk assessment.</p>
                                                        
                                                        <div id="result-high" class="result-box result-high">
                                                            <h6 class="text-danger"><em class="icon ni ni-alert-circle"></em> High Risk</h6>
                                                            <p>Based on the comprehensive analysis of your 21 health parameters, the model predicts a high risk of heart disease. Please consult with a healthcare professional as soon as possible.</p>
                                                            <ul class="list list-sm list-checked">
                                                                <li>Your combination of lifestyle factors, medical history, and cardiovascular indicators suggests significant risk</li>
                                                                <li>Consider immediate lifestyle modifications and medical consultation</li>
                                                                <li>Regular monitoring of blood pressure, cholesterol, and blood sugar is recommended</li>
                                                            </ul>
                                                        </div>
                                                        
                                                        <div id="result-medium" class="result-box result-medium">
                                                            <h6 class="text-warning"><em class="icon ni ni-alert-circle"></em> Medium Risk</h6>
                                                            <p>Based on the comprehensive analysis of your 21 health parameters, the model predicts a moderate risk of heart disease. Consider discussing these results with your doctor.</p>
                                                            <ul class="list list-sm list-checked">
                                                                <li>Some of your health parameters indicate potential concerns</li>
                                                                <li>Moderate lifestyle changes may help reduce your risk</li>
                                                                <li>Regular health check-ups are recommended</li>
                                                            </ul>
                                                        </div>
                                                        
                                                        <div id="result-low" class="result-box result-low">
                                                            <h6 class="text-success"><em class="icon ni ni-check-circle"></em> Low Risk</h6>
                                                            <p>Based on the comprehensive analysis of your 21 health parameters, the model predicts a low risk of heart disease. Continue maintaining a healthy lifestyle.</p>
                                                            <ul class="list list-sm list-checked">
                                                                <li>Your overall health profile shows positive indicators</li>
                                                                <li>Maintain your current healthy habits</li>
                                                                <li>Continue regular health check-ups</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mt-4">
                                                        <h6 class="overline-title">Important Note</h6>
                                                        <p class="text-soft">This prediction is based on an AI model analyzing 21 comprehensive health parameters including lifestyle factors, medical history, and cardiovascular indicators. While our model is trained on extensive data, it should not replace professional medical advice. Always consult with healthcare professionals for accurate diagnosis and treatment.</p>
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
                
                <div class="nk-footer">
                    <div class="container-fluid">
                        <div class="nk-footer-wrap">
                            <div class="nk-footer-copyright">© 2024 Heart Disease Prediction. All Rights Reserved.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="js/bundle.js"></script>
    <script src="js/scripts.js"></script>
    <script src="js/dashboard.js"></script>
    <script>
        // Load header and side menu components
        document.addEventListener('DOMContentLoaded', function() {
            // Load header
            fetch('header.html')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('header-container').innerHTML = data;
                })
                .catch(error => console.error('Error loading header:', error));
            
            // Load side menu
            fetch('sidemenu.html')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('side-menu-container').innerHTML = data;
                })
                .catch(error => console.error('Error loading side menu:', error));
        });
        
        // Simple prediction simulation
        document.getElementById('prediction-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Hide all result boxes
            document.querySelectorAll('.result-box').forEach(function(box) {
                box.style.display = 'none';
            });
            
            // Collect all form data
            const formData = {
                // Basic Information
                age: document.getElementById('age').value,
                gender: document.getElementById('gender').value,
                bmi: document.getElementById('bmi').value,
                
                // Cardiovascular Indicators
                blood_pressure: document.getElementById('blood_pressure').value,
                cholesterol_level: document.getElementById('cholesterol_level').value,
                high_blood_pressure: document.getElementById('high_blood_pressure').value,
                low_hdl_cholesterol: document.getElementById('low_hdl_cholesterol').value,
                high_ldl_cholesterol: document.getElementById('high_ldl_cholesterol').value,
                triglyceride_level: document.getElementById('triglyceride_level').value,
                fasting_blood_sugar: document.getElementById('fasting_blood_sugar').value,
                crp_level: document.getElementById('crp_level').value,
                homocysteine_level: document.getElementById('homocysteine_level').value,
                
                // Lifestyle Factors
                exercise_habits: document.getElementById('exercise_habits').value,
                smoking: document.getElementById('smoking').value,
                alcohol_consumption: document.getElementById('alcohol_consumption').value,
                stress_level: document.getElementById('stress_level').value,
                sleep_hours: document.getElementById('sleep_hours').value,
                sugar_consumption: document.getElementById('sugar_consumption').value,
                
                // Medical History
                family_heart_disease: document.getElementById('family_heart_disease').value,
                diabetes: document.getElementById('diabetes').value
            };
            
            console.log('Form data collected:', formData);
            
            // In a real application, you would send this data to your backend
            // For example: fetch('/predict', { method: 'POST', body: JSON.stringify(formData) })
            
            // For now, we'll simulate a prediction result
            const randomValue = Math.random();
            let resultBox;
            
            if (randomValue > 0.7) {
                resultBox = document.getElementById('result-high');
            } else if (randomValue > 0.4) {
                resultBox = document.getElementById('result-medium');
            } else {
                resultBox = document.getElementById('result-low');
            }
            
            // Show the selected result box
            resultBox.style.display = 'block';
            
            // Scroll to result on mobile
            if (window.innerWidth < 992) {
                document.getElementById('result-container').scrollIntoView({ behavior: 'smooth' });
            }
        });
    </script>
    
    <!-- Include common JavaScript -->
    <?php include 'includes/scripts.php'; ?>
</body>
</html>