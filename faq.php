<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'database/connection.php';

// Fetch FAQs from database
function getFaqs() {
    try {
        $db = getDbConnection();
        $result = $db->query("SELECT id, faq_title, detail FROM faq ORDER BY id ASC");
        
        if (!$result) {
            throw new Exception("Query failed: " . $db->error);
        }
        
        $faqs = [];
        while ($row = $result->fetch_assoc()) {
            $faqs[] = $row;
        }
        
        $result->free();
        return $faqs;
    } catch (Exception $e) {
        // Log error and return empty array
        error_log('Database Error: ' . $e->getMessage());
        return [];
    }
}

// Get all FAQs
$faqs = getFaqs();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Heart Disease Prediction using AI - Frequently Asked Questions">
    <title>FAQ - Heart Disease Prediction</title>
    <!-- Include common stylesheets -->
    <?php include 'includes/styles.php'; ?>
    <style>
        .faq-card {
            transition: all 0.3s ease;
            height: 100%;
        }
        .faq-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(43, 84, 180, 0.15);
        }
        .faq-title {
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .faq-detail {
            color: #526484;
        }
    </style>
</head>
<body class="nk-body bg-lighter">
    <div class="nk-app-root">
        <!-- Include the side menu component -->
        <?php include 'sidemenu.php'; ?>
        
        <div class="nk-main">
            <!-- Include the header component -->
            <?php include 'header.php'; ?>
            
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
    <?php include 'includes/scripts.php'; ?>
    <script src="js/overlay-fix.js"></script>
</body>
</html>