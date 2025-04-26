<?php
// Include necessary files
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', __DIR__);
}
require_once PROJECT_ROOT . '/header.php';
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

<div class="nk-app-root">
    <!-- main @s -->
    <div class="nk-main">
        <!-- sidebar @s -->
        <?php include 'sidemenu.php'; ?>
        <!-- sidebar @e -->

        <!-- wrap @s -->
        <div class="nk-wrap">
            <!-- main header @s -->
            <div class="nk-header nk-header-fixed is-light">
                <div class="container-fluid">
                    <div class="nk-header-wrap">
                        <div class="nk-header-brand">
                            <a href="home.php" class="logo-link">
                                <span class="logo-text">Heart Disease AI</span>
                            </a>
                        </div>
                        <div class="nk-header-tools">
                            <ul class="nk-quick-nav">
                                <?php if(isLoggedIn()): ?>
                                <li class="dropdown user-dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                        <div class="user-toggle">
                                            <div class="user-avatar sm">
                                                <em class="icon ni ni-user-alt"></em>
                                            </div>
                                            <div class="user-info d-none d-md-block">
                                                <div class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                                            </div>
                                        </div>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-md dropdown-menu-right dropdown-menu-s1">
                                        <div class="dropdown-inner user-card-wrap bg-lighter d-none d-md-block">
                                            <div class="user-card">
                                                <div class="user-avatar">
                                                    <em class="icon ni ni-user-alt"></em>
                                                </div>
                                                <div class="user-info">
                                                    <span class="lead-text"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-inner">
                                            <ul class="link-list">
                                                <li><a href="profile.php"><em class="icon ni ni-user-alt"></em><span>View Profile</span></a></li>
                                                <li><a href="account_setting.php"><em class="icon ni ni-setting-alt"></em><span>Account Setting</span></a></li>
                                            </ul>
                                        </div>
                                        <div class="dropdown-inner">
                                            <ul class="link-list">
                                                <li><a href="logout.php"><em class="icon ni ni-signout"></em><span>Sign out</span></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                                <?php else: ?>
                                <li>
                                    <a href="login.php" class="btn btn-primary"><em class="icon ni ni-signin"></em><span>Sign in</span></a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- main header @e -->

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
                                                        <div class="drodown">
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

<?php require_once PROJECT_ROOT . '/includes/scripts.php'; ?>