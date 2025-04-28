<?php
/**
 * Form Validation and Preprocessing for Heart Disease Prediction
 * 
 * This file handles server-side validation and preprocessing of form data
 * based on the heart_2020_cleaned.csv dataset fields.
 */

/**
 * Validates and preprocesses form data based on the expected model inputs.
 * 
 * @param array $formData The raw form data from POST.
 * @return array Validation result with processed data or errors.
 */
function validateAndPreprocessFormData($formData) {
    $result = [
        'isValid' => true,
        'data' => [],
        'errors' => []
    ];

    // Helper function for required integer validation within a range/set
    $validateInt = function($key, $label, $options = []) use ($formData, &$result) {
        if (!isset($formData[$key]) || $formData[$key] === '') {
            $result['errors'][] = "{$label} is required.";
            $result['isValid'] = false;
            return null;
        }
        $value = filter_var($formData[$key], FILTER_VALIDATE_INT);
        if ($value === false) {
            $result['errors'][] = "{$label} must be a whole number.";
            $result['isValid'] = false;
            return null;
        }
        if (isset($options['min']) && $value < $options['min']) {
            $result['errors'][] = "{$label} must be at least {$options['min']}.";
            $result['isValid'] = false;
            return null;
        }
        if (isset($options['max']) && $value > $options['max']) {
            $result['errors'][] = "{$label} must be no more than {$options['max']}.";
            $result['isValid'] = false;
            return null;
        }
        if (isset($options['allowed']) && !in_array($value, $options['allowed'])) {
            $allowedStr = implode(', ', $options['allowed']);
            $result['errors'][] = "{$label} must be one of the following values: {$allowedStr}.";
            $result['isValid'] = false;
            return null;
        }
        $result['data'][$key] = $value;
        return $value;
    };

    // Helper function for required float validation within a range
    $validateFloat = function($key, $label, $options = []) use ($formData, &$result) {
        if (!isset($formData[$key]) || $formData[$key] === '') {
            $result['errors'][] = "{$label} is required.";
            $result['isValid'] = false;
            return null;
        }
        $value = filter_var($formData[$key], FILTER_VALIDATE_FLOAT);
        if ($value === false) {
            $result['errors'][] = "{$label} must be a number.";
            $result['isValid'] = false;
            return null;
        }
        if (isset($options['min']) && $value < $options['min']) {
            $result['errors'][] = "{$label} must be at least {$options['min']}.";
            $result['isValid'] = false;
            return null;
        }
        if (isset($options['max']) && $value > $options['max']) {
            $result['errors'][] = "{$label} must be no more than {$options['max']}.";
            $result['isValid'] = false;
            return null;
        }
        $result['data'][$key] = $value;
        return $value;
    };

    // Validate BMI (float, e.g., 10-60)
    $validateFloat('bmi', 'BMI', ['min' => 10, 'max' => 60]);

    // Validate Smoking (int, 0 or 1)
    $validateInt('smoking', 'Smoking Status', ['allowed' => [0, 1]]);

    // Validate Alcohol Drinking (int, 0 or 1)
    $validateInt('alcohol_drinking', 'Alcohol Drinking Status', ['allowed' => [0, 1]]);

    // Validate Stroke (int, 0 or 1)
    $validateInt('stroke', 'Stroke History', ['allowed' => [0, 1]]);

    // Validate Physical Health (int, days 0-30)
    $validateInt('physical_health', 'Physical Health (days)', ['min' => 0, 'max' => 30]);

    // Validate Mental Health (int, days 0-30)
    $validateInt('mental_health', 'Mental Health (days)', ['min' => 0, 'max' => 30]);

    // Validate Difficulty Walking (int, 0 or 1)
    $validateInt('diff_walking', 'Difficulty Walking', ['allowed' => [0, 1]]);

    // Validate Sex (int, 0=Female, 1=Male)
    $validateInt('sex', 'Sex', ['allowed' => [0, 1]]);

    // Validate Age (int, e.g., 18-100) - Adjust range as needed
    $validateInt('age', 'Age', ['min' => 18, 'max' => 100]);

    // Validate Race (int, 0-5)
    $validateInt('race', 'Race', ['allowed' => [0, 1, 2, 3, 4, 5]]);

    // Validate Diabetic (int, 0-3)
    $validateInt('diabetic', 'Diabetic Status', ['allowed' => [0, 1, 2, 3]]);

    // Validate Physical Activity (int, 0 or 1)
    $validateInt('physical_activity', 'Physical Activity', ['allowed' => [0, 1]]);

    // Validate General Health (int, 0-4)
    $validateInt('gen_health', 'General Health', ['allowed' => [0, 1, 2, 3, 4]]);

    // Validate Sleep Time (float, e.g., 1-24)
    $validateFloat('sleep_time', 'Sleep Time (hours)', ['min' => 1, 'max' => 24]);

    // Validate Asthma (int, 0 or 1)
    $validateInt('asthma', 'Asthma Status', ['allowed' => [0, 1]]);

    // Validate Kidney Disease (int, 0 or 1)
    $validateInt('kidney_disease', 'Kidney Disease Status', ['allowed' => [0, 1]]);

    // Validate Skin Cancer (int, 0 or 1)
    $validateInt('skin_cancer', 'Skin Cancer History', ['allowed' => [0, 1]]);

    // Note: 'save_history' checkbox doesn't need validation here, it's checked in result.php

    return $result;
}

?>