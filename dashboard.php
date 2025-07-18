<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: auth/login.php');
  exit();
}

$pageTitle = 'Dashboard';
ob_start();
?>

<div class="page-header">
  <nav aria-label="breadcrumb">
    <ul class="breadcrumb">
      <!-- <li class="breadcrumb-item active" aria-current="page">
        Overview
        <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle"></i>
      </li> -->
    </ul>
  </nav>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Selamat Datang di Dashboard</h4>
        <p class="card-description">
          Ini adalah menu Dashboard - Pusat kontrol sistem keuangan
        </p>
        <div class="alert alert-info" role="alert">
          <i class="mdi mdi-information-outline me-2"></i>
          <strong>Informasi:</strong> Masih dalam tahap pengembangan
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
include 'layout/main.php';
?>