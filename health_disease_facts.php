<?php
// Include necessary files
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', __DIR__);
}
require_once PROJECT_ROOT . '/session.php';
require_once PROJECT_ROOT . '/includes/styles.php';
require_once PROJECT_ROOT . '/database/get_health_recomend.php';

// Get category from URL parameter (default to 1 if not specified)
$category = isset($_GET['category']) ? (int)$_GET['category'] : 1;

// Validate category (must be 1, 2, or 3)
if ($category < 1 || $category > 3) {
    $category = 1; // Default to Heart Disease Facts if invalid
}

// Get health information for the selected category
$healthInfo = getHealthInformationByCategory($category);

// Category titles
$categoryTitles = [
    1 => 'Heart Disease Facts',
    2 => 'Prevention Tips',
    3 => 'Treatment Options'
];

// Current category title
$currentCategoryTitle = $categoryTitles[$category];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Heart Disease Information and Facts">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <title>Heart Disease - <?php echo htmlspecialchars($currentCategoryTitle); ?></title>
</head>

<body class="nk-body bg-lighter">
    <div class="nk-app-root">
        <!-- sidebar @s -->
        <?php require_once PROJECT_ROOT . '/sidemenu.php'; ?>
        <!-- sidebar @e -->
        
        <div class="nk-sidebar-overlay" data-target="sidebarMenu"></div>
        
        <!-- main @s -->
        <div class="nk-main">
            <!-- main header @s -->
            <?php include PROJECT_ROOT . '/header.php'; ?>
            <!-- main header @e -->

            <!-- wrap @s -->
            <div class="nk-wrap">

            <!-- content @s -->
            <div class="nk-content">
                <div class="container-fluid">
                    <div class="nk-content-inner">
                        <div class="nk-content-body">
                            <div class="nk-block-head nk-block-head-sm">
                                <div class="nk-block-between">
                                    <div class="nk-block-head-content">
                                        <h3 class="nk-block-title page-title"><?php echo htmlspecialchars($currentCategoryTitle); ?></h3>
                                    </div>
                                    <div class="nk-block-head-content">
                                        <div class="toggle-wrap nk-block-tools-toggle">
                                            <div class="toggle-expand-content">
                                                <ul class="nk-block-tools g-3">
                                                    <li>
                                                        <div class="dropdown">
                                                            <a href="#" class="dropdown-toggle btn btn-white btn-dim btn-outline-light" data-toggle="dropdown"><span>Category</span><em class="icon ni ni-chevron-down"></em></a>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <ul class="link-list-opt no-bdr">
                                                                    <li><a href="health_disease_facts.php?category=1"><span>Heart Disease Facts</span></a></li>
                                                                    <li><a href="health_disease_facts.php?category=2"><span>Prevention Tips</span></a></li>
                                                                    <li><a href="health_disease_facts.php?category=3"><span>Treatment Options</span></a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="nk-block">
                                <?php if (empty($healthInfo)): ?>
                                <div class="card card-bordered">
                                    <div class="card-inner">
                                        <div class="nk-block-head">
                                            <div class="nk-block-head-content text-center">
                                                <h5 class="nk-block-title">No information available</h5>
                                                <div class="nk-block-des">
                                                    <p>There is currently no health information available for this category.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php else: ?>
                                    <?php foreach ($healthInfo as $info): ?>
                                    <div class="card card-bordered mb-3">
                                        <div class="card-inner">
                                            <div class="nk-block-head">
                                                <div class="nk-block-head-content">
                                                    <h5 class="nk-block-title"><?php echo htmlspecialchars($info['title']); ?></h5>
                                                    <div class="nk-block-des">
                                                        <p><?php echo nl2br(htmlspecialchars($info['detail'])); ?></p>
                                                    </div>
                                                </div>
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
            <!-- content @e -->

            <!-- footer @s -->
            <?php include 'footer.php'; ?>
            <!-- footer @e -->
        </div>
        <!-- wrap @e -->
    </div>
    <!-- main @e -->
</div>
</body>

<?php require_once PROJECT_ROOT . '/includes/scripts.php'; ?>