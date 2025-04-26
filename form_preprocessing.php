<?php
/**
 * Form Preprocessing for Heart Disease Prediction
 *
 * This file handles the preprocessing of form data to match the format expected by the
 * heart disease prediction model based on heart_2020_cleaned.csv, and prepares it for
 * the Python API call.
 */

// Include necessary files
require_once 'database/form_validation_preprocessing.php'; // Assuming this file exists and is updated

/**
 * Process form data for the heart disease prediction API
 *
 * @param array $formData The raw form data from POST
 * @return array Processed data ready for API call or error information
 */
function processFormDataForAPI($formData) {
    // First validate the form data using existing validation function.
    // You will need to ensure that validateAndPreprocessFormData()
    // is updated to handle the 'age' integer input instead of 'age_category' string,
    // and also validate the integer values for race, diabetic, and gen_health.
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
    // Age - now directly use the integer age input
    $apiData['age'] = isset($data['age']) ? (int)$data['age'] : 50; // Default value if not set

    // BMI
    $apiData['bmi'] = isset($data['bmi']) ? (float)$data['bmi'] : 25.0;

    // Note: The following fields (blood_pressure, cholesterol, heart_rate, blood_sugar, family_history)
    // are present in your form_preprocessing.php but NOT in your user_input_form.php or database schema.
    // I am keeping them here for now but you might need to add them to your form and database
    // or remove them if they are not part of your model's required inputs.
    // Assuming they are required by your Python API based on this file's content.

    // Blood Pressure (systolic) - Assuming this is needed by the API
    $apiData['blood_pressure'] = isset($data['blood_pressure']) ? (float)$data['blood_pressure'] : 120.0;

    // Cholesterol - Assuming this is needed by the API
    // This still uses a switch based on string values, which might need adjustment
    // if your validation/preprocessing now outputs a different format.
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

    // Heart Rate - Assuming this is needed by the API
    $apiData['heart_rate'] = isset($data['heart_rate']) ? (float)$data['heart_rate'] : 75.0;

    // Blood Sugar - Assuming this is needed by the API
    // This logic is based on the 'diabetes' field, which is different from the 'diabetic' field in your form/DB.
    // You might need to align these field names or logic.
    if (isset($data['diabetes']) && $data['diabetes'] == 'Yes') {
        $apiData['blood_sugar'] = 130.0; // Elevated blood sugar for diabetics
    } else {
        $apiData['blood_sugar'] = 90.0; // Normal blood sugar
    }


    // Process categorical features
    // These now use the integer values from the form/database

    // Sex/Gender - now uses 0 or 1
    $apiData['sex'] = isset($data['sex']) ? (int)$data['sex'] : 1; // Default Male (1)

    // Smoking - now uses 0 or 1
    $apiData['smoking'] = isset($data['smoking']) ? (int)$data['smoking'] : 0; // Default No (0)

    // Alcohol Drinking - now uses 0 or 1
    $apiData['alcohol_drinking'] = isset($data['alcohol_drinking']) ? (int)$data['alcohol_drinking'] : 0; // Default No (0)

    // Stroke History - now uses 0 or 1
    $apiData['stroke'] = isset($data['stroke']) ? (int)$data['stroke'] : 0; // Default No (0)

    // Diabetes - Note: This field name ('diabetes') is different from your form/DB ('diabetic').
    // Assuming your API expects 'diabetes' as a string ('Yes'/'No'). You'll need to map the integer 'diabetic' value.
    // Mapping: 0=No, 1=Yes, 2=No, borderline, 3=Yes (during pregnancy)
    // If API expects 'Yes'/'No', you might map 1 and 3 to 'Yes', and 0 and 2 to 'No'.
    $apiData['diabetes'] = (isset($data['diabetic']) && ($data['diabetic'] == 1 || $data['diabetic'] == 3)) ? 'Yes' : 'No'; // Mapping to API expected string

    // Physical Activity - now uses 0 or 1
    $apiData['physical_activity'] = isset($data['physical_activity']) ? (int)$data['physical_activity'] : 0; // Default No (0)

    // General Health - Note: This field name ('general_health') is different from your form/DB ('gen_health').
    // Assuming your API expects 'general_health' as a string ('Excellent', 'Very good', etc.).
    // You'll need to map the integer 'gen_health' value.
    // Mapping: 0=Excellent, 1=Very good, 2=Good, 3=Fair, 4=Poor
     $genHealthMapping = [
        0 => 'Excellent',
        1 => 'Very good',
        2 => 'Good',
        3 => 'Fair',
        4 => 'Poor'
    ];
    $apiData['general_health'] = isset($data['gen_health']) && isset($genHealthMapping[$data['gen_health']]) ? $genHealthMapping[$data['gen_health']] : 'Good'; // Default Good (2)

    // Sleep Time - now uses float
    $apiData['sleep_time'] = isset($data['sleep_time']) ? (float)$data['sleep_time'] : 7.0; // Default 7.0

    // Asthma - now uses 0 or 1
    $apiData['asthma'] = isset($data['asthma']) ? (int)$data['asthma'] : 0; // Default No (0)

    // Kidney Disease - now uses 0 or 1
    $apiData['kidney_disease'] = isset($data['kidney_disease']) ? (int)$data['kidney_disease'] : 0; // Default No (0)

    // Skin Cancer - now uses 0 or 1
    $apiData['skin_cancer'] = isset($data['skin_cancer']) ? (int)$data['skin_cancer'] : 0; // Default No (0)

    // Race/Ethnicity - now uses integer values
     $raceMapping = [
        0 => 'White',
        1 => 'Black',
        2 => 'Asian',
        3 => 'Hispanic',
        4 => 'American Indian/Alaskan Native',
        5 => 'Other'
    ];
    // Assuming your API expects the string value for race
    $apiData['race'] = isset($data['race']) && isset($raceMapping[$data['race']]) ? $raceMapping[$data['race']] : 'White'; // Default White (0)


    // Family History - Assuming this is needed by the API but not in your form/DB.
    // You might need to add this to your form and database or remove it.
    $apiData['family_history'] = isset($data['family_history']) ? $data['family_history'] : 'No'; // Default No

    // Physical Health (days not good) - now uses float
    $apiData['physical_health'] = isset($data['physical_health']) ? (float)$data['physical_health'] : 0.0; // Default 0.0

    // Mental Health (days not good) - now uses float
    $apiData['mental_health'] = isset($data['mental_health']) ? (float)$data['mental_health'] : 0.0; // Default 0.0

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
    // Ensure this URL is correct for your deployed Python API
    $apiUrl = $_ENV['PREDICTION_API_URL'] ?? 'http://localhost:5000/predict'; // Use environment variable for API URL

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
    // Optional: Add a timeout
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout after 30 seconds

    // Execute cURL request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Check for errors
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        error_log("API request failed (cURL error): " . $error); // Log the error
        return [
            'success' => false,
            'error' => 'Prediction service is currently unavailable. Please try again later.' // User-friendly message
        ];
    }

    // Close cURL session
    curl_close($ch);

    // Process response
    if ($httpCode == 200) {
        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
             error_log("API response JSON decode error: " . json_last_error_msg()); // Log JSON error
             return [
                'success' => false,
                'error' => 'Invalid response from prediction service.'
            ];
        }
         // Check if the expected keys exist in the result
        if (!isset($result['prediction']) || !isset($result['confidence'])) {
             error_log("API response missing expected keys: " . print_r($result, true)); // Log missing keys
             return [
                'success' => false,
                'error' => 'Prediction service returned unexpected data.'
            ];
        }
        return [
            'success' => true,
            'result' => $result
        ];
    } else {
        error_log("API request failed with status code: " . $httpCode . " Response: " . $response); // Log the API error
        return [
            'success' => false,
            'error' => 'Prediction service returned an error. Status code: ' . $httpCode,
            'response' => $response // Include response for debugging if needed
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
