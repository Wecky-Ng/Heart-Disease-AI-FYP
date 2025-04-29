<?php
// Define PROJECT_ROOT - It's recommended to define this in a central config file
// This fallback is included in case this file is accessed directly for testing
// or if a central definition is missing.
if (!defined('PROJECT_ROOT')) {
    // Define PROJECT_ROOT as the directory containing this file (history.php)
    define('PROJECT_ROOT', __DIR__);
}

// Include session management
require_once PROJECT_ROOT . '/session.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get current user data from session
$userData = getCurrentUser();

// Ensure user data is available
if (!$userData || !isset($userData['user_id'])) {
    // Handle case where user data is not found in session (e.g., session expired)
    // Redirect to login or show an error message
    header('Location: login.php'); // Redirect to login as user data is essential
    exit();
}

// Include database connection and functions
// Ensure connection.php establishes the $db connection
require_once PROJECT_ROOT . '/database/connection.php';
// Ensure get_user_prediction_history.php contains the getUserPredictionHistory function
require_once PROJECT_ROOT . '/database/get_user_prediction_history.php';
// Include delete history functions
require_once PROJECT_ROOT . '/database/delete_history.php';

// Process delete actions if submitted
$success_message = "";
$error_message = "";

// Handle delete single record
if (isset($_POST['delete_record']) && isset($_POST['record_id'])) {
    $recordId = intval($_POST['record_id']);
    $userId = $userData['user_id'];
    $db = getDbConnection(); // Get DB connection
    if ($db && deletePredictionRecord($db, $recordId, $userId)) { // Pass $db
        $success_message = "Record deleted successfully.";
    } else {
        $error_message = "Failed to delete record. Please try again.";
    }
}

// Handle delete all records
if (isset($_POST['delete_all_records'])) {
    $userId = $userData['user_id'];
    $db = getDbConnection(); // Get DB connection
    if ($db && deleteAllPredictionRecords($db, $userId)) { // Pass $db
        $success_message = "All records deleted successfully.";
    } else {
        $error_message = "Failed to delete records. Please try again.";
    }
}

// Fetch the user's prediction history from the database
// Make sure getUserPredictionHistory handles database connection and queries securely
$predictionHistory = getUserPredictionHistory($userData['user_id']);

// Check if fetching history was successful (getUserPredictionHistory should return an array or empty array on success, or false/null on error)
if ($predictionHistory === false) {
    // Handle database error when fetching history
    $error_message = "Error fetching prediction history. Please try again later.";
    $predictionHistory = []; // Ensure $predictionHistory is an empty array to prevent errors in the loop
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

</head>

<body class="nk-body bg-lighter">
    <div class="nk-app-root">
        <?php include PROJECT_ROOT . '/sidemenu.php'; ?>

        <div class="nk-main">
            <?php include PROJECT_ROOT . '/header.php'; ?>

            <div class="nk-content">
                <div class="container-fluid">
                    <div class="nk-content-inner">
                        <div class="nk-content-body history-content-body">
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
                                                <a href="user_input_form.php" class="btn btn-primary"><span>New Prediction</span></a>
                                                <?php if (!empty($predictionHistory)): ?>
                                                <form method="post" action="" class="d-inline ml-2" id="delete-all-form">
                                                    <input type="hidden" name="delete_all_records" value="1">
                                                    <button type="button" class="btn btn-danger" onclick="confirmDeleteAll()"><span>Delete All</span></button>
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
                                            <table class="table table-hover datatable-init-export">
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
                                                    <?php foreach ($predictionHistory as $index => $prediction): ?>
                                                        <tr>
                                                            <td><?php echo $index + 1; ?></td>
                                                            <td><?php echo htmlspecialchars($prediction['date']); ?></td>
                                                            <td>
                                                                <?php
                                                                    // The result is already formatted in getUserPredictionHistory function
                                                                    $result_text = $prediction['result'];
                                                                    $badge_class = (strpos($result_text, 'High') !== false) ? 'badge-danger' : 'badge-success';
                                                                ?>
                                                                <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($result_text); ?></span>
                                                            </td>
                                                            <td>
                                                                <?php echo htmlspecialchars($prediction['probability']); ?>
                                                            </td>
                                                            <td>
                                                                <?php echo htmlspecialchars($prediction['details']); ?>
                                                            </td>
                                                            <td>
                                                                <a href="result.php?id=<?php echo htmlspecialchars($prediction['id']); ?>" class="btn btn-sm btn-primary">View</a>
                                                                <form method="post" action="" class="d-inline ml-1">
                                                                    <input type="hidden" name="record_id" value="<?php echo htmlspecialchars($prediction['id']); ?>">
                                                                    <input type="hidden" name="delete_record" value="1">
                                                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo htmlspecialchars($prediction['id']); ?>)">Delete</button>
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

            <?php include PROJECT_ROOT . '/footer.php'; ?>
        </div>
    </div>

    <?php include PROJECT_ROOT . '/includes/scripts.php'; ?>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Display SweetAlert2 messages
        <?php if (!empty($success_message)): ?>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '<?php echo addslashes(htmlspecialchars($success_message)); ?>',
            timer: 2000,
            showConfirmButton: false
        });
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo addslashes(htmlspecialchars($error_message)); ?>'
        });
        <?php endif; ?>

        // Confirmation dialogs
        function confirmDelete(recordId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create a form dynamically and submit it
                    const form = document.createElement('form');
                    form.method = 'post';
                    form.action = ''; // Submit to the same page

                    const recordIdInput = document.createElement('input');
                    recordIdInput.type = 'hidden';
                    recordIdInput.name = 'record_id';
                    recordIdInput.value = recordId;
                    form.appendChild(recordIdInput);

                    const deleteInput = document.createElement('input');
                    deleteInput.type = 'hidden';
                    deleteInput.name = 'delete_record';
                    deleteInput.value = '1';
                    form.appendChild(deleteInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function confirmDeleteAll() {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete all your prediction records permanently!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete all!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-all-form').submit();
                }
            });
        }
        
        var dataTableInitialized = false; // Flag to track initialization
        $(document).ready(function() {
            // Initialize DataTable with export options, only if not already initialized
            if ($('.datatable-init-export').length && !dataTableInitialized) {
                $('.datatable-init-export').DataTable({
                    // destroy: true, // Using flag instead
                    responsive: true,
                    initComplete: function(settings, json) {
                        dataTableInitialized = true; // Set flag after successful initialization
                    },
                    language: {
                        search: "",
                        searchPlaceholder: "Search Predictions"
                    }
                });
            }
        });
    </script>
</body>

</html>
