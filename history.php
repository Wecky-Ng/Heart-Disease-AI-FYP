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

// Get current user data from session
$userData = getCurrentUser(); // Assuming this function is in session.php

// Ensure user data is available
if (!$userData || !isset($userData['user_id'])) {
    // Handle case where user data is not found in session (e.g., session expired)
    // Redirect to login or show an error message
    header('Location: login.php'); // Redirect to login as user data is essential
    exit();
}


require_once PROJECT_ROOT . '/database/get_user_prediction_history.php'; // Includes getUserPredictionHistory and connection.php
require_once PROJECT_ROOT . '/database/delete_history.php'; // Include delete history functions

// Get current user ID
$userId = $userData['user_id'] ?? null; // Get user ID from session data


// Process delete actions if submitted
$success_message = "";
$error_message = "";

// Handle delete single record
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_record']) && isset($_POST['record_id'])) {
    $recordId = filter_input(INPUT_POST, 'record_id', FILTER_VALIDATE_INT);
    // User ID is already fetched into $userId from session

    if ($recordId && $userId) {
        if (deletePredictionRecord($recordId, $userId)) {
            $success_message = "Record deleted successfully.";
            // Redirect to clear POST data after successful delete
             header('Location: history.php');
             exit();
        } else {
            $error_message = "Failed to delete record. Please try again.";
        }
    } else {
        $error_message = "Invalid delete request or user not logged in.";
    }
}

// Handle delete all records
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_all_records'])) {
    // User ID is already fetched into $userId from session

    if ($userId) {
         if (deleteAllPredictionRecords($userId)) {
            $success_message = "All records deleted successfully.";
            // Redirect to clear POST data after successful delete
             header('Location: history.php');
             exit();
         } else {
            $error_message = "Failed to delete records. Please try again.";
         }
    } else {
         $error_message = "User not logged in.";
    }
}


// Fetch the user's prediction history from the database
$predictionHistory = [];
if ($userId) {
    $predictionHistory = getUserPredictionHistory($userId);
    // Note: getUserPredictionHistory should return an array or empty array on success, or false/null on error
    // We assume getUserPredictionHistory now returns data with keys: id, user_id, raw_data, prediction_result, prediction_confidence, created_at
}

// Check if fetching history was successful (getUserPredictionHistory should return an array or empty array on success, or false/null on error)
if ($predictionHistory === false) {
    // Handle database error when fetching history
    $error_message = "Error fetching prediction history. Please try again later.";
    $predictionHistory = []; // Ensure $predictionHistory is an empty array to prevent errors in the loop
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
            // Ensure the value is not null before passing to htmlspecialchars
            return htmlspecialchars($value ?? 'N/A'); // Default fallback with null check
    }
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Heart Disease Prediction using AI - Prediction History">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <title>Prediction History - Heart Disease Prediction</title>
    <?php include PROJECT_ROOT . '/includes/styles.php'; ?>
     <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
     <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
     <style>
        /* Optional: Add some basic styling to wrap text in the details column */
        #predictionHistoryTable td:nth-child(5) { /* Target the fifth column (Details) */
            white-space: normal; /* Allow text wrapping */
            word-break: break-word; /* Break long words */
        }
     </style>

</head>

<body class="nk-body bg-lighter">
    <div class="nk-app-root">
        <div class="nk-main ">
            <?php include PROJECT_ROOT . '/sidemenu.php'; ?>
            <div class="nk-sidebar-overlay" data-target="sidebarMenu"></div>
            <?php include PROJECT_ROOT . '/header.php'; ?>

            <div class="nk-wrap ">
                <div class="nk-content ">
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
                                    </div>
                                </div>

                                <div class="nk-block">
                                    <div class="card card-bordered card-stretch">
                                        <div class="card-inner card-inner-bordered">
                                            <div class="card-title-group">
                                                <div class="card-title">
                                                    <h5 class="title">Your Prediction Records</h5>
                                                </div>
                                                <div class="card-tools">
                                                    <a href="user_input_form.php" class="btn btn-primary"><em class="icon ni ni-plus"></em><span>New Prediction</span></a>
                                                    <?php if (!empty($predictionHistory)): ?>
                                                    <form method="post" action="" class="d-inline ml-2" id="delete-all-form">
                                                        <input type="hidden" name="delete_all_records" value="1">
                                                        <button type="button" class="btn btn-danger" onclick="confirmDeleteAll()"><em class="icon ni ni-trash"></em><span>Delete All</span></button>
                                                    </form>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-inner">
                                            <?php if (!empty($success_message)): ?>
                                                <div class="alert alert-success">
                                                    <p><?php echo htmlspecialchars($success_message); ?></p>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($error_message)): ?>
                                                <div class="alert alert-danger">
                                                    <p><?php echo htmlspecialchars($error_message); ?></p>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (empty($predictionHistory)): ?>
                                                <div class="alert alert-info">
                                                    <p>You haven't made any predictions yet. <a href="user_input_form.php">Make your first prediction</a>.</p>
                                                </div>
                                            <?php else: ?>
                                                <table class="table table-hover datatable-init-export display responsive nowrap" id="predictionHistoryTable" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Date</th>
                                                            <th>Result</th>
                                                            <th>Probability</th>
                                                            <th>Details</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($predictionHistory as $index => $record): ?>
                                                            <tr>
                                                                <td><?php echo $index + 1; ?></td>
                                                                <td><?php echo htmlspecialchars($record['created_at'] ?? 'N/A'); ?></td>
                                                                <td>
                                                                    <?php
                                                                        // Check if the key exists and is not null before accessing it
                                                                        $prediction_result = $record['prediction_result'] ?? null;
                                                                        $result_text = 'N/A';
                                                                        $badge_class = 'badge-secondary'; // Default badge

                                                                        // Check if prediction_result is a valid integer (0 or 1)
                                                                        if ($prediction_result !== null && ($prediction_result === 0 || $prediction_result === 1)) {
                                                                             $result_text = ($prediction_result == 1) ? 'High Risk' : 'Low Risk';
                                                                             $badge_class = getRiskBadgeClass($result_text); // Use the helper function
                                                                        }
                                                                    ?>
                                                                    <span class="badge <?php echo htmlspecialchars($result_text); ?>"><?php echo htmlspecialchars($result_text); ?></span>
                                                                </td>
                                                                <td>
                                                                    <?php
                                                                        // Check if the key exists and is a valid number before accessing it
                                                                        $prediction_confidence = $record['prediction_confidence'] ?? null;
                                                                        if ($prediction_confidence !== null && is_numeric($prediction_confidence)) {
                                                                             echo htmlspecialchars(round((float)$prediction_confidence * 100, 2) . '%');
                                                                        } else {
                                                                             echo 'N/A';
                                                                        }
                                                                    ?>
                                                                </td>
                                                                <td>
                                                                    <?php
                                                                        // Construct the details string using getParameterText and raw_data
                                                                        $detailsString = "";
                                                                        // Check if raw_data key exists and is not null
                                                                        $rawData = $record['raw_data'] ?? null;

                                                                        // Decode if it's a string, otherwise use directly if it's an array
                                                                        $processedRawData = null;
                                                                        if (is_string($rawData)) {
                                                                            $processedRawData = json_decode($rawData, true);
                                                                        } elseif (is_array($rawData)) {
                                                                            $processedRawData = $rawData;
                                                                        }


                                                                        if ($processedRawData && is_array($processedRawData)) {
                                                                            $paramsToDisplay = [
                                                                                'age' => 'Age',
                                                                                'sex' => 'Sex',
                                                                                'bmi' => 'BMI',
                                                                                'smoking' => 'Smoking',
                                                                                'alcohol_drinking' => 'Alcohol',
                                                                                'stroke' => 'Stroke',
                                                                                'physical_health' => 'Phys. Health', // Abbreviated for table
                                                                                'mental_health' => 'Ment. Health', // Abbreviated for table
                                                                                'diff_walking' => 'Diff. Walking',
                                                                                'race' => 'Race',
                                                                                'diabetic' => 'Diabetic',
                                                                                'physical_activity' => 'Phys. Activity', // Abbreviated
                                                                                'gen_health' => 'Gen. Health', // Abbreviated
                                                                                'sleep_time' => 'Sleep Time',
                                                                                'asthma' => 'Asthma',
                                                                                'kidney_disease' => 'Kidney Disease',
                                                                                'skin_cancer' => 'Skin Cancer',
                                                                            ];
                                                                            $detailParts = [];
                                                                            foreach ($paramsToDisplay as $key => $label) {
                                                                                // Check if the key exists in the processed raw data before accessing
                                                                                if (isset($processedRawData[$key])) {
                                                                                     // Ensure value is not null before passing to getParameterText
                                                                                     $formattedValue = getParameterText($key, $processedRawData[$key] ?? null);
                                                                                     $detailParts[] = "<strong>" . htmlspecialchars($label) . ":</strong> " . htmlspecialchars($formattedValue);
                                                                                }
                                                                            }
                                                                            $detailsString = implode(", ", $detailParts);
                                                                        } else {
                                                                            $detailsString = "Details N/A";
                                                                        }
                                                                        echo $detailsString;
                                                                    ?>
                                                                </td>
                                                                <td>
                                                                    <a href="result.php?id=<?php echo htmlspecialchars($record['id'] ?? ''); ?>" class="btn btn-sm btn-primary"><em class="icon ni ni-eye"></em> View</a>
                                                                    <form method="post" action="" class="d-inline ml-1">
                                                                        <input type="hidden" name="record_id" value="<?php echo htmlspecialchars($record['id'] ?? ''); ?>">
                                                                        <input type="hidden" name="delete_record" value="1">
                                                                        <button type="button" class="btn btn-sm btn-danger delete-record-btn" data-id="<?php echo htmlspecialchars($record['id'] ?? ''); ?>"><em class="icon ni ni-trash"></em> Delete</button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
     <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.flash.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script>
        $(document).ready(function() {
            // Check if the DataTable is already initialized on the table
            if (!$.fn.DataTable.isDataTable('#predictionHistoryTable')) {
                $('#predictionHistoryTable').DataTable({
                     dom: '<"row justify-between g-2"<"col-7 col-sm-6 text-left"f><"col-5 col-sm-6 text-right"B>>tip',
                     buttons: [
                         {
                             extend: 'copy',
                             text: '<em class="icon ni ni-copy"></em> Copy',
                             className: 'btn btn-outline-secondary btn-sm'
                         },
                         {
                             extend: 'csv',
                             text: '<em class="icon ni ni-file-text"></em> CSV',
                             className: 'btn btn-outline-secondary btn-sm'
                         },
                         {
                             extend: 'excel',
                             text: '<em class="icon ni ni-file-xls"></em> Excel',
                             className: 'btn btn-outline-secondary btn-sm'
                         },
                         {
                             extend: 'pdf',
                             text: '<em class="icon ni ni-file-pdf"></em> PDF',
                             className: 'btn btn-outline-secondary btn-sm'
                         },
                         {
                             extend: 'print',
                             text: '<em class="icon ni ni-printer"></em> Print',
                             className: 'btn btn-outline-secondary btn-sm'
                         }
                     ],
                     responsive: true, // Enable responsive features
                     "order": [[ 1, "desc" ]], // Order by the Date column (index 1) descending
                     "columnDefs": [
                         { "orderable": false, "targets": [0, 4, 5] }, // Disable sorting on #, Details, and Actions columns
                         // Add responsive priority to columns
                         { "responsivePriority": 1, "targets": 0 }, // #
                         { "responsivePriority": 2, "targets": 1 }, // Date
                         { "responsivePriority": 3, "targets": 2 }, // Result
                         { "responsivePriority": 4, "targets": 3 }, // Probability
                         { "responsivePriority": 6, "targets": 4 }, // Details (lower priority)
                         { "responsivePriority": 5, "targets": 5 }  // Actions (higher priority than details)
                     ],
                     language: {
                         search: "",
                         searchPlaceholder: "Search Predictions"
                     }
                });
            }

             // Handle delete single record using SweetAlert2
            $('.datatable-init-export').on('click', '.delete-record-btn', function(e) {
                e.preventDefault();
                const recordId = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Find the form with the matching record_id and submit it
                         // Use closest('form') to find the parent form of the clicked button
                         $(this).closest('form').submit();
                    }
                });
            });

            // Handle delete all records using SweetAlert2
            $('#delete-all-form button').on('click', function(e) {
                 e.preventDefault();
                 Swal.fire({
                    title: 'Are you sure?',
                    text: "This will delete ALL your prediction records and cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete all!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-all-form').submit();
                    }
                });
            });
        });
    </script>
</body>

</html>
