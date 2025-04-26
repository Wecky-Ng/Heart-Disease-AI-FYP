<?php
// Include session management
require_once PROJECT_ROOT . '/session.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get current user data
$userData = getCurrentUser();

// In a real application, you would fetch the user's prediction history from the database
// For demonstration purposes, we'll create some sample data
$predictionHistory = [
    [
        'id' => 1,
        'date' => '2023-11-15',
        'result' => 'Low Risk',
        'probability' => '15%',
        'details' => 'Age: 45, BP: 120/80, Cholesterol: 180'
    ],
    [
        'id' => 2,
        'date' => '2023-11-10',
        'result' => 'Medium Risk',
        'probability' => '45%',
        'details' => 'Age: 45, BP: 130/85, Cholesterol: 210'
    ],
    [
        'id' => 3,
        'date' => '2023-11-05',
        'result' => 'Low Risk',
        'probability' => '20%',
        'details' => 'Age: 45, BP: 125/82, Cholesterol: 190'
    ]
];
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
                                <div class="card">
                                    <div class="card-inner">
                                        <?php if (empty($predictionHistory)): ?>
                                        <div class="alert alert-info">
                                            <p>You haven't made any predictions yet. <a href="user_input_form.php">Make your first prediction</a>.</p>
                                        </div>
                                        <?php else: ?>
                                        <table class="table table-hover">
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
                                                        <a href="result.php?id=<?php echo $prediction['id']; ?>" class="btn btn-sm btn-primary">View Details</a>
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
            
            <!-- Footer -->
            <div class="nk-footer">
                <div class="container-fluid">
                    <div class="nk-footer-wrap">
                        <div class="nk-footer-copyright"> &copy; 2023 Heart Disease AI. All Rights Reserved.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Include common JavaScript -->
    <?php include PROJECT_ROOT . '/includes/scripts.php'; ?>
</body>
</html>