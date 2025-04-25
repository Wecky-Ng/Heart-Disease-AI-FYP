<?php
/**
 * Form Preprocessing for Heart Disease Prediction
 * 
 * This file handles the preprocessing of form data to match the format expected by the
 * heart disease prediction model based on heart_2020_cleaned.csv, and prepares it for
 * the Python API call.
 */

// Include necessary files
require_once 'database/form_validation_preprocessing.php';

/**
 * Process form data for the heart disease prediction API
 * 
 * @param array $formData The raw form data from POST
 * @return array Processed data ready for API call or error information
 */
function processFormDataForAPI($formData) {
    // First validate the form data using existing validation function
    $validationResult = validateAndPreprocessFormData($formData);
    
    // If validation failed, return the validation result
    if (!$validationResult['isValid']) {
        return $validationResult;
    }
    
    // Get the validated data
    $data = $validationResult['data'];
    $apiData = [];
    
    // Map form fields to API expected fields
    
    // Process numerical features
    // Age - convert age category to numerical value (midpoint of range)
    if (isset($data['age_category'])) {
        $ageCategory = $data['age_category'];
        switch ($ageCategory) {
            case '18-24':
                $apiData['age'] = 21;
                break;
            case '25-29':
                $apiData['age'] = 27;
                break;
            case '30-34':
                $apiData['age'] = 32;
                break;
            case '35-39':
                $apiData['age'] = 37;
                break;
            case '40-44':
                $apiData['age'] = 42;
                break;
            case '45-49':
                $apiData['age'] = 47;
                break;
            case '50-54':
                $apiData['age'] = 52;
                break;
            case '55-59':
                $apiData['age'] = 57;
                break;
            case '60-64':
                $apiData['age'] = 62;
                break;
            case '65-69':
                $apiData['age'] = 67;
                break;
            case '70-74':
                $apiData['age'] = 72;
                break;
            case '75-79':
                $apiData['age'] = 77;
                break;
            case '80 or older':
                $apiData['age'] = 85;
                break;
            default:
                $apiData['age'] = 50; // Default value
        }
    } else if (isset($data['age'])) {
        $apiData['age'] = $data['age'];
    }
    
    // BMI
    $apiData['bmi'] = isset($data['bmi']) ? (float)$data['bmi'] : 25.0;
    
    // Blood Pressure (systolic)
    $apiData['blood_pressure'] = isset($data['blood_pressure']) ? (float)$data['blood_pressure'] : 120.0;
    
    // Cholesterol
    if (isset($data['cholesterol_level'])) {
        switch ($data['cholesterol_level']) {
            case 'High':
                $apiData['cholesterol'] = 240.0;
                break;
            case 'Borderline':
                $apiData['cholesterol'] = 200.0;
                break;
            case 'Normal':
            default:
                $apiData['cholesterol'] = 170.0;
                break;
        }
    } else {
        $apiData['cholesterol'] = 170.0; // Default normal value
    }
    
    // Heart Rate
    $apiData['heart_rate'] = isset($data['heart_rate']) ? (float)$data['heart_rate'] : 75.0;
    
    // Blood Sugar
    if (isset($data['diabetes']) && $data['diabetes'] == 'Yes') {
        $apiData['blood_sugar'] = 130.0; // Elevated blood sugar for diabetics
    } else {
        $apiData['blood_sugar'] = 90.0; // Normal blood sugar
    }
    
    // Process categorical features
    
    // Sex/Gender
    $apiData['sex'] = isset($data['sex']) ? $data['sex'] : 'Male';
    
    // Smoking
    $apiData['smoking'] = isset($data['smoking']) ? $data['smoking'] : 'No';
    
    // Alcohol Drinking
    $apiData['alcohol_drinking'] = isset($data['alcohol_drinking']) ? $data['alcohol_drinking'] : 'No';
    
    // Stroke History
    $apiData['stroke'] = isset($data['stroke']) ? $data['stroke'] : 'No';
    
    // Diabetes
    $apiData['diabetes'] = isset($data['diabetes']) ? $data['diabetes'] : 'No';
    
    // Physical Activity
    $apiData['physical_activity'] = isset($data['physical_activity']) ? $data['physical_activity'] : 'No';
    
    // General Health
    $apiData['general_health'] = isset($data['general_health']) ? $data['general_health'] : 'Good';
    
    // Sleep Time
    $apiData['sleep_time'] = isset($data['sleep_time']) ? (float)$data['sleep_time'] : 7.0;
    
    // Asthma
    $apiData['asthma'] = isset($data['asthma']) ? $data['asthma'] : 'No';
    
    // Kidney Disease
    $apiData['kidney_disease'] = isset($data['kidney_disease']) ? $data['kidney_disease'] : 'No';
    
    // Skin Cancer
    $apiData['skin_cancer'] = isset($data['skin_cancer']) ? $data['skin_cancer'] : 'No';
    
    // Race/Ethnicity
    $apiData['race'] = isset($data['race']) ? $data['race'] : 'White';
    
    // Family History
    $apiData['family_history'] = isset($data['family_history']) ? $data['family_history'] : 'No';
    
    // Physical Health (days not good)
    $apiData['physical_health'] = isset($data['physical_health']) ? (float)$data['physical_health'] : 0.0;
    
    // Mental Health (days not good)
    $apiData['mental_health'] = isset($data['mental_health']) ? (float)$data['mental_health'] : 0.0;
    
    // Return the processed data ready for API
    return [
        'isValid' => true,
        'data' => $apiData,
        'errors' => []
    ];
}

/**
 * Call the Python API with the processed data
 * 
 * @param array $processedData The processed data ready for API
 * @return array API response or error information
 */
function callPredictionAPI($processedData) {
    // API endpoint URL
    $apiUrl = 'http://localhost:5000/predict';
    
    // Initialize cURL session
    $ch = curl_init($apiUrl);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($processedData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    // Execute cURL request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Check for errors
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return [
            'success' => false,
            'error' => 'API request failed: ' . $error
        ];
    }
    
    // Close cURL session
    curl_close($ch);
    
    // Process response
    if ($httpCode == 200) {
        $result = json_decode($response, true);
        return [
            'success' => true,
            'result' => $result
        ];
    } else {
        return [
            'success' => false,
            'error' => 'API request failed with status code: ' . $httpCode,
            'response' => $response
        ];
    }
}

/**
 * Process form data and call prediction API
 * 
 * @param array $formData The raw form data from POST
 * @return array Complete result with prediction or error information
 */
function processAndPredict($formData) {
    // Process the form data
    $processResult = processFormDataForAPI($formData);
    
    // If processing failed, return the result
    if (!$processResult['isValid']) {
        return [
            'success' => false,
            'errors' => $processResult['errors']
        ];
    }
    
    // Call the prediction API
    $apiResult = callPredictionAPI($processResult['data']);
    
    // Return the complete result
    return $apiResult;
}