<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title><?= $title ?? 'GraceFull Wedding - Modern Dashboard' ?></title>

    <!-- Essential CSS -->
    <link rel="stylesheet" href="./assets/css/main.style.css">
    <link rel="stylesheet" href="./assets/vendors/mdi/css/materialdesignicons.min.css" />
    <link rel="stylesheet" href="./assets/vendors/ti-icons/css/themify-icons.css" />
    <link rel="stylesheet" href="./assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="./assets/vendors/css/vendor.bundle.base.css" />
    <link rel="stylesheet" href="./assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css" />
    <link rel="stylesheet" href="./assets/css/style.css" />
    <link rel="shortcut icon" href="./assets/images/favicon.png" />
</head>

<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="modern-container">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Include Navbar -->
        <?php include 'navbar.php'; ?>

        <!-- Main Content Area -->
        <div class="main-content">
            <div class="content-wrapper">
                <div class="content-inner">
                    <!-- Page Header -->
                    <div class="page-header">
                        <h1 class="page-title"><?= $pageTitle ?? '' ?></h1>
                        <p class="page-subtitle"><?= $pageSubtitle ?? 'Welcome ' ?></p>

                        <!-- Breadcrumb -->
                        <?php if (isset($breadcrumb)): ?>
                            <nav class="breadcrumb-modern">
                                <?= $breadcrumb ?>
                            </nav>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Actions -->
                    <?php if (isset($quickActions)): ?>
                        <div class="quick-actions">
                            <?= $quickActions ?>
                        </div>
                    <?php endif; ?>

                    <!-- Main Content -->
                    <div class="page-content">
                        <?= $content ?? '<div class="modern-card"><p style="color: #ff4757; font-weight: 600;"><i class="mdi mdi-alert-circle"></i> Konten kosong! Silakan tambahkan konten pada variabel $content.</p></div>' ?>
                    </div>
                </div>
            </div>

            <!-- Modern Footer -->
            <footer class="modern-footer">
                <p>&copy; 2024 <a href="#">GraceFull Wedding</a>. Made with ❤️ for beautiful weddings.</p>
            </footer>
        </div>
    </div>

    <!-- Essential Scripts -->
    <script src="./assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="./assets/vendors/chart.js/chart.umd.js"></script>
    <script src="./assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="./assets/js/off-canvas.js"></script>
    <script src="./assets/js/misc.js"></script>
    <script src="./assets/js/settings.js"></script>
    <script src="./assets/js/todolist.js"></script>
    <script src="./assets/js/jquery.cookie.js"></script>
    <script src="./assets/js/dashboard.js"></script>

    <!-- Modern Layout Scripts -->
    <script src="./assets/js/main.anim.js">

    </script>
</body>

</html>