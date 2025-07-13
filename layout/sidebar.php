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

<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <li class="nav-item nav-profile">
      <a href="#" class="nav-link">
        <div class="nav-profile-image">
          <img src="../assets/images/faces/face1.jpg" alt="profile" />
          <span class="login-status online"></span>
        </div>
        <div class="nav-profile-text d-flex flex-column">
          <span class="font-weight-bold mb-2"><?= htmlspecialchars($username) ?></span>
          <span class="text-secondary text-small">User Login</span>
        </div>
        <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
      </a>
    </li>

    <!-- menu lainnya -->
    <li class="nav-item">
      <a class="nav-link" href="dashboard.php">
        <span class="menu-title">Dashboard</span>
        <i class="mdi mdi-home menu-icon"></i>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="pemasukan.php">
        <span class="menu-title">Pemasukan</span>
        <i class="mdi mdi-cash-plus menu-icon"></i>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="pengeluaran.php">
        <span class="menu-title">Pengeluaran</span>
        <i class="mdi mdi-cash-minus menu-icon"></i>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="event.php">
        <span class="menu-title">Event</span>
        <i class="mdi mdi-calendar-multiple menu-icon"></i>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="laporan.php">
        <span class="menu-title">Laporan</span>
        <i class="mdi mdi-file-document-box menu-icon"></i>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="cashflow.php">
        <span class="menu-title">Cashflow</span>
        <i class="mdi mdi-chart-line menu-icon"></i>
      </a>
    </li>
  </ul>
</nav>