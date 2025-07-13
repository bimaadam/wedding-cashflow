<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ./auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title><?= $title ?? 'GraceFull Wedding' ?></title>

    <link rel="stylesheet" href="./assets/vendors/mdi/css/materialdesignicons.min.css" />
    <link rel="stylesheet" href="./assets/vendors/ti-icons/css/themify-icons.css" />
    <link rel="stylesheet" href="./assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="./assets/vendors/css/vendor.bundle.base.css" />
    <link rel="stylesheet" href="./assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css" />
    <link rel="stylesheet" href="./assets/css/style.css" />
    <link rel="shortcut icon" href="./assets/images/favicon.png" />
</head>

<body>
    <div class="container-scroller">
        <?php include 'navbar.php'; ?>

        <div class="container-fluid page-body-wrapper">
            <?php include 'sidebar.php'; ?>

            <div class="main-panel">
                <div class="content-wrapper">
                    <?= $content ?? '<p style="color:red">Konten kosong njir</p>' ?>
                </div>

                <?php include 'footer.php'; ?>
            </div>
        </div>
    </div>

    <script src="./assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="./assets/vendors/chart.js/chart.umd.js"></script>
    <script src="./assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="./assets/js/off-canvas.js"></script>
    <script src="./assets/js/misc.js"></script>
    <script src="./assets/js/settings.js"></script>
    <script src="./assets/js/todolist.js"></script>
    <script src="./assets/js/jquery.cookie.js"></script>
    <script src="./assets/js/dashboard.js"></script>
</body>

</html>