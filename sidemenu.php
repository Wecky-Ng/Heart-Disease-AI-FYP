<?php
// Include session management
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', __DIR__);
}
require_once PROJECT_ROOT . '/session.php';
?>
<!-- Sidebar Menu for Heart Disease Prediction Dashboard -->
<div class="nk-sidebar nk-sidebar-fixed is-light" data-content="sidebarMenu">
    <div class="nk-sidebar-element nk-sidebar-head">
        <div class="nk-sidebar-brand">
            <a href="home.php" class="logo-link nk-sidebar-logo">
                <span class="logo-text">Heart Disease AI</span>
            </a>
        </div>
        <div class="nk-menu-trigger mr-n2">
            <!-- <a href="#" class="nk-nav-toggle nk-quick-nav-icon d-xl-none" data-target="sidebarMenu"><em class="icon ni ni-arrow-left"></em></a> -->
        </div>
    </div>
    <div class="nk-sidebar-element">
        <div class="nk-sidebar-content p-3">
            <div class="nk-sidebar-menu" data-simplebar>
                <ul class="nk-menu">
                    <li class="nk-menu-heading">
                        <h6 class="overline-title text-primary-alt">Dashboard</h6>
                    </li>
                    <li class="nk-menu-item">
                        <a href="home.php" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-dashboard"></em></span>
                            <span class="nk-menu-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nk-menu-heading">
                        <h6 class="overline-title text-primary-alt">Prediction Tools</h6>
                    </li>
                    <li class="nk-menu-item">
                        <a href="user_input_form.php" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-heart"></em></span>
                            <span class="nk-menu-text">Heart Disease Prediction</span>
                        </a>
                    </li>
                    <li class="nk-menu-heading">
                        <h6 class="overline-title text-primary-alt">About</h6>
                    </li>
                    <li class="nk-menu-item">
                        <a href="model_details.php" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-info"></em></span>
                            <span class="nk-menu-text">Model Details</span>
                        </a>
                    </li>
                    <li class="nk-menu-heading">
                        <h6 class="overline-title text-primary-alt">Health Resources</h6>
                    </li>
                    <li class="nk-menu-item has-sub">
                        <a href="#" class="nk-menu-link nk-menu-toggle">
                            <span class="nk-menu-icon"><em class="icon ni ni-file-docs"></em></span>
                            <span class="nk-menu-text">Health Information</span>
                        </a>
                        <ul class="nk-menu-sub">
                            <li class="nk-menu-item">
                                <a href="health_disease_facts.php?category=1" class="nk-menu-link"><span class="nk-menu-text">Heart Disease Facts</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="health_disease_facts.php?category=2" class="nk-menu-link"><span class="nk-menu-text">Prevention Tips</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="health_disease_facts.php?category=3" class="nk-menu-link"><span class="nk-menu-text">Treatment Options</span></a>
                            </li>
                        </ul>
                    </li>
                    <li class="nk-menu-item">
                        <a href="faq.php" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-help-alt"></em></span>
                            <span class="nk-menu-text">FAQ</span>
                        </a>
                    </li>
                    <?php if(isLoggedIn()): ?>
                    <li class="nk-menu-heading">
                        <h6 class="overline-title text-primary-alt">Account</h6>
                    </li>
                    <li class="nk-menu-item">
                        <a href="profile.php" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-user-alt"></em></span>
                            <span class="nk-menu-text">My Profile</span>
                        </a>
                    </li>
                    <li class="nk-menu-item">
                        <a href="history.php" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-history"></em></span>
                            <span class="nk-menu-text">Prediction History</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>