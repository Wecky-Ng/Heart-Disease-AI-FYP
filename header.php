<?php
// Include session management
require_once 'session.php';
?>
<!-- Header for Heart Disease Prediction Dashboard -->
<div class="nk-header nk-header-fixed is-light">
    <div class="container-fluid">
        <div class="nk-header-wrap">
            <div class="nk-menu-trigger d-xl-none ml-n1">
                <a href="#" class="nk-nav-toggle nk-quick-nav-icon" data-target="sidebarMenu"><em class="icon ni ni-menu"></em></a>
            </div>
            <div class="nk-header-brand d-xl-none">
                <a href="index.html" class="logo-link">
                    <span class="logo-light logo-img">Heart Disease AI</span>
                    <span class="logo-dark logo-img">Heart Disease AI</span>
                </a>
            </div>
            <div class="nk-header-search ml-3 ml-xl-0">
                <form action="#" class="form-inline">
                    <div class="form-wrap" style="width: 300px;">
                        <div class="form-icon form-icon-left" style="left: 10px;">
                            <em class="icon ni ni-search"></em>
                        </div>
                        <input type="text" class="form-control form-control-sm" placeholder="Search for health information" style="padding-left: 40px;">
                    </div>
                </form>
            </div>
            <div class="nk-header-tools">
                <ul class="nk-quick-nav">
                    <li class="dropdown user-dropdown pe-4">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <div class="user-toggle">
                                <div class="user-avatar sm">
                                    <em class="icon ni ni-user-alt"></em>
                                </div>
                                <div class="user-info d-none d-md-block">
                                    <div class="user-status"><?php echo $_SESSION['user_role'] ?? 'Guest'; ?></div>
                                    <div class="user-name dropdown-indicator"><?php echo $_SESSION['username'] ?? 'Guest User'; ?></div>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-md dropdown-menu-right dropdown-menu-s1" style="right: 0; left: auto;">
                            <div class="dropdown-inner user-card-wrap bg-lighter d-none d-md-block">
                                <div class="user-card">
                                    <div class="user-avatar">
                                        <em class="icon ni ni-user-alt"></em>
                                    </div>
                                    <div class="user-info">
                                        <span class="lead-text"><?php echo $_SESSION['username'] ?? 'Guest User'; ?></span>
                                        <span class="sub-text"><?php echo $_SESSION['email'] ?? 'guest@example.com'; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-inner">
                                <ul class="link-list">
                                    <?php if(isLoggedIn()): ?>
                                        <li><a href="profile.php"><em class="icon ni ni-user-alt"></em><span>View Profile</span></a></li>
                                        <li><a href="history.php"><em class="icon ni ni-activity-alt"></em><span>Prediction History</span></a></li>
                                        <li><a href="logout.php"><em class="icon ni ni-signout"></em><span>Sign out</span></a></li>
                                    <?php else: ?>
                                        <li><a href="login.php"><em class="icon ni ni-signin"></em><span>Login</span></a></li>
                                        <li><a href="register.php"><em class="icon ni ni-user-add"></em><span>Register</span></a></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </li>
                    <!-- <li class="dropdown notification-dropdown mr-n1">
                        <a href="#" class="dropdown-toggle nk-quick-nav-icon" data-toggle="dropdown">
                            <div class="icon-status icon-status-info"><em class="icon ni ni-bell"></em></div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-xl dropdown-menu-right dropdown-menu-s1">
                            <div class="dropdown-head">
                                <span class="sub-title nk-dropdown-title">Notifications</span>
                            </div>
                            <div class="dropdown-body">
                                <div class="nk-notification">
                                    <div class="nk-notification-item dropdown-inner">
                                        <div class="nk-notification-icon">
                                            <em class="icon icon-circle bg-warning-dim ni ni-curve-down-right"></em>
                                        </div>
                                        <div class="nk-notification-content">
                                            <div class="nk-notification-text">Welcome to Heart Disease Prediction AI</div>
                                            <div class="nk-notification-time">Just now</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-foot center">
                                <a href="#">View All</a>
                            </div>
                        </div>
                    </li> -->
                </ul>
            </div>
        </div>
    </div>
</div>