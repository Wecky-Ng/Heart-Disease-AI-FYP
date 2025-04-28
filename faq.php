<?php
if (!defined('PROJECT_ROOT')) {
    // Assuming home.php is at the project root
    define('PROJECT_ROOT', __DIR__);
    // If home.php is in a subdirectory, adjust __DIR__ accordingly, e.g., dirname(__DIR__)
}
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include FAQ data retrieval function
require_once PROJECT_ROOT . '/database/get_faq.php';

// Get all FAQs
$faqs = getFaqs();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Heart Disease Prediction using AI - Frequently Asked Questions">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <title>FAQ - Heart Disease Prediction</title>
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
            
            <div class="nk-wrap">
                <div class="nk-content">
                    <div class="container-fluid">
                        <div class="nk-content-inner">
                            <div class="nk-content-body">
                                <div class="nk-block-head nk-block-head-sm">
                                    <div class="nk-block-between">
                                        <div class="nk-block-head-content">
                                            <h3 class="nk-block-title page-title">Frequently Asked Questions</h3>
                                            <div class="nk-block-des text-soft">
                                                <p>Find answers to common questions about heart disease and our prediction system.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="nk-block">
                                    <div class="row g-gs">
                                        <?php if (empty($faqs)): ?>
                                            <div class="col-12">
                                                <div class="card card-bordered">
                                                    <div class="card-inner">
                                                        <p class="text-center">No FAQs available at the moment. Please check back later.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($faqs as $faq): ?>
                                                <div class="col-lg-6">
                                                    <div class="card card-bordered faq-card">
                                                        <div class="card-inner">
                                                            <div class="card-head">
                                                                <h5 class="card-title faq-title"><?php echo htmlspecialchars($faq['faq_title']); ?></h5>
                                                            </div>
                                                            <p class="faq-detail"><?php echo htmlspecialchars($faq['detail']); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Include common JavaScript -->
    <?php include PROJECT_ROOT . '/includes/scripts.php'; ?>
    <script src="js/overlay-fix.js"></script>
</body>
</html>