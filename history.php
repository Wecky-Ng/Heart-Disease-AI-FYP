<?php
// Define PROJECT_ROOT if it hasn't been defined (for local development when not routed through api/index.php)
if (!defined('PROJECT_ROOT')) {
    // Assuming home.php is at the project root
    define('PROJECT_ROOT', __DIR__);
    // If home.php is in a subdirectory, adjust __DIR__ accordingly, e.g., dirname(__DIR__)
}
// Include session management
require_once PROJECT_ROOT . '/session.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get current user data
$userData = getCurrentUser();

// Include database connection and functions
require_once PROJECT_ROOT . '/database/db_connect.php';
require_once PROJECT_ROOT . '/database/get_user_prediction_history.php';

// Fetch the user's prediction history from the database
$predictionHistory = getUserPredictionHistory($userData['id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Heart Disease Prediction using AI - Prediction History">
    <title>Prediction History - Heart Disease Prediction</title>
    <!-- Include common stylesheets -->
    <?php include PROJECT_ROOT . '/includes/styles.php'; ?>
    
</head>

<body class="nk-body bg-lighter">
    <div class="nk-app-root">
        <!-- Include the side menu component -->
        <?php include PROJECT_ROOT . '/sidemenu.php'; ?>

        <div class="nk-main">
            <!-- Include the header component -->
            <?php include PROJECT_ROOT . '/header.php'; ?>

            <!-- Main content -->
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
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-inner">
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
                                                                <?php if ($prediction['result'] === 'Low Risk'): ?>
                                                                    <span class="badge badge-success"><?php echo htmlspecialchars($prediction['result']); ?></span>
                                                                <?php elseif ($prediction['result'] === 'Medium Risk'): ?>
                                                                    <span class="badge badge-warning"><?php echo htmlspecialchars($prediction['result']); ?></span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-danger"><?php echo htmlspecialchars($prediction['result']); ?></span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($prediction['probability']); ?></td>
                                                            <td><?php echo htmlspecialchars($prediction['details']); ?></td>
                                                            <td>
                                                                <a href="result.php?id=<?php echo $prediction['id']; ?>" class="btn btn-sm btn-primary"><em class="icon ni ni-eye"></em> View</a>
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

    <!-- Include common JavaScript -->
    <?php include PROJECT_ROOT . '/includes/scripts.php'; ?>
    <script>
        $(document).ready(function() {
            // Initialize DataTable with export options
            if ($('.datatable-init-export').length) {
                $('.datatable-init-export').DataTable({
                    dom: '<"row justify-between g-2"<"col-7 col-sm-6 text-left"f><"col-5 col-sm-6 text-right"B>>tip',
                    buttons: [{
                            extend: 'copy',
                            className: 'btn-sm'
                        },
                        {
                            extend: 'csv',
                            className: 'btn-sm'
                        },
                        {
                            extend: 'excel',
                            className: 'btn-sm'
                        },
                        {
                            extend: 'pdf',
                            className: 'btn-sm'
                        },
                        {
                            extend: 'print',
                            className: 'btn-sm'
                        }
                    ],
                    responsive: true,
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