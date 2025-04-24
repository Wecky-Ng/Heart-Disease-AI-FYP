<?php
/**
 * Form Validation and Preprocessing for Heart Disease Prediction
 * 
 * This file handles server-side validation and preprocessing of form data
 * before it's used for prediction.
 */

/**
 * Validates and preprocesses form data
 * 
 * @param array $formData The raw form data from POST
 * @return array Validation result with processed data or errors
 */
function validateAndPreprocessFormData($formData) {
    $result = [
        'isValid' => true,
        'data' => [],
        'errors' => []
    ];
    
    // Validate Age (float64)
    if (isset($formData['age']) && !empty($formData['age'])) {
        $age = filter_var($formData['age'], FILTER_VALIDATE_FLOAT);
        if ($age === false || $age < 1 || $age > 120) {
            $result['errors'][] = 'Age must be between 1 and 120.';
            $result['isValid'] = false;
        } else {
            $result['data']['age'] = $age;
        }
    } else {
        $result['errors'][] = 'Age is required.';
        $result['isValid'] = false;
    }
    
    // Validate Gender (object/string)
    if (isset($formData['gender']) && !empty($formData['gender'])) {
        $gender = trim($formData['gender']);
        if ($gender !== 'Male' && $gender !== 'Female') {
            $result['errors'][] = 'Gender must be either Male or Female.';
            $result['isValid'] = false;
        } else {
            $result['data']['gender'] = $gender;
        }
    } else {
        $result['errors'][] = 'Gender is required.';
        $result['isValid'] = false;
    }
    
    // Validate Blood Pressure (float64)
    if (isset($formData['blood_pressure']) && !empty($formData['blood_pressure'])) {
        $bp = filter_var($formData['blood_pressure'], FILTER_VALIDATE_FLOAT);
        if ($bp === false || $bp < 80 || $bp > 200) {
            $result['errors'][] = 'Blood Pressure must be between 80 and 200.';
            $result['isValid'] = false;
        } else {
            $result['data']['blood_pressure'] = $bp;
        }
    } else {
        $result['errors'][] = 'Blood Pressure is required.';
        $result['isValid'] = false;
    }
    
    // Validate Cholesterol Level (float64)
    if (isset($formData['cholesterol_level']) && !empty($formData['cholesterol_level'])) {
        $chol = filter_var($formData['cholesterol_level'], FILTER_VALIDATE_FLOAT);
        if ($chol === false || $chol < 100 || $chol > 600) {
            $result['errors'][] = 'Cholesterol Level must be between 100 and 600.';
            $result['isValid'] = false;
        } else {
            $result['data']['cholesterol_level'] = $chol;
        }
    } else {
        $result['errors'][] = 'Cholesterol Level is required.';
        $result['isValid'] = false;
    }
    
    // Validate Exercise Habits (object/string)
    if (isset($formData['exercise_habits']) && !empty($formData['exercise_habits'])) {
        $exercise = trim($formData['exercise_habits']);
        if (!in_array($exercise, ['Low', 'Medium', 'High'])) {
            $result['errors'][] = 'Exercise Habits must be Low, Medium, or High.';
            $result['isValid'] = false;
        } else {
            $result['data']['exercise_habits'] = $exercise;
        }
    } else {
        $result['errors'][] = 'Exercise Habits is required.';
        $result['isValid'] = false;
    }
    
    // Validate Smoking (object/string)
    if (isset($formData['smoking']) && !empty($formData['smoking'])) {
        $smoking = trim($formData['smoking']);
        if ($smoking !== 'Yes' && $smoking !== 'No') {
            $result['errors'][] = 'Smoking must be either Yes or No.';
            $result['isValid'] = false;
        } else {
            $result['data']['smoking'] = $smoking;
        }
    } else {
        $result['errors'][] = 'Smoking status is required.';
        $result['isValid'] = false;
    }
    
    // Validate Family Heart Disease (object/string)
    if (isset($formData['family_heart_disease']) && !empty($formData['family_heart_disease'])) {
        $family = trim($formData['family_heart_disease']);
        if ($family !== 'Yes' && $family !== 'No') {
            $result['errors'][] = 'Family Heart Disease must be either Yes or No.';
            $result['isValid'] = false;
        } else {
            $result['data']['family_heart_disease'] = $family;
        }
    } else {
        $result['errors'][] = 'Family Heart Disease status is required.';
        $result['isValid'] = false;
    }
    
    // Validate Diabetes (object/string)
    if (isset($formData['diabetes']) && !empty($formData['diabetes'])) {
        $diabetes = trim($formData['diabetes']);
        if ($diabetes !== 'Yes' && $diabetes !== 'No') {
            $result['errors'][] = 'Diabetes must be either Yes or No.';
            $result['isValid'] = false;
        } else {
            $result['data']['diabetes'] = $diabetes;
        }
    } else {
        $result['errors'][] = 'Diabetes status is required.';
        $result['isValid'] = false;
    }
    
    // Validate BMI (float64)
    if (isset($formData['bmi']) && !empty($formData['bmi'])) {
        $bmi = filter_var($formData['bmi'], FILTER_VALIDATE_FLOAT);
        if ($bmi === false || $bmi < 10 || $bmi > 50) {
            $result['errors'][] = 'BMI must be between 10 and 50.';
            $result['isValid'] = false;
        } else {
            $result['data']['bmi'] = $bmi;
        }
    } else {
        $result['errors'][] = 'BMI is required.';
        $result['isValid'] = false;
    }
    
    // Validate High Blood Pressure (object/string)
    if (isset($formData['high_blood_pressure']) && !empty($formData['high_blood_pressure'])) {
        $highBP = trim($formData['high_blood_pressure']);
        if ($highBP !== 'Yes' && $highBP !== 'No') {
            $result['errors'][] = 'High Blood Pressure must be either Yes or No.';
            $result['isValid'] = false;
        } else {
            $result['data']['high_blood_pressure'] = $highBP;
        }
    } else {
        $result['errors'][] = 'High Blood Pressure status is required.';
        $result['isValid'] = false;
    }
    
    // Validate Low HDL Cholesterol (object/string)
    if (isset($formData['low_hdl_cholesterol']) && !empty($formData['low_hdl_cholesterol'])) {
        $lowHDL = trim($formData['low_hdl_cholesterol']);
        if ($lowHDL !== 'Yes' && $lowHDL !== 'No') {
            $result['errors'][] = 'Low HDL Cholesterol must be either Yes or No.';
            $result['isValid'] = false;
        } else {
            $result['data']['low_hdl_cholesterol'] = $lowHDL;
        }
    } else {
        $result['errors'][] = 'Low HDL Cholesterol status is required.';
        $result['isValid'] = false;
    }
    
    // Validate High LDL Cholesterol (object/string)
    if (isset($formData['high_ldl_cholesterol']) && !empty($formData['high_ldl_cholesterol'])) {
        $highLDL = trim($formData['high_ldl_cholesterol']);
        if ($highLDL !== 'Yes' && $highLDL !== 'No') {
            $result['errors'][] = 'High LDL Cholesterol must be either Yes or No.';
            $result['isValid'] = false;
        } else {
            $result['data']['high_ldl_cholesterol'] = $highLDL;
        }
    } else {
        $result['errors'][] = 'High LDL Cholesterol status is required.';
        $result['isValid'] = false;
    }
    
    // Validate Alcohol Consumption (object/string)
    if (isset($formData['alcohol_consumption']) && !empty($formData['alcohol_consumption'])) {
        $alcohol = trim($formData['alcohol_consumption']);
        if (!in_array($alcohol, ['Low', 'Medium', 'High'])) {
            $result['errors'][] = 'Alcohol Consumption must be Low, Medium, or High.';
            $result['isValid'] = false;
        } else {
            $result['data']['alcohol_consumption'] = $alcohol;
        }
    } else {
        $result['errors'][] = 'Alcohol Consumption is required.';
        $result['isValid'] = false;
    }
    
    // Validate Stress Level (object/string)
    if (isset($formData['stress_level']) && !empty($formData['stress_level'])) {
        $stress = trim($formData['stress_level']);
        if (!in_array($stress, ['Low', 'Medium', 'High'])) {
            $result['errors'][] = 'Stress Level must be Low, Medium, or High.';
            $result['isValid'] = false;
        } else {
            $result['data']['stress_level'] = $stress;
        }
    } else {
        $result['errors'][] = 'Stress Level is required.';
        $result['isValid'] = false;
    }
    
    // Validate Sleep Hours (float64)
    if (isset($formData['sleep_hours']) && !empty($formData['sleep_hours'])) {
        $sleep = filter_var($formData['sleep_hours'], FILTER_VALIDATE_FLOAT);
        if ($sleep === false || $sleep < 3 || $sleep > 12) {
            $result['errors'][] = 'Sleep Hours must be between 3 and 12.';
            $result['isValid'] = false;
        } else {
            $result['data']['sleep_hours'] = $sleep;
        }
    } else {
        $result['errors'][] = 'Sleep Hours is required.';
        $result['isValid'] = false;
    }
    
    // Validate Sugar Consumption (object/string)
    if (isset($formData['sugar_consumption']) && !empty($formData['sugar_consumption'])) {
        $sugar = trim($formData['sugar_consumption']);
        if (!in_array($sugar, ['Low', 'Medium', 'High'])) {
            $result['errors'][] = 'Sugar Consumption must be Low, Medium, or High.';
            $result['isValid'] = false;
        } else {
            $result['data']['sugar_consumption'] = $sugar;
        }
    } else {
        $result['errors'][] = 'Sugar Consumption is required.';
        $result['isValid'] = false;
    }
    
    // Validate Triglyceride Level (float64)
    if (isset($formData['triglyceride_level']) && !empty($formData['triglyceride_level'])) {
        $trig = filter_var($formData['triglyceride_level'], FILTER_VALIDATE_FLOAT);
        if ($trig === false || $trig < 50 || $trig > 500) {
            $result['errors'][] = 'Triglyceride Level must be between 50 and 500.';
            $result['isValid'] = false;
        } else {
            $result['data']['triglyceride_level'] = $trig;
        }
    } else {
        $result['errors'][] = 'Triglyceride Level is required.';
        $result['isValid'] = false;
    }
    
    // Validate Fasting Blood Sugar (float64)
    if (isset($formData['fasting_blood_sugar']) && !empty($formData['fasting_blood_sugar'])) {
        $fbs = filter_var($formData['fasting_blood_sugar'], FILTER_VALIDATE_FLOAT);
        if ($fbs === false || $fbs < 70 || $fbs > 200) {
            $result['errors'][] = 'Fasting Blood Sugar must be between 70 and 200.';
            $result['isValid'] = false;
        } else {
            $result['data']['fasting_blood_sugar'] = $fbs;
        }
    } else {
        $result['errors'][] = 'Fasting Blood Sugar is required.';
        $result['isValid'] = false;
    }
    
    // Validate CRP Level (float64)
    if (isset($formData['crp_level']) && !empty($formData['crp_level'])) {
        $crp = filter_var($formData['crp_level'], FILTER_VALIDATE_FLOAT);
        if ($crp === false || $crp < 0 || $crp > 20) {
            $result['errors'][] = 'CRP Level must be between 0 and 20.';
            $result['isValid'] = false;
        } else {
            $result['data']['crp_level'] = $crp;
        }
    } else {
        $result['errors'][] = 'CRP Level is required.';
        $result['isValid'] = false;
    }
    
    // Validate Homocysteine Level (float64)
    if (isset($formData['homocysteine_level']) && !empty($formData['homocysteine_level'])) {
        $homo = filter_var($formData['homocysteine_level'], FILTER_VALIDATE_FLOAT);
        if ($homo === false || $homo < 0 || $homo > 30) {
            $result['errors'][] = 'Homocysteine Level must be between 0 and 30.';
            $result['isValid'] = false;
        } else {
            $result['data']['homocysteine_level'] = $homo;
        }
    } else {
        $result['errors'][] = 'Homocysteine Level is required.';
        $result['isValid'] = false;
    }
    
    return $result;
}