<?php
// Define PROJECT_ROOT if it hasn't been defined (for local development when not routed through api/index.php)
if (!defined('PROJECT_ROOT')) {
    // Assuming history.php is at the project root
    define('PROJECT_ROOT', __DIR__);
    // If history.php is in a subdirectory, adjust __DIR__ accordingly, e.g., dirname(__DIR__)
}

// Include necessary files FIRST, especially session.php
require_once PROJECT_ROOT . '/session.php'; // Start session and provide isLoggedIn()

// --- Redirect if not logged in ---
if (!isLoggedIn()) {
    header('Location: home.php'); // Redirect to home page
    exit(); // Stop script execution
}
// --- End Redirect ---

require_once PROJECT_ROOT . '/database/get_user_prediction_history.php'; // Includes getUserPredictionHistory and connection.php

// Get current user ID
$userId = $_SESSION['user_id'] ?? null; // Already checked login status above

// Fetch prediction history for the logged-in user
$predictionHistory = [];
if ($userId) {
    $predictionHistory = getUserPredictionHistory($userId);
    // Note: getUserPredictionHistory closes the DB connection.
    // If you need the connection open later, you might need to adjust that function.
}

// Helper function to determine badge class based on risk level
function getRiskBadgeClass($riskLevel) {
    switch ($riskLevel) {
        case 'High Risk':
            return 'badge-danger';
        case 'Medium Risk': // If you implement medium risk
            return 'badge-warning';
        case 'Low Risk':
        default:
            return 'badge-success';
    }
}

// Helper function to get text representation of parameters (copied from result.php for consistency)
function getParameterText($key, $value) {
    // Handle null or empty values gracefully
    if ($value === null || $value === '') {
        return 'N/A';
    }

    switch ($key) {
        case 'smoking':
        case 'alcohol_drinking':
        case 'stroke':
        case 'diff_walking':
        case 'physical_activity':
        case 'asthma':
        case 'kidney_disease':
        case 'skin_cancer':
            // Ensure value is treated as integer for comparison
            return ((int)$value == 1) ? 'Yes' : 'No';
        case 'sex':
             // Ensure value is treated as integer for comparison
            return ((int)$value == 1) ? 'Male' : 'Female';
        case 'race':
            $races = ['White', 'Black', 'Asian', 'Hispanic', 'American Indian/Alaskan Native', 'Other'];
            // Ensure value is treated as integer for array index
            return $races[(int)$value] ?? 'Unknown';
        case 'diabetic':
            $diabeticStatus = ['No', 'Yes', 'No, borderline diabetes', 'Yes (during pregnancy)'];
             // Ensure value is treated as integer for array index
            return $diabeticStatus[(int)$value] ?? 'Unknown';
        case 'gen_health':
            $healthStatus = ['Excellent', 'Very good', 'Good', 'Fair', 'Poor'];
             // Ensure value is treated as integer for array index
            return $healthStatus[(int)$value] ?? 'Unknown';
        case 'physical_health':
        case 'mental_health':
            // Ensure value is treated as float for display
            return (float)$value . ' days';
        case 'sleep_time':
             // Ensure value is treated as float for display
            return (float)$value . ' hours';
        case 'bmi':
             // Ensure value is treated as float for display
             return (float)$value; // Return numerical value directly
        case 'age':
             // Ensure value is treated as integer for display
             return (int)$value; // Return numerical value directly as integer
        case 'prediction_result': // Although not typically displayed in params table, handle just in case
             // Ensure value is treated as integer for comparison
             return ((int)$value == 1) ? 'Heart Disease' : 'No Heart Disease';
        case 'prediction_confidence':
             // Ensure value is treated as float for display
             return round((float)$value * 100, 2) . '%';
        default:
            return htmlspecialchars($value); // Default fallback
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Heart Disease Prediction History">
    <title>Prediction History - Heart Disease Prediction</title>
    <?php include PROJECT_ROOT . '/includes/styles.php'; ?>
     <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
     <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
</head>

<body class="nk-body bg-lighter">
    <div class="nk-app-root">
        <?php include PROJECT_ROOT . '/sidemenu.php'; ?>

        <div class="nk-sidebar-overlay" data-target="sidebarMenu"></div>

        <div class="nk-main">
            <?php include PROJECT_ROOT . '/header.php'; ?>

            <div class="nk-wrap">
                <div class="nk-content">
                    <div class="container-fluid">
                        <div class="nk-content-inner">
                            <div class="nk-content-body">
                                <div class="nk-block-head nk-block-head-sm">
                                    <div class="nk-block-between">
                                        <div class="nk-block-head-content">
                                            <h3 class="nk-block-title page-title">Prediction History</h3>
                                            <div class="nk-block-des text-soft">
                                                <p>View your past heart disease prediction results.</p>
                                            </div>
                                        </div>
                                        <div class="nk-block-head-content">
                                            <a href="user_input_form.php" class="btn btn-primary d-none d-sm-inline-flex"><em class="icon ni ni-plus"></em><span>New Prediction</span></a>
                                            <a href="user_input_form.php" class="btn btn-icon btn-primary d-inline-flex d-sm-none"><em class="icon ni ni-plus"></em></a>
                                        </div>
                                    </div>
                                </div><div class="nk-block">
                                    <div class="card card-bordered">
                                        <div class="card-inner">
                                            <?php if ($predictionHistory): ?>
                                                <table class="datatable-init nk-tb-list nk-tb-ulist display responsive nowrap" id="predictionHistoryTable" style="width:100%">
                                                    <thead>
                                                        <tr class="nk-tb-item nk-tb-head">
                                                            <th class="nk-tb-col"><span>Date</span></th>
                                                            <th class="nk-tb-col tb-col-mb"><span>Risk Level</span></th>
                                                            <th class="nk-tb-col tb-col-md"><span>Probability</span></th>
                                                            <th class="nk-tb-col"><span>Age</span></th>
                                                            <th class="nk-tb-col"><span>Sex</span></th>
                                                            <th class="nk-tb-col"><span>BMI</span></th>
                                                            <th class="nk-tb-col tb-col-md"><span>Smoking</span></th>
                                                            <th class="nk-tb-col tb-col-md"><span>Alcohol</span></th>
                                                            <th class="nk-tb-col tb-col-md"><span>Stroke</span></th>
                                                            <th class="nk-tb-col tb-col-lg"><span>Diabetic</span></th>
                                                            <th class="nk-tb-col nk-tb-col-tools text-right no-sort">
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($predictionHistory as $record): ?>
                                                            <tr class="nk-tb-item">
                                                                <td class="nk-tb-col"><?php echo htmlspecialchars($record['date']); ?></td>
                                                                <td class="nk-tb-col tb-col-mb">
                                                                    <span class="badge badge-dot <?php echo getRiskBadgeClass($record['result']); ?>"><?php echo htmlspecialchars($record['result']); ?></span>
                                                                </td>
                                                                <td class="nk-tb-col tb-col-md"><?php echo htmlspecialchars($record['probability']); ?></td>
                                                                <td class="nk-tb-col"><?php echo htmlspecialchars(getParameterText('age', $record['raw_data']['age'])); ?></td>
                                                                <td class="nk-tb-col"><?php echo htmlspecialchars(getParameterText('sex', $record['raw_data']['sex'])); ?></td>
                                                                <td class="nk-tb-col"><?php echo htmlspecialchars(getParameterText('bmi', $record['raw_data']['bmi'])); ?></td>
                                                                <td class="nk-tb-col tb-col-md"><?php echo htmlspecialchars(getParameterText('smoking', $record['raw_data']['smoking'])); ?></td>
                                                                <td class="nk-tb-col tb-col-md"><?php echo htmlspecialchars(getParameterText('alcohol_drinking', $record['raw_data']['alcohol_drinking'])); ?></td>
                                                                <td class="nk-tb-col tb-col-md"><?php echo htmlspecialchars(getParameterText('stroke', $record['raw_data']['stroke'])); ?></td>
                                                                <td class="nk-tb-col tb-col-lg"><?php echo htmlspecialchars(getParameterText('diabetic', $record['raw_data']['diabetic'])); ?></td>
                                                                <td class="nk-tb-col nk-tb-col-tools">
                                                                    <ul class="nk-tb-actions gx-1">
                                                                        <li>
                                                                            <a href="result.php?id=<?php echo htmlspecialchars($record['id']); ?>" class="btn btn-trigger btn-icon" data-toggle="tooltip" data-placement="top" title="View Details">
                                                                                <em class="icon ni ni-eye"></em>
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            <?php else: ?>
                                                <div class="alert alert-info" role="alert">
                                                    You have no prediction history yet. Make your first prediction!
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include PROJECT_ROOT . '/footer.php'; ?>
            </div>
        </div>
    <?php include PROJECT_ROOT . '/includes/scripts.php'; ?>
     <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
     <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

    <script>
        $(document).ready(function() {
            // Check if the DataTable is already initialized on the table
            if (!$.fn.DataTable.isDataTable('#predictionHistoryTable')) {
                $('#predictionHistoryTable').DataTable({
                    "order": [[ 0, "desc" ]], // Order by the first column (Date) descending
                     "responsive": true // Enable responsive features
                });
            }
        });
    </script>
</body>

</html>
