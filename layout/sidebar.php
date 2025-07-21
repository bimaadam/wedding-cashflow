<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/koneksi.php';

$user_id = $_SESSION['user_id'] ?? null;
$username = 'Pengguna';

if ($user_id) {
  $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  if ($user) {
    $username = $user['username'];
  }

  $stmt->close();
}
?>

<head>
  <link rel="stylesheet" href="./assets/css/sidebar.style.css"
    </head>

  <nav class="modern-sidebar" id="sidebar">
    <div class="sidebar-content">
      <!-- Brand Section -->
      <div class="brand-section">
        <h2 class="brand-title">💰 GracefulAdmin</h2>
        <p class="brand-subtitle">kelola keuangan</p>
      </div>

      <!-- Profile Section -->
      <div class="profile-section">
        <a href="#" class="profile-card">
          <div style="position: relative;">
            <img src="./assets/images/faces-clipart/pic-1.png" alt="profile" class="profile-avatar" />
            <div class="online-indicator"></div>
          </div>
          <div class="profile-info">
            <h4><?= htmlspecialchars($username) ?></h4>
            <p>🟢 Online</p>
          </div>
        </a>
      </div>

      <!-- Navigation Menu -->
      <ul class="nav-menu">
        <li class="nav-item">
          <a class="nav-link active" href="dashboard.php">
            <div class="menu-icon">
              <i class="mdi mdi-view-dashboard"></i>
            </div>
            <span class="menu-title">Dashboard</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="pemasukan.php">
            <div class="menu-icon">
              <i class="mdi mdi-trending-up"></i>
            </div>
            <span class="menu-title">Pemasukan</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="pengeluaran.php">
            <div class="menu-icon">
              <i class="mdi mdi-trending-down"></i>
            </div>
            <span class="menu-title">Pengeluaran</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="event.php">
            <div class="menu-icon">
              <i class="mdi mdi-calendar-star"></i>
            </div>
            <span class="menu-title">Event</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="laporan.php">
            <div class="menu-icon">
              <i class="mdi mdi-chart-box"></i>
            </div>
            <span class="menu-title">Laporan</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="cashflow.php">
            <div class="menu-icon">
              <i class="mdi mdi-cash-flow"></i>
            </div>
            <span class="menu-title">Cashflow</span>
          </a>
        </li>
      </ul>
    </div>
  </nav>