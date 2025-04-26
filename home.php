<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Heart Disease Prediction using AI">
    <title>Heart Disease Prediction - Dashboard</title>
    <?php
    // Define PROJECT_ROOT if it hasn't been defined (for local development when not routed through api/index.php)
    if (!defined('PROJECT_ROOT')) {
        // Assuming home.php is at the project root
        define('PROJECT_ROOT', __DIR__);
        // If home.php is in a subdirectory, adjust __DIR__ accordingly, e.g., dirname(__DIR__)
    }
    require_once PROJECT_ROOT . '/includes/styles.php';
    ?>
</head>

<body class="nk-body bg-lighter">
    <div class="nk-app-root">
        <?php require_once PROJECT_ROOT . '/sidemenu.php'; ?>

        <div class="nk-sidebar-overlay" data-target="sidebarMenu"></div>

        <div class="nk-main">
            <?php require_once PROJECT_ROOT . '/header.php'; ?>

            <div class="nk-wrap">
                <div class="nk-content">
                    <div class="container-fluid">
                        <div class="nk-content-inner">
                            <div class="nk-content-body">
                                <div class="nk-block-head nk-block-head-sm">
                                    <div class="nk-block-between">
                                        <div class="nk-block-head-content">
                                            <h3 class="nk-block-title page-title">Heart Disease Prediction Dashboard</h3>
                                            <div class="nk-block-des text-soft">
                                                <p>Welcome to the Heart Disease Prediction AI System.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="nk-block">
                                    <div class="row g-gs">
                                        <div class="col-lg-6">
                                            <div class="card card-bordered h-100">
                                                <div class="card-inner">
                                                    <div class="card-head">
                                                        <h5 class="card-title">Heart Disease Prediction</h5>
                                                    </div>
                                                    <p>Use our AI-powered tool to predict your risk of heart disease based on your health parameters.</p>
                                                    <a href="user_input_form.php" class="btn btn-primary">Start Prediction</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="card card-bordered h-100">
                                                <div class="card-inner">
                                                    <div class="card-head">
                                                        <h5 class="card-title">Health Information</h5>
                                                    </div>
                                                    <p>Learn about heart disease risk factors and prevention strategies.</p>
                                                    <a href="#" class="btn btn-outline-primary">Learn More</a>
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
        </div>
    </div>

    <?php require_once PROJECT_ROOT . '/includes/scripts.php'; ?>
</body>

</html>